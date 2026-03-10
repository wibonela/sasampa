<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\EfdmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EfdSettingsController extends Controller
{
    public function __construct(protected EfdmsService $efdmsService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCompanyOwner() && !$user->hasPermission('manage_settings')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $company = $user->company;

        return response()->json([
            'data' => [
                'tin' => $company->tin,
                'vrn' => $company->vrn,
                'efd_serial_number' => $company->efd_serial_number,
                'efd_uin' => $company->efd_uin,
                'efd_enabled' => $company->efd_enabled,
                'efd_environment' => $company->efd_environment,
                'efd_registered_at' => $company->efd_registered_at?->toIso8601String(),
                'is_efd_ready' => $company->isEfdEnabled(),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCompanyOwner() && !$user->hasPermission('manage_settings')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'tin' => 'nullable|string|max:50',
            'vrn' => 'nullable|string|max:50',
            'efd_serial_number' => 'nullable|string|max:100',
            'efd_enabled' => 'nullable|boolean',
            'efd_environment' => 'nullable|in:sandbox,production,stub',
        ]);

        $company = $user->company;
        $company->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'EFD settings updated successfully.',
            'data' => [
                'tin' => $company->tin,
                'vrn' => $company->vrn,
                'efd_serial_number' => $company->efd_serial_number,
                'efd_uin' => $company->efd_uin,
                'efd_enabled' => $company->efd_enabled,
                'efd_environment' => $company->efd_environment,
                'efd_registered_at' => $company->efd_registered_at?->toIso8601String(),
                'is_efd_ready' => $company->isEfdEnabled(),
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCompanyOwner() && !$user->hasPermission('manage_settings')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $company = $user->company;

        if (!$company->tin || !$company->efd_serial_number) {
            return response()->json([
                'success' => false,
                'message' => 'TIN and EFD Serial Number are required for registration.',
            ], 422);
        }

        $result = $this->efdmsService->registerDevice($company);

        if ($result['success']) {
            $company->update([
                'efd_uin' => $result['uin'],
                'efd_registered_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'efd_uin' => $result['uin'],
                    'efd_registered_at' => $company->fresh()->efd_registered_at->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    public function test(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCompanyOwner() && !$user->hasPermission('manage_settings')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $company = $user->company;

        if (!$company->isEfdEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'EFD is not fully configured. Ensure TIN, serial number, UIN are set and EFD is enabled.',
            ], 422);
        }

        // Find the most recent completed transaction to test with
        $transaction = Transaction::where('company_id', $company->id)
            ->completed()
            ->latest()
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'No completed transactions found to test with.',
            ], 422);
        }

        $transaction->load(['items', 'user.company']);
        $result = $this->efdmsService->signReceipt($transaction);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Test receipt signed successfully.',
                'data' => [
                    'fiscal_receipt_number' => $result['fiscal_receipt_number'],
                    'fiscal_verification_code' => $result['fiscal_verification_code'],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        $pendingCount = Transaction::where('company_id', $company->id)
            ->fiscalPending()
            ->count();

        $recentPending = Transaction::where('company_id', $company->id)
            ->fiscalPending()
            ->latest()
            ->limit(10)
            ->get(['id', 'transaction_number', 'total', 'fiscal_submission_error', 'created_at']);

        return response()->json([
            'data' => [
                'pending_count' => $pendingCount,
                'recent' => $recentPending->map(fn ($t) => [
                    'id' => $t->id,
                    'transaction_number' => $t->transaction_number,
                    'total' => (float) $t->total,
                    'error' => $t->fiscal_submission_error,
                    'created_at' => $t->created_at->toIso8601String(),
                ]),
            ],
        ]);
    }

    public function retry(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCompanyOwner() && !$user->hasPermission('manage_settings')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $company = $user->company;
        $results = $this->efdmsService->retryFailedReceipts($company);

        return response()->json([
            'success' => true,
            'message' => "Retried {$results['total']} receipts: {$results['success']} succeeded, {$results['failed']} failed.",
            'data' => $results,
        ]);
    }
}
