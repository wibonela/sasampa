<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Services\BillingService;
use Illuminate\Http\Request;

class CompanyBillingController extends Controller
{
    public function __construct(private BillingService $billing) {}

    /**
     * Set a company's plan and/or period end directly.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'current_period_end' => 'nullable|date',
        ]);

        $this->billing->assign(
            $company,
            Plan::findOrFail($validated['plan_id']),
            isset($validated['current_period_end']) ? \Carbon\Carbon::parse($validated['current_period_end'])->endOfDay() : null,
        );

        return back()->with('success', 'Subscription updated.');
    }

    /**
     * Record a payment received outside the gateway and extend the subscription.
     */
    public function recordPayment(Request $request, Company $company)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'months' => 'required|integer|in:' . implode(',', array_keys(config('billing.discounts'))),
            'amount_tzs' => 'required|integer|min:0',
            'reference' => 'nullable|string|max:100|unique:payments,gateway_ref',
            'note' => 'nullable|string|max:500',
        ]);

        $this->billing->recordManualPayment(
            $company,
            Plan::findOrFail($validated['plan_id']),
            (int) $validated['months'],
            (int) $validated['amount_tzs'],
            $validated['reference'] ?? null,
            $validated['note'] ?? null,
            $request->user()->id,
        );

        return back()->with('success', 'Payment recorded and subscription extended.');
    }
}
