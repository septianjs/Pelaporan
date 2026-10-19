<?php

namespace App\Services;

/**
 * Crop uploaded photos to fill their PDF frames without stretching.
 * This service is independent from PowerPoint and can be used by DomPDF.
 */
class ImageCropService
{
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
