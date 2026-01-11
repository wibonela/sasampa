<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Platform admins bypass this check
        if ($user->isPlatformAdmin()) {
            return $next($request);
        }

        // User must have a company
        if (!$user->company_id) {
            // Logout user and redirect to register - they need to start fresh
            auth()->logout();
            return redirect()->route('onboarding.step1')
                ->with('error', 'Please complete the registration process.');
        }

        $company = $user->company;

        // Check if onboarding is incomplete - redirect to appropriate step
        if ($company->needsOnboarding()) {
            // For authenticated users, minimum step is 2 (step 1 is guest-only registration)
            $step = max(2, $company->onboarding_step ?? 2);

            // If not verified, go to step 2
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('onboarding.step2');
            }

            // Redirect based on current step (skip step 1 for authenticated users)
            return match($step) {
                2 => redirect()->route('onboarding.step2'),
                3 => redirect()->route('onboarding.step3'),
                4 => redirect()->route('onboarding.step4'),
                default => redirect()->route('onboarding.step2'),
            };
        }

        // Company must be approved
        if (!$company->isApproved()) {
            return redirect()->route('company.pending');
        }

        return $next($request);
    }
}
