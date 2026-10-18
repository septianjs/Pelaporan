<?php

namespace App\Services;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\DocumentLayout;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Fill;
use PhpOffice\PhpPresentation\Style\Border;
use PhpOffice\PhpPresentation\Shape\RichText;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BuiltinTemplateGeneratorService
{
    /**
     * Definition of 10 distinct built-in templates with unique visual styles
     */
    public static function getTemplateDefinitions(): array
    {
        return [
            [
                'key' => 'corporate-blue',
                'name' => 'Corporate Blue',
                'description' => 'Template formal bernuansa biru korporat dengan layout simetris dan rapi.',
                'primary_color' => '1E3A8A',    // Deep Navy
                'accent_color' => '0284C7',     // Sky Blue
                'bg_color' => 'F8FAFC',         // Slate 50
                'card_bg' => 'FFFFFF',
                'style_type' => 'card_modern',
            ],
            [
                'key' => 'government-formal',
                'name' => 'Government Formal',
                'description' => 'Template institusi kedinasan formal dengan perpaduan warna hijau botol dan emas.',
                'primary_color' => '065F46',    // Emerald / Pine
                'accent_color' => 'D97706',     // Amber / Gold
                'bg_color' => 'FAFBF9',
                'card_bg' => 'FFFFFF',
                'style_type' => 'institutional_header',
            ],
            [
                'key' => 'modern-clean',
                'name' => 'Modern Clean',
                'description' => 'Desain kontemporer berorientasi minimalis dengan aksen teal yang segar.',
                'primary_color' => '0F172A',    // Slate 900
                'accent_color' => '0D9488',     // Teal 600
                'bg_color' => 'F1F5F9',
                'card_bg' => 'FFFFFF',
                'style_type' => 'split_hero',
            ],
            [
                'key' => 'minimal-professional',
                'name' => 'Minimal Professional',
                'description' => 'Layout hitam-putih presisi dengan aksen merah bata untuk kesan tegas dan profesional.',
                'primary_color' => '18181B',    // Zinc 900
                'accent_color' => 'E11D48',     // Rose 600
                'bg_color' => 'FFFFFF',
                'card_bg' => 'F4F4F5',
                'style_type' => 'minimal_grid',
            ],
            [
                'key' => 'executive-report',
                'name' => 'Executive Report',
                'description' => 'Tampilan mewah eksekutif dengan nuansa indigo gelap dan sentuhan warna tembaga.',
                'primary_color' => '1E1B4B',    // Indigo 950
                'accent_color' => 'B45309',     // Amber 700
                'bg_color' => 'F8FAFC',
                'card_bg' => 'FFFFFF',
                'style_type' => 'executive_banner',
            ],
            [
                'key' => 'photo-focus',
                'name' => 'Photo Focus',
                'description' => 'Fokus utama pada dokumentasi visual foto berukuran besar dengan panel informasi samping.',
                'primary_color' => '27272A',    // Zinc 800
                'accent_color' => 'F59E0B',     // Amber 500
                'bg_color' => 'FAFAFA',
                'card_bg' => 'FFFFFF',
                'style_type' => 'photo_dominant',
            ],
            [
                'key' => 'elegant-report',
                'name' => 'Elegant Report',
                'description' => 'Kombinasi elegan biru malam dengan gradasi ungu lembut dan kartu informasi dinamis.',
                'primary_color' => '312E81',    // Indigo 900
                'accent_color' => '7C3AED',     // Violet 600
                'bg_color' => 'FDF4FF',
                'card_bg' => 'FFFFFF',
                'style_type' => 'elegant_cards',
            ],
            [
                'key' => 'bpsdm-classic',
                'name' => 'BPSDM Classic',
                'description' => 'Identitas resmi BPSDM dengan dominasi biru maritim dan aksen kuning keemasan.',
                'primary_color' => '1D4ED8',    // Blue 700
                'accent_color' => 'EAB308',     // Yellow 500
                'bg_color' => 'F0F9FF',
                'card_bg' => 'FFFFFF',
                'style_type' => 'bpsdm_badge',
            ],
            [
                'key' => 'modern-government',
                'name' => 'Modern Government',
                'description' => 'Tata letak modern untuk instansi pemerintah dengan garis aksen oranye dan cyan cerah.',
                'primary_color' => '0E7490',    // Cyan 700
                'accent_color' => 'EA580C',     // Orange 600
                'bg_color' => 'F8FAFC',
                'card_bg' => 'FFFFFF',
                'style_type' => 'modern_gov',
            ],
            [
                'key' => 'clean-documentation',
                'name' => 'Clean Documentation',
                'description' => 'Format inspeksi teknis lapangan dengan fokus kerapian tabel data dan dokumentasi.',
                'primary_color' => '334155',    // Slate 700
                'accent_color' => '059669',     // Emerald 600
                'bg_color' => 'F8FAFC',
                'card_bg' => 'FFFFFF',
                'style_type' => 'technical_doc',
            ],
        ];
    }

    /**
     * Generate all 10 PPTX files and their preview thumbnails
     */
    public function generateAll(): array
    {
        $dirPptx = storage_path('app/templates/builtin');
        $dirPreviews = storage_path('app/public/previews');

        if (!File::isDirectory($dirPptx)) {
            File::makeDirectory($dirPptx, 0755, true);
        }
        if (!File::isDirectory($dirPreviews)) {
            File::makeDirectory($dirPreviews, 0755, true);
        }

        $results = [];
        $templates = self::getTemplateDefinitions();

        foreach ($templates as $t) {
            $pptxFileName = "{$t['key']}.pptx";
            $pptxPath = "templates/builtin/{$pptxFileName}";
            $fullPptxPath = storage_path("app/{$pptxPath}");

            $previewFileName = "{$t['key']}.png";
            $previewPath = "previews/{$previewFileName}";
            $fullPreviewPath = storage_path("app/public/{$previewPath}");

            // 1. Create PPTX file (6 slides: 1 Cover + 5 Locations)
            $this->createPptxFile($t, $fullPptxPath);

            // 2. Create Preview Thumbnail image (16:9 PNG via GD)
            $this->createPreviewImage($t, $fullPreviewPath);

            $results[] = [
                'key' => $t['key'],
                'name' => $t['name'],
                'description' => $t['description'],
                'pptx_path' => $pptxPath,
                'preview_path' => $previewPath,
            ];
        }

        return $results;
    }

    /**
     * Create 16:9 Widescreen PPTX Presentation with 6 Slides
     */
    protected function createPptxFile(array $template, string $savePath): void
    {
        $ppt = new PhpPresentation();

        // Standard Widescreen 16:9 (13.333 x 7.5 inch)
        $layout = $ppt->getLayout();
        $layout->setCX(13.333, DocumentLayout::UNIT_INCH);
        $layout->setCY(7.5, DocumentLayout::UNIT_INCH);

        $primaryColor = $template['primary_color'];
        $accentColor = $template['accent_color'];

        // ----------------------------------------------------
        // SLIDE 1: COVER
        // ----------------------------------------------------
        $coverSlide = $ppt->getActiveSlide();
        $this->buildCoverSlide($coverSlide, $template);

        // ----------------------------------------------------
        // SLIDES 2 - 6: 5 LOCATIONS
        // ----------------------------------------------------
        $locations = [
            'Halaman Depan Kantor',
            'Ruang Pelayanan',
            'Ruang Kerja',
            'Toilet',
            'Area Parkir',
        ];

        foreach ($locations as $index => $locationName) {
            $locNum = $index + 1;
            $locSlide = $ppt->createSlide();
            $this->buildLocationSlide($locSlide, $template, $locNum, $locationName);
        }

        $writer = IOFactory::createWriter($ppt, 'PowerPoint2007');
        $writer->save($savePath);
    }

    /**
     * Draw a plain filled rectangle (optionally bordered) used as a color
     * band, card background or accent strip. Returns the shape so callers
     * can stack more shapes on top of it (z-order = creation order).
     */
    protected function addFilledBox($slide, float $x, float $y, float $w, float $h, string $fillHex, ?string $borderHex = null, float $borderWidth = 1.0)
    {
        $box = $slide->createRichTextShape()
            ->setOffsetX($x)
            ->setOffsetY($y)
            ->setWidth($w)
            ->setHeight($h);

        $box->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('FF' . ltrim($fillHex, '#')));

        if ($borderHex) {
            $box->getBorder()->setLineStyle(Border::LINE_SINGLE)->setLineWidth($borderWidth);
            $box->getBorder()->getColor()->setARGB('FF' . ltrim($borderHex, '#'));
        } else {
            $box->getBorder()->setLineStyle(Border::LINE_NONE);
        }

        return $box;
    }

    /**
     * Thin accent rule (a flattened filled box), used as a divider line.
     */
    protected function addAccentLine($slide, float $x, float $y, float $w, string $colorHex, float $thickness = 3.0)
    {
        return $this->addFilledBox($slide, $x, $y, $w, $thickness, $colorHex);
    }

    /**
     * Build Cover Slide (Slide 1) with placeholders
     */
    protected function buildCoverSlide($slide, array $template): void
    {
        $styleType = $template['style_type'] ?? 'card_modern';
        $isPhotoDominant = $styleType === 'photo_dominant';
        $isSplitHero = $styleType === 'split_hero';
        $isMinimal = $styleType === 'minimal_grid';

        // ── Background canvas ──────────────────────────────────────────
        $this->addFilledBox($slide, 0, 0, 960, 540, $template['bg_color']);

        if ($isSplitHero) {
            // Variant: full-height colored panel on the left (hero split),
            // content card floats on the right instead of a top banner.
            $this->addFilledBox($slide, 0, 0, 330, 540, $template['primary_color']);
            $this->addAccentLine($slide, 330, 0, 6, $template['accent_color'], 540);

            $badge = $slide->createRichTextShape()->setHeight(30)->setWidth(260)->setOffsetX(40)->setOffsetY(50);
            $rb = $badge->createTextRun('BPSDM PROVINSI');
            $rb->getFont()->setBold(true)->setSize(10)->setColor(new Color('FF' . $template['accent_color']));

            $title = $slide->createRichTextShape()->setHeight(160)->setWidth(260)->setOffsetX(40)->setOffsetY(90);
            $rt = $title->createTextRun("{{JUDUL_LAPORAN}}\nPENGAWASAN\nLINGKUNGAN\nKANTOR");
            $rt->getFont()->setBold(true)->setSize(21)->setColor(new Color('FFFFFFFF'));

            $sub = $slide->createRichTextShape()->setHeight(60)->setWidth(260)->setOffsetX(40)->setOffsetY(440);
            $rs = $sub->createTextRun("Template: {$template['name']}\n16:9 Widescreen");
            $rs->getFont()->setSize(9)->setColor(new Color('FFCBD5E1'));

            $metaX = 370; $photoX = 700;
        } elseif ($isMinimal) {
            // Variant: no colored header band, high-contrast thin rule only.
            $this->addAccentLine($slide, 70, 90, 820, $template['accent_color'], 3);

            $badge = $slide->createRichTextShape()->setHeight(26)->setWidth(600)->setOffsetX(70)->setOffsetY(40);
            $rb = $badge->createTextRun('BPSDM PROVINSI · PENGAWASAN LINGKUNGAN KANTOR');
            $rb->getFont()->setBold(true)->setSize(10)->setColor(new Color('FF' . $template['accent_color']));

            $title = $slide->createRichTextShape()->setHeight(70)->setWidth(850)->setOffsetX(70)->setOffsetY(55);
            $rt = $title->createTextRun('{{JUDUL_LAPORAN}}');
            $rt->getFont()->setBold(true)->setSize(27)->setColor(new Color('FF' . $template['primary_color']));

            $metaX = 70; $photoX = 650;
        } else {
            // Default / card_modern / institutional_header / others:
            // full-width colored header band + accent underline (mirrors the PDF cover).
            $this->addFilledBox($slide, 0, 0, 960, 108, $template['primary_color']);
            $this->addAccentLine($slide, 0, 108, 960, $template['accent_color'], 5);

            $badge = $slide->createRichTextShape()->setHeight(24)->setWidth(700)->setOffsetX(70)->setOffsetY(32);
            $rb = $badge->createTextRun('BPSDM PROVINSI • PENGAWASAN LINGKUNGAN KANTOR');
            $rb->getFont()->setBold(true)->setSize(10)->setColor(new Color('FF' . $template['accent_color']));

            $title = $slide->createRichTextShape()->setHeight(50)->setWidth(850)->setOffsetX(70)->setOffsetY(56);
            $rt = $title->createTextRun('{{JUDUL_LAPORAN}}');
            $rt->getFont()->setBold(true)->setSize(25)->setColor(new Color('FFFFFFFF'));

            $metaX = 70; $photoX = 650;
        }

        $bodyTop = $isSplitHero ? 60 : 150;
        // split_hero punya panel kiri + foto lebih dekat ke tengah (photoX=700),
        // jadi kartu meta harus lebih sempit agar tidak tumpang tindih dengan frame foto.
        $cardW = $isPhotoDominant ? 350 : ($isSplitHero ? 290 : 460);
        $photoW = $isPhotoDominant ? 300 : 230;
        $photoH = $photoW;

        // ── Left: meta info card (white card, matches PDF styling) ──────
        if (!$isMinimal) {
            $this->addFilledBox($slide, $metaX, $bodyTop, $cardW, 300, $template['card_bg'], 'E2E8F0', 1);
        }
        $metaPad = $isMinimal ? 0 : 24;

        $shapeMeta = $slide->createRichTextShape()
            ->setHeight(170)
            ->setWidth($cardW - ($metaPad * 2))
            ->setOffsetX($metaX + $metaPad)
            ->setOffsetY($bodyTop + $metaPad);

        $dark = new Color('FF1E293B');
        $gray = new Color('FF64748B');
        $primary = new Color('FF' . $template['primary_color']);

        $p1 = $shapeMeta->getActiveParagraph();
        $r1 = $p1->createTextRun("Nomor Laporan Resmi\n");
        $r1->getFont()->setSize(9)->setColor($gray)->setBold(true);
        $r2 = $p1->createTextRun("{{NOMOR_LAPORAN}}\n\n");
        $r2->getFont()->setBold(true)->setSize(16)->setColor($primary);

        $r3 = $p1->createTextRun("Hari & Tanggal Inspeksi\n");
        $r3->getFont()->setSize(9)->setColor($gray)->setBold(true);
        $r4 = $p1->createTextRun("{{HARI_TANGGAL}}\n\n");
        $r4->getFont()->setBold(true)->setSize(13)->setColor($dark);

        $r5 = $p1->createTextRun("Petugas Pengawas Lapangan\n");
        $r5->getFont()->setSize(9)->setColor($gray)->setBold(true);
        $r6 = $p1->createTextRun('{{NAMA_PETUGAS}}');
        $r6->getFont()->setBold(true)->setSize(15)->setColor($primary);

        // ── Right: officer photo card frame ──────────────────────────────
        $frameX = $photoX;
        $frameY = $bodyTop;
        $frameW = $photoW + 20;
        $frameH = $photoH + 46;

        $this->addFilledBox($slide, $frameX, $frameY, $frameW, $frameH, $template['card_bg'], $template['accent_color'], 2);

        $shapePhoto = $slide->createRichTextShape()
            ->setHeight($photoH)
            ->setWidth($photoW)
            ->setOffsetX($frameX + 10)
            ->setOffsetY($frameY + 10);
        $shapePhoto->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rPhoto = $shapePhoto->createTextRun("\n\n\n[ FOTO PETUGAS ]\n{{FOTO_PETUGAS}}");
        $rPhoto->getFont()->setBold(true)->setSize(10)->setColor(new Color('FF' . $template['accent_color']));

        $caption = $slide->createRichTextShape()->setHeight(20)->setWidth($photoW)->setOffsetX($frameX + 10)->setOffsetY($frameY + $photoH + 18);
        $caption->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rc = $caption->createTextRun('FOTO PETUGAS PENGAWAS');
        $rc->getFont()->setBold(true)->setSize(8)->setColor($gray);

        // ── Footer ──────────────────────────────────────────────────────
        $this->addAccentLine($slide, $metaX, 500, 960 - $metaX - 40, 'E2E8F0', 1);
        $shapeFooter = $slide->createRichTextShape()->setHeight(22)->setWidth(850)->setOffsetX($metaX)->setOffsetY(506);
        $rFoot = $shapeFooter->createTextRun("Template: {$template['name']} (16:9 Widescreen) • Sistem Pelaporan Pengawasan Lingkungan BPSDM");
        $rFoot->getFont()->setSize(9)->setColor($gray);
    }

    /**
     * Build Location Slide (Slide 2 to 6) with placeholders
     */
    protected function buildLocationSlide($slide, array $template, int $locNum, string $defaultName): void
    {
        $styleType = $template['style_type'] ?? 'card_modern';
        $isPhotoDominant = $styleType === 'photo_dominant';
        $isSplitHero = $styleType === 'split_hero';
        $isMinimal = $styleType === 'minimal_grid';

        $primary = new Color('FF' . $template['primary_color']);
        $accent = new Color('FF' . $template['accent_color']);
        $gray = new Color('FF64748B');
        $dark = new Color('FF1E293B');

        // ── Background canvas ──────────────────────────────────────────
        $this->addFilledBox($slide, 0, 0, 960, 540, $template['bg_color']);

        $contentX = 60;

        if ($isMinimal) {
            // Thin rule header, no color band — keeps the black/white/rose look.
            $this->addAccentLine($slide, $contentX, 72, 840, $template['accent_color'], 3);
        } elseif ($isSplitHero) {
            // Slim full-height accent stripe on the left edge for brand continuity
            // with the split-hero cover, without eating into the content width.
            $this->addFilledBox($slide, 0, 0, 14, 540, $template['primary_color']);
            $this->addFilledBox($slide, 0, 0, 960, 72, $template['bg_color']); // keep header area plain
            $contentX = 60;
        } else {
            // Default: colored header band, same family as the cover slide.
            $this->addFilledBox($slide, 0, 0, 960, 70, $template['primary_color']);
            $this->addAccentLine($slide, 0, 70, 960, $template['accent_color'], 4);
        }

        $headerTextColor = $isMinimal || $isSplitHero ? $primary : new Color('FFFFFFFF');
        $headerLabelColor = $isMinimal || $isSplitHero ? $accent : new Color('FFE0E7FF');

        $shapeHeader = $slide->createRichTextShape()
            ->setHeight(55)
            ->setWidth(820)
            ->setOffsetX($contentX)
            ->setOffsetY(14);
        $pHeader = $shapeHeader->getActiveParagraph();
        $rTop = $pHeader->createTextRun("TITIK PEMANTAUAN {$locNum} DARI 5\n");
        $rTop->getFont()->setBold(true)->setSize(10)->setColor($headerLabelColor);
        $rLocName = $pHeader->createTextRun("{{NAMA_LOKASI}}");
        $rLocName->getFont()->setBold(true)->setSize(19)->setColor($headerTextColor);

        // ── Photo frames ──────────────────────────────────────────────
        $photoTop = $isMinimal ? 95 : 90;
        // photo_dominant tetap lebih besar dari default, tapi tidak terlalu besar —
        // panel data di bawahnya tetap butuh ruang cukup untuk 4 bagian teks
        // (kondisi, catatan, permasalahan, solusi) tanpa meluber ke footer.
        $photoH = $isPhotoDominant ? 240 : 225;
        $photoW = $isPhotoDominant ? 410 : 395;
        $gap = 20;
        $p1X = $contentX;
        $p2X = $contentX + $photoW + $gap;

        foreach ([[$p1X, 'DOKUMENTASI 1', '{{FOTO_1}}'], [$p2X, 'DOKUMENTASI 2', '{{FOTO_2}}']] as [$px, $label, $placeholder]) {
            $this->addFilledBox($slide, $px, $photoTop, $photoW, $photoH, $template['card_bg'], $template['accent_color'], 1);

            $labelShape = $slide->createRichTextShape()->setHeight(18)->setWidth($photoW - 16)->setOffsetX($px + 8)->setOffsetY($photoTop + 6);
            $rLabel = $labelShape->createTextRun($label);
            $rLabel->getFont()->setBold(true)->setSize(9)->setColor($accent);

            $shapeP = $slide->createRichTextShape()
                ->setHeight($photoH - 32)
                ->setWidth($photoW - 16)
                ->setOffsetX($px + 8)
                ->setOffsetY($photoTop + 26);
            $shapeP->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rP = $shapeP->createTextRun("\n\n[ FRAME FOTO ]\n{$placeholder}");
            $rP->getFont()->setBold(true)->setSize(10)->setColor($gray);
        }

        // ── Bottom inspection data panel ─────────────────────────────────
        $dataTop = $photoTop + $photoH + 15;
        $dataH = 540 - $dataTop - 35;
        $dataW = ($p2X + $photoW) - $contentX;

        if (!$isMinimal) {
            $this->addFilledBox($slide, $contentX, $dataTop, $dataW, $dataH, $template['card_bg'], 'E2E8F0', 1);
            $this->addFilledBox($slide, $contentX, $dataTop, 5, $dataH, $template['accent_color']);
        } else {
            $this->addAccentLine($slide, $contentX, $dataTop, $dataW, $template['accent_color'], 2);
        }

        $innerPad = $isMinimal ? 0 : 22;
        $shapeData = $slide->createRichTextShape()
            ->setHeight($dataH - ($innerPad * 2))
            ->setWidth($dataW - ($innerPad * 2) - ($isMinimal ? 0 : 10))
            ->setOffsetX($contentX + $innerPad + ($isMinimal ? 0 : 10))
            ->setOffsetY($dataTop + $innerPad);

        $pData = $shapeData->getActiveParagraph();
        $rCondLabel = $pData->createTextRun('KONDISI KEBERSIHAN   ');
        $rCondLabel->getFont()->setBold(true)->setSize(10)->setColor($primary);
        $rCond = $pData->createTextRun("{{KONDISI}}\n\n");
        $rCond->getFont()->setBold(true)->setSize(10)->setColor($accent);

        $rNotes = $pData->createTextRun("CATATAN PEMANTAUAN\n{{CATATAN}}\n\n");
        $rNotes->getFont()->setSize(10)->setColor($dark);

        $rProb = $pData->createTextRun("PERMASALAHAN\n{{PERMASALAHAN}}\n\n");
        $rProb->getFont()->setSize(10)->setColor($dark);

        $rSol = $pData->createTextRun("SOLUSI / TINDAK LANJUT\n{{SOLUSI}}");
        $rSol->getFont()->setSize(10)->setColor($dark);

        // ── Footer ──────────────────────────────────────────────────────
        $shapeFoot = $slide->createRichTextShape()->setHeight(20)->setWidth($dataW)->setOffsetX($contentX)->setOffsetY(508);
        $rFoot = $shapeFoot->createTextRun('Halaman ' . ($locNum + 1) . " • BPSDM Pengawasan Lingkungan Kantor • Template: {$template['name']}");
        $rFoot->getFont()->setSize(8)->setColor($gray);
    }

    /**
     * Create high-quality 16:9 Preview PNG image using PHP GD
     */
    protected function createPreviewImage(array $template, string $savePath): void
    {
        $w = 640;
        $h = 360; // 16:9 ratio exactly

        $img = imagecreatetruecolor($w, $h);
        imageantialias($img, true);

        // Parse hex colors
        $bgR = hexdec(substr($template['bg_color'], 0, 2));
        $bgG = hexdec(substr($template['bg_color'], 2, 2));
        $bgB = hexdec(substr($template['bg_color'], 4, 2));

        $priR = hexdec(substr($template['primary_color'], 0, 2));
        $priG = hexdec(substr($template['primary_color'], 2, 2));
        $priB = hexdec(substr($template['primary_color'], 4, 2));

        $accR = hexdec(substr($template['accent_color'], 0, 2));
        $accG = hexdec(substr($template['accent_color'], 2, 2));
        $accB = hexdec(substr($template['accent_color'], 4, 2));

        $cBg = imagecolorallocate($img, $bgR, $bgG, $bgB);
        $cPrimary = imagecolorallocate($img, $priR, $priG, $priB);
        $cAccent = imagecolorallocate($img, $accR, $accG, $accB);
        $cWhite = imagecolorallocate($img, 255, 255, 255);
        $cDark = imagecolorallocate($img, 30, 41, 59);
        $cMuted = imagecolorallocate($img, 148, 163, 184);
        $cCardBorder = imagecolorallocate($img, 226, 232, 240);

        // Fill background
        imagefilledrectangle($img, 0, 0, $w, $h, $cBg);

        // Header Accent Strip
        imagefilledrectangle($img, 0, 0, $w, 12, $cPrimary);
        imagefilledrectangle($img, 0, 12, $w, 16, $cAccent);

        // Simulated Badge
        imagefilledrectangle($img, 30, 32, 160, 48, $cAccent);
        imagestring($img, 2, 38, 34, "BPSDM PROVINSI", $cWhite);

        // Title
        imagestring($img, 5, 30, 60, "PENGAWASAN LINGKUNGAN", $cPrimary);
        imagestring($img, 4, 30, 80, "KANTOR BPSDM", $cDark);

        // Template Name Watermark Banner
        imagefilledrectangle($img, 30, 110, 250, 135, $cPrimary);
        imagestring($img, 4, 38, 114, strtoupper($template['name']), $cWhite);

        // Report Meta Card
        imagefilledrectangle($img, 30, 150, 250, 260, $cWhite);
        imagerectangle($img, 30, 150, 250, 260, $cCardBorder);
        imagestring($img, 2, 42, 162, "Nomor Laporan:", $cMuted);
        imagestring($img, 2, 42, 178, "LAP-BPSDM-2026-0001", $cDark);
        imagestring($img, 2, 42, 205, "Hari, Tanggal:", $cMuted);
        imagestring($img, 2, 42, 220, "Kamis, 8 Okt 2026", $cDark);
        imagestring($img, 2, 42, 240, "Status: Siap", $cAccent);

        // Simulated Officer Photo Frame
        imagefilledrectangle($img, 280, 70, 440, 260, $cWhite);
        imagerectangle($img, 280, 70, 440, 260, $cAccent);
        imagefilledrectangle($img, 290, 80, 430, 220, imagecolorallocate($img, 241, 245, 249));
        imagestring($img, 2, 310, 140, "[ FOTO PETUGAS ]", $cMuted);
        imagestring($img, 3, 295, 235, "Nama Petugas", $cPrimary);

        // Location Thumbnail Card
        imagefilledrectangle($img, 460, 70, 610, 260, $cWhite);
        imagerectangle($img, 460, 70, 610, 260, $cCardBorder);
        imagefilledrectangle($img, 470, 80, 600, 155, imagecolorallocate($img, 241, 245, 249));
        imagestring($img, 2, 485, 115, "[ DOKUMENTASI 1 ]", $cMuted);
        imagefilledrectangle($img, 470, 165, 600, 240, imagecolorallocate($img, 241, 245, 249));
        imagestring($img, 2, 485, 200, "[ DOKUMENTASI 2 ]", $cMuted);
        imagestring($img, 2, 470, 245, "5 Titik Lokasi Pantau", $cPrimary);

        // Footer Bar
        imagefilledrectangle($img, 0, 325, $w, $h, $cPrimary);
        imagestring($img, 2, 30, 335, "PowerPoint Widescreen 16:9 • 13.333 x 7.5 inch • 6 Halaman Slide", $cWhite);
        imagestring($img, 2, 530, 335, "BPSDM", $cAccent);

        imagepng($img, $savePath);
        imagedestroy($img);
    }
}

