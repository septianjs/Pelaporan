<?php

namespace Tests\Unit;

use App\Services\BuiltinTemplateGeneratorService;
use App\Services\PdfTemplateCssService;
use PHPUnit\Framework\TestCase;

/**
 * Menjaga agar desain template berasal dari satu sumber:
 * resources/css/pdf/base.css + template-NN.css (NN = urutan template di katalog).
 */
class PdfTemplateCssServiceTest extends TestCase
{
    private PdfTemplateCssService $css;

    protected function setUp(): void
    {
        $this->css = new PdfTemplateCssService(dirname(__DIR__, 2) . '/resources/css/pdf');
    }

    private function keys(): array
    {
        return array_column(BuiltinTemplateGeneratorService::getTemplateDefinitions(), 'key');
    }

    public function test_katalog_punya_sepuluh_template_unik(): void
    {
        $this->assertCount(10, $this->keys());
        $this->assertCount(10, array_unique($this->keys()));
    }

    public function test_setiap_template_punya_file_css_sesuai_urutan(): void
    {
        $this->assertFileExists(dirname(__DIR__, 2) . '/resources/css/pdf/base.css');

        foreach ($this->keys() as $index => $key) {
            $this->assertSame($index + 1, $this->css->templateNumber($key));
            $this->assertFileExists(
                $this->css->templateFile($index + 1),
                "Template '{$key}' tidak punya file CSS (template-" . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . '.css).'
            );
        }
    }

    public function test_file_css_tanpa_template_katalog_tidak_yatim(): void
    {
        $files = glob(dirname(__DIR__, 2) . '/resources/css/pdf/template-*.css');

        $this->assertCount(count($this->keys()), $files, 'Jumlah template-NN.css harus sama dengan jumlah template di katalog.');
    }

    public function test_key_tidak_dikenal_memakai_template_pertama(): void
    {
        $this->assertSame(1, $this->css->templateNumber('tidak-ada'));
        $this->assertSame(1, $this->css->templateNumber(null));
    }

    public function test_css_final_tidak_menyisakan_var_dan_memuat_base_serta_template(): void
    {
        foreach ($this->keys() as $key) {
            $final = $this->css->css($key);

            $this->assertStringNotContainsString('var(', $final, "CSS '{$key}' masih punya var() yang belum diganti (DomPDF tidak mendukungnya).");
            $this->assertStringContainsString('BASE PDF REPORT', $final);
            $this->assertStringContainsString('.cover-header', $final);
        }
    }

    public function test_ukuran_foto_dibaca_dari_css_dengan_aturan_cascade(): void
    {
        // template-01 menimpa base untuk kedua foto.
        $one = $this->css->photoSizes($this->keys()[0]);
        $this->assertSame([550, 300], $one['officer']);
        $this->assertSame([410, 205], $one['location']);

        // template-02 hanya menimpa foto lokasi; foto petugas mewarisi base.css.
        $two = $this->css->photoSizes($this->keys()[1]);
        $this->assertSame([560, 300], $two['officer']);
        $this->assertSame([405, 220], $two['location']);
    }

    public function test_setiap_template_punya_ukuran_foto_dan_warna_utama_valid(): void
    {
        foreach ($this->keys() as $key) {
            $sizes = $this->css->photoSizes($key);

            foreach (['officer', 'location'] as $name) {
                $this->assertGreaterThan(0, $sizes[$name][0]);
                $this->assertGreaterThan(0, $sizes[$name][1]);
            }
            $this->assertMatchesRegularExpression('/^[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', (string) $this->css->primaryColor($key), "Warna utama '{$key}' tidak terbaca dari .cover-header.");
        }
    }
}
