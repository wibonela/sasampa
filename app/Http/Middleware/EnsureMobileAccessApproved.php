<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileAccessApproved
{
    /**
     * Handle an incoming request.
     *
     * Ensure the user's company has approved mobile app access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Platform admins bypass this check
        if ($user->isPlatformAdmin()) {
            return $next($request);
        }

        // User must belong to a company
        if (!$user->company) {
            return response()->json([
                'message' => 'No company associated with this account.',
                'error_code' => 'no_company',
            ], 403);
        }

        // Company must be approved
        if (!$user->company->isApproved()) {
            return response()->json([
                'message' => 'Your company account is pending approval.',
                'error_code' => 'company_not_approved',
            ], 403);
        }

        // Company must have approved mobile access
        if (!$user->company->hasMobileAccess()) {
            $mobileRequest = $user->company->mobileAppRequest;

            if (!$mobileRequest) {
                return response()->json([
                    'message' => 'Mobile access has not been requested for your company.',
                    'error_code' => 'mobile_access_not_requested',
                ], 403);
            }

            if ($mobileRequest->isPending()) {
                return response()->json([
                    'message' => 'Your mobile access request is pending approval.',
                    'error_code' => 'mobile_access_pending',
                ], 403);
            }

            if ($mobileRequest->isRejected()) {
                return response()->json([
                    'message' => 'Your mobile access request was rejected.',
                    'error_code' => 'mobile_access_rejected',
                    'reason' => $mobileRequest->rejection_reason,
                ], 403);
            }

            if ($mobileRequest->isRevoked()) {
                return response()->json([
                    'message' => 'Your mobile access has been revoked.',
                    'error_code' => 'mobile_access_revoked',
                    'reason' => $mobileRequest->revocation_reason,
                ], 403);
            }
        }

        return $next($request);
    }
}
