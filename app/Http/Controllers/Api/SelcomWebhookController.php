<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\BillingService;
use App\Services\Payments\SelcomGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SelcomWebhookController extends Controller
{
    /**
     * The webhook body is never trusted to mark a payment paid. It only tells us
     * which order to look up; the status comes from a signed server-to-server call.
     */
    public function handle(Request $request, SelcomGateway $gateway, BillingService $billing)
    {
        $orderId = (string) $request->input('order_id');
        $headers = collect($request->headers->all())->map(fn ($values) => $values[0] ?? '')->all();

        if (!$gateway->signatureIsValid($headers, $request->all())) {
            Log::warning('Selcom webhook signature did not verify', ['order_id' => $orderId]);
        }

        $payment = Payment::where('gateway', 'selcom')->where('gateway_ref', $orderId)->first();

        if ($payment) {
            try {
                $billing->settle($payment, $gateway);
            } catch (\Throwable $e) {
                Log::error('Selcom status lookup failed', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            }
        }

        // Always acknowledge so Selcom does not keep retrying for unknown orders.
        return response()->json(['result' => 'SUCCESS']);
    }
}
