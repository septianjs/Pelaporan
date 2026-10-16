<?php

namespace App\Services;

use ZipArchive;

class PptxValidatorService
{
    /**
     * Standard 16:9 Widescreen Target
     * 13.333 inch x 7.5 inch in EMU (914400 EMU = 1 inch)
     * 13.333 * 914400 = 12191695.2 EMU
     * 7.5 * 914400 = 6858000 EMU
     */
    const TARGET_ASPECT_RATIO = 16.0 / 9.0; // ~1.7778
    const MIN_SLIDE_COUNT = 6;              // 1 Cover + 5 Lokasi

    /**
     * List of mandatory / recognized placeholders
     */
    const COVER_PLACEHOLDERS = [
        '{{NOMOR_LAPORAN}}',
        '{{NAMA_PETUGAS}}',
    ];

    const COVER_DATE_PLACEHOLDERS = [
        '{{TANGGAL}}',
        '{{HARI_TANGGAL}}',
    ];

    const COVER_PHOTO_PLACEHOLDERS = [
        '{{FOTO_PETUGAS}}',
    ];

    const LOCATION_GENERIC_PLACEHOLDERS = [
        '{{NAMA_LOKASI}}',
        '{{KONDISI}}',
        '{{FOTO_1}}',
        '{{FOTO_2}}',
    ];

    /**
     * Run full inspection and validation of a PPTX file.
     *
     * @param string $filePath Full filesystem path to the .pptx file
     * @return array
     */
    public function validate(string $filePath): array
    {
        $errors = [];
        $warnings = [];

        // 1. Verify file exists
        if (!file_exists($filePath)) {
            return [
                'valid' => false,
                'errors' => ['File PPTX tidak ditemukan pada sistem.'],
                'warnings' => [],
                'details' => null,
            ];
        }

        // 2. Open ZIP
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [
                'valid' => false,
                'errors' => ['File PPTX rusak atau bukan arsip ZIP OpenXML yang valid.'],
                'warnings' => [],
                'details' => null,
            ];
        }

        // 3. Verify presentation.xml
        $presentationXml = $zip->getFromName('ppt/presentation.xml');
        if ($presentationXml === false) {
            $zip->close();
            return [
                'valid' => false,
                'errors' => ['File PPTX tidak memiliki struktur presentasi (ppt/presentation.xml tidak ditemukan).'],
                'warnings' => [],
                'details' => null,
            ];
        }

        // 4. Verify 16:9 Widescreen Aspect Ratio & Dimensions
        $dimensionInfo = $this->extractDimensions($presentationXml);
        if (!$dimensionInfo['success']) {
            $errors[] = $dimensionInfo['message'];
        } else {
            if (!$dimensionInfo['is_16_9']) {
                $errors[] = "Rasio slide bukan 16:9 Widescreen. Slide Anda berukuran {$dimensionInfo['width_inch']}\" × {$dimensionInfo['height_inch']}\" (rasio {$dimensionInfo['aspect_ratio_formatted']}:1). Diperlukan 16:9 Widescreen (13.333 × 7.5 inch).";
            }
        }

        // 5. Count slides
        $slideFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = $stat['name'] ?? '';
            if (preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name, $sm)) {
                $slideFiles[(int)$sm[1]] = $name;
            }
        }
        ksort($slideFiles);
        $slideCount = count($slideFiles);

        if ($slideCount < self::MIN_SLIDE_COUNT) {
            $errors[] = "Jumlah slide tidak mencukupi ({$slideCount} slide). Template minimal harus memiliki 6 slide (1 Slide Cover + 5 Slide Lokasi Pengawasan).";
        } elseif ($slideCount > self::MIN_SLIDE_COUNT) {
            $warnings[] = "Template memiliki {$slideCount} slide (standar adalah 6 slide). Slide tambahan akan tetap diproses.";
        }

        // 6. Inspect placeholders across all slides
        $allFoundPlaceholders = [];
        $slideAnalysis = [];

        foreach ($slideFiles as $slideNum => $slidePath) {
            $slideXml = $zip->getFromName($slidePath);
            $foundInSlide = [];

            if ($slideXml !== false) {
                // Find all {{PLACEHOLDER}} occurrences
                if (preg_match_all('/\{\{[A-Z0-9_\-\s]+\}\}/i', $slideXml, $matches)) {
                    foreach ($matches[0] as $ph) {
                        $cleanPh = strtoupper(trim($ph));
                        $foundInSlide[] = $cleanPh;
                        $allFoundPlaceholders[] = $cleanPh;
                    }
                }
            }

            $slideType = ($slideNum === 1) ? 'Cover Laporan' : ('Titik Lokasi ' . ($slideNum - 1));
            $slideAnalysis[] = [
                'slide_number' => $slideNum,
                'type' => $slideType,
                'placeholders' => array_values(array_unique($foundInSlide)),
            ];
        }

        $allFoundPlaceholders = array_values(array_unique($allFoundPlaceholders));
        $zip->close();

        // 7. Check Mandatory Cover Placeholders
        $missingCover = [];
        foreach (self::COVER_PLACEHOLDERS as $req) {
            if (!in_array($req, $allFoundPlaceholders)) {
                $missingCover[] = $req;
            }
        }

        // Date placeholder check (either {{TANGGAL}} or {{HARI_TANGGAL}})
        $hasDate = false;
        foreach (self::COVER_DATE_PLACEHOLDERS as $datePh) {
            if (in_array($datePh, $allFoundPlaceholders)) {
                $hasDate = true;
                break;
            }
        }
        if (!$hasDate) {
            $missingCover[] = '{{TANGGAL}} atau {{HARI_TANGGAL}}';
        }

        // Officer photo check
        if (!in_array('{{FOTO_PETUGAS}}', $allFoundPlaceholders)) {
            $warnings[] = 'Placeholder {{FOTO_PETUGAS}} tidak ditemukan pada cover. Foto petugas tidak akan muncul jika tidak ada placeholder.';
        }

        if (!empty($missingCover)) {
            $errors[] = 'Placeholder wajib pada Cover belum lengkap: ' . implode(', ', $missingCover);
        }

        // 8. Check Location Placeholders
        $hasLocationName = in_array('{{NAMA_LOKASI}}', $allFoundPlaceholders) ||
            in_array('{{NAMA_LOKASI_1}}', $allFoundPlaceholders);
        $hasLocationPhotos = (in_array('{{FOTO_1}}', $allFoundPlaceholders) && in_array('{{FOTO_2}}', $allFoundPlaceholders)) ||
            (in_array('{{FOTO_1_1}}', $allFoundPlaceholders) && in_array('{{FOTO_1_2}}', $allFoundPlaceholders));
        $hasCondition = in_array('{{KONDISI}}', $allFoundPlaceholders) ||
            in_array('{{KONDISI_1}}', $allFoundPlaceholders);

        if (!$hasLocationName) {
            $warnings[] = 'Placeholder nama lokasi ({{NAMA_LOKASI}}) belum terdeteksi pada slide lokasi.';
        }
        if (!$hasLocationPhotos) {
            $warnings[] = 'Placeholder foto lokasi ({{FOTO_1}} & {{FOTO_2}}) belum terdeteksi lengkap.';
        }
        if (!$hasCondition) {
            $warnings[] = 'Placeholder kondisi kebersihan ({{KONDISI}}) belum terdeteksi.';
        }

        $isValid = empty($errors);

        return [
            'valid' => $isValid,
            'errors' => $errors,
            'warnings' => $warnings,
            'details' => [
                'slide_count' => $slideCount,
                'is_16_9' => $dimensionInfo['is_16_9'] ?? false,
                'width_inch' => $dimensionInfo['width_inch'] ?? null,
                'height_inch' => $dimensionInfo['height_inch'] ?? null,
                'aspect_ratio' => $dimensionInfo['aspect_ratio_formatted'] ?? null,
                'all_placeholders' => $allFoundPlaceholders,
                'slide_analysis' => $slideAnalysis,
            ],
        ];
    }

    /**
     * Extract slide dimensions from presentation.xml
     */
    protected function extractDimensions(string $presentationXml): array
    {
        if (!preg_match('/<[a-zA-Z0-9:]*sldSz[^>]+cx="([\d.]+)"[^>]+cy="([\d.]+)"/i', $presentationXml, $matches)
            && !preg_match('/<[a-zA-Z0-9:]*sldSz[^>]+cy="([\d.]+)"[^>]+cx="([\d.]+)"/i', $presentationXml, $matches2)
        ) {
            return [
                'success' => false,
                'message' => 'Tidak dapat membaca elemen <p:sldSz> pada file presentation.xml.',
            ];
        }

        if (!empty($matches)) {
            $cx = (float) $matches[1];
            $cy = (float) $matches[2];
        } else {
            $cy = (float) $matches2[1];
            $cx = (float) $matches2[2];
        }

        if ($cy <= 0) {
            return [
                'success' => false,
                'message' => 'Dimensi slide tidak valid (tinggi slide <= 0).',
            ];
        }

        $ratio = $cx / $cy;
        $is16by9 = abs($ratio - self::TARGET_ASPECT_RATIO) < 0.05;

        $widthInch = round($cx / 914400, 3);
        $heightInch = round($cy / 914400, 3);

        return [
            'success' => true,
            'cx' => $cx,
            'cy' => $cy,
            'width_inch' => $widthInch,
            'height_inch' => $heightInch,
            'is_16_9' => $is16by9,
            'aspect_ratio' => $ratio,
            'aspect_ratio_formatted' => round($ratio, 2),
        ];
    }
}
