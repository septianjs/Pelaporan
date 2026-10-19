<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Services\BuiltinTemplateGeneratorService;
use Illuminate\Database\Seeder;

class BuiltinTemplateSeeder extends Seeder
{
    /** Seed built-in PDF themes and previews only. */
    public function run(): void
    {
        $generator = new BuiltinTemplateGeneratorService();
        $generated = $generator->generateAll();

        foreach ($generated as $item) {
            Template::updateOrCreate(
                ['template_key' => $item['key']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'type' => 'builtin',
                    'pptx_path' => null,
                    'preview_path' => $item['preview_path'],
                    'active' => true,
                ]
            );
        }

        // Legacy uploaded PPTX templates are no longer selectable in PDF-only mode.
        Template::where('type', 'uploaded')->update(['active' => false]);
    }
}
