<?php

namespace Tests\Unit;

use App\Services\BuiltinTemplateGeneratorService;
use PHPUnit\Framework\TestCase;

/**
 * Menjaga agar pilihan template -> pratinjau -> PDF memakai desain yang sama.
 * Desain tiap template adalah kelas ".theme-{style_type}" di
 * resources/css/report-templates.css (dipakai blade PDF dan aplikasi).
 */
class TemplateThemeConsistencyTest extends TestCase
{
    private function css(): string
    {
        return file_get_contents(dirname(__DIR__, 2) . '/resources/css/report-templates.css');
    }

    public function test_ada_sepuluh_template_dengan_key_dan_style_type_unik(): void
    {
        $definitions = BuiltinTemplateGeneratorService::getTemplateDefinitions();

        $this->assertCount(10, $definitions);
        $this->assertCount(10, array_unique(array_column($definitions, 'key')));
        $this->assertCount(10, array_unique(array_column($definitions, 'style_type')));
    }

    public function test_setiap_style_type_punya_desain_css(): void
    {
        $css = $this->css();

        foreach (BuiltinTemplateGeneratorService::getTemplateDefinitions() as $definition) {
            $this->assertStringContainsString(
                '.theme-' . $definition['style_type'] . ' ',
                $css,
                "Template '{$definition['key']}' memakai style_type '{$definition['style_type']}' tetapi tidak ada desain CSS-nya."
            );
        }
    }

    public function test_css_tidak_punya_desain_yatim(): void
    {
        preg_match_all('/\.theme-([a-z_]+)\s/', $this->css(), $matches);
        $styleTypes = array_column(BuiltinTemplateGeneratorService::getTemplateDefinitions(), 'style_type');

        foreach (array_unique($matches[1]) as $name) {
            $this->assertContains($name, $styleTypes, "CSS .theme-{$name} tidak dipakai template mana pun.");
        }
    }

    public function test_resolve_theme_mengembalikan_data_yang_sama_untuk_key_yang_sama(): void
    {
        foreach (BuiltinTemplateGeneratorService::getTemplateDefinitions() as $definition) {
            $theme = BuiltinTemplateGeneratorService::resolveTheme($definition['key']);

            $this->assertSame($definition['key'], $theme['key']);
            $this->assertSame($definition['style_type'], $theme['style_type']);
            $this->assertSame($definition['primary_color'], $theme['primary_color']);
            $this->assertSame($definition['accent_color'], $theme['accent_color']);
            $this->assertCount(2, $theme['photo']['officer']);
            $this->assertCount(2, $theme['photo']['location']);
        }
    }

    public function test_key_tidak_dikenal_jatuh_ke_template_pertama_bukan_tema_tanpa_desain(): void
    {
        $first = BuiltinTemplateGeneratorService::getTemplateDefinitions()[0];

        $this->assertSame($first['style_type'], BuiltinTemplateGeneratorService::resolveTheme('tidak-ada')['style_type']);
        $this->assertSame($first['style_type'], BuiltinTemplateGeneratorService::resolveTheme(null)['style_type']);
    }

    public function test_ukuran_foto_tema_sesuai_dengan_yang_dipakai_desain(): void
    {
        $this->assertSame([550, 205], BuiltinTemplateGeneratorService::photoSpecs('memphis')['officer']);
        $this->assertSame([300, 158], BuiltinTemplateGeneratorService::photoSpecs('paper_cut')['location']);
        $this->assertSame([560, 300], BuiltinTemplateGeneratorService::photoSpecs('botanical')['officer']);
        $this->assertSame([400, 220], BuiltinTemplateGeneratorService::photoSpecs('botanical')['location']);
    }
}
