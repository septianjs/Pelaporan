<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

/**
 * Membuat baris template untuk setiap key di config/report_templates.php
 * (template-01, template-02, ...). Endpoint /api/templates dan validasi simpan
 * laporan hanya menerima key tersebut, jadi tanpa seeder ini daftar template kosong
 * di database baru. Aman dijalankan berulang (tidak menimpa data lain).
 */
class PptxTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('report_templates.templates', []) as $key => $meta) {
            $template = Template::firstOrNew(['template_key' => $key]);
            $template->name         = $meta['name'];
            $template->description  = $meta['description'] ?? 'Desain dari file PPTX';
            $template->type         = $template->type ?: 'builtin';
            $template->pptx_path    = $meta['file'] ?? null;
            $template->preview_path = $template->preview_path ?: "template-previews/{$key}.png";
            $template->active       = true;
            $template->save();
        }
    }
}
