<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportLocation;
use App\Models\ReportPhoto;
use App\Models\Template;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ReportPdfService
{
    /**
     * Generate 16:9 Widescreen PDF for a Report (13.333 x 7.5 inch / 960 x 540 pt)
     *
     * @param Report $report
     * @return string Full path to the generated PDF
     */
    public function generatePdf(Report $report): string
    {
        $report->loadMissing(['template', 'reportLocations.location', 'reportLocations.reportPhotos']);

        // 1. Resolve Theme
        $theme = $this->resolveTheme($report->template);

        // 2. Prepare Output Directory
        $outputDir = storage_path("app/public/reports/{$report->id}");
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $cleanReportNum = preg_replace('/[^A-Za-z0-9_\-]/', '_', $report->report_number);
        $outputFileName = "Laporan_{$cleanReportNum}.pdf";
        $outputPath = "{$outputDir}/{$outputFileName}";
        $relativeStoragePath = "reports/{$report->id}/{$outputFileName}";

        // 3. Prepare Photos Data (Base64 encoded for DomPDF reliability)
        $officerPhotoBase64 = $this->fileToBase64($report->officer_photo);

        $locationsData = [];
        foreach ($report->reportLocations as $idx => $reportLoc) {
            $photo1 = $reportLoc->reportPhotos->where('photo_type', 'photo_1')->first();
            $photo2 = $reportLoc->reportPhotos->where('photo_type', 'photo_2')->first();

            $locationsData[] = [
                'name' => $reportLoc->location->name ?? "Lokasi " . ($idx + 1),
                'condition' => $reportLoc->condition,
                'condition_label' => $reportLoc->condition_label,
                'notes' => $reportLoc->notes,
                'problem' => $reportLoc->problem,
                'solution' => $reportLoc->solution,
                'photo_1_base64' => $photo1 ? $this->fileToBase64($photo1->file_path) : null,
                'photo_2_base64' => $photo2 ? $this->fileToBase64($photo2->file_path) : null,
            ];
        }

        // 4. Render HTML View
        $html = view('pdf.report_16_9', [
            'report' => $report,
            'theme' => $theme,
            'locations' => $locationsData,
            'officerPhotoBase64' => $officerPhotoBase64,
        ])->render();

        // 5. Generate 16:9 PDF with DomPDF
        // 960pt x 540pt is exactly 13.333 x 7.5 inch (16:9 Widescreen Presentation)
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper([0, 0, 960, 540], 'landscape');
        $pdf->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 150,
            'defaultFont' => 'Helvetica',
        ]);

        $pdf->save($outputPath);

        // 6. Update Report Record
        $report->update([
            'pdf_path' => $relativeStoragePath,
        ]);

        return $outputPath;
    }

    /**
     * Resolve Color Palette and Theme based on Report Template
     */
    protected function resolveTheme(?Template $template): array
    {
        $defaultTheme = [
            'primary_color' => '1E3A8A',    // Deep Navy
            'accent_color'  => '0284C7',    // Sky Blue
            'bg_color'      => '#F8FAFC',    // Slate 50
            'card_bg'       => '#FFFFFF',
        ];

        if (!$template) {
            return $defaultTheme;
        }

        $definitions = BuiltinTemplateGeneratorService::getTemplateDefinitions();
        foreach ($definitions as $def) {
            if ($def['key'] === $template->template_key) {
                return [
                    'primary_color' => $def['primary_color'],
                    'accent_color'  => $def['accent_color'],
                    'bg_color'      => '#' . $def['bg_color'],
                    'card_bg'       => '#' . $def['card_bg'],
                ];
            }
        }

        return $defaultTheme;
    }

    /**
     * Convert storage file to Base64 URI for safe rendering in DomPDF
     */
    protected function fileToBase64(?string $filePath): ?string
    {
        if (!$filePath) return null;

        $fullPath = null;
        if (Storage::disk('public')->exists($filePath)) {
            $fullPath = Storage::disk('public')->path($filePath);
        } elseif (Storage::disk('local')->exists($filePath)) {
            $fullPath = Storage::disk('local')->path($filePath);
        } elseif (file_exists($filePath)) {
            $fullPath = $filePath;
        }

        if (!$fullPath || !file_exists($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/jpeg';
        $data = file_get_contents($fullPath);
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
}
