<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileWaitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class AdminWaitlistController extends Controller
{
    public function index(Request $request): View
    {
        $query = MobileWaitlist::query()->orderByDesc('created_at');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by platform
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        // Filter by business type
        if ($request->filled('business_type')) {
            $query->where('business_type', $request->business_type);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $entries = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => MobileWaitlist::count(),
            'this_week' => MobileWaitlist::thisWeek()->count(),
            'pending' => MobileWaitlist::pending()->count(),
            'contacted' => MobileWaitlist::contacted()->count(),
            'converted' => MobileWaitlist::converted()->count(),
            'ios' => MobileWaitlist::where('platform', 'ios')->orWhere('platform', 'both')->count(),
            'android' => MobileWaitlist::where('platform', 'android')->orWhere('platform', 'both')->count(),
        ];

        return view('admin.waitlist.index', compact('entries', 'stats'));
    }

    public function show(MobileWaitlist $waitlist): View
    {
        return view('admin.waitlist.show', compact('waitlist'));
    }

    public function update(Request $request, MobileWaitlist $waitlist)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,contacted,converted,cancelled',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        if (isset($validated['status'])) {
            if ($validated['status'] === 'contacted' && $waitlist->status !== 'contacted') {
                $validated['contacted_at'] = now();
            }
            if ($validated['status'] === 'converted' && $waitlist->status !== 'converted') {
                $validated['converted_at'] = now();
            }
        }

        $waitlist->update($validated);

        return back()->with('success', 'Entry updated successfully.');
    }

    public function destroy(MobileWaitlist $waitlist)
    {
        $waitlist->delete();

        return redirect()->route('admin.waitlist.index')
            ->with('success', 'Entry deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = MobileWaitlist::query()->orderByDesc('created_at');

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }
        if ($request->filled('business_type')) {
            $query->where('business_type', $request->business_type);
        }

        $entries = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="mobile-waitlist-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($entries) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Name',
                'Phone',
                'Email',
                'Business Name',
                'Business Type',
                'Platform',
                'Status',
                'Joined Date',
                'Contacted At',
                'Converted At',
                'Notes',
            ]);

            // Data rows
            foreach ($entries as $entry) {
                fputcsv($file, [
                    $entry->name,
                    $entry->phone,
                    $entry->email ?? '',
                    $entry->business_name,
                    $entry->business_type_label,
                    $entry->platform_label,
                    $entry->status_label,
                    $entry->created_at->format('Y-m-d H:i:s'),
                    $entry->contacted_at?->format('Y-m-d H:i:s') ?? '',
                    $entry->converted_at?->format('Y-m-d H:i:s') ?? '',
                    $entry->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function analytics(): View
    {
        // Signups over time (last 30 days)
        $signupsByDay = MobileWaitlist::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Business type distribution
        $businessTypes = MobileWaitlist::selectRaw('business_type, COUNT(*) as count')
            ->groupBy('business_type')
            ->pluck('count', 'business_type')
            ->toArray();

        // Platform distribution
        $platforms = MobileWaitlist::selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        // Status distribution
        $statuses = MobileWaitlist::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Conversion rate
        $total = MobileWaitlist::count();
        $converted = MobileWaitlist::converted()->count();
        $conversionRate = $total > 0 ? round(($converted / $total) * 100, 1) : 0;

        return view('admin.waitlist.analytics', compact(
            'signupsByDay',
            'businessTypes',
            'platforms',
            'statuses',
            'conversionRate'
        ));
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:mobile_waitlist,id',
            'status' => 'required|in:pending,contacted,converted,cancelled',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'contacted') {
            $updateData['contacted_at'] = now();
        }
        if ($validated['status'] === 'converted') {
            $updateData['converted_at'] = now();
        }

        MobileWaitlist::whereIn('id', $validated['ids'])->update($updateData);

        return back()->with('success', count($validated['ids']) . ' entries updated successfully.');
    }
}
