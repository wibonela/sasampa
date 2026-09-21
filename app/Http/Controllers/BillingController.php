<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Services\Payments\PaymentGateway;
use Illuminate\Support\Str;
use App\Services\BillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(private BillingService $billing, private PaymentGateway $gateway) {}

    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless($user->isCompanyOwner(), 403);

        $company = $user->company;
        $subscription = $company->subscription?->load('plan');
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $payments = $company->payments()->with('plan')->latest()->limit(20)->get();
        $discounts = config('billing.discounts');

        return view('billing.index', [
            'company' => $company,
            'subscription' => $subscription,
            'plans' => $plans,
            'payments' => $payments,
            'discounts' => $discounts,
            'billing' => $this->billing,
            'instructions' => config('billing.payment_instructions'),
            'onlinePayment' => $this->gateway->isConfigured(),
        ]);
    }

    /**
     * Start an online payment: create a pending payment, then push the prompt to the owner's phone.
     */
    public function checkout(Request $request)
    {
        abort_unless($request->user()->isCompanyOwner(), 403);
        abort_unless($this->gateway->isConfigured(), 404);

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'months' => 'required|integer|in:' . implode(',', array_keys(config('billing.discounts'))),
            'msisdn' => ['required', 'regex:/^(?:\+?255|0)[67]\d{8}$/'],
        ], ['msisdn.regex' => 'Enter a valid Tanzanian mobile number, e.g. 0712345678.']);

        $plan = Plan::where('is_active', true)->findOrFail($validated['plan_id']);
        $months = (int) $validated['months'];
        $msisdn = '255' . substr(preg_replace('/\D/', '', $validated['msisdn']), -9);

        $payment = Payment::create([
            'company_id' => $request->user()->company_id,
            'plan_id' => $plan->id,
            'amount_tzs' => $this->billing->priceFor($plan, $months),
            'months' => $months,
            'msisdn' => $msisdn,
            'gateway' => 'selcom',
            'gateway_ref' => 'SSP' . strtoupper(Str::random(10)),
            'status' => Payment::STATUS_PENDING,
            'recorded_by' => $request->user()->id,
        ]);

        try {
            $this->gateway->initiate($payment, $msisdn);
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => Payment::STATUS_FAILED]);

            return back()->with('error', 'We could not start the payment. Please try again or use the manual instructions.');
        }

        return redirect()->route('billing.index')
            ->with('success', 'Check your phone and approve the payment prompt. Your plan updates automatically once it goes through.');
    }
}
