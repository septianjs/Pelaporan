<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    /**
     * Display a listing of templates (builtin and uploaded).
     */
    public function index(): JsonResponse
    {
        $templates = Template::where('active', true)
            ->orderBy('type', 'asc') // builtin first, then uploaded
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $templates,
        ]);
    }

    /**
     * Display the specified template details.
     */
    public function show(int $id): JsonResponse
    {
        $template = Template::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $template,
        ]);
    }

    /**
     * Store a newly uploaded PPTX template.
     * Validates that the file is a real PPTX (ZIP-based Office Open XML)
     * and that its first slide is 16:9 Widescreen (13.333 × 7.5 inch).
     */
    public function store(Request $request): JsonResponse
    {
        // ── 1. Basic validation ──────────────────────────────────────────────
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'pptx_file'  => [
                'required',
                'file',
                'max:20480', // 20 MB max
                function ($attribute, $value, $fail) {
                    $mime = $value->getMimeType();
                    $ext  = strtolower($value->getClientOriginalExtension());

                    // Accept both the correct MIME and generic application/zip
                    $allowedMimes = [
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'application/zip',
                        'application/x-zip',
                        'application/x-zip-compressed',
                        'application/octet-stream',
                    ];

                    if ($ext !== 'pptx') {
                        $fail('File harus berekstensi .pptx.');
                        return;
                    }

                    if (!in_array($mime, $allowedMimes)) {
                        $fail('File harus berformat PPTX (PowerPoint).');
                    }
                },
            ],
        ], [
            'name.required'      => 'Nama template wajib diisi.',
            'pptx_file.required' => 'File PPTX wajib diunggah.',
            'pptx_file.max'      => 'Ukuran file PPTX maksimal 20 MB.',
        ]);

        $file = $request->file('pptx_file');

        // ── 2. Validate 16:9 slide dimensions via ZIP/XML inspection ────────
        $validationResult = $this->validatePptxSlideSize($file->getRealPath());
        if (!$validationResult['valid']) {
            return response()->json([
                'status'  => 'error',
                'message' => $validationResult['message'],
            ], 422);
        }

        // ── 3. Store PPTX file ───────────────────────────────────────────────
        $templateKey = 'uploaded-' . Str::slug($request->name) . '-' . uniqid();
        $pptxFilename = $templateKey . '.pptx';
        $pptxStoragePath = 'templates/uploaded/' . $pptxFilename;

        Storage::disk('local')->put(
            $pptxStoragePath,
            file_get_contents($file->getRealPath())
        );

        // ── 4. Generate preview PNG via GD ───────────────────────────────────
        $previewPath = $this->generatePreview($templateKey, $request->name);

        // ── 5. Create DB record ──────────────────────────────────────────────
        $template = Template::create([
            'name'         => $request->name,
            'template_key' => $templateKey,
            'description'  => $request->description ?? 'Template yang diunggah oleh pengguna.',
            'type'         => 'uploaded',
            'pptx_path'    => $pptxStoragePath,
            'preview_path' => $previewPath,
            'active'       => true,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Template berhasil diunggah.',
            'data'    => $template,
        ], 201);
    }

    /**
     * Delete an uploaded template.
     * Builtin templates cannot be deleted.
     */
    public function destroy(int $id): JsonResponse
    {
        $template = Template::findOrFail($id);

        if ($template->type === 'builtin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Template bawaan tidak dapat dihapus.',
            ], 403);
        }

        // Delete files
        if ($template->pptx_path && Storage::disk('local')->exists($template->pptx_path)) {
            Storage::disk('local')->delete($template->pptx_path);
        }
        if ($template->preview_path && Storage::disk('public')->exists($template->preview_path)) {
            Storage::disk('public')->delete($template->preview_path);
        }

        $template->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Template berhasil dihapus.',
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Open the PPTX as a ZIP and read slide1.xml to extract cx/cy dimensions.
     * PPTX uses EMU (English Metric Units): 914400 EMU = 1 inch.
     * 16:9 Widescreen = 12192000 cx × 6858000 cy  (13.333 × 7.5 inch).
     * We also accept the older 10" × 7.5" format (9144000 × 6858000)
     * but flag anything else as invalid.
     */
    private function validatePptxSlideSize(string $filePath): array
    {
        // Open as ZIP
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return ['valid' => false, 'message' => 'File PPTX tidak valid atau rusak.'];
        }

        // Read presentation.xml for slide size
        $presentationXml = $zip->getFromName('ppt/presentation.xml');
        $zip->close();

        if ($presentationXml === false) {
            return ['valid' => false, 'message' => 'File PPTX tidak memiliki struktur yang valid (bukan file presentasi).'];
        }

        // Extract sldSz element: <p:sldSz cx="12192000" cy="6858000" type="screen16x9"/>
        if (!preg_match('/<p:sldSz[^>]+cx="(\d+)"[^>]+cy="(\d+)"/', $presentationXml, $matches)
            && !preg_match('/<p:sldSz[^>]+cy="(\d+)"[^>]+cx="(\d+)"/', $presentationXml, $matches2)
        ) {
            return ['valid' => false, 'message' => 'Tidak dapat membaca ukuran slide dari file PPTX.'];
        }

        // Handle both attribute orders
        if (!empty($matches)) {
            $cx = (int) $matches[1];
            $cy = (int) $matches[2];
        } else {
            $cy = (int) $matches2[1];
            $cx = (int) $matches2[2];
        }

        // Tolerance: ±1 EMU to handle rounding
        $targetCx = 12192000; // 13.333 inch × 914400
        $targetCy = 6858000;  // 7.5 inch × 914400
        $tolerance = 1000;    // ~0.001 inch tolerance

        $cxOk = abs($cx - $targetCx) <= $tolerance;
        $cyOk = abs($cy - $targetCy) <= $tolerance;

        if (!$cxOk || !$cyOk) {
            $actualW = round($cx / 914400, 2);
            $actualH = round($cy / 914400, 2);
            return [
                'valid'   => false,
                'message' => "Ukuran slide tidak sesuai. Diperlukan 13.333 × 7.5 inch (16:9 Widescreen), "
                    . "file Anda berukuran {$actualW} × {$actualH} inch. "
                    . "Buka PowerPoint → Design → Slide Size → Widescreen (16:9), lalu simpan ulang.",
            ];
        }

        return ['valid' => true];
    }

    /**
     * Generate a simple PNG preview for an uploaded template using GD.
     * Returns the public-disk relative path (for storage accessor).
     */
    private function generatePreview(string $templateKey, string $templateName): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        try {
            // 16:9 thumbnail: 640×360 px
            $w = 640;
            $h = 360;
            $img = imagecreatetruecolor($w, $h);

            // Random but consistent color based on template key hash
            $hash = crc32($templateKey);
            $r = 50 + abs($hash % 80);
            $g = 60 + abs(($hash >> 8) % 60);
            $b = 100 + abs(($hash >> 16) % 100);

            $bg    = imagecolorallocate($img, $r, $g, $b);
            $light = imagecolorallocate($img, min(255, $r + 60), min(255, $g + 60), min(255, $b + 60));
            $white = imagecolorallocate($img, 255, 255, 255);
            $dim   = imagecolorallocate($img, min(255, $r + 30), min(255, $g + 30), min(255, $b + 50));

            imagefill($img, 0, 0, $bg);

            // Header bar
            imagefilledrectangle($img, 0, 0, $w, 70, $light);

            // Title text
            $label = mb_strtoupper($templateName);
            if (strlen($label) > 30) $label = mb_substr($label, 0, 28) . '…';
            imagestring($img, 5, 20, 22, $label, $white);
            imagestring($img, 2, 20, 48, 'Template Upload • 16:9 Widescreen', $dim);

            // Decorative rectangles simulating slide content
            imagefilledrectangle($img, 20, 90, $w - 20, 110, $dim);
            imagefilledrectangle($img, 20, 125, 280, 280, $dim);
            imagefilledrectangle($img, 300, 125, $w - 20, 280, $dim);

            // Footer
            imagefilledrectangle($img, 0, 320, $w, $h, $light);
            imagestring($img, 1, 20, 333, 'PELAPORAN PENGAWASAN LINGKUNGAN KANTOR BPSDM', $white);

            // "USER" badge top-right
            imagefilledrectangle($img, $w - 120, 15, $w - 10, 42, imagecolorallocate($img, 220, 100, 30));
            imagestring($img, 3, $w - 108, 22, 'UPLOAD', $white);

            $previewFilename = 'previews/' . $templateKey . '.png';
            $previewFullPath = Storage::disk('public')->path($previewFilename);

            // Ensure previews dir exists
            if (!is_dir(dirname($previewFullPath))) {
                mkdir(dirname($previewFullPath), 0755, true);
            }

            imagepng($img, $previewFullPath, 6);
            imagedestroy($img);

            return $previewFilename;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
