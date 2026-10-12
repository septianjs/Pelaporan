<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Report;
use App\Models\ReportLocation;
use App\Models\ReportPhoto;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $reports = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $reports,
        ]);
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'officer_name' => 'required|string|max:255',
            'template_id' => 'required|exists:templates,id',
            'officer_photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'locations' => 'required|array|min:1',
        ]);

        return DB::transaction(function () use ($request, $validated) {
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

                $reportLocation = ReportLocation::create([
                    'report_id' => $report->id,
                    'location_id' => $locationId,
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

            $report->load(['template', 'reportLocations.location', 'reportLocations.reportPhotos']);

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil disimpan.',
                'data' => $report,
            ], 201);
        });
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
     * Remove the specified report.
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

        // Delete PDF if exists
        if ($report->pdf_path && Storage::disk('public')->exists($report->pdf_path)) {
            Storage::disk('public')->delete($report->pdf_path);
        }

        $report->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan berhasil dihapus.',
        ]);
    }
}

