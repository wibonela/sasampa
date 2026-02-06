<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CompanyApproved;
use App\Mail\CompanyRejected;
use App\Models\Company;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CompanyManagementController extends Controller
{
    public function __construct(
        private AdminNotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $query = Company::with('owner');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $companies = $query->latest()->paginate(20);

        $stats = [
            'pending' => Company::pending()->count(),
            'approved' => Company::approved()->count(),
            'total' => Company::count(),
        ];

        return view('admin.companies.index', compact('companies', 'stats'));
    }

    public function show(Company $company)
    {
        $company->load(['users', 'owner']);
        return view('admin.companies.show', compact('company'));
    }

    public function approve(Company $company)
    {
        $company->update([
            'status' => Company::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        // Create notification
        $this->notificationService->notifyCompanyApproved($company);

        // Send email to company owner
        if ($company->owner) {
            Mail::to($company->owner->email)->queue(new CompanyApproved($company));
        }

        return back()->with('success', "Company '{$company->name}' has been approved.");
    }

    public function reject(Request $request, Company $company)
    {
        $reason = $request->input('reason');

        $company->update([
            'status' => Company::STATUS_REJECTED,
        ]);

        // Create notification
        $this->notificationService->notifyCompanyRejected($company);

        // Send email to company owner
        if ($company->owner) {
            Mail::to($company->owner->email)->queue(new CompanyRejected($company, $reason));
        }

        return back()->with('success', "Company '{$company->name}' has been rejected.");
    }

    public function suspend(Company $company)
    {
        $company->update(['is_suspended' => true]);

        return back()->with('success', "Company '{$company->name}' has been suspended.");
    }

    public function unsuspend(Company $company)
    {
        $company->update(['is_suspended' => false]);

        return back()->with('success', "Company '{$company->name}' has been unsuspended.");
    }

    public function updateLimit(Request $request, Company $company)
    {
        $validated = $request->validate([
            'user_limit' => 'required|integer|min:1|max:100',
        ]);

        $company->update(['user_limit' => $validated['user_limit']]);

        return back()->with('success', "User limit for '{$company->name}' has been updated to {$validated['user_limit']}.");
    }

    public function destroy(Company $company)
    {
        // Only allow deletion of pending companies older than 3 days
        if ($company->status !== Company::STATUS_PENDING) {
            return back()->with('error', 'Only pending companies can be deleted.');
        }

        if ($company->created_at->diffInDays(now()) < 3) {
            return back()->with('error', 'Companies can only be deleted after being pending for 3 days.');
        }

        $companyName = $company->name;

        // Delete related data
        $company->users()->delete();
        $company->branches()->delete();
        $company->categories()->delete();

        // Delete the company
        $company->delete();

        return back()->with('success', "Company '{$companyName}' has been permanently deleted.");
    }
}
