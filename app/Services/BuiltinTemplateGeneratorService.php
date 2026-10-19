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
                'name' => 'Tropis Nusantara',
                'description' => 'Daun tropis, bingkai dekoratif, dan panel foto lebar.',
                'primary_color' => '166534',    // Royal Navy
                'secondary_color' => '15803D',  // Electric Blue
                'accent_color' => 'D97706',     // Amber Gold
                'bg_color' => 'F0FDF4',         // Soft Ice Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'DCFCE7',
                'style_type' => 'tropical_pop',
            ],
            [
                'key' => 'government-formal',
                'name' => 'Batik Nusantara',
                'description' => 'Ornamen tradisional sebagai bingkai dan aksen sudut dengan warna hijau dan emas.',
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
                'name' => 'Infografis Geometris',
                'description' => 'Angka dan panel tegas, komposisi asimetris, serta bentuk geometris.',
                'primary_color' => '0F766E',    // Deep Ocean Teal
                'secondary_color' => '06B6D4',  // Vivid Turquoise
                'accent_color' => 'F97316',     // Sunset Orange
                'bg_color' => 'F0FDFA',         // Aquamarine Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'CFFAFE',
                'style_type' => 'comic_pop',
            ],
            [
                'key' => 'minimal-professional',
                'name' => 'Jurnal Dokumentasi',
                'description' => 'Kolase dokumentasi, label keterangan, dan bingkai seperti jurnal.',
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
                'name' => 'Merah Putih Institusional',
                'description' => 'Tata letak resmi simetris dengan dominasi merah dan putih.',
                'primary_color' => '991B1B',    // Royal Midnight Violet
                'secondary_color' => 'DC2626',  // Electric Violet
                'accent_color' => 'FCA5A5',     // Vibrant Coral
                'bg_color' => 'FFFFFF',         // Lilac Glow Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FEE2E2',
                'style_type' => 'memphis',
            ],
            [
                'key' => 'photo-focus',
                'name' => 'Eco Green Report',
                'description' => 'Tema lingkungan dengan palet hijau dan indikator kondisi kebersihan.',
                'primary_color' => '166534',    // Rich Terracotta
                'secondary_color' => '16A34A',  // Warm Sunset
                'accent_color' => '84CC16',     // Golden Amber
                'bg_color' => 'F0FDF4',         // Warm Peach Sand Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'DCFCE7',
                'style_type' => 'festival',
            ],
            [
                'key' => 'elegant-report',
                'name' => 'Arsip Klasik',
                'description' => 'Warna kertas arsip, bingkai klasik, dan aksen dokumentasi formal.',
                'primary_color' => '78350F',    // Deep Royal Plum
                'secondary_color' => '92400E',  // Vivid Magenta
                'accent_color' => 'D6A756',     // Bright Citron Gold
                'bg_color' => 'FFFBEB',         // Orchid Glow Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FEF3C7',
                'style_type' => 'retro_groovy',
            ],
            [
                'key' => 'bpsdm-classic',
                'name' => 'Majalah Editorial',
                'description' => 'Sampul foto dominan, judul kuat, dan komposisi editorial.',
                'primary_color' => '0F172A',    // Deep Pine Institutional
                'secondary_color' => '0EA5E9',  // Forest Pine
                'accent_color' => '38BDF8',     // Prestige Champagne Gold
                'bg_color' => 'F8FAFC',         // Warm Stone Canvas
                'card_bg' => 'FAF9F5',
                'card_alt' => 'E0F2FE',
                'style_type' => 'rainbow_infographic',
            ],
            [
                'key' => 'modern-government',
                'name' => 'Blueprint Inspeksi',
                'description' => 'Gaya teknis biru dengan garis kisi dan bingkai inspeksi.',
                'primary_color' => '1E40AF',    // Slate Navy
                'secondary_color' => '1D4ED8',  // Cobalt Sky
                'accent_color' => '38BDF8',     // Blaze Orange
                'bg_color' => 'EFF6FF',         // Steel Ice Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'DBEAFE',
                'style_type' => 'paper_cut',
            ],
            [
                'key' => 'clean-documentation',
                'name' => 'Emerald Gold Ornamental',
                'description' => 'Bingkai berlapis dengan hijau zamrud dan ornamen emas simetris.',
                'primary_color' => '064E3B',    // Dark Graphite
                'secondary_color' => '047857',  // Slate Steel
                'accent_color' => 'D4AF37',     // Electric Lime Volt
                'bg_color' => 'ECFDF5',         // Cool Stone Canvas
                'card_bg' => 'FFFFFF',
                'card_alt' => 'D1FAE5',
                'style_type' => 'botanical',
            ],
        ];
    }

    /**
     * Generate preview thumbnails for all ten PDF themes.
     */
    /**
     * Build only PDF template previews. The application no longer creates PowerPoint files.
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
        if (!extension_loaded('gd')) {
            return;
        }

        $w = 640;
        $h = 360;
        $img = imagecreatetruecolor($w, $h);
        imageantialias($img, true);

        $hexColor = static function (string $hex) use ($img): int {
            $hex = ltrim($hex, '#');
            return imagecolorallocate($img,
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            );
        };

        $bg = $hexColor($template['bg_color']);
        $primary = $hexColor($template['primary_color']);
        $secondary = $hexColor($template['secondary_color']);
        $accent = $hexColor($template['accent_color']);
        $white = imagecolorallocate($img, 255, 255, 255);
        $ink = imagecolorallocate($img, 30, 41, 59);
        $muted = imagecolorallocate($img, 100, 116, 139);
        $pale = $hexColor($template['card_alt'] ?? $template['bg_color']);

        imagefilledrectangle($img, 0, 0, $w, $h, $bg);
        $style = $template['style_type'] ?? 'tropical_pop';

        // Distinct graphic signatures per PDF theme; previews mirror each design family.
        if ($style === 'tropical_pop') {
            imagefilledellipse($img, 610, 24, 120, 120, $accent);
            imagefilledellipse($img, 570, 12, 72, 72, $secondary);
            imagefilledrectangle($img, 0, 0, $w, 58, $primary);
        } elseif ($style === 'batik_nusantara') {
            imagefilledrectangle($img, 0, 0, $w, 58, $primary);
            for ($x = 8; $x < $w; $x += 24) {
                imageline($img, $x, 60, $x + 20, 82, $accent);
                imageline($img, $x + 20, 60, $x, 82, $accent);
            }
        } elseif ($style === 'comic_pop') {
            imagefilledrectangle($img, 0, 0, $w, 62, $primary);
            imagefilledellipse($img, 570, 30, 110, 110, $accent);
            imagefilledellipse($img, 570, 30, 68, 68, $secondary);
        } elseif ($style === 'scrapbook') {
            imagefilledrectangle($img, 0, 0, $w, 58, $primary);
            imagefilledrectangle($img, 480, 5, 630, 49, $accent);
            imagerectangle($img, 12, 70, 310, 230, $accent);
        } elseif ($style === 'memphis') {
            imagefilledrectangle($img, 0, 0, $w, 58, $primary);
            imagefilledrectangle($img, 585, 0, $w, 100, $accent);
            imagefilledellipse($img, 580, 26, 62, 62, $secondary);
            imageline($img, 0, 63, $w, 63, $accent);
        } elseif ($style === 'festival') {
            imagefilledrectangle($img, 0, 0, $w, 66, $primary);
            imagefilledrectangle($img, 0, 58, $w, 66, $accent);
            for ($x = 20; $x < $w; $x += 70) {
                imagefilledellipse($img, $x, 82, 18, 18, $secondary);
            }
        } elseif ($style === 'retro_groovy') {
            imagefilledrectangle($img, 0, 0, $w, 58, $primary);
            imagefilledellipse($img, 560, 28, 120, 120, $accent);
            imagefilledellipse($img, 560, 28, 80, 80, $secondary);
            imagefilledellipse($img, 560, 28, 40, 40, $primary);
        } elseif ($style === 'rainbow_infographic') {
            imagefilledrectangle($img, 0, 0, $w, 58, $primary);
            $colors = [$accent, $secondary, $primary, $muted, $pale];
            foreach ($colors as $i => $color) {
                imagefilledrectangle($img, $i * 128, 58, ($i + 1) * 128, 66, $color);
            }
        } elseif ($style === 'paper_cut') {
            imagefilledrectangle($img, 0, 0, $w, 58, $primary);
            imagefilledrectangle($img, 12, 70, 310, 230, $secondary);
            imagefilledrectangle($img, 24, 82, 298, 218, $white);
            imagefilledrectangle($img, 330, 70, 628, 230, $accent);
        } else {
            imagefilledrectangle($img, 0, 0, $w, 58, $primary);
            imagefilledellipse($img, 615, 26, 90, 90, $accent);
            imagefilledellipse($img, 585, 45, 56, 56, $secondary);
        }

        imagestring($img, 2, 16, 10, 'BPSDM • LAPORAN PENGAWASAN LINGKUNGAN', $white);
        $label = strtoupper($template['name']);
        if (strlen($label) > 36) $label = substr($label, 0, 34) . '..';
        imagestring($img, 4, 16, 31, $label, $white);

        // Photo and information blocks. Placement shifts by design family.
        if (in_array($style, ['paper_cut', 'comic_pop', 'memphis'], true)) {
            imagefilledrectangle($img, 18, 92, 405, 250, $white);
            imagefilledrectangle($img, 28, 102, 395, 240, $pale);
            imagefilledrectangle($img, 420, 92, 622, 250, $secondary);
            imagefilledrectangle($img, 432, 104, 610, 238, $white);
        } elseif (in_array($style, ['scrapbook', 'batik_nusantara'], true)) {
            imagefilledrectangle($img, 18, 92, 315, 250, $white);
            imagerectangle($img, 18, 92, 315, 250, $accent);
            imagefilledrectangle($img, 326, 92, 622, 250, $white);
            imagerectangle($img, 326, 92, 622, 250, $accent);
        } else {
            imagefilledrectangle($img, 18, 92, 315, 250, $white);
            imagefilledrectangle($img, 326, 92, 622, 250, $white);
            imagefilledrectangle($img, 26, 100, 307, 242, $pale);
            imagefilledrectangle($img, 334, 100, 614, 242, $pale);
        }
        imagestring($img, 2, 88, 160, 'FOTO LOKASI 1', $muted);
        imagestring($img, 2, 397, 160, 'FOTO LOKASI 2', $muted);

        // Full-width findings panel with colorful status and section accents.
        imagefilledrectangle($img, 18, 260, 622, 324, $white);
        imagefilledrectangle($img, 18, 260, 622, 278, $primary);
        imagefilledrectangle($img, 28, 288, 210, 314, $pale);
        imagefilledrectangle($img, 220, 288, 410, 314, $pale);
        imagefilledrectangle($img, 420, 288, 612, 314, $pale);
        imagestring($img, 2, 28, 264, 'STATUS • CATATAN • PERMASALAHAN • SOLUSI', $white);
        imagefilledrectangle($img, 0, 336, $w, $h, $primary);
        imagefilledrectangle($img, 0, 332, $w, 336, $accent);
        imagestring($img, 2, 16, 344, 'PDF WIDESCREEN 16:9 • 6 HALAMAN', $white);

        $directory = dirname($savePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        imagepng($img, $savePath);
        imagedestroy($img);
    }

}
