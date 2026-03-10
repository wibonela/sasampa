<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\WhatsappReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppReceiptController extends Controller
{
    public function __construct(
        protected WhatsappReceiptService $receiptService,
    ) {}

    /**
     * Send or resend a WhatsApp receipt for a transaction.
     */
    public function send(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $transaction = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:50',
            'resend' => 'nullable|boolean',
        ]);

        $phone = $validated['phone'] ?? null;
        $isResend = $validated['resend'] ?? false;

        if ($isResend && $phone) {
            $result = $this->receiptService->resendReceipt($transaction, $phone);
        } else {
            $result = $this->receiptService->sendReceipt($transaction, $phone);
        }

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => [
                'log_id' => $result['log_id'] ?? null,
            ],
        ], $result['success'] ? 200 : 422);
    }

    /**
     * Get WhatsApp receipt delivery status for a transaction.
     */
    public function status(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $transaction = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        $status = $this->receiptService->getReceiptStatus($id);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }
}
