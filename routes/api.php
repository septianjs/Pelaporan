<?php

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ReportController;
use App\Models\Report;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Pelaporan Pengawasan Lingkungan Kantor BPSDM API
| No authentication required.
*/

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API Pelaporan Pengawasan Lingkungan Kantor BPSDM siap digunakan',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Master Lokasi Pengawasan
Route::get('/locations', [LocationController::class, 'index']);

// Katalog template PDF: hanya baris database yang punya file PPTX di config
Route::get('/templates', function () {
    $cfg = config('report_templates.templates');

    return ['data' => \App\Models\Template::whereIn('template_key', array_keys($cfg))
        ->orderBy('id')
        ->get()
        ->map(fn ($t) => [
            'id'          => $t->id,
            'name'        => $cfg[$t->template_key]['name'],
            'description' => $cfg[$t->template_key]['description'] ?? 'Desain dari file PPTX',
            'preview_url' => asset("storage/template-previews/{$t->template_key}.png"),
        ])->values()];
});

// Helper Nomor Laporan Otomatis
Route::get('/reports/next-number', function () {
    return response()->json([
        'status' => 'success',
        'report_number' => Report::generateNextNumber(),
    ]);
});

// Laporan Pengawasan Lingkungan
Route::get('/reports', [ReportController::class, 'index']);
Route::post('/reports', [ReportController::class, 'store']);
Route::get('/reports/{id}', [ReportController::class, 'show']);
Route::delete('/reports/{id}', [ReportController::class, 'destroy']);
Route::post('/reports/{id}/generate-pdf', [ReportController::class, 'generatePdf']);
Route::get('/reports/{id}/pdf', [ReportController::class, 'downloadPdf']);
Route::post('/reports/{id}/email', [ReportController::class, 'sendEmail']);