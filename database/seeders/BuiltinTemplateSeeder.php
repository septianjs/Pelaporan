<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Services\BuiltinTemplateGeneratorService;
use Illuminate\Database\Seeder;

class BuiltinTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
                    'pptx_path' => $item['pptx_path'],
                    'preview_path' => $item['preview_path'],
                    'active' => true,
                ]
            );
        }
    }
}

