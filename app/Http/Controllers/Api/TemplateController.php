<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\PptxValidatorService;
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
     * Pre-flight validation endpoint for PPTX templates.
     * Allows frontend to inspect PPTX (16:9 ratio, slide count, placeholders)
     * before uploading or committing to database.
     */
    public function validateTemplate(Request $request, PptxValidatorService $validator): JsonResponse
    {
        $request->validate([
            'pptx_file' => [
                'required',
                'file',
                'max:20480',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if ($ext !== 'pptx') {
                        $fail('File harus berekstensi .pptx.');
                    }
                },
            ],
        ], [
            'pptx_file.required' => 'File PPTX wajib diunggah untuk divalidasi.',
            'pptx_file.max'      => 'Ukuran file PPTX maksimal 20 MB.',
        ]);

        $file = $request->file('pptx_file');
        $report = $validator->validate($file->getRealPath());

        return response()->json([
            'status' => $report['valid'] ? 'success' : 'invalid',
            'report' => $report,
        ], $report['valid'] ? 200 : 422);
    }

    /**
     * Store a newly uploaded PPTX template.
     * Runs full inspection (16:9 Widescreen ratio, slide count >= 6, placeholders)
     * and saves to storage + DB.
     */
    public function store(Request $request, PptxValidatorService $validator): JsonResponse
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

        // ── 2. Run Comprehensive Inspection via PptxValidatorService ─────────
        $report = $validator->validate($file->getRealPath());
        if (!$report['valid']) {
            $primaryError = !empty($report['errors']) ? $report['errors'][0] : 'Validasi template PPTX gagal.';
            return response()->json([
                'status'  => 'error',
                'message' => $primaryError,
                'errors'  => $report['errors'],
                'warnings'=> $report['warnings'],
                'details' => $report['details'],
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
            'status'   => 'success',
            'message'  => 'Template berhasil diunggah dan divalidasi.',
            'data'     => $template,
            'warnings' => $report['warnings'],
            'details'  => $report['details'],
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

            // Consistent palette from template key hash
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

            // "UPLOAD" badge top-right
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
