<?php

namespace App\Services;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\DocumentLayout;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Alignment;
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
     * Build Cover Slide (Slide 1) with placeholders
     */
    protected function buildCoverSlide($slide, array $template): void
    {
        $primary = new Color('FF' . $template['primary_color']);
        $accent = new Color('FF' . $template['accent_color']);
        $gray = new Color('FF64748B');
        $dark = new Color('FF1E293B');

        // Header Brand / Subtitle
        $shapeBadge = $slide->createRichTextShape()
            ->setHeight(36)
            ->setWidth(600)
            ->setOffsetX(70)
            ->setOffsetY(50);
        $runBadge = $shapeBadge->createTextRun('BPSDM PROVINSI • PENGAWASAN LINGKUNGAN KANTOR');
        $runBadge->getFont()->setBold(true)->setSize(11)->setColor($accent);

        // Main Title
        $shapeTitle = $slide->createRichTextShape()
            ->setHeight(130)
            ->setWidth(850)
            ->setOffsetX(70)
            ->setOffsetY(85);
        $runTitle = $shapeTitle->createTextRun("{{JUDUL_LAPORAN}}\nLAPORAN PENGAWASAN LINGKUNGAN KANTOR");
        $runTitle->getFont()->setBold(true)->setSize(28)->setColor($primary);

        // Report Meta Box (Nomor Laporan & Tanggal)
        $shapeMeta = $slide->createRichTextShape()
            ->setHeight(90)
            ->setWidth(500)
            ->setOffsetX(70)
            ->setOffsetY(230);
        
        $p1 = $shapeMeta->getActiveParagraph();
        $r1 = $p1->createTextRun("Nomor Laporan:\n");
        $r1->getFont()->setSize(10)->setColor($gray);
        $r2 = $p1->createTextRun("{{NOMOR_LAPORAN}}\n\n");
        $r2->getFont()->setBold(true)->setSize(14)->setColor($dark);

        $r3 = $p1->createTextRun("Hari, Tanggal:\n");
        $r3->getFont()->setSize(10)->setColor($gray);
        $r4 = $p1->createTextRun("{{HARI_TANGGAL}}");
        $r4->getFont()->setBold(true)->setSize(13)->setColor($dark);

        // Officer Info Box
        $shapeOfficer = $slide->createRichTextShape()
            ->setHeight(70)
            ->setWidth(500)
            ->setOffsetX(70)
            ->setOffsetY(340);
        $rOffLabel = $shapeOfficer->createTextRun("Petugas Pengawas:\n");
        $rOffLabel->getFont()->setSize(10)->setColor($gray);
        $rOffName = $shapeOfficer->createTextRun("{{NAMA_PETUGAS}}");
        $rOffName->getFont()->setBold(true)->setSize(16)->setColor($primary);

        // Officer Photo Frame Box (16:9 or 4:3 frame placeholder)
        $shapePhoto = $slide->createRichTextShape()
            ->setHeight(280)
            ->setWidth(280)
            ->setOffsetX(620)
            ->setOffsetY(120);
        $shapePhoto->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rPhoto = $shapePhoto->createTextRun("[ FOTO PETUGAS ]\n{{FOTO_PETUGAS}}");
        $rPhoto->getFont()->setBold(true)->setSize(11)->setColor($accent);

        // Footer Brand Line
        $shapeFooter = $slide->createRichTextShape()
            ->setHeight(30)
            ->setWidth(900)
            ->setOffsetX(70)
            ->setOffsetY(460);
        $rFoot = $shapeFooter->createTextRun("Template: {$template['name']} (16:9 Widescreen) • Sistem Pelaporan Pengawasan Lingkungan BPSDM");
        $rFoot->getFont()->setSize(9)->setColor($gray);
    }

    /**
     * Build Location Slide (Slide 2 to 6) with placeholders
     */
    protected function buildLocationSlide($slide, array $template, int $locNum, string $defaultName): void
    {
        $primary = new Color('FF' . $template['primary_color']);
        $accent = new Color('FF' . $template['accent_color']);
        $gray = new Color('FF64748B');
        $dark = new Color('FF1E293B');

        // Top Navigation / Header
        $shapeHeader = $slide->createRichTextShape()
            ->setHeight(55)
            ->setWidth(850)
            ->setOffsetX(60)
            ->setOffsetY(30);
        
        $pHeader = $shapeHeader->getActiveParagraph();
        $rTop = $pHeader->createTextRun("TITIK PEMANTAUAN {$locNum} DARI 5\n");
        $rTop->getFont()->setBold(true)->setSize(10)->setColor($accent);
        
        $rLocName = $pHeader->createTextRun("{{NAMA_LOKASI}} - {$defaultName}");
        $rLocName->getFont()->setBold(true)->setSize(18)->setColor($primary);

        // Photo Frame 1 (Dokumentasi 1)
        $shapeP1 = $slide->createRichTextShape()
            ->setHeight(230)
            ->setWidth(400)
            ->setOffsetX(60)
            ->setOffsetY(95);
        $shapeP1->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rP1 = $shapeP1->createTextRun("DOKUMENTASI 1\n\n[ FRAME FOTO 1 ]\n{{FOTO_1}}");
        $rP1->getFont()->setBold(true)->setSize(11)->setColor($accent);

        // Photo Frame 2 (Dokumentasi 2)
        $shapeP2 = $slide->createRichTextShape()
            ->setHeight(230)
            ->setWidth(400)
            ->setOffsetX(490)
            ->setOffsetY(95);
        $shapeP2->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rP2 = $shapeP2->createTextRun("DOKUMENTASI 2\n\n[ FRAME FOTO 2 ]\n{{FOTO_2}}");
        $rP2->getFont()->setBold(true)->setSize(11)->setColor($accent);

        // Bottom Inspection Data Box
        $shapeData = $slide->createRichTextShape()
            ->setHeight(130)
            ->setWidth(830)
            ->setOffsetX(60)
            ->setOffsetY(340);
        
        $pData = $shapeData->getActiveParagraph();
        $rCondLabel = $pData->createTextRun("KONDISI KEBERSIHAN: ");
        $rCondLabel->getFont()->setBold(true)->setSize(11)->setColor($primary);
        $rCond = $pData->createTextRun("{{KONDISI}}\n\n");
        $rCond->getFont()->setBold(true)->setSize(11)->setColor($accent);

        $rNotes = $pData->createTextRun("CATATAN PEMANTAUAN:\n{{CATATAN}}\n\n");
        $rNotes->getFont()->setSize(10)->setColor($dark);

        $rProb = $pData->createTextRun("PERMASALAHAN:\n{{PERMASALAHAN}}\n\n");
        $rProb->getFont()->setSize(10)->setColor($dark);

        $rSol = $pData->createTextRun("SOLUSI / TINDAK LANJUT:\n{{SOLUSI}}");
        $rSol->getFont()->setSize(10)->setColor($dark);

        // Footer Slide
        $shapeFoot = $slide->createRichTextShape()
            ->setHeight(25)
            ->setWidth(830)
            ->setOffsetX(60)
            ->setOffsetY(485);
        $rFoot = $shapeFoot->createTextRun("Halaman " . ($locNum + 1) . " • BPSDM Pengawasan Lingkungan Kantor • Template: {$template['name']}");
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

