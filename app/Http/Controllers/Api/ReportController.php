<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Report;
use App\Models\ReportLocation;
use App\Models\ReportPhoto;
use App\Models\Template;
use App\Services\ReportPdfService;
use App\Mail\ReportSubmittedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * Display a listing of reports (History).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::with(['template', 'reportLocations.location', 'reportLocations.reportPhotos'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                  ->orWhere('officer_name', 'like', "%{$search}%")
                  ->orWhere('inspection_date', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('inspection_date', $request->input('date'));
        }

        if ($request->filled('month')) {
            $parts = explode('-', $request->input('month'));
            if (count($parts) === 2) {
                $query->whereYear('inspection_date', (int)$parts[0])
                      ->whereMonth('inspection_date', (int)$parts[1]);
            }
        }

        if ($request->filled('template_id')) {
            $query->where('template_id', $request->input('template_id'));
        }

        // Filter by condition status ('clean' = all clean, 'needs_attention' = has problem)
        if ($request->input('condition_status') === 'needs_attention') {
            $query->whereHas('reportLocations', function ($q) {
                $q->where('condition', 'needs_attention');
            });
        } elseif ($request->input('condition_status') === 'clean') {
            $query->whereDoesntHave('reportLocations', function ($q) {
                $q->where('condition', 'needs_attention');
            });
        }

        $reports = $query->paginate($request->input('per_page', 20));

        // Quick Stats Summary
        $stats = [
            'total' => Report::count(),
            'this_month' => Report::whereYear('inspection_date', date('Y'))
                ->whereMonth('inspection_date', date('m'))
                ->count(),
            'needs_attention' => Report::whereHas('reportLocations', function ($q) {
                $q->where('condition', 'needs_attention');
            })->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data'   => $reports,
            'stats'  => $stats,
        ]);
    }

    /**
     * Store a report and generate its 16:9 PDF.
     */
    public function store(
        Request $request,
        ReportPdfService $pdfService
    ): JsonResponse {
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'officer_name' => 'required|string|max:255',
            'template_id' => 'required|exists:templates,id',
            'officer_photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'locations' => 'required|array|min:1',
            'locations.*.location_id' => 'required|exists:locations,id',
            'locations.*.name' => 'required|string|max:150',
            'locations.*.condition' => 'required|in:clean,needs_attention',
        ]);

        $report = DB::transaction(function () use ($request, $validated) {
            $year = date('Y', strtotime($validated['inspection_date']));
            $reportNumber = $request->input('report_number') ?: Report::generateNextNumber((int)$year);

            // Handle Officer Photo
            $officerPhotoPath = null;
            if ($request->hasFile('officer_photo')) {
                $file = $request->file('officer_photo');
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $fileName = 'officer_' . time() . '_' . Str::random(8) . '.' . $ext;
                $officerPhotoPath = $file->storeAs('photos', $fileName, 'public');
            }

            // Create Report
            $report = Report::create([
                'report_number' => $reportNumber,
                'inspection_date' => $validated['inspection_date'],
                'officer_name' => $validated['officer_name'],
                'officer_photo' => $officerPhotoPath,
                'template_id' => $validated['template_id'],
                'status' => 'completed',
            ]);

            // Save Locations and Photos
            $locationsData = $request->input('locations', []);

            foreach ($locationsData as $index => $locData) {
                $locationId = $locData['location_id'] ?? null;
                if (!$locationId) continue;

                $condition = ($locData['condition'] ?? 'clean') === 'needs_attention'
                    ? 'needs_attention'
                    : 'clean';

                $customName = trim((string)($locData['name'] ?? ''));
                if ($customName === '') {
                    // Fallback ke nama master jika field kosong (seharusnya sudah divalidasi required).
                    $masterLocation = Location::find($locationId);
                    $customName = $masterLocation->name ?? "Lokasi {$index}";
                }

                $reportLocation = ReportLocation::create([
                    'report_id' => $report->id,
                    'location_id' => $locationId,
                    'name' => $customName,
                    'condition' => $condition,
                    'notes' => $condition === 'clean' ? ($locData['notes'] ?? null) : null,
                    'problem' => $condition === 'needs_attention' ? ($locData['problem'] ?? null) : null,
                    'solution' => $condition === 'needs_attention' ? ($locData['solution'] ?? null) : null,
                ]);

                // Check for photo 1
                $p1Key = "locations.{$index}.photo_1";
                if ($request->hasFile($p1Key)) {
                    $file1 = $request->file($p1Key);
                    $ext = $file1->getClientOriginalExtension() ?: 'jpg';
                    $fileName1 = "loc_{$locationId}_p1_" . time() . '_' . Str::random(8) . '.' . $ext;
                    $path1 = $file1->storeAs('photos', $fileName1, 'public');

                    ReportPhoto::create([
                        'report_location_id' => $reportLocation->id,
                        'photo_type' => 'photo_1',
                        'file_path' => $path1,
                    ]);
                }

                // Check for photo 2
                $p2Key = "locations.{$index}.photo_2";
                if ($request->hasFile($p2Key)) {
                    $file2 = $request->file($p2Key);
                    $ext = $file2->getClientOriginalExtension() ?: 'jpg';
                    $fileName2 = "loc_{$locationId}_p2_" . time() . '_' . Str::random(8) . '.' . $ext;
                    $path2 = $file2->storeAs('photos', $fileName2, 'public');

                    ReportPhoto::create([
                        'report_location_id' => $reportLocation->id,
                        'photo_type' => 'photo_2',
                        'file_path' => $path2,
                    ]);
                }
            }

            return $report;
        });

        // Generate 16:9 Widescreen PDF
        try {
            $pdfService->generatePdf($report);
            $report->refresh();
        } catch (\Throwable $e) {
            Log::error("PDF Generation Error for Report #{$report->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        $report->load(['template', 'reportLocations.location', 'reportLocations.reportPhotos']);

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan berhasil disimpan dan PDF 16:9 siap digunakan.',
            'data' => $report,
        ], 201);
    }

    /**
     * Display the specified report.
     */
    public function show(int $id): JsonResponse
    {
        $report = Report::with(['template', 'reportLocations.location', 'reportLocations.reportPhotos'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $report,
        ]);
    }

    /**
     * Trigger manual generation / regeneration of 16:9 PDF for a report.
     */
    public function generatePdf(int $id, ReportPdfService $pdfService): JsonResponse
    {
        $report = Report::findOrFail($id);
        try {
            $pdfService->generatePdf($report);
            $report->refresh();
            $report->load(['template', 'reportLocations.location', 'reportLocations.reportPhotos']);

            return response()->json([
                'status' => 'success',
                'message' => 'Dokumen PDF 16:9 berhasil digenerate.',
                'data' => $report,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat dokumen PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download or stream the generated 16:9 PDF presentation.
     */
    public function downloadPdf(int $id, Request $request)
    {
        $report = Report::findOrFail($id);

        $fullPath = $report->pdf_path ? storage_path("app/public/{$report->pdf_path}") : null;

        // Regenerasi jika belum ada, atau file rusak/kosong (0 byte).
        if (!$fullPath || !file_exists($fullPath) || filesize($fullPath) === 0) {
            try {
                $pdfService = app(ReportPdfService::class);
                $pdfService->generatePdf($report);
                $report->refresh();
                $fullPath = storage_path("app/public/{$report->pdf_path}");
            } catch (\Throwable $e) {
                Log::error("Failed to generate PDF for download, Report #{$id}: " . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal membuat file PDF: ' . $e->getMessage(),
                ], 500);
            }
        }

        if (!file_exists($fullPath) || filesize($fullPath) === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'File PDF belum tersedia di server.',
            ], 404);
        }

        // Nama dasar file yang dilihat pengguna saat mengunduh/membagikan laporan.
        // Nama penyimpanan internal (unik per laporan, berisi nomor laporan) tetap
        // dipakai di disk agar tidak saling menimpa antar laporan; keduanya sengaja dipisah.
        $downloadName = 'laporan ring lingkungan BPSDM.pdf';

        // If inline=1 (e.g. for previewing inside mobile webview or browser tab)
        if ($request->boolean('inline')) {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
            ]);
        }

        return response()->download($fullPath, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Send the report with 16:9 PDF attached via email.
     */
    public function sendEmail(Request $request, int $id, ReportPdfService $pdfService): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $report = Report::with(['template', 'reportLocations.location', 'reportLocations.reportPhotos'])
            ->findOrFail($id);

        // Ensure 16:9 PDF exists and is valid (not empty); generate/regenerate if missing or broken.
        $pdfFullPath = $report->pdf_path ? storage_path("app/public/{$report->pdf_path}") : null;
        if (!$pdfFullPath || !file_exists($pdfFullPath) || filesize($pdfFullPath) === 0) {
            try {
                $pdfService->generatePdf($report);
                $report->refresh();
                $report->load(['template', 'reportLocations.location', 'reportLocations.reportPhotos']);
                $pdfFullPath = storage_path("app/public/{$report->pdf_path}");
            } catch (\Throwable $e) {
                Log::error("Failed to generate PDF before emailing Report #{$id}: " . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal membuat file PDF untuk lampiran email: ' . $e->getMessage(),
                ], 500);
            }
        }

        // Jangan pernah mengirim email "berhasil" tanpa lampiran PDF yang valid.
        if (!file_exists($pdfFullPath) || filesize($pdfFullPath) === 0) {
            Log::error("PDF attachment missing/empty when emailing Report #{$id}");
            return response()->json([
                'status' => 'error',
                'message' => 'File PDF lampiran tidak valid atau kosong, email tidak dikirim.',
            ], 500);
        }

        try {
            Mail::to($validated['email'])->send(
                new ReportSubmittedMail(
                    $report,
                    $validated['notes'] ?? null,
                    $validated['subject'] ?? null
                )
            );

            return response()->json([
                'status' => 'success',
                'message' => "Laporan berhasil dikirim ke alamat email {$validated['email']}.",
                'data' => [
                    'recipient' => $validated['email'],
                    'report_number' => $report->report_number,
                    'has_pdf_attached' => !empty($report->pdf_path),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to send email for Report #{$id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified report and associated files.
     */
    public function destroy(int $id): JsonResponse
    {
        $report = Report::findOrFail($id);

        // Delete officer photo if exists
        if ($report->officer_photo && Storage::disk('public')->exists($report->officer_photo)) {
            Storage::disk('public')->delete($report->officer_photo);
        }

        // Delete location photos
        foreach ($report->reportLocations as $rLoc) {
            foreach ($rLoc->reportPhotos as $photo) {
                if ($photo->file_path && Storage::disk('public')->exists($photo->file_path)) {
                    Storage::disk('public')->delete($photo->file_path);
                }
            }
        }

        // Delete report files directory (PDF and temporary files)
        $reportDir = storage_path("app/public/reports/{$report->id}");
        if (File::isDirectory($reportDir)) {
            File::deleteDirectory($reportDir);
        }

        $report->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan berhasil dihapus.',
        ]);
    }
}
