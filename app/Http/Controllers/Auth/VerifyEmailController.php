<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->company;

        if ($user->hasVerifiedEmail()) {
            // Check if onboarding is incomplete
            if ($company && $company->needsOnboarding()) {
                return redirect()->route('onboarding.step3');
            }
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Update onboarding step and redirect to step 3
        if ($company && $company->needsOnboarding()) {
            $company->update(['onboarding_step' => 3]);
            return redirect()->route('onboarding.step3')->with('verified', true);
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
