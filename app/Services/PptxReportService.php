<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use FilesystemIterator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Mengisi template .pptx (gaya AutoCrat: <<Token>>, <<Image:...>>, [[normalN]]/[[tidakN]])
 * dengan data laporan, lalu mengubahnya menjadi PDF lewat LibreOffice.
 *
 * Bentuk $data:
 * [
 *   'hari' => 'Rabu', 'tanggal' => '21 Oktober 2026', 'tim' => 'Budi, Sari',
 *   'foto_petugas' => '/path/foto.jpg',
 *   'lokasi' => [ // tepat 5 item, urut
 *     ['nama'=>'Toilet', 'status'=>'normal'|'tidak_normal', 'kondisi'=>'Bersih',
 *      'catatan'=>'...', 'masalah'=>'...', 'solusi'=>'...',
 *      'foto'=>['/path/dok1.jpg', '/path/dok2.jpg']],
 *     ...
 *   ],
 * ]
 */
class PptxReportService
{
    private const A  = 'http://schemas.openxmlformats.org/drawingml/2006/main';
    private const P  = 'http://schemas.openxmlformats.org/presentationml/2006/main';
    private const R  = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const PR = 'http://schemas.openxmlformats.org/package/2006/relationships';
    private const CT = 'http://schemas.openxmlformats.org/package/2006/content-types';
    private const PHOTO_FIT = 'cover';

    private int $imgCounter = 0;

    /** Buat PPTX terisi, kembalikan path-nya. */
    public function buildPptx(array $data, string $templateKey, string $outPptx): string
    {
        $src = $this->resolveSource($templateKey);

        $work = storage_path('app/tmp/pptx_' . uniqid());
        File::ensureDirectoryExists($work);

        try {
            $zip = new ZipArchive();
            if ($zip->open($src) !== true) {
                throw new RuntimeException('Gagal membuka template PPTX.');
            }
            $zip->extractTo($work);
            $zip->close();

            $this->process($work, $data);
            $this->zipDir($work, $outPptx);
        } finally {
            File::deleteDirectory($work);
        }

        return $outPptx;
    }

    /**
     * Cari file .pptx untuk sebuah template_key.
     * Urutan: (1) config/report_templates.php (template bawaan 01-10),
     *         (2) tabel templates bertipe "uploaded" (template buatan pengguna).
     */
    public function resolveSource(string $templateKey): string
    {
        $base = rtrim(config('report_templates.path'), '/\\');

        $tpl = config("report_templates.templates.$templateKey");
        if ($tpl) {
            $src = $base . DIRECTORY_SEPARATOR . $tpl['file'];
        } else {
            $row = \App\Models\Template::where('template_key', $templateKey)
                ->where('type', 'uploaded')
                ->first();
            if (!$row || !$row->pptx_path) {
                throw new RuntimeException("Template '$templateKey' tidak terdaftar.");
            }
            $src = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $row->pptx_path);
        }

        if (!is_file($src)) {
            throw new RuntimeException("File template tidak ditemukan: $src");
        }
        return $src;
    }

    /**
     * Periksa sebuah file .pptx memakai aturan yang SAMA dengan process()/classify().
     * Tidak mengubah file. Kembalikan ['errors'=>[], 'warnings'=>[], 'info'=>[]].
     */
    public function inspectTemplate(string $file): array
    {
        $errors = []; $warnings = []; $info = [];
        $result = fn () => ['errors' => $errors, 'warnings' => $warnings, 'info' => $info];

        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            $errors[] = 'File bukan PPTX yang valid (tidak bisa dibuka sebagai arsip).';
            return $result();
        }

        // Keamanan: batasi jumlah entri, ukuran hasil ekstrak, dan nama path berbahaya.
        $total = 0;
        if ($zip->numFiles > 2000) {
            $errors[] = 'Struktur file tidak wajar (terlalu banyak berkas di dalam PPTX).';
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $st = $zip->statIndex($i);
            $name = (string) ($st['name'] ?? '');
            $total += (int) ($st['size'] ?? 0);
            if (str_contains($name, '..') || str_starts_with($name, '/') || str_contains($name, '\\')) {
                $errors[] = 'File berisi path yang tidak aman.';
                break;
            }
            if (stripos($name, 'vbaProject') !== false) {
                $errors[] = 'File berisi makro (vbaProject). Simpan ulang sebagai .pptx tanpa makro.';
                break;
            }
        }
        if ($total > 300 * 1024 * 1024) {
            $errors[] = 'Ukuran isi PPTX setelah diekstrak terlalu besar (maks 300 MB).';
        }
        if ($errors) { $zip->close(); return $result(); }

        $presXml = $zip->getFromName('ppt/presentation.xml');
        $relsXml = $zip->getFromName('ppt/_rels/presentation.xml.rels');
        if ($presXml === false || $relsXml === false || $zip->getFromName('[Content_Types].xml') === false) {
            $zip->close();
            $errors[] = 'Struktur PPTX tidak lengkap (presentation.xml / rels / [Content_Types].xml tidak ada).';
            return $result();
        }

        $pres = new DOMDocument(); $rels = new DOMDocument();
        if (!@$pres->loadXML($presXml) || !@$rels->loadXML($relsXml)) {
            $zip->close();
            $errors[] = 'XML di dalam PPTX rusak.';
            return $result();
        }
        $px = $this->xp($pres);

        // Ukuran slide harus 16:9
        $sz = $px->query('//p:sldSz')->item(0);
        $cx = $sz ? (int) $sz->getAttribute('cx') : 0;
        $cy = $sz ? (int) $sz->getAttribute('cy') : 0;
        if ($cx <= 0 || $cy <= 0 || abs($cx / $cy - 16 / 9) > 0.02) {
            $errors[] = 'Ukuran slide harus Widescreen 16:9 (Design > Slide Size > Widescreen).';
        }

        $rmap = [];
        foreach ($rels->getElementsByTagName('Relationship') as $r) {
            $rmap[$r->getAttribute('Id')] = $r->getAttribute('Target');
        }

        $slideIds = $px->query('//p:sldIdLst/p:sldId');
        $info['jumlah_slide'] = $slideIds->length;
        if ($slideIds->length < 2) {
            $zip->close();
            $errors[] = 'Template minimal berisi 1 slide sampul dan slide lokasi.';
            return $result();
        }

        $coverText = '';
        $normal = []; $tidak = [];   // [nomorLokasi => true]
        $locKinds = [];              // [nomorLokasi => [jenis => true]]
        $unknown = [];

        foreach ($slideIds as $i => $sldId) {
            $target = $rmap[$sldId->getAttributeNS(self::R, 'id')] ?? null;
            $xml = $target ? $zip->getFromName('ppt/' . $target) : false;
            $slide = new DOMDocument();
            if ($xml === false || !@$slide->loadXML($xml)) {
                $errors[] = 'Slide ' . ($i + 1) . ' tidak bisa dibaca.';
                continue;
            }
            $sx = $this->xp($slide);

            // Gabungkan teks slide persis seperti process()
            $all = '';
            foreach ($sx->query('//a:t') as $t) $all .= $t->textContent;

            // Placeholder gambar wajib punya ukuran eksplisit (a:xfrm/a:ext), kalau tidak generator error.
            foreach ($sx->query('//p:sp') as $sp) {
                $txBody = $sx->query('p:txBody', $sp)->item(0);
                if (!$txBody) continue;
                $full = '';
                foreach ($sx->query('.//a:t', $txBody) as $t) $full .= $t->textContent;
                if (preg_match('/^<<(Image:.+?)>>$/', trim($full))) {
                    $ext = $sx->query('p:spPr/a:xfrm/a:ext', $sp)->item(0);
                    if (!$ext || (int) $ext->getAttribute('cx') <= 0) {
                        $errors[] = 'Slide ' . ($i + 1) . ': placeholder "' . trim($full) . '" harus berupa bentuk (shape) dengan ukuran sendiri, bukan placeholder bawaan layout.';
                    }
                }
            }

            if ($i === 0) { $coverText = $all; }

            preg_match_all('/<<(.+?)>>/', $all, $m);
            foreach ($m[1] as $tok) {
                [$kind, $n] = $this->classify($tok);
                if (!$kind) { $unknown[$tok] = true; continue; }
                if ($i > 0 && $n) $locKinds[$n][$kind] = true;
            }
            if ($i === 0) continue;

            $nums = [];
            foreach ($m[1] as $tok) { [, $n] = $this->classify($tok); if ($n) $nums[] = $n; }
            if (!$nums) {
                $warnings[] = 'Slide ' . ($i + 1) . ' tidak punya penanda lokasi, akan dibuang saat laporan dibuat.';
                continue;
            }
            $counts = array_count_values($nums); arsort($counts);
            $loc = (int) array_key_first($counts);
            if (preg_match('/\[\[tidak\d+\]\]/', $all)) $tidak[$loc] = true; else $normal[$loc] = true;
        }
        $zip->close();

        if ($unknown) {
            $errors[] = 'Penanda tidak dikenal: ' . implode(', ', array_map(fn ($t) => "<<$t>>", array_keys($unknown))) . '.';
        }

        foreach (['hari' => '<<Hari>>', 'tanggal' => '<<Tanggal>>', 'tim' => '<<Tim Pengawasan>>'] as $kind => $label) {
            // nilai: ada di sampul?
            if (!preg_match($kind === 'tim' ? '/<<[^>]*tim pengawasan[^>]*>>/i' : '/<<' . $kind . '>>/i', $coverText)) {
                $warnings[] = "Sampul belum memuat penanda $label.";
            }
        }

        for ($n = 1; $n <= 5; $n++) {
            if (empty($normal[$n])) {
                $errors[] = "Lokasi $n belum punya slide kondisi normal (slide berisi penanda lokasi $n tanpa [[tidak$n]]).";
            }
            if (empty($tidak[$n])) {
                $warnings[] = "Lokasi $n belum punya slide kondisi tidak normal (berisi [[tidak$n]]). Jika kondisi lokasi ini \"perlu penanganan\", halaman lokasi $n akan hilang dari PDF.";
            }
            foreach (['nama' => '<<Nama Tempat ' . $n . '>>', 'kondisi' => '<<Kondisi Kebersihan ' . $n . '>>'] as $kind => $label) {
                if (!empty($normal[$n]) && empty($locKinds[$n][$kind])) {
                    $warnings[] = "Lokasi $n belum memuat penanda $label.";
                }
            }
        }
        for ($n = 6; $n <= 20; $n++) {
            if (!empty($normal[$n]) || !empty($tidak[$n])) {
                $warnings[] = "Ada slide untuk lokasi $n. Laporan hanya memakai lokasi 1-5, slide ini akan dibuang.";
                break;
            }
        }

        return $result();
    }

    /** PPTX -> PDF. Kembalikan path PDF. */
    public function generatePdf(array $data, string $templateKey, string $outPdf): string
    {
        $tmpDir = storage_path('app/tmp');
        File::ensureDirectoryExists($tmpDir);
        $base = 'laporan_' . uniqid();
        $pptx = "$tmpDir/$base.pptx";

        $this->buildPptx($data, $templateKey, $pptx);
        $this->convert($pptx, $tmpDir, 'pdf');

        $pdf = "$tmpDir/$base.pdf";
        if (!is_file($pdf)) {
            throw new RuntimeException('LibreOffice gagal membuat PDF.');
        }
        File::ensureDirectoryExists(dirname($outPdf));
        File::delete($outPdf);
        File::move($pdf, $outPdf);
        File::delete($pptx);

        return $outPdf;
    }

    /** Jalankan soffice headless. $format: 'pdf' atau 'png' (png = slide pertama saja). */
    public function convert(string $file, string $outDir, string $format = 'pdf'): void
    {
        // Windows: seragamkan pemisah path (campuran \ dan / memicu error di cmd)
        $norm = fn (string $p) => DIRECTORY_SEPARATOR === '\\' ? str_replace('/', '\\', $p) : $p;

        $soffice = $norm((string) config('report_templates.soffice'));
        if ($soffice !== 'soffice' && !is_file($soffice)) {
            throw new RuntimeException("soffice tidak ditemukan di: $soffice (periksa SOFFICE_PATH di .env)");
        }

        $profile = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'lo_' . uniqid();
        File::ensureDirectoryExists($profile);
        $profileUrl = 'file:///' . str_replace(' ', '%20', ltrim(str_replace('\\', '/', $profile), '/'));

        $p = new Process([
            $soffice,
            '--headless', '--norestore', '--nolockcheck',
            "-env:UserInstallation=$profileUrl", // profil unik => aman dijalankan paralel
            '--convert-to', $format,
            '--outdir', $norm($outDir),
            $norm($file),
        ]);
        $p->setTimeout(180);
        $p->setEnv(array_filter([
            'SystemRoot'   => getenv('SystemRoot') ?: 'C:\\Windows',
            'USERPROFILE'  => getenv('USERPROFILE') ?: 'C:\\Users\\ACER',
            'APPDATA'      => getenv('APPDATA') ?: 'C:\\Users\\ACER\\AppData\\Roaming',
            'LOCALAPPDATA' => getenv('LOCALAPPDATA') ?: 'C:\\Users\\ACER\\AppData\\Local',
            'TEMP'         => sys_get_temp_dir(),
            'TMP'          => sys_get_temp_dir(),
        ]));
        $p->run();
        $p->run();
        File::deleteDirectory($profile);

        if (!$p->isSuccessful()) {
            $detail = 'exit=' . $p->getExitCode()
                . ' | cmd=' . $p->getCommandLine()
                . ' | err=' . trim($p->getErrorOutput())
                . ' | out=' . trim($p->getOutput());
            Log::error('soffice gagal: ' . $detail);
            @copy($file, storage_path('app/tmp/gagal_terakhir.pptx'));
            throw new RuntimeException('soffice error: ' . $detail);
        }
    }

    // ------------------------------------------------------------------ inti

    private function process(string $work, array $data): void
    {
        $presPath = "$work/ppt/presentation.xml";
        $relsPath = "$work/ppt/_rels/presentation.xml.rels";
        $ctPath   = "$work/[Content_Types].xml";

        $pres = $this->load($presPath);
        $rels = $this->load($relsPath);
        $ct   = $this->load($ctPath);

        $px = $this->xp($pres);
        $rmap = [];
        foreach ($rels->getElementsByTagName('Relationship') as $r) {
            $rmap[$r->getAttribute('Id')] = $r->getAttribute('Target');
        }

        $this->ensureJpegDefault($ct);

        $drop = [];
        foreach ($px->query('//p:sldIdLst/p:sldId') as $i => $sldId) {
            $path = $rmap[$sldId->getAttributeNS(self::R, 'id')];
            $slideFile = "$work/ppt/$path";
            $slide = $this->load($slideFile);
            $sx = $this->xp($slide);

            // Slide 1 = sampul
            if ($i === 0) {
                $this->fillSlide($slide, $sx, $data, null, $work, $path);
                $slide->save($slideFile);
                continue;
            }

            $all = '';
            foreach ($sx->query('//a:t') as $t) {
                $all .= $t->textContent;
            }
            $isTidak = (bool) preg_match('/\[\[tidak\d+\]\]/', $all);

            // nomor lokasi = angka yang paling sering muncul di token <<...>>
            $nums = [];
            preg_match_all('/<<(.+?)>>/', $all, $m);
            foreach ($m[1] as $tok) {
                [, $n] = $this->classify($tok);
                if ($n) $nums[] = $n;
            }
            if (!$nums) { $drop[] = [$sldId, $path]; continue; }
            $counts = array_count_values($nums);
            arsort($counts);
            $loc = (int) array_key_first($counts);

            // lokasi yang tidak ada di data => halaman dibuang (tidak jadi halaman kosong)
            if (!isset($data['lokasi'][$loc - 1])) { $drop[] = [$sldId, $path]; continue; }
            $status = $data['lokasi'][$loc - 1]['status'] ?? 'normal';
            $wantTidak = ($status === 'tidak_normal');

            if ($isTidak === $wantTidak) {
                $this->fillSlide($slide, $sx, $data, $loc, $work, $path);
                $slide->save($slideFile);
            } else {
                $drop[] = [$sldId, $path];
            }
        }

        // hapus slide yang tidak dipakai
        $ctx = $this->xp($ct, true);
        foreach ($drop as [$sldId, $path]) {
            $sldId->parentNode->removeChild($sldId);
            foreach (iterator_to_array($rels->getElementsByTagName('Relationship')) as $r) {
                if ($r->getAttribute('Target') === $path) {
                    $r->parentNode->removeChild($r);
                }
            }
            $name = basename($path);
            $slideRels = "$work/ppt/slides/_rels/$name.rels";
            if (is_file($slideRels)) {
                $sr = $this->load($slideRels);
                foreach ($sr->getElementsByTagName('Relationship') as $r) {
                    if (str_ends_with($r->getAttribute('Type'), '/notesSlide')) {
                        $nf = realpath("$work/ppt/slides/" . $r->getAttribute('Target'));
                        if ($nf) {
                            foreach ($ctx->query("//c:Override[@PartName='/ppt/notesSlides/" . basename($nf) . "']") as $o) {
                                $o->parentNode->removeChild($o);
                            }
                            @unlink(dirname($nf) . '/_rels/' . basename($nf) . '.rels');
                            @unlink($nf);
                        }
                    }
                }
                @unlink($slideRels);
            }
            @unlink("$work/ppt/$path");
            foreach ($ctx->query("//c:Override[@PartName='/ppt/$path']") as $o) {
                $o->parentNode->removeChild($o);
            }
        }

        $pres->save($presPath);
        $rels->save($relsPath);
        $ct->save($ctPath);
    }

    private function fillSlide(DOMDocument $slide, DOMXPath $sx, array $data, ?int $loc, string $work, string $path): void
    {
        $relsFile = "$work/ppt/slides/_rels/" . basename($path) . '.rels';
        $rels = $this->load($relsFile);

        foreach (iterator_to_array($sx->query('//p:sp')) as $sp) {
            $txBody = $sx->query('p:txBody', $sp)->item(0);
            if (!$txBody) continue;

            $full = '';
            foreach ($sx->query('.//a:t', $txBody) as $t) $full .= $t->textContent;
            $full = trim($full);

            // placeholder foto
            if (preg_match('/^<<(Image:.+?)>>$/', $full, $m)) {
                [$kind, $n, $d] = $this->classify($m[1]);
                $img = $kind === 'foto_petugas'
                    ? ($data['foto_petugas'] ?? null)
                    : ($data['lokasi'][$n - 1]['foto'][$d - 1] ?? null);
                $this->putImage($slide, $sx, $sp, $txBody, $img, $rels, $work, $kind === 'foto_petugas' ? 0.2 : 0.5);
                continue;
            }

            // teks: proses per paragraf (token bisa terpecah di beberapa run)
            foreach ($sx->query('a:p', $txBody) as $p) {
                $runs = $sx->query('a:r', $p);
                if ($runs->length === 0) continue;
                $txt = '';
                foreach ($runs as $r) $txt .= $sx->query('a:t', $r)->item(0)?->textContent;
                $hasToken = preg_match('/<<.+?>>|\[\[.+?\]\]/', $txt);
                // Ganti nomor contoh yang mungkin sudah ditulis langsung pada template PPTX lama.
                $new = preg_replace('/LAP-BPSDM-\d{4}-\d+/i', (string) ($data['report_number'] ?? ''), $txt);
                if (!$hasToken && $new === $txt) continue;

                if ($hasToken) {
                    $new = preg_replace_callback('/<<(.+?)>>|\[\[(.+?)\]\]/', function ($mm) use ($data) {
                        if (!empty($mm[2])) return ''; // penanda [[normal1]] dihapus
                        [$kind, $n] = $this->classify($mm[1]);
                        if (!$kind) {
                            Log::warning("Token template tidak dikenal: {$mm[1]}");
                            return '';
                        }
                        return $this->value($kind, $n, $data);
                    }, $new);
                }

                $first = true;
                foreach (iterator_to_array($runs) as $r) {
                    if ($first) {
                        $sx->query('a:t', $r)->item(0)->nodeValue = '';
                        $sx->query('a:t', $r)->item(0)->appendChild($slide->createTextNode($new));
                        $first = false;
                    } else {
                        $p->removeChild($r);
                    }
                }
            }
        }

        $rels->save($relsFile);
    }

        private function putImage(DOMDocument $slide, DOMXPath $sx, DOMElement $sp, DOMElement $txBody, ?string $img, DOMDocument $rels, string $work, float $focusY = 0.5): void
    {
        $spPr = $sx->query('p:spPr', $sp)->item(0);
        $ext  = $sx->query('a:xfrm/a:ext', $spPr)->item(0);
        $bw = (int) $ext->getAttribute('cx');
        $bh = (int) $ext->getAttribute('cy');

        $sp->removeChild($txBody); // hapus teks "<<Image:...>>"
        if (!$img || !is_file($img) || $bw <= 0 || $bh <= 0) return; // tanpa foto: biarkan bingkai kosong

        $name = 'fill_' . (++$this->imgCounter) . '_' . uniqid() . '.jpg';
        File::ensureDirectoryExists("$work/ppt/media");
        // foto sudah dipotong/diratakan ke rasio bingkai, jadi tidak perlu srcRect
        $this->prepareImage($img, "$work/ppt/media/$name", $bw / $bh, $focusY);

        $rid = 'rIdImg' . $this->imgCounter . substr(uniqid(), -4);
        $rel = $rels->createElementNS(self::PR, 'Relationship');
        $rel->setAttribute('Id', $rid);
        $rel->setAttribute('Type', self::R . '/image');
        $rel->setAttribute('Target', "../media/$name");
        $rels->documentElement->appendChild($rel);

        foreach (['solidFill', 'noFill', 'gradFill', 'blipFill'] as $tag) {
            foreach (iterator_to_array($sx->query("a:$tag", $spPr)) as $old) $spPr->removeChild($old);
        }

        $frag = $slide->createDocumentFragment();
        $frag->appendXML(
            '<a:blipFill xmlns:a="' . self::A . '" xmlns:r="' . self::R . '" rotWithShape="1">'
            . '<a:blip r:embed="' . $rid . '"/>'
            . '<a:stretch><a:fillRect/></a:stretch></a:blipFill>'
        );

        $anchor = $sx->query('a:prstGeom|a:custGeom', $spPr)->item(0) ?? $sx->query('a:xfrm', $spPr)->item(0);
        $anchor->parentNode->insertBefore($frag, $anchor->nextSibling);
    }

    // --------------------------------------------------------------- helper

    /**
     * Mengenali token walaupun penamaannya tidak konsisten
     * (mis. "Catatan Terhadap Pemantauan Lokasi 1" vs "Catatan Pemantauan Lokasi 4").
     * @return array [jenis, nomorLokasi|null, nomorFoto|null]
     */
    private function classify(string $tok): array
    {
        $t = strtolower($tok);
        preg_match('/(\d+)/', $t, $m);
        $n = isset($m[1]) ? (int) $m[1] : null;

        if (str_starts_with($t, 'image:')) {
            if (str_contains($t, 'foto petugas')) return ['foto_petugas', null, null];
            preg_match('/dok(\d)/', $t, $d);
            return ['foto', $n, (int) ($d[1] ?? 1)];
        }
        return match (true) {
            $t === 'hari'                           => ['hari', null, null],
            $t === 'tanggal'                        => ['tanggal', null, null],
            str_contains($t, 'nomor laporan') || str_contains($t, 'nomor registrasi') => ['report_number', null, null],
            str_contains($t, 'tim pengawasan')      => ['tim', null, null],
            str_contains($t, 'nama tempat')         => ['nama', $n, null],
            str_contains($t, 'kondisi kebersihan')  => ['kondisi', $n, null],
            str_contains($t, 'catatan')             => ['catatan', $n, null],
            str_contains($t, 'permasalahan')        => ['masalah', $n, null],
            str_contains($t, 'solusi')              => ['solusi', $n, null],
            default                                 => [null, null, null],
        };
    }

    private function value(string $kind, ?int $n, array $data): string
    {
        $v = match ($kind) {
            'hari'    => $data['hari'] ?? '',
            'tanggal' => $data['tanggal'] ?? '',
            'tim'     => $data['tim'] ?? '',
            'report_number' => $data['report_number'] ?? '',
            default   => $data['lokasi'][($n ?? 1) - 1][$kind] ?? '',
        };
        $v = trim((string) $v);
        return $v === '' ? '-' : $v;
    }

    /** Putar sesuai EXIF, kecilkan (maks 1600px), simpan JPEG. Kembalikan [lebar, tinggi]. */
    /** Putar sesuai EXIF, kecilkan (maks 1600px), sesuaikan ke rasio bingkai, simpan JPEG. */
    private function prepareImage(string $src, string $dest, ?float $ratio = null, float $focusY = 0.5): array
    {
        $info = getimagesize($src);
        if (!$info) throw new RuntimeException("Bukan gambar valid: $src");
        $im = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            default        => throw new RuntimeException('Format gambar tidak didukung.'),
        };
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
            $rot = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
            if ($rot) $im = imagerotate($im, $rot, 0);
        }
        $w = imagesx($im); $h = imagesy($im);
        $max = 1600;
        if (max($w, $h) > $max) {
            $s = $max / max($w, $h);
            $im = imagescale($im, (int) round($w * $s), (int) round($h * $s));
            $w = imagesx($im); $h = imagesy($im);
        }

        if ($ratio) {
            $ia = $w / $h;
            if (self::PHOTO_FIT === 'contain') {
                if ($ia > $ratio) { $cw = $w; $ch = (int) round($w / $ratio); }
                else              { $ch = $h; $cw = (int) round($h * $ratio); }
                $canvas = imagecreatetruecolor($cw, $ch);
                imagefill($canvas, 0, 0, imagecolorallocate($canvas, 245, 243, 236));
                imagecopy($canvas, $im, (int) (($cw - $w) / 2), (int) (($ch - $h) / 2), 0, 0, $w, $h);
                imagedestroy($im);
                $im = $canvas; $w = $cw; $h = $ch;
            } else {
                if ($ia > $ratio) { $nw = (int) round($h * $ratio); $nh = $h; $x = (int) round(($w - $nw) / 2); $y = 0; }
                else              { $nw = $w; $nh = (int) round($w / $ratio); $x = 0; $y = (int) round(($h - $nh) * $focusY); }
                $crop = imagecrop($im, ['x' => $x, 'y' => $y, 'width' => max(1, $nw), 'height' => max(1, $nh)]);
                if ($crop !== false) {
                    imagedestroy($im);
                    $im = $crop; $w = imagesx($im); $h = imagesy($im);
                }
            }
        }

        imagejpeg($im, $dest, 82);
        imagedestroy($im);
        return [$w, $h];
    }

    private function ensureJpegDefault(DOMDocument $ct): void
    {
        foreach ($ct->getElementsByTagName('Default') as $d) {
            if (strtolower($d->getAttribute('Extension')) === 'jpg') return;
        }
        $d = $ct->createElementNS(self::CT, 'Default');
        $d->setAttribute('Extension', 'jpg');
        $d->setAttribute('ContentType', 'image/jpeg');
        $ct->documentElement->insertBefore($d, $ct->documentElement->firstChild);
    }

    private function load(string $file): DOMDocument
    {
        $d = new DOMDocument();
        $d->preserveWhiteSpace = true;
        $d->load($file);
        return $d;
    }

    private function xp(DOMDocument $d, bool $ct = false): DOMXPath
    {
        $x = new DOMXPath($d);
        $x->registerNamespace('a', self::A);
        $x->registerNamespace('p', self::P);
        $x->registerNamespace('r', self::R);
        $x->registerNamespace('c', self::CT);
        return $x;
    }

    private function zipDir(string $dir, string $out): void
    {
        File::ensureDirectoryExists(dirname($out));
        @unlink($out);
        $zip = new ZipArchive();
        $zip->open($out, ZipArchive::CREATE);
        $zip->addFile("$dir/[Content_Types].xml", '[Content_Types].xml'); // harus pertama
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($dir) + 1));
            if ($rel !== '[Content_Types].xml') $zip->addFile($f->getPathname(), $rel);
        }
        $zip->close();
    }
}