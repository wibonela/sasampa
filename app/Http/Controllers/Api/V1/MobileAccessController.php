<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MobileAppRequest;
use App\Models\MobileDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MobileAccessController extends Controller
{
    /**
     * Request mobile app access for the company.
     *
     * POST /api/v1/mobile-access/request
     */
    public function request(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only company owners can request mobile access
        if (!$user->isCompanyOwner()) {
            return response()->json([
                'message' => 'Only company owners can request mobile access.',
            ], 403);
        }

        // Check if there's already a request
        $existingRequest = $user->company->mobileAppRequest;

        if ($existingRequest) {
            if ($existingRequest->isApproved()) {
                return response()->json([
                    'message' => 'Mobile access is already approved for your company.',
                    'request' => $this->formatRequest($existingRequest),
                ], 400);
            }

            if ($existingRequest->isPending()) {
                return response()->json([
                    'message' => 'A mobile access request is already pending.',
                    'request' => $this->formatRequest($existingRequest),
                ], 400);
            }

            // If rejected or revoked, allow a new request
        }

        $validated = $request->validate([
            'request_reason' => 'required|string|max:1000',
            'expected_devices' => 'required|integer|min:1|max:100',
        ]);

        $mobileRequest = MobileAppRequest::create([
            'company_id' => $user->company_id,
            'status' => MobileAppRequest::STATUS_PENDING,
            'request_reason' => $validated['request_reason'],
            'expected_devices' => $validated['expected_devices'],
        ]);

        return response()->json([
            'message' => 'Mobile access request submitted successfully.',
            'request' => $this->formatRequest($mobileRequest),
        ], 201);
    }

    /**
     * Get the status of the mobile access request.
     *
     * GET /api/v1/mobile-access/status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isPlatformAdmin()) {
            return response()->json([
                'status' => 'approved',
                'can_use_mobile' => true,
                'message' => 'Platform admins have full mobile access.',
            ]);
        }

        if (!$user->company) {
            return response()->json([
                'status' => null,
                'can_use_mobile' => false,
                'message' => 'No company associated with this account.',
            ]);
        }

        $mobileRequest = $user->company->mobileAppRequest;

        if (!$mobileRequest) {
            return response()->json([
                'status' => null,
                'can_use_mobile' => false,
                'message' => 'Mobile access has not been requested.',
                'can_request' => $user->isCompanyOwner(),
            ]);
        }

        return response()->json([
            'request' => $this->formatRequest($mobileRequest),
            'can_use_mobile' => $mobileRequest->isApproved(),
            'registered_devices' => $user->company->getActiveMobileDevicesCount(),
        ]);
    }

    /**
     * Register a device after mobile access is approved.
     *
     * POST /api/v1/mobile-access/register-device
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check mobile access
        if (!$user->isPlatformAdmin()) {
            if (!$user->company->hasMobileAccess()) {
                return response()->json([
                    'message' => 'Mobile access has not been approved for your company.',
                ], 403);
            }
        }

        $validated = $request->validate([
            'device_identifier' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
            'device_model' => 'nullable|string|max:255',
            'os_version' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:50',
            'push_token' => 'nullable|string|max:500',
        ]);

        // Check if device is already registered to another user
        $existingDevice = MobileDevice::where('device_identifier', $validated['device_identifier'])
            ->where('user_id', '!=', $user->id)
            ->first();

        if ($existingDevice) {
            throw ValidationException::withMessages([
                'device_identifier' => ['This device is already registered to another user.'],
            ]);
        }

        $device = MobileDevice::findOrCreateByIdentifier(
            $validated['device_identifier'],
            $user->id,
            $user->company_id,
            [
                'device_name' => $validated['device_name'] ?? null,
                'device_model' => $validated['device_model'] ?? null,
                'os_version' => $validated['os_version'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'push_token' => $validated['push_token'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Device registered successfully.',
            'device' => $this->formatDevice($device),
        ]);
    }

    /**
     * Update device push token.
     *
     * PATCH /api/v1/mobile-access/device/push-token
     */
    public function updatePushToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_identifier' => 'required|string',
            'push_token' => 'required|string|max:500',
        ]);

        $user = $request->user();

        $device = MobileDevice::where('device_identifier', $validated['device_identifier'])
            ->where('user_id', $user->id)
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'Device not found.',
            ], 404);
        }

        $device->updatePushToken($validated['push_token']);

        return response()->json([
            'message' => 'Push token updated successfully.',
        ]);
    }

    /**
     * Get list of registered devices for the current user.
     *
     * GET /api/v1/mobile-access/devices
     */
    public function devices(Request $request): JsonResponse
    {
        $user = $request->user();

        $devices = $user->mobileDevices()
            ->orderByDesc('last_active_at')
            ->get()
            ->map(fn ($device) => $this->formatDevice($device));

        return response()->json([
            'devices' => $devices,
        ]);
    }

    /**
     * Deactivate a device.
     *
     * DELETE /api/v1/mobile-access/devices/{device_identifier}
     */
    public function deactivateDevice(Request $request, string $deviceIdentifier): JsonResponse
    {
        $user = $request->user();

        $device = MobileDevice::where('device_identifier', $deviceIdentifier)
            ->where('user_id', $user->id)
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'Device not found.',
            ], 404);
        }

        $device->deactivate();

        // Also revoke any tokens associated with this device name
        $user->tokens()->where('name', $device->device_name)->delete();

        return response()->json([
            'message' => 'Device has been deactivated.',
        ]);
    }

    /**
     * Format mobile access request for API response.
     */
    protected function formatRequest(MobileAppRequest $request): array
    {
        return [
            'id' => $request->id,
            'status' => $request->status,
            'request_reason' => $request->request_reason,
            'expected_devices' => $request->expected_devices,
            'created_at' => $request->created_at->toIso8601String(),
            'approved_at' => $request->approved_at?->toIso8601String(),
            'rejected_at' => $request->rejected_at?->toIso8601String(),
            'revoked_at' => $request->revoked_at?->toIso8601String(),
            'rejection_reason' => $request->rejection_reason,
            'revocation_reason' => $request->revocation_reason,
        ];
    }

    /**
     * Format device for API response.
     */
    protected function formatDevice(MobileDevice $device): array
    {
        return [
            'id' => $device->id,
            'device_identifier' => $device->device_identifier,
            'device_name' => $device->device_name,
            'device_model' => $device->device_model,
            'os_version' => $device->os_version,
            'app_version' => $device->app_version,
            'is_active' => $device->is_active,
            'last_active_at' => $device->last_active_at?->toIso8601String(),
            'registered_at' => $device->created_at->toIso8601String(),
        ];
    }
}
