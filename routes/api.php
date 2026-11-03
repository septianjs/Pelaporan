<?php

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TemplateController;
use App\Models\Report;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Pelaporan Pengawasan Lingkungan Kantor BPSDM API
| Device registration is public; protected resources require device bearer authentication.
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

// Registrasi perangkat baru dibuat publik karena belum memiliki token.
Route::post('/devices/register', [DeviceController::class, 'register'])->middleware('throttle:10,1');

Route::middleware(['device.auth'])->group(function () {
    Route::get('/templates', [TemplateController::class, 'index']);
    Route::get('/templates/{id}/preview', [TemplateController::class, 'preview'])->whereNumber('id');
    Route::post('/templates', [TemplateController::class, 'store'])->middleware('throttle:10,1');
    Route::delete('/templates/{id}', [TemplateController::class, 'destroy'])->middleware('throttle:30,1');

    // Helper Nomor Laporan Otomatis
    Route::get('/reports/next-number', function () {
        return response()->json([
            'status' => 'success',
            'report_number' => Report::generateNextNumber(),
        ]);
    });

    // Laporan Pengawasan Lingkungan
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/reports/{id}', [ReportController::class, 'show'])->whereNumber('id');
    Route::delete('/reports/{id}', [ReportController::class, 'destroy'])->whereNumber('id')->middleware('throttle:30,1');
    Route::post('/reports/{id}/generate-pdf', [ReportController::class, 'generatePdf'])->whereNumber('id')->middleware('throttle:10,1');
    Route::get('/reports/{id}/pdf', [ReportController::class, 'downloadPdf'])->whereNumber('id');
});