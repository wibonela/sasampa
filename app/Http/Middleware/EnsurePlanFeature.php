<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('feature:expenses')
 *
 * A no-op while billing.enforced is false. Mobile/API callers get a neutral
 * `feature_unavailable` error (no plan or pricing wording) so the apps stay
 * store-review friendly; web users are sent to the billing page.
 */
class EnsurePlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();
        $company = $user?->company;

        if (!$user || $user->isPlatformAdmin() || !$company || !$company->billingEnforced()) {
            return $next($request);
        }

        if ($company->hasFeature($feature)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'This feature is not available for your account. Please contact your administrator.',
                'error_code' => 'feature_unavailable',
            ], 403);
        }

        return redirect()->route('billing.index')
            ->with('error', 'That feature is not included in your current plan.');
    }
}
