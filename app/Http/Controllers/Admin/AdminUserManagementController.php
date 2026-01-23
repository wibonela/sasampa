<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitation;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Inventory;
use App\Models\PinSession;
use App\Models\Setting;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AdminUserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['company', 'branches'])
            ->whereNotNull('company_id'); // Exclude platform admins

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by company
        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        // Filter by verification status
        if ($request->filled('verification')) {
            if ($request->verification === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->verification === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        // Filter by invitation status
        if ($request->filled('invitation')) {
            if ($request->invitation === 'pending') {
                $query->whereNotNull('invitation_token')
                    ->whereNull('invitation_accepted_at');
            } elseif ($request->invitation === 'accepted') {
                $query->whereNotNull('invitation_accepted_at');
            } elseif ($request->invitation === 'expired') {
                $query->whereNotNull('invitation_token')
                    ->whereNull('invitation_accepted_at')
                    ->where('invitation_sent_at', '<', now()->subDays(2));
            }
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by active status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        // Get companies for filter dropdown
        $companies = Company::orderBy('name')->get();

        // Calculate stats
        $stats = [
            'total' => User::whereNotNull('company_id')->count(),
            'verified' => User::whereNotNull('company_id')->whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNotNull('company_id')->whereNull('email_verified_at')->count(),
            'pending_invitations' => User::whereNotNull('company_id')
                ->whereNotNull('invitation_token')
                ->whereNull('invitation_accepted_at')
                ->count(),
            'inactive' => User::whereNotNull('company_id')->where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'companies', 'stats'));
    }

    public function show(User $user): View
    {
        // Ensure we're not viewing a platform admin
        if ($user->isPlatformAdmin()) {
            abort(404);
        }

        $user->load(['company', 'branches']);

        // Build status diagnosis
        $diagnosis = [
            'has_company' => $user->company !== null,
            'company_approved' => $user->company?->isApproved() ?? false,
            'email_verified' => $user->email_verified_at !== null,
            'invitation_accepted' => $user->invitation_accepted_at !== null,
            'invitation_pending' => $user->hasPendingInvitation(),
            'invitation_expired' => $user->hasPendingInvitation() && $user->isInvitationExpired(),
            'is_active' => $user->is_active,
            'has_pin' => $user->hasPin(),
            'has_branches' => $user->branches->count() > 0,
        ];

        // Determine if user can log in
        $diagnosis['can_login'] = $diagnosis['company_approved']
            && $diagnosis['email_verified']
            && $diagnosis['is_active']
            && ($diagnosis['invitation_accepted'] || $user->invitation_method === 'pin');

        return view('admin.users.show', compact('user', 'diagnosis'));
    }

    public function verifyEmail(User $user): RedirectResponse
    {
        if ($user->isPlatformAdmin()) {
            abort(404);
        }

        if ($user->email_verified_at) {
            return back()->with('error', 'Email is already verified.');
        }

        $user->update(['email_verified_at' => now()]);

        return back()->with('success', "Email for {$user->name} has been manually verified.");
    }

    public function resendVerification(User $user): RedirectResponse
    {
        if ($user->isPlatformAdmin()) {
            abort(404);
        }

        if ($user->email_verified_at) {
            return back()->with('error', 'Email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', "Verification email has been resent to {$user->email}.");
    }

    public function regenerateInvitation(User $user): RedirectResponse
    {
        if ($user->isPlatformAdmin()) {
            abort(404);
        }

        if ($user->invitation_accepted_at) {
            return back()->with('error', 'User has already accepted their invitation.');
        }

        $token = $user->generateInvitationToken();
        Mail::to($user->email)->queue(new UserInvitation($user, route('invitation.show', $token)));

        return back()->with('success', "New invitation email has been sent to {$user->email}.");
    }

    public function forceAcceptInvitation(User $user): RedirectResponse
    {
        if ($user->isPlatformAdmin()) {
            abort(404);
        }

        if ($user->invitation_accepted_at) {
            return back()->with('error', 'Invitation is already accepted.');
        }

        $user->update([
            'invitation_token' => null,
            'invitation_accepted_at' => now(),
        ]);

        return back()->with('success', "Invitation for {$user->name} has been force-accepted.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        if ($user->isPlatformAdmin()) {
            abort(404);
        }

        // Generate password reset token and send email
        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);

        return back()->with('success', "Password reset link has been sent to {$user->email}.");
    }

    public function resetPin(User $user): RedirectResponse
    {
        if ($user->isPlatformAdmin()) {
            abort(404);
        }

        if (!$user->hasPin()) {
            return back()->with('error', 'User does not have a PIN set.');
        }

        // Generate new 4-digit PIN
        $newPin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $user->setPin($newPin);

        return back()->with('success', "PIN has been reset for {$user->name}. New PIN: {$newPin}");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->isPlatformAdmin()) {
            abort(404);
        }

        // Don't allow deactivating company owners
        if ($user->isCompanyOwner() && $user->is_active) {
            return back()->with('error', 'Cannot deactivate a company owner. Suspend the company instead.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "{$user->name} has been {$status}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isPlatformAdmin()) {
            abort(404);
        }

        $userName = $user->name;
        $isOwner = $user->isCompanyOwner();

        if ($isOwner && $user->company) {
            // Delete company owner - this deletes the entire company and all related data
            $companyName = $user->company->name;
            $company = $user->company;

            DB::transaction(function () use ($company) {
                // Delete all related data explicitly to ensure clean removal
                $companyId = $company->id;

                // Delete transactions and their items
                $company->transactions()->each(function ($transaction) {
                    $transaction->items()->delete();
                });
                $company->transactions()->delete();

                // Delete stock adjustments
                StockAdjustment::where('company_id', $companyId)->delete();

                // Delete inventory
                Inventory::where('company_id', $companyId)->delete();

                // Delete products
                $company->products()->delete();

                // Delete categories
                $company->categories()->delete();

                // Delete expenses
                Expense::where('company_id', $companyId)->delete();

                // Delete expense categories
                ExpenseCategory::where('company_id', $companyId)->delete();

                // Delete settings
                Setting::where('company_id', $companyId)->delete();

                // Delete PIN sessions
                PinSession::where('company_id', $companyId)->delete();

                // Delete user limit requests
                $company->userLimitRequests()->delete();

                // Delete branches (detach users first)
                $company->branches()->each(function ($branch) {
                    $branch->users()->detach();
                });
                $company->branches()->delete();

                // Delete all users permissions first
                $company->users()->each(function ($user) {
                    $user->permissions()->detach();
                });

                // Delete all users
                $company->users()->delete();

                // Finally delete the company
                $company->delete();
            });

            return redirect()->route('admin.users.index')
                ->with('success', "Company '{$companyName}' and all its data (including user {$userName}) have been permanently deleted.");
        }

        // Regular user (cashier) - just delete the user
        DB::transaction(function () use ($user) {
            // Detach from branches
            $user->branches()->detach();

            // Delete user's permissions
            $user->permissions()->detach();

            // Delete PIN sessions
            $user->pinSessions()->delete();

            // Delete the user
            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' has been permanently deleted.");
    }
}
