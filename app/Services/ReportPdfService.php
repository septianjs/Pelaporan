<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Report;
use App\Models\ReportLocation;
use App\Models\ReportPhoto;
use App\Models\Template;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ReportPdfService
{
    // Ukuran foto TIDAK lagi ditulis di sini. Ukuran per tema berasal dari
    // BuiltinTemplateGeneratorService::photoSpecs() dan dipakai untuk (a) crop
    // server-side di bawah, (b) style inline <img> di blade, dan (c) pratinjau di
    // aplikasi. DomPDF tidak mendukung object-fit, jadi crop harus persis sama
    // dengan ukuran tampil, kalau tidak foto tampak gepeng/melar.
    protected const OFFICER_PHOTO_FOCUS_Y = 0.2; // kepala biasanya di bagian atas foto potret

    public function __construct(
        protected ImageCropService $imageCropper = new ImageCropService()
    ) {
    }

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

        // 3. Prepare Photos Data (Base64 encoded for DomPDF reliability).
        // Foto di-crop dulu (object-fit: cover manual) sebelum di-base64,
        // karena DomPDF mengabaikan CSS object-fit sepenuhnya.
        $officerPhotoBase64 = $this->croppedPhotoBase64(
            $report->officer_photo,
            $theme['photo']['officer'][0],
            $theme['photo']['officer'][1],
            $outputDir,
            'officer',
            self::OFFICER_PHOTO_FOCUS_Y
        );

        $locationsData = [];
        [$locationPhotoWidth, $locationPhotoHeight] = $theme['photo']['location'];
        $reportLocationsByOrder = $report->reportLocations->values();

        // Selalu hasilkan tepat 5 entri lokasi (sesuai 5 titik pemantauan tetap),
        // walaupun data yang tersimpan kurang dari 5, agar PDF selalu 6 halaman
        // (1 cover + 5 lokasi) dan tidak ada halaman kosong/hilang.
        $masterLocations = Location::orderBy('sort_order')->limit(5)->get();

        for ($idx = 0; $idx < 5; $idx++) {
            /** @var ReportLocation|null $reportLoc */
            $reportLoc = $reportLocationsByOrder->get($idx);

            if ($reportLoc) {
                $photo1 = $reportLoc->reportPhotos->where('photo_type', 'photo_1')->first();
                $photo2 = $reportLoc->reportPhotos->where('photo_type', 'photo_2')->first();

                $locationsData[] = [
                    'name' => $reportLoc->display_name,
                    'condition' => $reportLoc->condition,
                    'condition_label' => $reportLoc->condition_label,
                    'notes' => $reportLoc->notes,
                    'problem' => $reportLoc->problem,
                    'solution' => $reportLoc->solution,
                    'photo_1_base64' => $photo1
                        ? $this->croppedPhotoBase64($photo1->file_path, $locationPhotoWidth, $locationPhotoHeight, $outputDir, "loc_{$idx}_p1")
                        : null,
                    'photo_2_base64' => $photo2
                        ? $this->croppedPhotoBase64($photo2->file_path, $locationPhotoWidth, $locationPhotoHeight, $outputDir, "loc_{$idx}_p2")
                        : null,
                ];
            } else {
                // Titik pemantauan tanpa data tersimpan: tetap tampil sebagai halaman
                // dengan placeholder yang jelas, bukan dihilangkan diam-diam.
                $locationsData[] = [
                    'name' => $masterLocations->get($idx)->name ?? 'Lokasi ' . ($idx + 1),
                    'condition' => 'clean',
                    'condition_label' => 'Data belum diisi',
                    'notes' => 'Data pemantauan untuk titik ini belum diisi.',
                    'problem' => null,
                    'solution' => null,
                    'photo_1_base64' => null,
                    'photo_2_base64' => null,
                ];
            }
        }
            // Muat CSS dasar PDF.
        $baseCssPath = resource_path('css/pdf/base.css');

        $pdfCss = file_exists($baseCssPath)
            ? file_get_contents($baseCssPath)
            : '';

        // Pilih CSS berdasarkan template yang digunakan laporan.
        $definitions = BuiltinTemplateGeneratorService::getTemplateDefinitions();

        $templateIndex = 0;

        if ($report->template) {
            foreach ($definitions as $index => $definition) {
                if ($definition['key'] === $report->template->template_key) {
                    $templateIndex = $index + 1;
                    break;
                }
            }
        }

        $templateCssPath = resource_path(
            'css/pdf/template-' . str_pad((string) $templateIndex, 2, '0', STR_PAD_LEFT) . '.css'
        );

        if (file_exists($templateCssPath)) {
            $pdfCss .= "\n" . file_get_contents($templateCssPath);
        }
            \Log::info('PDF TEMPLATE CSS DEBUG', [
        'template_key' => $report->template?->template_key,
        'template_index' => $templateIndex,
        'base_css_loaded' => file_exists($baseCssPath),
        'template_css_path' => $templateCssPath,
        'template_css_loaded' => file_exists($templateCssPath),
        'template_css_size' => file_exists($templateCssPath)
            ? filesize($templateCssPath)
            : 0,
        'total_css_size' => strlen($pdfCss),
    ]);
    
        // Kompatibilitas variabel CSS dengan DomPDF.
        // Ambil definisi variabel seperti --theme-primary: #172554;
        $cssVariables = [];

        preg_match_all(
            '/(--[\w-]+)\s*:\s*([^;{}]+)\s*;/',
            $pdfCss,
            $variableMatches,
            PREG_SET_ORDER
        );

        foreach ($variableMatches as $variableMatch) {
            $cssVariables[$variableMatch[1]] = trim($variableMatch[2]);
        }

        // Ganti var(--nama-variabel) dengan nilai CSS konkret.
        $pdfCss = preg_replace_callback(
            '/var\(\s*(--[\w-]+)\s*(?:,\s*([^()]+))?\)/',
            function ($matches) use ($cssVariables) {
                if (isset($cssVariables[$matches[1]])) {
                    return $cssVariables[$matches[1]];
                }

                // Gunakan nilai fallback jika variabel tidak ditemukan.
                if (isset($matches[2])) {
                    return trim($matches[2]);
                }

                // Pertahankan ekspresi jika variabel tidak ditemukan.
                return $matches[0];
            },
            $pdfCss
        );

        // 4. Render HTML View
        $html = view('pdf.report_16_9', [
            'report' => $report,
            'theme' => $theme,
            'pdfCss' => $pdfCss,
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
     * Resolve palet warna, kelas desain (style_type) dan ukuran foto dari template.
     * Memakai BuiltinTemplateGeneratorService::resolveTheme() — sumber yang sama
     * dengan yang dikirim ke aplikasi — supaya PDF = pratinjau = pilihan pengguna.
     * Format warna mengikuti blade: primary/accent tanpa '#', sisanya dengan '#'.
     */
    protected function resolveTheme(?Template $template): array
    {
        $theme = BuiltinTemplateGeneratorService::resolveTheme($template?->template_key);

        return [
            'style_type'      => $theme['style_type'],
            'primary_color'   => $theme['primary_color'],
            'accent_color'    => $theme['accent_color'],
            'secondary_color' => $theme['secondary_color'],
            'bg_color'        => '#' . $theme['bg_color'],
            'card_bg'         => '#' . $theme['card_bg'],
            'card_alt'        => '#' . $theme['card_alt'],
            'photo'           => $theme['photo'],
        ];
    }

    /**
     * Crop a stored photo to match its target box (object-fit: cover, manual
     * implementation) then return it as a Base64 data URI for DomPDF.
     * Returns null if the source photo cannot be found/read; a missing photo
     * must stay null so the Blade template shows the explicit placeholder
     * instead of a broken/distorted image.
     */
    protected function croppedPhotoBase64(?string $filePath, int $targetW, int $targetH, string $outputDir, string $tag, float $focusY = 0.5): ?string
    {
        if (!$filePath) return null;

        $srcPath = null;
        if (Storage::disk('public')->exists($filePath)) {
            $srcPath = Storage::disk('public')->path($filePath);
        } elseif (Storage::disk('local')->exists($filePath)) {
            $srcPath = Storage::disk('local')->path($filePath);
        } elseif (file_exists($filePath)) {
            $srcPath = $filePath;
        }

        if (!$srcPath || !file_exists($srcPath)) {
            return null;
        }

        $croppedPath = "{$outputDir}/pdf_cropped_{$tag}.jpg";
        $ok = $this->imageCropper->cropImageCover($srcPath, $targetW, $targetH, $croppedPath, $focusY);

        if (!$ok || !file_exists($croppedPath)) {
            return null;
        }

        $data = file_get_contents($croppedPath);
        return 'data:image/jpeg;base64,' . base64_encode($data);
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
