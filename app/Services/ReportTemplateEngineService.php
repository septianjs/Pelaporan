<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportLocation;
use App\Models\ReportPhoto;
use App\Models\Template;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Shape\Drawing\File as DrawingFile;
use PhpOffice\PhpPresentation\Shape\RichText;

class ReportTemplateEngineService
{
    /**
     * Generate rendered PPTX presentation for a Report.
     * Replaces all text placeholders and injects photos with object-fit: cover cropping.
     *
     * @param Report $report
     * @return string Full path to the generated PPTX file
     */
    public function generatePresentation(Report $report): string
    {
        // 1. Ensure report relationships are loaded
        $report->loadMissing(['template', 'reportLocations.location', 'reportLocations.reportPhotos']);

        $template = $report->template;
        if (!$template) {
            $template = Template::where('active', true)->firstOrFail();
        }

        // 2. Resolve Template PPTX file path
        $templatePath = $this->resolveTemplatePath($template);
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template PPTX tidak ditemukan di path: {$templatePath}");
        }

        // 3. Prepare output directory
        $outputDir = storage_path("app/public/reports/{$report->id}");
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $cleanReportNum = preg_replace('/[^A-Za-z0-9_\-]/', '_', $report->report_number);
        $outputFileName = "Laporan_{$cleanReportNum}.pptx";
        $outputPath = "{$outputDir}/{$outputFileName}";
        $relativeStoragePath = "reports/{$report->id}/{$outputFileName}";

        // 4. Load PPTX with PhpPresentation Reader
        $reader = IOFactory::createReader('PowerPoint2007');
        $ppt = $reader->load($templatePath);

        // 5. Build Data Dictionary
        $locations = $report->reportLocations->values();
        $dateFormatted = $report->formatted_date ?: $report->inspection_date->format('d/m/Y');
        $dateOnly = $report->inspection_date ? $report->inspection_date->translatedFormat('d F Y') : date('d/m/Y');

        // 6. Iterate through slides and replace text / inject photos
        $slides = $ppt->getAllSlides();
        $totalSlides = count($slides);

        foreach ($slides as $slideIdx => $slide) {
            $slideNum = $slideIdx + 1;
            $shapes = $slide->getShapeCollection();

            $imagesToAdd = [];
            $shapesToClear = [];

            // ── SLIDE 1: COVER ───────────────────────────────────────────────
            if ($slideNum === 1) {
                foreach ($shapes as $shapeIdx => $shape) {
                    if (!($shape instanceof RichText)) continue;
                    $plain = $shape->getPlainText();

                    // Check for Officer Photo placeholder
                    if (str_contains($plain, '{{FOTO_PETUGAS}}')) {
                        $officerPhotoFullPath = $this->resolvePhotoPath($report->officer_photo);
                        if ($officerPhotoFullPath && file_exists($officerPhotoFullPath)) {
                            $croppedPath = "{$outputDir}/cropped_officer.jpg";
                            if ($this->cropImageCover($officerPhotoFullPath, $shape->getWidth(), $shape->getHeight(), $croppedPath)) {
                                $drawing = new DrawingFile();
                                $drawing->setPath($croppedPath)
                                        ->setWidth($shape->getWidth())
                                        ->setHeight($shape->getHeight())
                                        ->setOffsetX($shape->getOffsetX())
                                        ->setOffsetY($shape->getOffsetY());
                                $imagesToAdd[] = $drawing;
                                $shapesToClear[] = $shape;
                            }
                        } else {
                            $this->clearRichText($shape);
                        }
                        continue;
                    }

                    // Text replacements for Cover
                    $this->replaceTextInShape($shape, [
                        '{{JUDUL_LAPORAN}}'  => 'LAPORAN PENGAWASAN LINGKUNGAN KANTOR BPSDM',
                        '{{NOMOR_LAPORAN}}'  => $report->report_number,
                        '{{HARI_TANGGAL}}'   => $dateFormatted,
                        '{{TANGGAL}}'        => $dateOnly,
                        '{{NAMA_PETUGAS}}'   => $report->officer_name,
                    ]);
                }
            }
            // ── SLIDES 2..N: LOCATIONS 1..5 ──────────────────────────────────
            else {
                $locIndex = $slideNum - 2; // Slide 2 -> locIndex 0
                /** @var ReportLocation|null $reportLoc */
                $reportLoc = $locations->get($locIndex);

                $locName = $reportLoc ? $reportLoc->display_name : "Lokasi {$locIndex}";
                $conditionText = $reportLoc ? $reportLoc->condition_label : 'Kondisi Sudah Bersih dan Rapi';
                $notesText = ($reportLoc && $reportLoc->notes) ? $reportLoc->notes : ($reportLoc && $reportLoc->condition === 'clean' ? 'Area terpantau bersih dan terawat dengan baik.' : '-');
                $problemText = ($reportLoc && $reportLoc->problem) ? $reportLoc->problem : ($reportLoc && $reportLoc->condition === 'needs_attention' ? '-' : 'Tidak ada permasalahan');
                $solutionText = ($reportLoc && $reportLoc->solution) ? $reportLoc->solution : ($reportLoc && $reportLoc->condition === 'needs_attention' ? '-' : 'Tidak memerlukan tindakan khusus');

                // Get Photos for this location
                $photo1Record = $reportLoc ? $reportLoc->reportPhotos->where('photo_type', 'photo_1')->first() : null;
                $photo2Record = $reportLoc ? $reportLoc->reportPhotos->where('photo_type', 'photo_2')->first() : null;

                $locHumanNum = $locIndex + 1;

                foreach ($shapes as $shapeIdx => $shape) {
                    if (!($shape instanceof RichText)) continue;
                    $plain = $shape->getPlainText();

                    // Check for Photo 1 placeholder: {{FOTO_1}} or {{FOTO_X_1}}
                    if (str_contains($plain, '{{FOTO_1}}') || str_contains($plain, "{{FOTO_{$locHumanNum}_1}}")) {
                        $p1Path = $photo1Record ? $this->resolvePhotoPath($photo1Record->file_path) : null;
                        if ($p1Path && file_exists($p1Path)) {
                            $croppedP1 = "{$outputDir}/cropped_loc_{$locHumanNum}_p1.jpg";
                            if ($this->cropImageCover($p1Path, $shape->getWidth(), $shape->getHeight(), $croppedP1)) {
                                $drawing = new DrawingFile();
                                $drawing->setPath($croppedP1)
                                        ->setWidth($shape->getWidth())
                                        ->setHeight($shape->getHeight())
                                        ->setOffsetX($shape->getOffsetX())
                                        ->setOffsetY($shape->getOffsetY());
                                $imagesToAdd[] = $drawing;
                                $shapesToClear[] = $shape;
                            }
                        } else {
                            $this->clearRichText($shape);
                        }
                        continue;
                    }

                    // Check for Photo 2 placeholder: {{FOTO_2}} or {{FOTO_X_2}}
                    if (str_contains($plain, '{{FOTO_2}}') || str_contains($plain, "{{FOTO_{$locHumanNum}_2}}")) {
                        $p2Path = $photo2Record ? $this->resolvePhotoPath($photo2Record->file_path) : null;
                        if ($p2Path && file_exists($p2Path)) {
                            $croppedP2 = "{$outputDir}/cropped_loc_{$locHumanNum}_p2.jpg";
                            if ($this->cropImageCover($p2Path, $shape->getWidth(), $shape->getHeight(), $croppedP2)) {
                                $drawing = new DrawingFile();
                                $drawing->setPath($croppedP2)
                                        ->setWidth($shape->getWidth())
                                        ->setHeight($shape->getHeight())
                                        ->setOffsetX($shape->getOffsetX())
                                        ->setOffsetY($shape->getOffsetY());
                                $imagesToAdd[] = $drawing;
                                $shapesToClear[] = $shape;
                            }
                        } else {
                            $this->clearRichText($shape);
                        }
                        continue;
                    }

                    // Text replacements for Location slides
                    $this->replaceTextInShape($shape, [
                        '{{NAMA_LOKASI}}'                    => $locName,
                        "{{NAMA_LOKASI_{$locHumanNum}}}"     => $locName,
                        '{{KONDISI}}'                        => $conditionText,
                        "{{KONDISI_{$locHumanNum}}}"         => $conditionText,
                        '{{CATATAN}}'                        => $notesText,
                        "{{CATATAN_{$locHumanNum}}}"         => $notesText,
                        '{{PERMASALAHAN}}'                   => $problemText,
                        "{{PERMASALAHAN_{$locHumanNum}}}"    => $problemText,
                        '{{SOLUSI}}'                         => $solutionText,
                        "{{SOLUSI_{$locHumanNum}}}"          => $solutionText,
                        '{{NOMOR_LAPORAN}}'                  => $report->report_number,
                        '{{HARI_TANGGAL}}'                   => $dateFormatted,
                        '{{TANGGAL}}'                        => $dateOnly,
                        '{{NAMA_PETUGAS}}'                   => $report->officer_name,
                    ]);
                }
            }

            // Clear text from placeholder shapes that are replaced by images
            foreach ($shapesToClear as $stc) {
                $this->clearRichText($stc);
            }

            // Inject the cropped DrawingFile image shapes
            foreach ($imagesToAdd as $img) {
                $slide->addShape($img);
            }
        }

        // 7. Save generated PPTX
        $writer = IOFactory::createWriter($ppt, 'PowerPoint2007');
        $writer->save($outputPath);

        // 8. Update report record with pptx_path
        $report->update([
            'pptx_path' => $relativeStoragePath,
        ]);

        return $outputPath;
    }

    /**
     * Clear all text runs in a RichText shape so placeholder text doesn't show behind images
     */
    protected function clearRichText(RichText $shape): void
    {
        foreach ($shape->getParagraphs() as $p) {
            foreach ($p->getRichTextElements() as $elem) {
                if (method_exists($elem, 'setText')) {
                    $elem->setText('');
                }
            }
        }
    }

    /**
     * Replace text in a RichText shape using a key-value mapping
     */
    protected function replaceTextInShape(RichText $shape, array $replacements): void
    {
        foreach ($shape->getParagraphs() as $paragraph) {
            foreach ($paragraph->getRichTextElements() as $elem) {
                if (!method_exists($elem, 'getText') || !method_exists($elem, 'setText')) {
                    continue;
                }
                $text = $elem->getText();
                $changed = false;
                foreach ($replacements as $search => $replace) {
                    if (str_contains($text, $search)) {
                        $text = str_replace($search, (string)$replace, $text);
                        $changed = true;
                    }
                }
                if ($changed) {
                    $elem->setText($text);
                }
            }
        }
    }

    /**
     * Resolve template file full path on disk
     */
    protected function resolveTemplatePath(Template $template): string
    {
        // Try exact pptx_path
        if ($template->pptx_path) {
            $local = storage_path("app/{$template->pptx_path}");
            if (file_exists($local)) return $local;

            $public = storage_path("app/public/{$template->pptx_path}");
            if (file_exists($public)) return $public;
        }

        // Try builtin template
        $builtin = storage_path("app/templates/builtin/{$template->template_key}.pptx");
        if (file_exists($builtin)) return $builtin;

        // Fallback to corporate-blue
        $fallback = storage_path('app/templates/builtin/corporate-blue.pptx');
        if (file_exists($fallback)) return $fallback;

        throw new \RuntimeException("Berkas template tidak dapat ditemukan.");
    }

    /**
     * Resolve uploaded photo path from database storage path
     */
    protected function resolvePhotoPath(?string $filePath): ?string
    {
        if (!$filePath) return null;

        // Try public storage path
        $public = storage_path("app/public/{$filePath}");
        if (file_exists($public)) return $public;

        // Try local storage path
        $local = storage_path("app/{$filePath}");
        if (file_exists($local)) return $local;

        return null;
    }

    /**
     * Crop image with object-fit: cover algorithm.
     * Prevents stretching or distortion, maintaining sharp aspect ratio.
     *
     * @param string $srcPath Source photo path
     * @param float $targetW Target frame width in points/pixels
     * @param float $targetH Target frame height in points/pixels
     * @param string $dstPath Output cropped JPEG path
     * @param float $focusY Posisi vertikal crop saat foto lebih tinggi dari frame (0..1, default 0.5)
     * @return bool
     */
    public function cropImageCover(string $srcPath, float $targetW, float $targetH, string $dstPath, float $focusY = 0.5): bool
    {
        if (!file_exists($srcPath) || $targetW <= 0 || $targetH <= 0) {
            return false;
        }

        $info = @getimagesize($srcPath);
        if (!$info) return false;

        $srcW = $info[0];
        $srcH = $info[1];
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $srcImg = @imagecreatefromjpeg($srcPath);
                break;
            case 'image/png':
                $srcImg = @imagecreatefrompng($srcPath);
                break;
            case 'image/webp':
                $srcImg = @imagecreatefromwebp($srcPath);
                break;
            default:
                return false;
        }

        if (!$srcImg) return false;

        // Handle EXIF orientation (mobile phone photos)
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($srcPath);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $srcImg = imagerotate($srcImg, 180, 0);
                        break;
                    case 6:
                        $srcImg = imagerotate($srcImg, -90, 0);
                        $tmp = $srcW; $srcW = $srcH; $srcH = $tmp;
                        break;
                    case 8:
                        $srcImg = imagerotate($srcImg, 90, 0);
                        $tmp = $srcW; $srcW = $srcH; $srcH = $tmp;
                        break;
                }
            }
        }

        $srcRatio = $srcW / $srcH;
        $targetRatio = $targetW / $targetH;

        if ($srcRatio > $targetRatio) {
            // Source is wider than target frame -> crop symmetrically on left and right
            $cropH = $srcH;
            $cropW = (int) round($srcH * $targetRatio);
            $cropX = (int) round(($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            // Source is taller than target frame -> crop symmetrically on top and bottom
            $cropW = $srcW;
            $cropH = (int) round($srcW / $targetRatio);
            $cropX = 0;
            // focusY: 0 = rata atas, 0.5 = tengah (default), 1 = rata bawah.
            // Untuk foto potret orang dipakai ±0.2 agar kepala tidak terpotong.
            $cropY = (int) round(($srcH - $cropH) * max(0.0, min(1.0, $focusY)));
        }

        // Render at 2x resolution for crisp high-DPI display, max 1920x1080
        $outW = (int) min(1920, max(100, round($targetW * 2)));
        $outH = (int) min(1080, max(100, round($targetH * 2)));

        $dstImg = imagecreatetruecolor($outW, $outH);

        // Preserve PNG transparency or fill white background
        $white = imagecolorallocate($dstImg, 255, 255, 255);
        imagefilledrectangle($dstImg, 0, 0, $outW, $outH, $white);

        imagecopyresampled($dstImg, $srcImg, 0, 0, $cropX, $cropY, $outW, $outH, $cropW, $cropH);

        // Ensure parent directory exists
        $dir = dirname($dstPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $saved = imagejpeg($dstImg, $dstPath, 88);

        imagedestroy($srcImg);
        imagedestroy($dstImg);

        return $saved;
    }
}
