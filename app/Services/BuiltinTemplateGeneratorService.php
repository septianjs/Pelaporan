<?php

namespace App\Services;

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
                'primary_color' => '7C3AED',    // Royal Navy
                'secondary_color' => 'F43F5E',  // Electric Blue
                'accent_color' => 'FDE047',     // Amber Gold
                'bg_color' => 'FDF2F8',         // Soft Ice Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FCE7F3',
                'style_type' => 'tropical_pop',
            ],
            [
                'key' => 'government-formal',
                'name' => 'Batik Nusantara',
                'description' => 'Identitas hijau zamrud mewah dan emas prestisius berkarakter dinas resmi.',
                'primary_color' => '166534',    // Deep Emerald Pine
                'secondary_color' => '16A34A',  // Radiant Emerald
                'accent_color' => 'F59E0B',     // Imperial Gold
                'bg_color' => 'FEF3C7',         // Soft Mint Sage Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FDE68A',
                'style_type' => 'batik_nusantara',
            ],
            [
                'key' => 'modern-clean',
                'name' => 'Comic Pop Art',
                'description' => 'Perpaduan segar toska samudra dengan aksen oranye terbenam yang energik.',
                'primary_color' => '0F766E',    // Deep Ocean Teal
                'secondary_color' => '06B6D4',  // Vivid Turquoise
                'accent_color' => 'FB923C',     // Sunset Orange
                'bg_color' => 'F0FDFA',         // Aquamarine Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'CFFAFE',
                'style_type' => 'comic_pop',
            ],
            [
                'key' => 'minimal-professional',
                'name' => 'Scrapbook Ceria',
                'description' => 'Desain tegas berwibawa dengan merah marun gelap dan aksen ruby menyala.',
                'primary_color' => 'BE123C',    // Deep Burgundy
                'secondary_color' => 'FB7185',  // Vivid Ruby
                'accent_color' => 'FBBF24',     // Warm Sand Gold
                'bg_color' => 'FFF7ED',         // Rose Blush Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FFE4E6',
                'style_type' => 'scrapbook',
            ],
            [
                'key' => 'executive-report',
                'name' => 'Memphis Geometry',
                'description' => 'Nuansa ultraviolet mewah dipadu koral menyala untuk presentasi pimpinan.',
                'primary_color' => '5B21B6',    // Royal Midnight Violet
                'secondary_color' => '8B5CF6',  // Electric Violet
                'accent_color' => 'F43F5E',     // Vibrant Coral
                'bg_color' => 'FDF4FF',         // Lilac Glow Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'EDE9FE',
                'style_type' => 'memphis',
            ],
            [
                'key' => 'photo-focus',
                'name' => 'Festival Nusantara',
                'description' => 'Kehangatan terakota bumi dengan aksen jingga sunset yang kaya warna.',
                'primary_color' => '9A3412',    // Rich Terracotta
                'secondary_color' => 'EA580C',  // Warm Sunset
                'accent_color' => 'FACC15',     // Golden Amber
                'bg_color' => 'FFF7ED',         // Warm Peach Sand Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FED7AA',
                'style_type' => 'festival',
            ],
            [
                'key' => 'elegant-report',
                'name' => 'Retro Groovy',
                'description' => 'Elegansi magenta anggur dengan aksen kuning sitrun menyala yang berani.',
                'primary_color' => '86198F',    // Deep Royal Plum
                'secondary_color' => 'D946EF',  // Vivid Magenta
                'accent_color' => 'A3E635',     // Bright Citron Gold
                'bg_color' => 'FDF4FF',         // Orchid Glow Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'F5D0FE',
                'style_type' => 'retro_groovy',
            ],
            [
                'key' => 'bpsdm-classic',
                'name' => 'Infografik Pelangi',
                'description' => 'Identitas institusional BPSDM bernuansa Deep Pine dan emas champagne.',
                'primary_color' => '1D4ED8',    // Deep Pine Institutional
                'secondary_color' => '06B6D4',  // Forest Pine
                'accent_color' => 'FDE047',     // Prestige Champagne Gold
                'bg_color' => 'F0F9FF',         // Warm Stone Canvas
                'card_bg' => 'FAF9F5',
                'card_alt' => 'DBEAFE',
                'style_type' => 'rainbow_infographic',
            ],
            [
                'key' => 'modern-government',
                'name' => 'Paper Cut Explosion',
                'description' => 'Kombinasi modern biru kobalt dingin dengan aksen jingga api kontras tinggi.',
                'primary_color' => '1E40AF',    // Slate Navy
                'secondary_color' => '38BDF8',  // Cobalt Sky
                'accent_color' => 'F97316',     // Blaze Orange
                'bg_color' => 'FFF7ED',         // Steel Ice Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FFE4D6',
                'style_type' => 'paper_cut',
            ],
            [
                'key' => 'clean-documentation',
                'name' => 'Botanical Maximalist',
                'description' => 'Tampilan kontemporer grafit gelap dengan aksen hijau neon volt yang tajam.',
                'primary_color' => '166534',    // Dark Graphite
                'secondary_color' => '65A30D',  // Slate Steel
                'accent_color' => 'FACC15',     // Electric Lime Volt
                'bg_color' => 'F7FEE7',         // Cool Stone Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'D9F99D',
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
