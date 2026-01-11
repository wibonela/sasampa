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
}
