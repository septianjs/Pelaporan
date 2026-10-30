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
                'key' => '',
                'name' => '',
                'description' => '',
                'primary_color' => '',    // Jungle Emerald
                'secondary_color' => '',  // Lagoon Turquoise
                'accent_color' => '',     // Coral Bloom
                'bg_color' => '',         // Cream Blossom
                'card_bg' => '',
                'card_alt' => '',         // Banana Yellow
                'style_type' => '',
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
        $dir  = storage_path('app/public/template-previews');
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
