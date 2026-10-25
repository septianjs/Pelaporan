<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\PptxReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    /**
     * Daftar template: bawaan (config/report_templates.php) + buatan pengguna (type=uploaded).
     */
    public function index(): JsonResponse
    {
        $cfg = config('report_templates.templates');

        $rows = Template::where('active', true)
            ->where(fn ($q) => $q->whereIn('template_key', array_keys($cfg))->orWhere('type', 'uploaded'))
            ->orderBy('id')
            ->get();

        $data = $rows->map(function (Template $t) use ($cfg) {
            if ($t->type === 'uploaded') {
                return [
                    'id'          => $t->id,
                    'name'        => $t->name,
                    'description' => $t->description ?: 'Template buatan pengguna',
                    'type'        => 'uploaded',
                    'preview_url' => $t->preview_path ? asset('storage/' . $t->preview_path) : null,
                ];
            }

            return [
                'id'          => $t->id,
                'name'        => $cfg[$t->template_key]['name'],
                'description' => $cfg[$t->template_key]['description'] ?? 'Desain dari file PPTX',
                'type'        => 'builtin',
                'preview_url' => asset("storage/template-previews/{$t->template_key}.png"),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Buat template baru dari file PPTX + thumbnail.
     */
    public function store(Request $request, PptxReportService $pptx): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'pptx'        => ['required', 'file', 'extensions:pptx', 'max:20480'],          // 20 MB
            'thumbnail'   => ['required', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'], // 2 MB
        ], [
            'pptx.extensions' => 'File desain harus berformat .pptx.',
            'pptx.max'        => 'Ukuran file PPTX maksimal 20 MB.',
            'thumbnail.max'   => 'Ukuran thumbnail maksimal 2 MB.',
            'thumbnail.image' => 'Thumbnail harus berupa gambar PNG, JPG, atau WEBP.',
        ]);

        // Nama unik (tanpa membedakan huruf besar/kecil) agar tidak membingungkan di daftar.
        if (Template::whereRaw('LOWER(name) = ?', [Str::lower($validated['name'])])->exists()) {
            return response()->json([
                'message' => 'Nama template sudah dipakai.',
                'errors'  => ['name' => ['Nama template sudah dipakai.']],
            ], 422);
        }

        // Periksa isi PPTX dengan aturan yang sama dengan generator.
        $check = $pptx->inspectTemplate($request->file('pptx')->getRealPath());
        if ($check['errors']) {
            return response()->json([
                'message'  => 'File PPTX belum memenuhi syarat template.',
                'errors'   => ['pptx' => $check['errors']],
                'warnings' => $check['warnings'],
            ], 422);
        }

        $key = 'upload-' . Str::lower(Str::random(10));

        $pptxDir = rtrim(config('report_templates.path'), '/\\') . DIRECTORY_SEPARATOR . 'uploaded';
        File::ensureDirectoryExists($pptxDir);
        $pptxFile = $key . '.pptx';
        $request->file('pptx')->move($pptxDir, $pptxFile);

        // Thumbnail: nama file dibuat sendiri, ekstensi dari isi file (bukan dari nama asli).
        $thumb = $request->file('thumbnail');
        $ext   = match ($thumb->getMimeType()) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
        $previewDir = config('report_templates.preview_path');
        File::ensureDirectoryExists($previewDir);
        $thumb->move($previewDir, "$key.$ext");

        try {
            $template = Template::create([
                'name'         => $validated['name'],
                'template_key' => $key,
                'description'  => $validated['description'] ?? null,
                'type'         => 'uploaded',
                'pptx_path'    => "uploaded/$pptxFile",
                'preview_path' => "template-previews/$key.$ext",
                'active'       => true,
            ]);
        } catch (\Throwable $e) {
            File::delete([$pptxDir . DIRECTORY_SEPARATOR . $pptxFile, "$previewDir/$key.$ext"]);
            Log::error('Gagal menyimpan template: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan template.'], 500);
        }

        return response()->json([
            'status'   => 'success',
            'message'  => 'Template berhasil dibuat.',
            'data'     => [
                'id'          => $template->id,
                'name'        => $template->name,
                'description' => $template->description,
                'type'        => 'uploaded',
                'preview_url' => $template->preview_url,
            ],
            'warnings' => $check['warnings'],
        ], 201);
    }

    /**
     * Hapus template buatan pengguna. Template bawaan tidak boleh dihapus, dan template
     * yang sudah dipakai laporan ditolak (relasi laporan -> template bersifat cascade delete,
     * sehingga menghapusnya akan ikut menghapus laporan).
     */
    public function destroy(int $id): JsonResponse
    {
        $template = Template::findOrFail($id);

        if ($template->type !== 'uploaded') {
            return response()->json(['message' => 'Template bawaan tidak dapat dihapus.'], 403);
        }

        $used = $template->reports()->count();
        if ($used > 0) {
            return response()->json([
                'message' => "Template dipakai oleh $used laporan dan tidak dapat dihapus.",
            ], 409);
        }

        $base = rtrim(config('report_templates.path'), '/\\');
        if ($template->pptx_path) {
            File::delete($base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $template->pptx_path));
        }
        if ($template->preview_path) {
            File::delete(storage_path('app/public/' . $template->preview_path));
        }
        $template->delete();

        return response()->json(['status' => 'success', 'message' => 'Template dihapus.']);
    }
}
