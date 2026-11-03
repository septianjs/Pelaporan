<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'legacy_device_id' => ['nullable', 'string', 'max:64'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $plainToken = Str::random(64);
        $device = DB::transaction(function () use ($validated, $plainToken) {
            return Device::create([
                'public_id' => 'DEV-' . strtoupper(Str::random(12)),
                'token_hash' => hash('sha256', $plainToken),
                'label' => $validated['label'] ?? null,
                'last_used_at' => now(),
            ]);
        });

        return response()->json([
            'device_id' => $device->public_id,
            'legacy_device_id' => $validated['legacy_device_id'] ?? null,
            'token' => $plainToken,
        ], 201);
    }
}
