<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;

class AuthenticateDevice
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = trim((string) $request->header('Authorization', ''));

        if (!preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches)) {
            return response()->json(['message' => 'Token perangkat diperlukan.'], 401);
        }

        $tokenHash = hash('sha256', $matches[1]);
        $device = Device::where('token_hash', $tokenHash)->first();

        if (!$device) {
            return response()->json(['message' => 'Token perangkat tidak valid atau sudah dicabut.'], 401);
        }

        $device->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('authenticatedDevice', $device);

        return $next($request);
    }
}
