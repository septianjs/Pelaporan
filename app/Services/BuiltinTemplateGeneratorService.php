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
     * Definition of 10 distinct, highly colorful, gapless built-in templates
     */
    public static function getTemplateDefinitions(): array
    {
        return [
            [
                'key' => 'corporate-blue',
                'name' => 'Tropical Pop',
                'description' => 'Nuansa biru sapphire elegan dengan aksen emas cerah dan kontras tinggi.',
                'primary_color' => '1E3A8A',    // Royal Navy
                'secondary_color' => '2563EB',  // Electric Blue
                'accent_color' => 'F59E0B',     // Amber Gold
                'bg_color' => 'EFF6FF',         // Soft Ice Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'DBEAFE',
                'style_type' => 'tropical_pop',
            ],
            [
                'key' => 'government-formal',
                'name' => 'Batik Nusantara',
                'description' => 'Identitas hijau zamrud mewah dan emas prestisius berkarakter dinas resmi.',
                'primary_color' => '064E3B',    // Deep Emerald Pine
                'secondary_color' => '059669',  // Radiant Emerald
                'accent_color' => 'F59E0B',     // Imperial Gold
                'bg_color' => 'ECFDF5',         // Soft Mint Sage Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'D1FAE5',
                'style_type' => 'batik_nusantara',
            ],
            [
                'key' => 'modern-clean',
                'name' => 'Comic Pop Art',
                'description' => 'Perpaduan segar toska samudra dengan aksen oranye terbenam yang energik.',
                'primary_color' => '115E59',    // Deep Ocean Teal
                'secondary_color' => '0D9488',  // Vivid Turquoise
                'accent_color' => 'F97316',     // Sunset Orange
                'bg_color' => 'F0FDFA',         // Aquamarine Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'CCFBF1',
                'style_type' => 'comic_pop',
            ],
            [
                'key' => 'minimal-professional',
                'name' => 'Scrapbook Ceria',
                'description' => 'Desain tegas berwibawa dengan merah marun gelap dan aksen ruby menyala.',
                'primary_color' => '881337',    // Deep Burgundy
                'secondary_color' => 'E11D48',  // Vivid Ruby
                'accent_color' => 'FBBF24',     // Warm Sand Gold
                'bg_color' => 'FFF1F2',         // Rose Blush Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FFE4E6',
                'style_type' => 'scrapbook',
            ],
            [
                'key' => 'executive-report',
                'name' => 'Memphis Geometry',
                'description' => 'Nuansa ultraviolet mewah dipadu koral menyala untuk presentasi pimpinan.',
                'primary_color' => '3B0764',    // Royal Midnight Violet
                'secondary_color' => '7C3AED',  // Electric Violet
                'accent_color' => 'F43F5E',     // Vibrant Coral
                'bg_color' => 'FAF5FF',         // Lilac Glow Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'F3E8FF',
                'style_type' => 'memphis',
            ],
            [
                'key' => 'photo-focus',
                'name' => 'Festival Nusantara',
                'description' => 'Kehangatan terakota bumi dengan aksen jingga sunset yang kaya warna.',
                'primary_color' => '9A3412',    // Rich Terracotta
                'secondary_color' => 'EA580C',  // Warm Sunset
                'accent_color' => 'F59E0B',     // Golden Amber
                'bg_color' => 'FFF7ED',         // Warm Peach Sand Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FFEDD5',
                'style_type' => 'festival',
            ],
            [
                'key' => 'elegant-report',
                'name' => 'Retro Groovy',
                'description' => 'Elegansi magenta anggur dengan aksen kuning sitrun menyala yang berani.',
                'primary_color' => '701A75',    // Deep Royal Plum
                'secondary_color' => 'C026D3',  // Vivid Magenta
                'accent_color' => 'FACC15',     // Bright Citron Gold
                'bg_color' => 'FDF4FF',         // Orchid Glow Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FAE8FF',
                'style_type' => 'retro_groovy',
            ],
            [
                'key' => 'bpsdm-classic',
                'name' => 'Infografik Pelangi',
                'description' => 'Identitas institusional BPSDM bernuansa Deep Pine dan emas champagne.',
                'primary_color' => '14251D',    // Deep Pine Institutional
                'secondary_color' => '1C362A',  // Forest Pine
                'accent_color' => 'D8C79C',     // Prestige Champagne Gold
                'bg_color' => 'F4F3EE',         // Warm Stone Canvas
                'card_bg' => 'FAF9F5',
                'card_alt' => 'ECEAE3',
                'style_type' => 'rainbow_infographic',
            ],
            [
                'key' => 'modern-government',
                'name' => 'Paper Cut Explosion',
                'description' => 'Kombinasi modern biru kobalt dingin dengan aksen jingga api kontras tinggi.',
                'primary_color' => '1E293B',    // Slate Navy
                'secondary_color' => '0284C7',  // Cobalt Sky
                'accent_color' => 'EA580C',     // Blaze Orange
                'bg_color' => 'F1F5F9',         // Steel Ice Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'E2E8F0',
                'style_type' => 'paper_cut',
            ],
            [
                'key' => 'clean-documentation',
                'name' => 'Botanical Maximalist',
                'description' => 'Tampilan kontemporer grafit gelap dengan aksen hijau neon volt yang tajam.',
                'primary_color' => '18181B',    // Dark Graphite
                'secondary_color' => '334155',  // Slate Steel
                'accent_color' => '84CC16',     // Electric Lime Volt
                'bg_color' => 'F4F4F5',         // Cool Stone Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'E4E4E7',
                'style_type' => 'botanical',
            ],
        ];
    }

    /**
     * Generate all 10 PPTX files and their preview thumbnails
     */
    /**
     * Build only PDF template previews. The application no longer creates PPTX files.
     * The template definitions remain the source of the PDF palettes and layouts.
     */
    public function generateAll(): array
    {
        $dirPreviews = storage_path('app/public/previews');
        if (!File::isDirectory($dirPreviews)) {
            File::makeDirectory($dirPreviews, 0755, true);
        }

        $results = [];
        foreach (self::getTemplateDefinitions() as $template) {
            $previewPath = "previews/{$template['key']}.png";
            $fullPreviewPath = storage_path("app/public/{$previewPath}");
            $this->createPreviewImage($template, $fullPreviewPath);

            $results[] = [
                'key' => $template['key'],
                'name' => $template['name'],
                'description' => $template['description'],
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

        // ----------------------------------------------------
        // SLIDE 1: COVER (Edge-to-Edge, Zero Dead Space)
        // ----------------------------------------------------
        $coverSlide = $ppt->getActiveSlide();
        $this->buildCoverSlide($coverSlide, $template);

        // ----------------------------------------------------
        // SLIDES 2 - 6: 5 LOCATIONS (Full Coverage Layout)
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
     * Draw a plain filled rectangle used as cards, banners, or color blocks.
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
     * Thin accent rule used as a divider line.
     */
    protected function addAccentLine($slide, float $x, float $y, float $w, string $colorHex, float $thickness = 3.0)
    {
        return $this->addFilledBox($slide, $x, $y, $w, $thickness, $colorHex);
    }

    /**
     * Build Cover Slide (Slide 1) with rich full-bleed layout and zero empty space
     */
    protected function buildCoverSlide($slide, array $template): void
    {
        $primary = new Color('FF' . $template['primary_color']);
        $accent = new Color('FF' . $template['accent_color']);
        $white = new Color('FFFFFFFF');
        $dark = new Color('FF1E293B');
        $muted = new Color('FF64748B');

        // ── 1. Full Canvas Background ──────────────────────────────────
        $this->addFilledBox($slide, 0, 0, 960, 540, $template['bg_color']);

        // ── 2. Top Header Banner (Full-Bleed 0 to 960) ─────────────────
        $this->addFilledBox($slide, 0, 0, 960, 105, $template['primary_color']);
        $this->addAccentLine($slide, 0, 105, 960, $template['accent_color'], 5);

        // Header Eyebrow Tag
        $eyebrow = $slide->createRichTextShape()->setHeight(22)->setWidth(700)->setOffsetX(30)->setOffsetY(18);
        $rb = $eyebrow->createTextRun('BPSDM PROVINSI • SISTEM PENGAWASAN LINGKUNGAN KANTOR');
        $rb->getFont()->setBold(true)->setSize(10)->setColor($accent);

        // Header Title
        $title = $slide->createRichTextShape()->setHeight(48)->setWidth(750)->setOffsetX(30)->setOffsetY(44);
        $rt = $title->createTextRun('{{JUDUL_LAPORAN}}');
        $rt->getFont()->setBold(true)->setSize(23)->setColor($white);

        // 16:9 Badge on top-right
        $badge169 = $slide->createRichTextShape()->setHeight(32)->setWidth(140)->setOffsetX(790)->setOffsetY(35);
        $badge169->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $r169 = $badge169->createTextRun('16:9 WIDESCREEN');
        $r169->getFont()->setBold(true)->setSize(9)->setColor($accent);

        // ── 3. Middle Body: Two Balanced, Edge-to-Edge Cards ──────────
        $bodyY = 122;
        $bodyH = 360;

        // ── LEFT CARD: METADATA & SCOPE CHECKLIST (Width: 590) ─────────
        $this->addFilledBox($slide, 24, $bodyY, 590, $bodyH, $template['card_bg'], $template['accent_color'], 1.5);
        // Colored accent header inside card
        $this->addFilledBox($slide, 24, $bodyY, 590, 42, $template['card_alt'] ?? 'E2E8F0');
        $this->addAccentLine($slide, 24, $bodyY + 42, 590, $template['accent_color'], 2);

        $cardHead = $slide->createRichTextShape()->setHeight(32)->setWidth(570)->setOffsetX(36)->setOffsetY($bodyY + 8);
        $rc1 = $cardHead->createTextRun('NOMOR BERKAS LAPORAN RESMI: ');
        $rc1->getFont()->setSize(9)->setBold(true)->setColor($muted);
        $rc2 = $cardHead->createTextRun('{{NOMOR_LAPORAN}}');
        $rc2->getFont()->setSize(11)->setBold(true)->setColor($primary);

        // Meta Details Row
        $metaDetails = $slide->createRichTextShape()->setHeight(68)->setWidth(560)->setOffsetX(36)->setOffsetY($bodyY + 54);
        $pm = $metaDetails->getActiveParagraph();
        $rm1 = $pm->createTextRun("HARI & TANGGAL PEMERIKSAAN:\n");
        $rm1->getFont()->setSize(8)->setBold(true)->setColor($muted);
        $rm2 = $pm->createTextRun("{{HARI_TANGGAL}}\n");
        $rm2->getFont()->setSize(12)->setBold(true)->setColor($dark);

        $officerRow = $slide->createRichTextShape()->setHeight(50)->setWidth(560)->setOffsetX(36)->setOffsetY($bodyY + 115);
        $po = $officerRow->getActiveParagraph();
        $ro1 = $po->createTextRun("PETUGAS PENGAWAS LAPANGAN:\n");
        $ro1->getFont()->setSize(8)->setBold(true)->setColor($muted);
        $ro2 = $po->createTextRun("{{NAMA_PETUGAS}}");
        $ro2->getFont()->setSize(13)->setBold(true)->setColor($primary);

        // Scope of 5 Inspection Points (Gapless, filled card section)
        $this->addFilledBox($slide, 36, $bodyY + 175, 566, 170, $template['bg_color'], 'CBD5E1', 1);
        $this->addAccentLine($slide, 36, $bodyY + 175, 566, $template['accent_color'], 3);

        $scopeTitle = $slide->createRichTextShape()->setHeight(22)->setWidth(540)->setOffsetX(46)->setOffsetY($bodyY + 182);
        $rst = $scopeTitle->createTextRun('CAKUPAN LOKASI INSPEKSI KEBERSIHAN & TATA KELOLA:');
        $rst->getFont()->setBold(true)->setSize(9)->setColor($primary);

        $scopeList = $slide->createRichTextShape()->setHeight(130)->setWidth(540)->setOffsetX(46)->setOffsetY($bodyY + 204);
        $ps = $scopeList->getActiveParagraph();
        $points = [
            '1. Halaman Depan & Akses Utama Gedung',
            '2. Ruang Pelayanan Publik & Informasi',
            '3. Ruang Kerja & Fasilitas Pegawai',
            '4. Sarana Toilet & Kebersihan Sanitasi',
            '5. Area Parkir & Jalur Lingkungan Kantor',
        ];
        foreach ($points as $idx => $pt) {
            $rDot = $ps->createTextRun("[✓] {$pt}\n");
            $rDot->getFont()->setSize(9)->setColor($dark)->setBold(true);
        }

        // ── RIGHT CARD: LARGE OFFICER PHOTO (Width: 310) ───────────────
        $photoX = 626;
        $this->addFilledBox($slide, $photoX, $bodyY, 310, $bodyH, $template['card_bg'], $template['accent_color'], 1.5);

        // Photo Frame Header
        $this->addFilledBox($slide, $photoX, $bodyY, 310, 36, $template['primary_color']);
        $pHead = $slide->createRichTextShape()->setHeight(26)->setWidth(290)->setOffsetX($photoX + 10)->setOffsetY($bodyY + 6);
        $pHead->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rp = $pHead->createTextRun('DOKUMENTASI PETUGAS');
        $rp->getFont()->setBold(true)->setSize(9)->setColor($white);

        // Photo Box
        $photoBoxW = 286;
        $photoBoxH = 260;
        $this->addFilledBox($slide, $photoX + 12, $bodyY + 46, $photoBoxW, $photoBoxH, $template['card_alt'] ?? 'F1F5F9', '94A3B8', 1);

        $shapePhoto = $slide->createRichTextShape()
            ->setHeight($photoBoxH)
            ->setWidth($photoBoxW)
            ->setOffsetX($photoX + 12)
            ->setOffsetY($bodyY + 46);
        $shapePhoto->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rPhoto = $shapePhoto->createTextRun("\n\n\n\n[ FOTO PETUGAS ]\n{{FOTO_PETUGAS}}");
        $rPhoto->getFont()->setBold(true)->setSize(11)->setColor($primary);

        // Photo Caption Footer
        $sealBadge = $slide->createRichTextShape()->setHeight(36)->setWidth(286)->setOffsetX($photoX + 12)->setOffsetY($bodyY + 314);
        $sealBadge->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rsb = $sealBadge->createTextRun("TERVERIFIKASI RESMI BPSDM\n{{HARI_TANGGAL}}");
        $rsb->getFont()->setBold(true)->setSize(8)->setColor($primary);

        // ── 4. Full-Bleed Bottom Footer Ribbon ─────────────────────────
        $this->addFilledBox($slide, 0, 495, 960, 45, $template['primary_color']);
        $this->addAccentLine($slide, 0, 495, 960, $template['accent_color'], 3);

        $shapeFooter = $slide->createRichTextShape()->setHeight(26)->setWidth(900)->setOffsetX(30)->setOffsetY(507);
        $rFoot = $shapeFooter->createTextRun("Template: {$template['name']} (16:9 HD) • Sistem Pelaporan Pengawasan Lingkungan Kantor BPSDM • Halaman 1");
        $rFoot->getFont()->setSize(9)->setColor($white);
    }

    /**
     * Build Location Slide (Slide 2 to 6) with edge-to-edge photos and compact 3-column findings
     */
    protected function buildLocationSlide($slide, array $template, int $locNum, string $defaultName): void
    {
        $primary = new Color('FF' . $template['primary_color']);
        $accent = new Color('FF' . $template['accent_color']);
        $white = new Color('FFFFFFFF');
        $dark = new Color('FF1E293B');
        $muted = new Color('FF64748B');

        // ── 1. Full Canvas Background ──────────────────────────────────
        $this->addFilledBox($slide, 0, 0, 960, 540, $template['bg_color']);

        // ── 2. Top Header Banner (Full-Bleed 0 to 960) ─────────────────
        $this->addFilledBox($slide, 0, 0, 960, 78, $template['primary_color']);
        $this->addAccentLine($slide, 0, 78, 960, $template['accent_color'], 4);

        $shapeHeader = $slide->createRichTextShape()
            ->setHeight(56)
            ->setWidth(680)
            ->setOffsetX(24)
            ->setOffsetY(12);
        $pHeader = $shapeHeader->getActiveParagraph();
        $rTop = $pHeader->createTextRun("TITIK PEMANTAUAN {$locNum} DARI 5 • BPSDM LINGKUNGAN KANTOR\n");
        $rTop->getFont()->setBold(true)->setSize(9)->setColor($accent);
        $rLocName = $pHeader->createTextRun("{{NAMA_LOKASI}}");
        $rLocName->getFont()->setBold(true)->setSize(19)->setColor($white);

        // Status Badge Pill on Header Right
        $this->addFilledBox($slide, 720, 18, 216, 42, $template['card_bg'], $template['accent_color'], 2);
        $shapeStatus = $slide->createRichTextShape()->setHeight(36)->setWidth(208)->setOffsetX(724)->setOffsetY(22);
        $shapeStatus->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rst = $shapeStatus->createTextRun("STATUS AREA:\n{{KONDISI}}");
        $rst->getFont()->setBold(true)->setSize(9)->setColor($primary);

        // ── 3. Middle Section: TWO LARGE PHOTOS (Edge-to-Edge) ─────────
        $photoY = 92;
        $photoW = 444;
        $photoH = 228;
        $p1X = 24;
        $p2X = 492;

        $photos = [
            [$p1X, 'DOKUMENTASI 1 : PERSPEKTIF UTAMA', '{{FOTO_1}}'],
            [$p2X, 'DOKUMENTASI 2 : DETAIL AREA PEMANTAUAN', '{{FOTO_2}}'],
        ];

        foreach ($photos as [$px, $label, $placeholder]) {
            // Container Card
            $this->addFilledBox($slide, $px, $photoY, $photoW, $photoH, $template['card_bg'], $template['accent_color'], 1.5);
            
            // Header Strip on Photo Frame
            $this->addFilledBox($slide, $px, $photoY, $photoW, 26, $template['primary_color']);
            $labelShape = $slide->createRichTextShape()->setHeight(20)->setWidth($photoW - 16)->setOffsetX($px + 8)->setOffsetY($photoY + 4);
            $rLabel = $labelShape->createTextRun($label);
            $rLabel->getFont()->setBold(true)->setSize(8)->setColor($accent);

            // Photo Placeholder Inner Area
            $innerPhotoY = $photoY + 28;
            $innerPhotoH = $photoH - 32;
            $innerPhotoW = $photoW - 8;
            $this->addFilledBox($slide, $px + 4, $innerPhotoY, $innerPhotoW, $innerPhotoH, $template['card_alt'] ?? 'F1F5F9');

            $shapeP = $slide->createRichTextShape()
                ->setHeight($innerPhotoH)
                ->setWidth($innerPhotoW)
                ->setOffsetX($px + 4)
                ->setOffsetY($innerPhotoY);
            $shapeP->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rP = $shapeP->createTextRun("\n\n\n[ FRAME FOTO DOKUMENTASI ]\n{$placeholder}");
            $rP->getFont()->setBold(true)->setSize(10)->setColor($muted);
        }

        // ── 4. Bottom Section: 3-COLUMN STRUCTURED FINDINGS ────────────
        // Fills the remaining vertical space (330 to 495) with zero empty void
        $dataY = 328;
        $dataH = 168;

        // Column 1: Catatan Pemantauan (Width: 295)
        $c1X = 24;
        $c1W = 295;
        $this->addFilledBox($slide, $c1X, $dataY, $c1W, $dataH, $template['card_bg'], 'CBD5E1', 1.5);
        $this->addFilledBox($slide, $c1X, $dataY, $c1W, 28, $template['primary_color']);
        $h1 = $slide->createRichTextShape()->setHeight(22)->setWidth($c1W - 16)->setOffsetX($c1X + 8)->setOffsetY($dataY + 4);
        $rh1 = $h1->createTextRun('CATATAN PEMANTAUAN');
        $rh1->getFont()->setBold(true)->setSize(8)->setColor($white);

        $body1 = $slide->createRichTextShape()->setHeight($dataH - 36)->setWidth($c1W - 16)->setOffsetX($c1X + 8)->setOffsetY($dataY + 32);
        $p1 = $body1->getActiveParagraph();
        $rc1 = $p1->createTextRun("Hasil Peninjauan:\n{{CATATAN}}");
        $rc1->getFont()->setSize(9)->setColor($dark);

        // Column 2: Identifikasi Masalah (Width: 295)
        $c2X = 333;
        $c2W = 295;
        $this->addFilledBox($slide, $c2X, $dataY, $c2W, $dataH, $template['card_bg'], 'CBD5E1', 1.5);
        $this->addFilledBox($slide, $c2X, $dataY, $c2W, 28, '991B1B'); // Muted Red Header
        $h2 = $slide->createRichTextShape()->setHeight(22)->setWidth($c2W - 16)->setOffsetX($c2X + 8)->setOffsetY($dataY + 4);
        $rh2 = $h2->createTextRun('IDENTIFIKASI PERMASALAHAN');
        $rh2->getFont()->setBold(true)->setSize(8)->setColor($white);

        $body2 = $slide->createRichTextShape()->setHeight($dataH - 36)->setWidth($c2W - 16)->setOffsetX($c2X + 8)->setOffsetY($dataY + 32);
        $p2 = $body2->getActiveParagraph();
        $rc2 = $p2->createTextRun("Kendala / Titik Masalah:\n{{PERMASALAHAN}}");
        $rc2->getFont()->setSize(9)->setColor($dark);

        // Column 3: Solusi & Tindak Lanjut (Width: 294)
        $c3X = 642;
        $c3W = 294;
        $this->addFilledBox($slide, $c3X, $dataY, $c3W, $dataH, $template['card_bg'], 'CBD5E1', 1.5);
        $this->addFilledBox($slide, $c3X, $dataY, $c3W, 28, '166534'); // Muted Green Header
        $h3 = $slide->createRichTextShape()->setHeight(22)->setWidth($c3W - 16)->setOffsetX($c3X + 8)->setOffsetY($dataY + 4);
        $rh3 = $h3->createTextRun('SOLUSI & TINDAK LANJUT');
        $rh3->getFont()->setBold(true)->setSize(8)->setColor($white);

        $body3 = $slide->createRichTextShape()->setHeight($dataH - 36)->setWidth($c3W - 16)->setOffsetX($c3X + 8)->setOffsetY($dataY + 32);
        $p3 = $body3->getActiveParagraph();
        $rc3 = $p3->createTextRun("Rekomendasi Tindakan:\n{{SOLUSI}}");
        $rc3->getFont()->setSize(9)->setColor($dark);

        // ── 5. Full-Bleed Bottom Footer Ribbon ─────────────────────────
        $this->addFilledBox($slide, 0, 506, 960, 34, $template['primary_color']);
        $this->addAccentLine($slide, 0, 506, 960, $template['accent_color'], 2);

        $shapeFoot = $slide->createRichTextShape()->setHeight(22)->setWidth(910)->setOffsetX(24)->setOffsetY(513);
        $rFoot = $shapeFoot->createTextRun("BPSDM Pengawasan Lingkungan • Halaman " . ($locNum + 1) . " dari 6 • {{NAMA_LOKASI}} • Template: {$template['name']}");
        $rFoot->getFont()->setSize(8)->setColor($white);
    }

    /**
     * Create high-quality, colorful, gapless 16:9 Preview PNG image using PHP GD
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
        $cMuted = imagecolorallocate($img, 100, 116, 139);
        $cCardBorder = imagecolorallocate($img, 203, 213, 225);
        $cPhotoBg = imagecolorallocate($img, 226, 232, 240);
        $cRed = imagecolorallocate($img, 185, 28, 28);
        $cGreen = imagecolorallocate($img, 22, 101, 52);

        // 1. Fill entire background
        imagefilledrectangle($img, 0, 0, $w, $h, $cBg);

        // 2. Full-bleed Top Header Banner
        imagefilledrectangle($img, 0, 0, $w, 56, $cPrimary);
        imagefilledrectangle($img, 0, 56, $w, 60, $cAccent);

        // Header Text
        imagestring($img, 2, 16, 10, "BPSDM PROVINSI • PENGAWASAN LINGKUNGAN", $cAccent);
        imagestring($img, 4, 16, 28, strtoupper($template['name']), $cWhite);
        imagestring($img, 2, 500, 20, "16:9 WIDESCREEN", $cAccent);

        // 3. Left Section: 2 Photos (Filling width without gap)
        // Photo 1
        imagefilledrectangle($img, 16, 68, 310, 220, $cWhite);
        imagerectangle($img, 16, 68, 310, 220, $cAccent);
        imagefilledrectangle($img, 16, 68, 310, 84, $cPrimary);
        imagestring($img, 2, 22, 70, "DOKUMENTASI 1: PERSPEKTIF", $cAccent);
        imagefilledrectangle($img, 20, 88, 306, 216, $cPhotoBg);
        imagestring($img, 2, 85, 145, "[ FOTO AREA UTAMA ]", $cMuted);

        // Photo 2
        imagefilledrectangle($img, 320, 68, 624, 220, $cWhite);
        imagerectangle($img, 320, 68, 624, 220, $cAccent);
        imagefilledrectangle($img, 320, 68, 624, 84, $cPrimary);
        imagestring($img, 2, 326, 70, "DOKUMENTASI 2: DETAIL", $cAccent);
        imagefilledrectangle($img, 324, 88, 620, 216, $cPhotoBg);
        imagestring($img, 2, 395, 145, "[ FOTO TITIK DETAIL ]", $cMuted);

        // 4. Bottom Section: 3-Column Structured Findings (No empty space)
        // Card 1: Catatan
        imagefilledrectangle($img, 16, 228, 212, 325, $cWhite);
        imagerectangle($img, 16, 228, 212, 325, $cCardBorder);
        imagefilledrectangle($img, 16, 228, 212, 244, $cPrimary);
        imagestring($img, 2, 20, 230, "CATATAN PEMANTAUAN", $cWhite);
        imagestring($img, 1, 22, 252, "Area bersih & terawat.", $cDark);
        imagestring($img, 1, 22, 266, "5 Titik pantau lengkap.", $cDark);
        imagestring($img, 1, 22, 280, "Status: Terverifikasi", $cGreen);

        // Card 2: Permasalahan
        imagefilledrectangle($img, 220, 228, 416, 325, $cWhite);
        imagerectangle($img, 220, 228, 416, 325, $cCardBorder);
        imagefilledrectangle($img, 220, 228, 416, 244, $cRed);
        imagestring($img, 2, 226, 230, "IDENTIFIKASI MASALAH", $cWhite);
        imagestring($img, 1, 226, 252, "Pengecekan saluran air,", $cDark);
        imagestring($img, 1, 226, 266, "pemilahan sampah,", $cDark);
        imagestring($img, 1, 226, 280, "ventilasi udara ruang.", $cDark);

        // Card 3: Solusi
        imagefilledrectangle($img, 424, 228, 624, 325, $cWhite);
        imagerectangle($img, 424, 228, 624, 325, $cCardBorder);
        imagefilledrectangle($img, 424, 228, 624, 244, $cGreen);
        imagestring($img, 2, 430, 230, "SOLUSI & TINDAK LANJUT", $cWhite);
        imagestring($img, 1, 430, 252, "Jadwal sanitasi rutin,", $cDark);
        imagestring($img, 1, 430, 266, "peremajaan tempat sampah,", $cDark);
        imagestring($img, 1, 430, 280, "tindak lanjut tim kebersihan.", $cDark);

        // 5. Full-bleed Footer Ribbon
        imagefilledrectangle($img, 0, 336, $w, $h, $cPrimary);
        imagefilledrectangle($img, 0, 334, $w, 336, $cAccent);
        imagestring($img, 2, 16, 342, "PowerPoint Widescreen 16:9 • 6 Halaman Slide • Sistem BPSDM", $cWhite);
        imagestring($img, 2, 545, 342, "BPSDM PROV", $cAccent);

        imagepng($img, $savePath);
        imagedestroy($img);
    }
}
