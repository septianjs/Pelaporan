<?php

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TemplateController;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Pelaporan Pengawasan Lingkungan Kantor BPSDM API
| No authentication required.
|
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

// Master & Detail Template PPTX
Route::get('/templates', [TemplateController::class, 'index']);
Route::post('/templates', [TemplateController::class, 'store']);
Route::post('/templates/validate', [TemplateController::class, 'validateTemplate']);
Route::get('/templates/{id}', [TemplateController::class, 'show']);
Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);

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
Route::post('/reports/{id}/generate-pptx', [ReportController::class, 'generatePptx']);
Route::get('/reports/{id}/pptx', [ReportController::class, 'downloadPptx']);
Route::post('/reports/{id}/generate-pdf', [ReportController::class, 'generatePdf']);
Route::get('/reports/{id}/pdf', [ReportController::class, 'downloadPdf']);
Route::post('/reports/{id}/email', [ReportController::class, 'sendEmail']);
