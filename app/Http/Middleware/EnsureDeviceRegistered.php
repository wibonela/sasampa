<?php

namespace App\Http\Middleware;

use App\Models\MobileDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceRegistered
{
    /**
     * Handle an incoming request.
     *
     * Ensure the device making the request is registered and active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Platform admins bypass this check
        if ($user->isPlatformAdmin()) {
            return $next($request);
        }

        // Get device identifier from header
        $deviceIdentifier = $request->header('X-Device-ID');

        if (!$deviceIdentifier) {
            return response()->json([
                'message' => 'Device identifier is required.',
                'error_code' => 'device_id_missing',
            ], 400);
        }

        // Find the device
        $device = MobileDevice::where('device_identifier', $deviceIdentifier)
            ->where('user_id', $user->id)
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'This device is not registered.',
                'error_code' => 'device_not_registered',
            ], 403);
        }

        if (!$device->is_active) {
            return response()->json([
                'message' => 'This device has been deactivated.',
                'error_code' => 'device_deactivated',
            ], 403);
        }

        // Store device in request for later use
        $request->merge(['mobile_device' => $device]);

        return $next($request);
    }
}
