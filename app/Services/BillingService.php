<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payments\PaymentGateway;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Start the free trial for a newly approved company. No-op if it already has a subscription.
     */
    public function startTrial(Company $company): ?Subscription
    {
        if ($company->subscription()->exists()) {
            return null;
        }

        $plan = Plan::where('key', config('billing.default_plan'))->first();

        if (!$plan) {
            \Illuminate\Support\Facades\Log::warning('Billing trial not started: default plan is missing', ['company' => $company->id]);
            return null;
        }

        $end = now()->addDays((int) config('billing.trial_days'));

        return Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_TRIALING,
            'trial_ends_at' => $end,
            'current_period_end' => $end,
        ]);
    }

    /**
     * Price for a plan over a number of months, applying the prepaid discount.
     */
    public function priceFor(Plan $plan, int $months): int
    {
        $discount = config('billing.discounts')[$months] ?? 0;

        return (int) round($plan->price_tzs * $months * (100 - $discount) / 100);
    }

    /**
     * Assign a plan and/or set the period end directly (admin override).
     */
    public function assign(Company $company, Plan $plan, ?\DateTimeInterface $periodEnd = null): Subscription
    {
        $subscription = $company->subscription ?? new Subscription(['company_id' => $company->id]);

        $subscription->plan_id = $plan->id;
        if ($periodEnd) {
            $subscription->current_period_end = $periodEnd;
        }
        $subscription->status = $this->statusFor($subscription);
        $subscription->save();

        return $subscription;
    }

    /**
     * Record a completed payment and extend the subscription. Idempotent per payment.
     */
    public function applyPaidPayment(Payment $payment): Subscription
    {
        return DB::transaction(function () use ($payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $company = Company::findOrFail($payment->company_id);
            $subscription = $company->subscription()->first() ?? new Subscription(['company_id' => $company->id]);

            if ($payment->status === Payment::STATUS_PAID && $payment->subscription_id) {
                return $subscription;
            }

            $plan = $payment->plan_id ? Plan::find($payment->plan_id) : $subscription->plan;
            $plan ??= Plan::where('key', config('billing.default_plan'))->firstOrFail();

            // Extend from the current end when still running, otherwise from now.
            $base = $subscription->isCurrent() ? $subscription->current_period_end : now();

            $subscription->plan_id = $plan->id;
            $subscription->current_period_end = $base->copy()->addMonths($payment->months);
            $subscription->status = Subscription::STATUS_ACTIVE;
            $subscription->save();

            $payment->update([
                'status' => Payment::STATUS_PAID,
                'paid_at' => $payment->paid_at ?? now(),
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
            ]);

            return $subscription;
        });
    }

    /**
     * Ask the gateway for a pending payment's real status and apply the outcome.
     * Returns the resulting status.
     */
    public function settle(Payment $payment, PaymentGateway $gateway): string
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return $payment->status;
        }

        $status = $gateway->status($payment);

        if ($status === Payment::STATUS_PAID) {
            $this->applyPaidPayment($payment);
        } elseif ($status === Payment::STATUS_FAILED) {
            $payment->update(['status' => Payment::STATUS_FAILED]);
        }

        return $status;
    }

    /**
     * Admin records a payment received outside the gateway (cash, bank, mobile money).
     */
    public function recordManualPayment(Company $company, Plan $plan, int $months, int $amount, ?string $reference, ?string $note, ?int $userId): Payment
    {
        $payment = Payment::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'amount_tzs' => $amount,
            'months' => $months,
            'gateway' => 'manual',
            'gateway_ref' => $reference ?: null,
            'status' => Payment::STATUS_PENDING,
            'note' => $note,
            'recorded_by' => $userId,
        ]);

        $this->applyPaidPayment($payment);

        return $payment->fresh();
    }

    /**
     * Move subscriptions through past_due -> expired as their period lapses.
     * Returns the number of rows changed.
     */
    public function expireDue(): int
    {
        $changed = 0;
        $graceDays = (int) config('billing.past_due_days');

        Subscription::whereIn('status', [Subscription::STATUS_TRIALING, Subscription::STATUS_ACTIVE, Subscription::STATUS_PAST_DUE])
            ->where('current_period_end', '<', now())
            ->each(function (Subscription $subscription) use ($graceDays, &$changed) {
                $new = $subscription->current_period_end->copy()->addDays($graceDays)->isPast()
                    ? Subscription::STATUS_EXPIRED
                    : Subscription::STATUS_PAST_DUE;

                if ($new !== $subscription->status) {
                    $subscription->update(['status' => $new]);
                    $changed++;
                }
            });

        return $changed;
    }

    private function statusFor(Subscription $subscription): string
    {
        if (!$subscription->current_period_end || $subscription->current_period_end->isPast()) {
            return Subscription::STATUS_EXPIRED;
        }

        return $subscription->trial_ends_at && $subscription->current_period_end->lte($subscription->trial_ends_at)
            ? Subscription::STATUS_TRIALING
            : Subscription::STATUS_ACTIVE;
    }
}
