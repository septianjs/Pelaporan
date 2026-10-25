<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Report;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Versi PPTX dari ReportPdfService. Antarmuka sama dengan yang lama:
 * generatePdf(Report): string (path lengkap PDF) dan mengisi kolom pdf_path.
 * Desain tidak lagi dari CSS/Blade, tetapi dari file template PPTX.
 */
class ReportPdfService
{
    public function __construct(protected PptxReportService $pptx)
    {
    }

    public function generatePdf(Report $report): string
    {
        @set_time_limit(300); // LibreOffice bisa >30 detik pada run pertama

        $report->loadMissing(['template', 'reportLocations.location', 'reportLocations.reportPhotos']);

        $outputDir = storage_path("app/public/reports/{$report->id}");
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $cleanReportNum = preg_replace('/[^A-Za-z0-9_\-]/', '_', $report->report_number);
        $outputFileName = "Laporan_{$cleanReportNum}.pdf";
        $outputPath = "{$outputDir}/{$outputFileName}";
        $relativeStoragePath = "reports/{$report->id}/{$outputFileName}";

        $this->pptx->generatePdf($this->buildData($report), $this->templateKey($report), $outputPath);

        $report->update(['pdf_path' => $relativeStoragePath]);

        return $outputPath;
    }

    /** Pilih file PPTX: utamakan template_key di database, cadangan: id angka di config. */
    protected function templateKey(Report $report): string
    {
        $cfg = config('report_templates.templates');

        $dbKey = $report->template?->template_key;
        if ($dbKey && isset($cfg[$dbKey])) {
            return $dbKey;
        }

        $key = collect($cfg)->search(fn ($t) => isset($t['id']) && (int) $t['id'] === (int) $report->template_id);
        if ($key !== false) {
            return $key;
        }

        $fallback = array_key_first($cfg);
        Log::warning("Report #{$report->id}: template tidak punya file PPTX, memakai {$fallback}.");
        return $fallback;
    }

    protected function buildData(Report $report): array
    {
        $pub = Storage::disk('public');
        $abs = function (?string $rel) use ($pub) {
            return ($rel && $pub->exists($rel)) ? $pub->path($rel) : null;
        };

        // "Rabu, 21 Oktober 2026" -> hari = Rabu, tanggal = 21 Oktober 2026
        $tanggal = trim(explode(',', (string) $report->formatted_date, 2)[1] ?? '');

        // Selalu tepat 5 lokasi (6 halaman: sampul + 5), sama seperti perilaku lama.
        $rows = $report->reportLocations->values();
        $masters = Location::orderBy('sort_order')->limit(5)->get();
        $lokasi = [];

        for ($i = 0; $i < 5; $i++) {
            $rl = $rows->get($i);

            if (!$rl) {
                $lokasi[] = [
                    'nama'    => $masters->get($i)->name ?? 'Lokasi ' . ($i + 1),
                    'status'  => 'normal',
                    'kondisi' => 'Data belum diisi',
                    'catatan' => 'Data pemantauan untuk titik ini belum diisi.',
                    'masalah' => null,
                    'solusi'  => null,
                    'foto'    => [null, null],
                ];
                continue;
            }

            $clean  = $rl->condition !== 'needs_attention';
            $photos = $rl->reportPhotos->keyBy('photo_type');

            $lokasi[] = [
                'nama'    => $rl->display_name ?: $rl->name,
                'status'  => $clean ? 'normal' : 'tidak_normal',
                'kondisi' => $rl->condition_label ?: ($clean ? 'Bersih & Rapi' : 'Perlu Penanganan'),
                'catatan' => $rl->notes,
                'masalah' => $rl->problem,
                'solusi'  => $rl->solution,
                'foto'    => [
                    $abs($photos['photo_1']->file_path ?? null),
                    $abs($photos['photo_2']->file_path ?? null),
                ],
            ];
        }

        return [
            'hari'         => $report->day_name,
            'tanggal'      => $tanggal,
            'tim'          => $report->officer_name,
            'foto_petugas' => $abs($report->officer_photo),
            'lokasi'       => $lokasi,
        ];
    }
}