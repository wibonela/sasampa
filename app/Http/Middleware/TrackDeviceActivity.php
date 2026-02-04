<?php

namespace App\Http\Middleware;

use App\Models\MobileDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackDeviceActivity
{
    /**
     * Handle an incoming request.
     *
     * Track the last activity time for the device.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track activity after the request is processed
        $this->trackActivity($request);

        return $response;
    }

    /**
     * Track device activity.
     */
    protected function trackActivity(Request $request): void
    {
        $user = $request->user();

        if (!$user || $user->isPlatformAdmin()) {
            return;
        }

        $deviceIdentifier = $request->header('X-Device-ID');

        if (!$deviceIdentifier) {
            return;
        }

        // Update device activity (non-blocking, quick update)
        MobileDevice::where('device_identifier', $deviceIdentifier)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update([
                'last_active_at' => now(),
                'app_version' => $request->header('X-App-Version') ?? null,
            ]);
    }
}
