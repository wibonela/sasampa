<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CompanyApproved;
use App\Mail\CompanyRejected;
use App\Models\Company;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class CompanyManagementController extends Controller
{
    public function __construct(
        private AdminNotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $query = Company::with('owner');

        if ($request->filled('status')) {
            if ($request->status === 'pending_setup') {
                // Approved but onboarding not completed
                $query->where('status', Company::STATUS_APPROVED)
                      ->where('onboarding_completed', false);
            } else {
                $query->where('status', $request->status);
            }
        }

        $companies = $query->latest()->paginate(20);

        $stats = [
            'pending' => Company::pending()->count(),
            'pending_setup' => Company::where('status', Company::STATUS_APPROVED)
                                      ->where('onboarding_completed', false)->count(),
            'approved' => Company::approved()->where('onboarding_completed', true)->count(),
            'total' => Company::count(),
        ];

        return view('admin.companies.index', compact('companies', 'stats'));
    }

    public function show(Company $company)
    {
        $company->load(['users', 'owner']);
        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        $company->load('owner');
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('companies', 'email')->ignore($company->id)],
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:30',
        ]);

        $company->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        $owner = $company->owner;
        if ($owner && ($request->filled('owner_name') || $request->filled('owner_email') || $request->filled('owner_phone'))) {
            $ownerEmailInput = $validated['owner_email'] ?? $owner->email;
            $request->validate([
                'owner_email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($owner->id)],
            ]);

            $ownerEmailChanged = strtolower(trim($ownerEmailInput)) !== strtolower((string) $owner->email);

            $owner->fill([
                'name' => $validated['owner_name'] ?? $owner->name,
                'email' => $ownerEmailInput,
                'phone' => $validated['owner_phone'] ?? $owner->phone,
            ]);

            if ($ownerEmailChanged) {
                $owner->email_verified_at = null;
            }

            $owner->save();

            if ($ownerEmailChanged) {
                try {
                    $owner->sendEmailVerificationNotification();
                } catch (\Throwable $e) {
                    return redirect()->route('admin.companies.show', $company)
                        ->with('success', "Company updated. Owner email changed but verification email could not be sent ({$e->getMessage()}).");
                }

                return redirect()->route('admin.companies.show', $company)
                    ->with('success', "Company and owner updated. A new verification email has been sent to {$owner->email}.");
            }
        }

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "Company '{$company->name}' has been updated.");
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
        // Allow deletion of:
        // 1. Pending companies older than 3 days
        // 2. Pending setup (approved but onboarding not completed) older than 3 days
        $isPending = $company->status === Company::STATUS_PENDING;
        $isPendingSetup = $company->status === Company::STATUS_APPROVED && !$company->onboarding_completed;

        if (!$isPending && !$isPendingSetup) {
            return back()->with('error', 'Only pending or pending setup companies can be deleted.');
        }

        if ($company->created_at->diffInDays(now()) < 3) {
            return back()->with('error', 'Companies can only be deleted after 3 days.');
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
