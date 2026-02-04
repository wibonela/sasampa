<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileAppRequest;
use App\Models\MobileDevice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMobileAccessController extends Controller
{
    /**
     * Display list of mobile access requests.
     */
    public function index(Request $request): View
    {
        $query = MobileAppRequest::with(['company.owner', 'reviewer']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(20);

        $stats = [
            'pending' => MobileAppRequest::pending()->count(),
            'approved' => MobileAppRequest::approved()->count(),
            'rejected' => MobileAppRequest::rejected()->count(),
            'revoked' => MobileAppRequest::revoked()->count(),
            'total' => MobileAppRequest::count(),
        ];

        return view('admin.mobile-access.index', compact('requests', 'stats'));
    }

    /**
     * Display details of a mobile access request.
     */
    public function show(MobileAppRequest $mobileAppRequest): View
    {
        $mobileAppRequest->load(['company.owner', 'company.users', 'reviewer']);

        // Get registered devices for this company
        $devices = MobileDevice::where('company_id', $mobileAppRequest->company_id)
            ->with('user')
            ->orderByDesc('last_active_at')
            ->get();

        return view('admin.mobile-access.show', [
            'request' => $mobileAppRequest,
            'devices' => $devices,
        ]);
    }

    /**
     * Approve a mobile access request.
     */
    public function approve(MobileAppRequest $mobileAppRequest)
    {
        if (!$mobileAppRequest->isPending()) {
            return back()->with('error', 'This request cannot be approved as it is not pending.');
        }

        $mobileAppRequest->approve(auth()->user());

        return back()->with('success', "Mobile access approved for '{$mobileAppRequest->company->name}'.");
    }

    /**
     * Reject a mobile access request.
     */
    public function reject(Request $request, MobileAppRequest $mobileAppRequest)
    {
        if (!$mobileAppRequest->isPending()) {
            return back()->with('error', 'This request cannot be rejected as it is not pending.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $mobileAppRequest->reject(auth()->user(), $validated['reason']);

        return back()->with('success', "Mobile access rejected for '{$mobileAppRequest->company->name}'.");
    }

    /**
     * Revoke an approved mobile access.
     */
    public function revoke(Request $request, MobileAppRequest $mobileAppRequest)
    {
        if (!$mobileAppRequest->isApproved()) {
            return back()->with('error', 'This request cannot be revoked as it is not approved.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $mobileAppRequest->revoke(auth()->user(), $validated['reason']);

        // Deactivate all devices for this company
        MobileDevice::where('company_id', $mobileAppRequest->company_id)
            ->update(['is_active' => false]);

        return back()->with('success', "Mobile access revoked for '{$mobileAppRequest->company->name}'.");
    }

    /**
     * Display list of all registered devices.
     */
    public function devices(Request $request): View
    {
        $query = MobileDevice::with(['company', 'user']);

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by status
        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $devices = $query->orderByDesc('last_active_at')->paginate(30);

        $stats = [
            'total' => MobileDevice::count(),
            'active' => MobileDevice::active()->count(),
            'inactive' => MobileDevice::inactive()->count(),
        ];

        return view('admin.mobile-access.devices', compact('devices', 'stats'));
    }

    /**
     * Deactivate a specific device.
     */
    public function deactivateDevice(MobileDevice $device)
    {
        $device->deactivate();

        return back()->with('success', "Device '{$device->device_name}' has been deactivated.");
    }

    /**
     * Activate a specific device.
     */
    public function activateDevice(MobileDevice $device)
    {
        // Check if company still has mobile access
        if (!$device->company->hasMobileAccess()) {
            return back()->with('error', 'Cannot activate device - company does not have mobile access.');
        }

        $device->activate();

        return back()->with('success', "Device '{$device->device_name}' has been activated.");
    }
}
