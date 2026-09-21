<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use App\Services\BillingService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BillingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    private function makeCompany(string $plan = 'starter', $end = null): array
    {
        $company = Company::create([
            'name' => 'Duka', 'email' => uniqid('duka') . '@example.com',
            'status' => Company::STATUS_APPROVED, 'onboarding_completed' => true,
        ]);
        $end ??= now()->addDays(20);
        Subscription::create([
            'company_id' => $company->id,
            'plan_id' => Plan::where('key', $plan)->value('id'),
            'status' => 'active',
            'current_period_end' => $end,
        ]);
        $owner = User::factory()->create(['company_id' => $company->id, 'role' => User::ROLE_COMPANY_OWNER, 'is_active' => true]);

        return [$company->fresh(), $owner];
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_PLATFORM_ADMIN, 'company_id' => null, 'is_active' => true]);
    }

    // ---- Stage 2 -------------------------------------------------------

    public function test_manual_payment_extends_running_period(): void
    {
        [$company] = $this->makeCompany('business', now()->addDays(10));

        app(BillingService::class)->recordManualPayment($company, Plan::where('key', 'business')->first(), 3, 99000, 'REC1', null, null);

        $sub = $company->fresh()->subscription;
        $this->assertTrue($sub->current_period_end->between(now()->addDays(10)->addMonths(3)->subMinute(), now()->addDays(10)->addMonths(3)->addMinute()));
        $this->assertSame('active', $sub->status);
    }

    public function test_manual_payment_on_lapsed_subscription_starts_from_now(): void
    {
        [$company] = $this->makeCompany('business', now()->subDays(40));

        app(BillingService::class)->recordManualPayment($company, Plan::where('key', 'business')->first(), 1, 35000, null, null, null);

        $this->assertTrue($company->fresh()->subscription->current_period_end->between(now()->addMonth()->subMinute(), now()->addMonth()->addMinute()));
    }

    public function test_applying_the_same_payment_twice_only_extends_once(): void
    {
        [$company] = $this->makeCompany('business', now()->addDays(5));
        $payment = Payment::create([
            'company_id' => $company->id, 'plan_id' => Plan::where('key', 'business')->value('id'),
            'amount_tzs' => 1, 'months' => 1, 'gateway' => 'selcom', 'gateway_ref' => 'X1', 'status' => 'pending',
        ]);
        $billing = app(BillingService::class);

        $billing->applyPaidPayment($payment);
        $first = $company->fresh()->subscription->current_period_end;
        $billing->applyPaidPayment($payment->fresh());

        $this->assertTrue($first->equalTo($company->fresh()->subscription->current_period_end));
    }

    public function test_admin_can_record_payment_over_http(): void
    {
        [$company] = $this->makeCompany('starter', now()->subDay());
        $plan = Plan::where('key', 'business')->first();

        $this->actingAs($this->admin())
            ->post(route('admin.companies.payments.store', $company), [
                'plan_id' => $plan->id, 'months' => 1, 'amount_tzs' => 35000, 'reference' => 'MPESA123',
            ])->assertRedirect();

        $this->assertSame($plan->id, $company->fresh()->subscription->plan_id);
        $this->assertDatabaseHas('payments', ['company_id' => $company->id, 'gateway_ref' => 'MPESA123', 'status' => 'paid']);
    }

    public function test_only_owner_sees_billing_page(): void
    {
        [$company, $owner] = $this->makeCompany();
        $cashier = User::factory()->create(['company_id' => $company->id, 'role' => User::ROLE_CASHIER, 'is_active' => true]);

        $this->actingAs($owner)->get('/billing')->assertOk()->assertSee('Starter');
        $this->actingAs($cashier)->get('/billing')->assertForbidden();
    }

    public function test_trial_starts_once(): void
    {
        $company = Company::create(['name' => 'N', 'email' => 'n@example.com', 'status' => 'approved']);
        $billing = app(BillingService::class);

        $this->assertNotNull($billing->startTrial($company));
        $this->assertNull($billing->startTrial($company));
        $this->assertSame('trialing', $company->fresh()->subscription->status);
    }

    // ---- Stage 3 -------------------------------------------------------

    public function test_expiry_moves_through_past_due_to_expired(): void
    {
        [$a] = $this->makeCompany('business', now()->subDay());
        $b = Company::create(['name' => 'B', 'email' => 'b@example.com', 'status' => 'approved']);
        Subscription::create(['company_id' => $b->id, 'plan_id' => Plan::first()->id, 'status' => 'active', 'current_period_end' => now()->subDays(10)]);

        app(BillingService::class)->expireDue();

        $this->assertSame('past_due', $a->fresh()->subscription->status);
        $this->assertSame('expired', $b->fresh()->subscription->status);
    }

    public function test_web_feature_gate_redirects_only_when_enforced(): void
    {
        [, $owner] = $this->makeCompany('starter');

        config(['billing.enforced' => false]);
        $this->actingAs($owner)->get('/expenses')->assertOk();

        config(['billing.enforced' => true]);
        $this->actingAs($owner)->get('/expenses')->assertRedirect(route('billing.index'));
    }

    public function test_business_plan_passes_the_gate(): void
    {
        config(['billing.enforced' => true]);
        [, $owner] = $this->makeCompany('business');

        $this->actingAs($owner)->get('/expenses')->assertOk();
    }

    public function test_api_gate_returns_neutral_error_without_billing_words(): void
    {
        Route::middleware(['api', 'auth:sanctum', 'feature:expenses'])->get('/api/_gate', fn () => ['ok' => true]);
        config(['billing.enforced' => true]);
        [, $owner] = $this->makeCompany('starter');
        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/_gate')->assertForbidden()->assertJson(['error_code' => 'feature_unavailable']);

        $text = strtolower($response->json('message'));
        foreach (['subscription', 'plan', 'price', 'upgrade', 'trial', 'pay', 'sasampa', '.com'] as $word) {
            $this->assertStringNotContainsString($word, $text);
        }
    }

    public function test_past_due_keeps_features_until_grace_ends(): void
    {
        config(['billing.enforced' => true, 'billing.past_due_days' => 3]);

        [$inGrace] = $this->makeCompany('business', now()->subDay());
        $this->assertTrue($inGrace->hasFeature('expenses'));

        [$pastGrace] = $this->makeCompany('business', now()->subDays(5));
        $this->assertFalse($pastGrace->hasFeature('expenses'));
    }

    public function test_product_limit_blocks_creation(): void
    {
        config(['billing.enforced' => true]);
        [$company, $owner] = $this->makeCompany('starter');
        Plan::where('key', 'starter')->update(['max_products' => 0]);

        $this->actingAs($owner)->post('/products', ['name' => 'X', 'cost_price' => 1, 'selling_price' => 2])
            ->assertRedirect(route('products.index'))->assertSessionHas('error');
        $this->assertSame(0, Product::withoutGlobalScopes()->count());
    }

    public function test_reminder_command_is_silent_when_not_enforced(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        [$company] = $this->makeCompany('business', now()->addDays(3));

        config(['billing.enforced' => false]);
        $this->artisan('subscriptions:remind')->assertSuccessful();
        \Illuminate\Support\Facades\Mail::assertNothingQueued();

        config(['billing.enforced' => true]);
        $this->artisan('subscriptions:remind')->assertSuccessful();
        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\SubscriptionReminder::class);
    }

    // ---- Stage 4 -------------------------------------------------------

    private function fakeHttp(array $body): void
    {
        // Http::fake() stubs accumulate (first match wins), so start from a clean factory.
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*' => Http::response($body)]);
    }

    private function configureSelcom(): void
    {
        config(['billing.selcom.api_key' => 'k', 'billing.selcom.api_secret' => 's', 'billing.selcom.vendor' => 'V1']);
    }

    public function test_checkout_is_unavailable_without_credentials(): void
    {
        [, $owner] = $this->makeCompany();

        $this->actingAs($owner)->post('/billing/checkout', ['plan_id' => 1, 'months' => 1, 'msisdn' => '0712345678'])->assertNotFound();
    }

    public function test_checkout_creates_pending_payment_and_pushes_prompt(): void
    {
        $this->configureSelcom();
        $this->fakeHttp(['result' => 'SUCCESS', 'resultcode' => '000', 'message' => 'ok', 'data' => []]);
        [$company, $owner] = $this->makeCompany();
        $plan = Plan::where('key', 'business')->first();

        $this->actingAs($owner)->post('/billing/checkout', ['plan_id' => $plan->id, 'months' => 3, 'msisdn' => '0712345678'])
            ->assertRedirect(route('billing.index'));

        $payment = Payment::first();
        $this->assertSame('pending', $payment->status);
        $this->assertSame('255712345678', $payment->msisdn);
        $this->assertSame(99750, $payment->amount_tzs); // 3 x 35,000 less 5%
        Http::assertSent(fn ($r) => str_contains($r->url(), 'wallet-payment') && $r->hasHeader('Digest') && $r['msisdn'] === '255712345678');
    }

    public function test_gateway_rejection_marks_payment_failed(): void
    {
        $this->configureSelcom();
        $this->fakeHttp(['result' => 'FAIL', 'message' => 'nope']);
        [, $owner] = $this->makeCompany();

        $this->actingAs($owner)->post('/billing/checkout', ['plan_id' => 1, 'months' => 1, 'msisdn' => '0712345678'])->assertSessionHas('error');

        $this->assertSame('failed', Payment::first()->status);
    }

    private function pendingSelcomPayment(Company $company): Payment
    {
        return Payment::create([
            'company_id' => $company->id, 'plan_id' => Plan::where('key', 'business')->value('id'),
            'amount_tzs' => 35000, 'months' => 1, 'gateway' => 'selcom', 'gateway_ref' => 'SSPTEST1', 'status' => 'pending',
        ]);
    }

    public function test_webhook_marks_paid_only_when_gateway_confirms(): void
    {
        $this->configureSelcom();
        [$company] = $this->makeCompany('starter', now()->subDay());
        $payment = $this->pendingSelcomPayment($company);

        // Forged webhook while the gateway still says pending: nothing changes.
        $this->fakeHttp(['result' => 'SUCCESS', 'data' => [['payment_status' => 'PENDING']]]);
        $this->postJson('/api/webhooks/selcom', ['order_id' => 'SSPTEST1', 'payment_status' => 'COMPLETED', 'result' => 'SUCCESS'])->assertOk();
        $this->assertSame('pending', $payment->fresh()->status);

        // Real completion.
        $this->fakeHttp(['result' => 'SUCCESS', 'data' => [['payment_status' => 'COMPLETED']]]);
        $this->postJson('/api/webhooks/selcom', ['order_id' => 'SSPTEST1'])->assertOk();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('business', $company->fresh()->subscription->plan->key);
        $this->assertTrue($company->fresh()->subscription->isCurrent());

        // Duplicate webhook does not extend again.
        $end = $company->fresh()->subscription->current_period_end;
        $this->postJson('/api/webhooks/selcom', ['order_id' => 'SSPTEST1'])->assertOk();
        $this->assertTrue($end->equalTo($company->fresh()->subscription->current_period_end));
    }

    public function test_webhook_for_unknown_order_is_acknowledged(): void
    {
        $this->postJson('/api/webhooks/selcom', ['order_id' => 'NOPE'])->assertOk();
    }

    public function test_verify_command_settles_missed_webhooks_and_expires_stale(): void
    {
        $this->configureSelcom();
        [$company] = $this->makeCompany();
        $paid = $this->pendingSelcomPayment($company);
        $this->fakeHttp(['result' => 'SUCCESS', 'data' => [['payment_status' => 'COMPLETED']]]);
        $this->artisan('payments:verify-pending')->assertSuccessful();
        $this->assertSame('paid', $paid->fresh()->status);

        $stale = Payment::create([
            'company_id' => $company->id, 'amount_tzs' => 1, 'months' => 1, 'gateway' => 'selcom',
            'gateway_ref' => 'OLD1', 'status' => 'pending',
        ]);
        $stale->forceFill(['created_at' => now()->subDays(2)])->save();
        $this->fakeHttp(['result' => 'SUCCESS', 'data' => [['payment_status' => 'PENDING']]]);
        $this->artisan('payments:verify-pending')->assertSuccessful();
        $this->assertSame('failed', $stale->fresh()->status);
    }

    public function test_signature_verification_round_trip(): void
    {
        $this->configureSelcom();
        $gateway = app(\App\Services\Payments\SelcomGateway::class);
        $payload = ['transid' => 'T1', 'order_id' => 'O1', 'result' => 'SUCCESS'];
        $ts = '2026-09-21T10:00:00+0300';
        $digest = base64_encode(hash_hmac('sha256', "timestamp={$ts}&transid=T1&order_id=O1&result=SUCCESS", 's', true));
        $headers = ['signed-fields' => 'transid,order_id,result', 'timestamp' => $ts, 'digest' => $digest];

        $this->assertTrue($gateway->signatureIsValid($headers, $payload));
        $this->assertFalse($gateway->signatureIsValid(['digest' => 'bad'] + $headers, $payload));
    }
}
