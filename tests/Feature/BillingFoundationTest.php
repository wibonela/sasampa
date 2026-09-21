<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Subscription;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingFoundationTest extends TestCase
{
    use RefreshDatabase;

    private function company(): Company
    {
        return Company::create([
            'name' => 'Duka',
            'email' => 'duka@example.com',
            'status' => Company::STATUS_APPROVED,
        ]);
    }

    private function subscribe(Company $company, string $plan, $periodEnd): void
    {
        Subscription::create([
            'company_id' => $company->id,
            'plan_id' => \App\Models\Plan::where('key', $plan)->value('id'),
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_end' => $periodEnd,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_flag_off_means_no_gating_and_no_limits(): void
    {
        config(['billing.enforced' => false]);
        $company = $this->company();

        $this->assertTrue($company->hasFeature('expenses'));
        $this->assertTrue($company->withinLimit('branches'));
    }

    public function test_active_business_plan_has_features(): void
    {
        config(['billing.enforced' => true]);
        $company = $this->company();
        $this->subscribe($company, 'business', now()->addDays(10));

        $this->assertTrue($company->hasFeature('expenses'));
    }

    public function test_lapsed_subscription_falls_back_to_starter(): void
    {
        config(['billing.enforced' => true]);
        $company = $this->company();
        $this->subscribe($company, 'business', now()->subDays(10));

        $this->assertFalse($company->hasFeature('expenses'));
    }

    public function test_company_without_subscription_gets_starter(): void
    {
        config(['billing.enforced' => true]);
        $company = $this->company();

        $this->assertFalse($company->hasFeature('export'));
    }

    public function test_branch_limit_is_enforced(): void
    {
        config(['billing.enforced' => true]);
        $company = $this->company();
        $this->subscribe($company, 'business', now()->addDays(10)); // 1 branch

        $this->assertTrue($company->withinLimit('branches'));

        Branch::create(['company_id' => $company->id, 'name' => 'Main', 'code' => 'HQ', 'is_main' => true, 'is_active' => true]);

        $this->assertFalse($company->withinLimit('branches'));
    }

    public function test_backfill_is_idempotent_and_skips_unapproved(): void
    {
        $approved = $this->company();
        Company::create(['name' => 'New', 'email' => 'n@example.com', 'status' => Company::STATUS_PENDING]);

        $this->artisan('billing:backfill')->assertSuccessful();
        $this->artisan('billing:backfill')->assertSuccessful();

        $this->assertSame(1, Subscription::count());
        $this->assertTrue($approved->fresh()->subscription->isCurrent());
    }
}
