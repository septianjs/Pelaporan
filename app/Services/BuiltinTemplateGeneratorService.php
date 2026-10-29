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
                'description' => 'Komposisi tropis dengan dedaunan, asimetris, dan warna-warna botani cerah.',
                'primary_color' => '176B45',    // Jungle Emerald
                'secondary_color' => '35B8AC',  // Lagoon Turquoise
                'accent_color' => 'F47768',     // Coral Bloom
                'bg_color' => 'FFF3D8',         // Cream Blossom
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FFD45A',         // Banana Yellow
                'style_type' => 'tropical_pop',
            ],
            [
                'key' => 'government-formal',
                'name' => 'Batik Nusantara',
                'description' => 'Bahasa visual ornamen warisan dengan bingkai bermotif bunga dan warna-warna yang hidup.',
                'primary_color' => '75452D',    // Sogan Cocoa
                'secondary_color' => '246D78',  // Indigo Teal
                'accent_color' => 'E8B84D',     // Marigold Gold
                'bg_color' => 'FFF4DC',         // Ivory Petal
                'card_bg' => 'FFFFFF',
                'card_alt' => '4F936B',         // Jade Leaf
                'style_type' => 'batik_nusantara',
            ],
            [
                'key' => 'modern-clean',
                'name' => 'Infografis Geometris',
                'description' => 'Grafis informasi berilustrasi ceria, bentuk geometris tak beraturan, dan pita anotasi.',
                'primary_color' => '40C9C3',    // Aqua Splash
                'secondary_color' => 'E85D91',  // Berry Pink
                'accent_color' => 'F47B42',     // Tangerine Pop
                'bg_color' => 'FFF7E7',         // Cloud Cream
                'card_bg' => 'FFFFFF',
                'card_alt' => 'FFD84D',         // Sunshine Lemon
                'style_type' => 'comic_pop',
            ],
            [
                'key' => 'minimal-professional',
                'name' => 'Jurnal Dokumentasi',
                'description' => 'Jurnal foto kreatif penuh warna dengan bentuk mirip kertas berlapis dan label anotasi.',
                'primary_color' => '719AE8',    // Cornflower Blue
                'secondary_color' => 'B9A5E8',  // Lilac Haze
                'accent_color' => 'F28C5B',     // Papaya Orange
                'bg_color' => 'FFF5E5',         // Warm Paper
                'card_bg' => 'FFFFFF',
                'card_alt' => 'A8DDB5',         // Mint Leaf
                'style_type' => 'scrapbook',
            ],
            [
                'key' => 'executive-report',
                'name' => 'Merah Putih Institusional',
                'description' => 'Perayaan flora Indonesia dengan palet merah-putih, pita mengalir, dan bunga.',
                'primary_color' => 'D93643',    // Flag Crimson
                'secondary_color' => '3F8A58',  // Leaf Green
                'accent_color' => 'F17F73',     // Rosy Coral
                'bg_color' => 'FFF9EE',         // Jasmine White
                'card_bg' => 'FFFFFF',
                'card_alt' => 'F4C451',         // Golden Sunrise
                'style_type' => 'memphis',
            ],
            [
                'key' => 'photo-focus',
                'name' => 'Eco Green Report',
                'description' => 'Penceritaan botani yang melimpah dengan batas dedaunan berlapis dan elemen alam.',
                'primary_color' => '245A3B',    // Forest Canopy
                'secondary_color' => '5AA9A2',  // River Blue
                'accent_color' => 'C96E4B',     // Clay Terracotta
                'bg_color' => 'F6EBD5',         // Oat Cream
                'card_bg' => 'FFFFFF',
                'card_alt' => '7B9D55',         // Moss Green
                'style_type' => 'festival',
            ],
            [
                'key' => 'elegant-report',
                'name' => 'Arsip Klasik',
                'description' => 'Buku catatan lapangan berilustrasi ceria dengan penanda observasi dan tab.',
                'primary_color' => '287F83',    // Notebook Teal
                'secondary_color' => '8C9CE6',  // Periwinkle
                'accent_color' => 'F4A261',     // Apricot Orange
                'bg_color' => 'FFF7E9',         // Paper White
                'card_bg' => 'FFFFFF',
                'card_alt' => '69A96B',         // Fresh Fern
                'style_type' => 'retro_groovy',
            ],
            [
                'key' => 'bpsdm-classic',
                'name' => 'Majalah Editorial',
                'description' => 'Majalah berilustrasi ekspresif dengan ukuran foto bervariasi dan sapuan dekoratif.',
                'primary_color' => '218C8D',    // Ocean Teal
                'secondary_color' => '9867C5',  // Violet Bloom
                'accent_color' => 'F59E42',     // Mango Orange
                'bg_color' => 'FFF3D9',         // Vanilla Cream
                'card_bg' => 'FFFFFF',
                'card_alt' => 'F06467',         // Electric Coral
                'style_type' => 'rainbow_infographic',
            ],
            [
                'key' => 'modern-government',
                'name' => 'Blueprint Inspeksi',
                'description' => 'Observasi lapangan berilustrasi dengan penanda lokasi, jalur putus-putus, dan simbol inspeksi.',
                'primary_color' => '38A6B8',    // Lagoon Blue
                'secondary_color' => '4E9669',  // Garden Green
                'accent_color' => 'F27D65',     // Papaya Coral
                'bg_color' => 'FFF4DF',         // Soft Sand
                'card_bg' => 'FFFFFF',
                'card_alt' => 'F4CB55',         // Sunflower Yellow
                'style_type' => 'paper_cut',
            ],
            [
                'key' => 'clean-documentation',
                'name' => 'Emerald Gold Ornamental',
                'description' => 'Taman botani zamrud dengan bingkai bunga berlapis, sulur, dan sentuhan emas.',
                'primary_color' => '176B50',    // Emerald Velvet
                'secondary_color' => '76B99A',  // Jade Blossom
                'accent_color' => 'ED8179',     // Peony Coral
                'bg_color' => 'FFF4DD',         // Pearl Cream
                'card_bg' => 'FFFFFF',
                'card_alt' => 'E7BD57',         // Marigold Gold
                'style_type' => 'botanical',
            ],
        ];
    }

    /**
     * Ambil definisi tema berdasarkan template_key. Key yang tidak dikenal
     * jatuh ke template pertama (bukan ke tema "tanpa desain") sehingga PDF
     * dan aplikasi selalu memakai desain yang sama untuk key yang sama.
     */
    public static function findDefinition(?string $key): array
    {
        $definitions = self::getTemplateDefinitions();
        foreach ($definitions as $definition) {
            if ($definition['key'] === $key) {
                return $definition;
            }
        }

        return $definitions[0];
    }

    /**
     * Tema lengkap untuk satu template: warna (tanpa '#'), style_type, dan
     * ukuran foto. Dipakai oleh PDF (ReportPdfService) DAN dikirim ke aplikasi
     * lewat atribut Template::theme, jadi pilihan -> pratinjau -> PDF identik.
     */
    public static function resolveTheme(?string $key): array
    {
        $definition = self::findDefinition($key);
        $styleType = $definition['style_type'];
        $css = new PdfTemplateCssService();

        return [
            'key'             => $definition['key'],
            'style_type'      => $styleType,
            'primary_color'   => $definition['primary_color'],
            'secondary_color' => $definition['secondary_color'],
            'accent_color'    => $definition['accent_color'],
            'bg_color'        => $definition['bg_color'],
            'card_bg'         => $definition['card_bg'],
            'card_alt'        => $definition['card_alt'],
            'number'          => $css->templateNumber($key),
            'photo'           => $css->photoSizes($key),
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

    /**
     * Pastikan gambar pratinjau untuk satu template ada di disk, lalu kembalikan path-nya.
     * Dibuat otomatis (GD) jika belum ada, sehingga tidak bergantung pada seeder,
     * `php artisan storage:link`, maupun LibreOffice. Key tak dikenal memakai tema pertama,
     * sama seperti PDF (lihat findDefinition).
     */
    public function ensurePreview(?string $key): ?string
    {
        $definition = self::findDefinition($key);
        $dir  = storage_path('app/public/previews');
        $path = $dir . DIRECTORY_SEPARATOR . $definition['key'] . '.png';

        if (is_file($path) && filesize($path) > 0) {
            return $path;
        }
        if (!extension_loaded('gd')) {
            return null;
        }

        File::ensureDirectoryExists($dir);
        $this->createPreviewImage($definition, $path);

        return (is_file($path) && filesize($path) > 0) ? $path : null;
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

        imagestring($img, 2, 16, 10, 'BPSDM - LAPORAN PENGAWASAN LINGKUNGAN', $white);
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
        imagestring($img, 2, 28, 264, 'STATUS | CATATAN | PERMASALAHAN | SOLUSI', $white);
        imagefilledrectangle($img, 0, 336, $w, $h, $primary);
        imagefilledrectangle($img, 0, 332, $w, 336, $accent);
        imagestring($img, 2, 16, 344, 'PDF WIDESCREEN 16:9 - 6 HALAMAN', $white);

        $directory = dirname($savePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        imagepng($img, $savePath);
        imagedestroy($img);
    }

}
