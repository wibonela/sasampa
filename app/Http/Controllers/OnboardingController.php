<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Step 1: Show account creation form
     */
    public function showStep1(): View
    {
        return view('onboarding.step1', ['currentStep' => 1]);
    }

    /**
     * Step 1: Process account creation
     */
    public function processStep1(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = DB::transaction(function () use ($validated) {
            // Create placeholder company
            $company = Company::create([
                'name' => 'Pending Setup',
                'email' => $validated['email'],
                'status' => Company::STATUS_PENDING,
                'onboarding_step' => 2,
            ]);

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'company_id' => $company->id,
                'role' => 'company_owner',
            ]);

            return $user;
        });

        // Fire Registered event (sends verification email)
        event(new Registered($user));

        // Log the user in
        Auth::login($user);

        return redirect()->route('onboarding.step2');
    }

    /**
     * Step 2: Show email verification page
     */
    public function showStep2(): View|RedirectResponse
    {
        $user = auth()->user();

        // If already verified, skip to step 3
        if ($user->hasVerifiedEmail()) {
            $user->company->update(['onboarding_step' => 3]);
            return redirect()->route('onboarding.step3');
        }

        return view('onboarding.step2', [
            'currentStep' => 2,
            'email' => $user->email,
        ]);
    }

    /**
     * Step 2: Resend verification email
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Step 3: Show business details form
     */
    public function showStep3(): View
    {
        $user = auth()->user();
        $company = $user->company;

        // Update step if needed
        if ($company->onboarding_step < 3) {
            $company->update(['onboarding_step' => 3]);
        }

        return view('onboarding.step3', [
            'currentStep' => 3,
            'company' => $company,
        ]);
    }

    /**
     * Step 3: Process business details
     */
    public function processStep3(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $company = auth()->user()->company;

        $updateData = [
            'name' => $validated['company_name'],
            'address' => $validated['company_address'],
            'phone' => $validated['company_phone'],
            'onboarding_step' => 4,
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $updateData['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        $company->update($updateData);

        return redirect()->route('onboarding.step4');
    }

    /**
     * Step 4: Show welcome/complete page
     */
    public function showStep4(): View
    {
        $user = auth()->user();
        $company = $user->company;

        return view('onboarding.step4', [
            'currentStep' => 4,
            'company' => $company,
            'user' => $user,
        ]);
    }

    /**
     * Step 4: Finish onboarding - AUTO-APPROVE company
     */
    public function finishOnboarding(Request $request): RedirectResponse
    {
        $company = auth()->user()->company;

        // AUTO-APPROVE the company (no admin approval needed)
        $company->update([
            'status' => Company::STATUS_APPROVED,
            'approved_at' => now(),
            'onboarding_completed' => true,
            'onboarding_step' => 4,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to Sasampa POS! Your account is ready to use.');
    }
}
