<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\BillingService;
use App\Services\Payments\PaymentGateway;
use Illuminate\Console\Command;

class VerifyPendingPayments extends Command
{
    protected $signature = 'payments:verify-pending';

    protected $description = 'Re-check pending gateway payments in case a webhook was missed; fail those older than a day';

    public function handle(BillingService $billing, PaymentGateway $gateway): int
    {
        if (!$gateway->isConfigured()) {
            return self::SUCCESS;
        }

        Payment::where('gateway', 'selcom')
            ->where('status', Payment::STATUS_PENDING)
            ->each(function (Payment $payment) use ($billing, $gateway) {
                try {
                    $status = $billing->settle($payment, $gateway);
                } catch (\Throwable $e) {
                    $this->error("Payment {$payment->id}: {$e->getMessage()}");
                    return;
                }

                if ($status === Payment::STATUS_PENDING && $payment->created_at->lt(now()->subDay())) {
                    $payment->update(['status' => Payment::STATUS_FAILED]);
                }
            });

        return self::SUCCESS;
    }
}
