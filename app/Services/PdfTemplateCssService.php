<?php

namespace App\Services;

/**
 * Pintu tunggal ke desain template laporan.
 *
 * SUMBER DESAIN = resources/css/pdf/base.css + resources/css/pdf/template-NN.css.
 * Semua hal yang bergantung pada desain diturunkan dari file-file itu, bukan
 * ditulis ulang di PHP/Vue:
 *   - CSS final untuk PDF         -> css()
 *   - warna tema (--theme-*)      -> variables()
 *   - ukuran foto (untuk crop)    -> photoSizes()
 *   - nomor file template         -> templateNumber()
 *
 * Nomor file = urutan template di BuiltinTemplateGeneratorService::getTemplateDefinitions()
 * (urutan ke-1 => template-01.css, dst). Jangan ubah urutan tanpa menukar nama file CSS.
 */
class PdfTemplateCssService
{
    public const DEFAULT_NUMBER = 1;

    protected string $cssDir;

    /** @var array<string,string> cache raw css per nomor template */
    protected array $rawCache = [];

    public function __construct(?string $cssDir = null)
    {
        $this->cssDir = rtrim(
            $cssDir ?? (function_exists('resource_path')
                ? resource_path('css/pdf')
                : dirname(__DIR__, 2) . '/resources/css/pdf'),
            '/\\'
        );
    }

    /**
     * Nomor template (1..N) dari template_key. Key tidak dikenal/null jatuh ke
     * template pertama, supaya tidak pernah membuat PDF "tanpa desain".
     */
    public function templateNumber(?string $templateKey): int
    {
        foreach (BuiltinTemplateGeneratorService::getTemplateDefinitions() as $index => $definition) {
            if ($definition['key'] === $templateKey) {
                return $index + 1;
            }
        }

        return self::DEFAULT_NUMBER;
    }

    public function templateFile(int $number): string
    {
        return $this->cssDir . '/template-' . str_pad((string) $number, 2, '0', STR_PAD_LEFT) . '.css';
    }

    /** base.css + template-NN.css, apa adanya (variabel var() belum diganti). */
    public function rawCss(?string $templateKey): string
    {
        $number = $this->templateNumber($templateKey);

        if (!isset($this->rawCache[$number])) {
            $parts = [];
            foreach ([$this->cssDir . '/base.css', $this->templateFile($number)] as $path) {
                if (is_file($path)) {
                    $parts[] = file_get_contents($path);
                }
            }
            $this->rawCache[$number] = implode("\n", $parts);
        }

        return $this->rawCache[$number];
    }

    /**
     * Nilai variabel CSS (mis. ['--theme-primary' => '#172554']). Definisi yang
     * muncul belakangan (di template) menimpa yang di base.
     */
    public function variables(?string $templateKey): array
    {
        preg_match_all('/(--[\w-]+)\s*:\s*([^;{}]+)\s*;/', $this->rawCss($templateKey), $matches, PREG_SET_ORDER);

        $variables = [];
        foreach ($matches as $match) {
            $variables[$match[1]] = trim($match[2]);
        }

        return $variables;
    }

    /**
     * CSS final untuk DomPDF: var(--x) diganti nilai konkret (DomPDF tidak
     * mendukung variabel CSS).
     */
    public function css(?string $templateKey): string
    {
        $variables = $this->variables($templateKey);

        return preg_replace_callback(
            '/var\(\s*(--[\w-]+)\s*(?:,\s*([^()]+))?\)/',
            function ($m) use ($variables) {
                if (isset($variables[$m[1]])) {
                    return $variables[$m[1]];
                }

                return isset($m[2]) ? trim($m[2]) : $m[0];
            },
            $this->rawCss($templateKey)
        );
    }

    /**
     * Ukuran foto (lebar, tinggi dalam pt) sebagaimana ditentukan CSS template,
     * dengan aturan cascade: aturan yang muncul belakangan menimpa per-properti
     * (base dulu, lalu template). Dipakai untuk crop server-side supaya ukuran
     * crop = ukuran tampil (DomPDF tidak mendukung object-fit).
     *
     * @return array{officer: array{0:int,1:int}, location: array{0:int,1:int}}
     */
    public function photoSizes(?string $templateKey): array
    {
        $css = preg_replace('~/\*.*?\*/~s', '', $this->css($templateKey));

        $targets = [
            'officer'  => '.cover-photo-img',
            'location' => '.photo-img',
        ];
        $sizes = ['officer' => [560, 300], 'location' => [400, 220]]; // setara base.css

        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER);

        foreach ($rules as $rule) {
            $selectors = array_map('trim', explode(',', $rule[1]));

            foreach ($targets as $name => $selector) {
                if (!in_array($selector, $selectors, true)) {
                    continue;
                }
                if (preg_match('/(?<![\w-])width\s*:\s*([\d.]+)pt/', $rule[2], $w)) {
                    $sizes[$name][0] = (int) round((float) $w[1]);
                }
                if (preg_match('/(?<![\w-])height\s*:\s*([\d.]+)pt/', $rule[2], $h)) {
                    $sizes[$name][1] = (int) round((float) $h[1]);
                }
            }
        }

        return $sizes;
    }

    /**
     * Warna utama desain (hex tanpa '#'), diambil dari background .cover-header di
     * CSS template (nilai terakhir menang). Dipakai untuk aksen kecil di blade
     * (nama petugas) agar warnanya selalu cocok dengan desain, bukan dengan
     * palet di PHP. Null bila tidak ditemukan.
     */
    public function primaryColor(?string $templateKey): ?string
    {
        $css = preg_replace('~/\*.*?\*/~s', '', $this->css($templateKey));
        $color = null;

        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER);
        foreach ($rules as $rule) {
            if (!in_array('.cover-header', array_map('trim', explode(',', $rule[1])), true)) {
                continue;
            }
            if (preg_match('/(?<![\w-])background(?:-color)?\s*:[^;]*?(#[0-9a-fA-F]{6}|#[0-9a-fA-F]{3})\b/', $rule[2], $m)) {
                $color = ltrim($m[1], '#');
            }
        }

        return $color;
    }
}
