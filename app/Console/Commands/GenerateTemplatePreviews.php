<?php

namespace App\Console\Commands;

use App\Services\PptxReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * php artisan templates:previews
 * Membuat gambar pratinjau dari file PPTX yang SAMA dengan yang dipakai untuk PDF,
 * sehingga pratinjau selalu identik dengan hasil akhir.
 */
class GenerateTemplatePreviews extends Command
{
    protected $signature = 'templates:previews';
    protected $description = 'Buat gambar pratinjau tiap template PPTX';

    public function handle(PptxReportService $svc): int
    {
        $out = config('report_templates.preview_path');
        File::ensureDirectoryExists($out);
        $tmp = storage_path('app/tmp');
        File::ensureDirectoryExists($tmp);

        $sample = [
            'hari' => 'Rabu', 'tanggal' => '21 Oktober 2026', 'tim' => 'Tim Pengawasan',
            'foto_petugas' => null,
            'lokasi' => array_map(fn ($n) => [
                'nama' => $n, 'status' => 'normal', 'kondisi' => 'Bersih',
                'catatan' => 'Contoh catatan pemantauan.', 'masalah' => '-', 'solusi' => '-', 'foto' => [],
            ], ['Halaman Depan Kantor', 'Ruang Pelayanan', 'Ruang Kerja', 'Toilet', 'Area Parkir']),
        ];

        foreach (array_keys(config('report_templates.templates')) as $key) {
            $pptx = "$tmp/preview_$key.pptx";
            $svc->buildPptx($sample, $key, $pptx);
            $svc->convert($pptx, $tmp, 'png');           // png = slide pertama (sampul)
            File::move("$tmp/preview_$key.png", "$out/$key.png");
            File::delete($pptx);
            $this->info("OK: $key");
        }
        return self::SUCCESS;
    }
}