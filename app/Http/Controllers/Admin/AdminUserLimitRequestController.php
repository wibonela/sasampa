<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLimitRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserLimitRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $requests = UserLimitRequest::with(['company', 'requester', 'handler'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'pending' => UserLimitRequest::pending()->count(),
            'approved' => UserLimitRequest::approved()->count(),
            'rejected' => UserLimitRequest::rejected()->count(),
        ];

        return view('admin.user-limit-requests.index', compact('requests', 'stats', 'status'));
    }

    public function show(UserLimitRequest $userLimitRequest): View
    {
        $userLimitRequest->load(['company.users', 'requester', 'handler']);

        return view('admin.user-limit-requests.show', compact('userLimitRequest'));
    }

    public function approve(Request $request, UserLimitRequest $userLimitRequest): RedirectResponse
    {
        if (!$userLimitRequest->isPending()) {
            return back()->with('error', 'This request has already been handled.');
        }

        $validated = $request->validate([
            'approved_limit' => 'nullable|integer|min:' . $userLimitRequest->current_limit,
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $userLimitRequest->approve(
            auth()->user(),
            $validated['admin_notes'] ?? null,
            $validated['approved_limit'] ?? null
        );

        return redirect()->route('admin.user-limit-requests.index')
            ->with('success', "User limit request approved. {$userLimitRequest->company->name} now has {$userLimitRequest->company->fresh()->user_limit} user slots.");
    }

    public function reject(Request $request, UserLimitRequest $userLimitRequest): RedirectResponse
    {
        if (!$userLimitRequest->isPending()) {
            return back()->with('error', 'This request has already been handled.');
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $userLimitRequest->reject(auth()->user(), $validated['admin_notes']);

        return redirect()->route('admin.user-limit-requests.index')
            ->with('success', 'User limit request has been rejected.');
    }
}
