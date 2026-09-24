<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserLimitRequest;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanRequestTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);

        $this->company = Company::create([
            'name' => 'Duka', 'email' => 'duka@example.com',
            'status' => Company::STATUS_APPROVED, 'onboarding_completed' => true,
        ]);
        Subscription::create([
            'company_id' => $this->company->id,
            'plan_id' => Plan::where('key', 'starter')->value('id'),
            'status' => 'active',
            'current_period_end' => now()->addDays(20),
        ]);
        $this->owner = User::factory()->create([
            'company_id' => $this->company->id, 'role' => User::ROLE_COMPANY_OWNER, 'is_active' => true,
        ]);
    }

    public function test_owner_requests_a_plan_and_limit_follows_the_plan(): void
    {
        $business = Plan::where('key', 'business')->first();

        $this->actingAs($this->owner)
            ->post(route('users.request-more'), ['plan_id' => $business->id, 'reason' => 'Growing team'])
            ->assertSessionHas('success');

        $request = UserLimitRequest::first();
        $this->assertSame($business->id, $request->requested_plan_id);
        $this->assertSame(5, $request->requested_limit);
        $this->assertSame('pending', $request->status);
    }

    public function test_plan_that_does_not_add_users_is_rejected(): void
    {
        $starter = Plan::where('key', 'starter')->first(); // 2 users, company already allows 3

        $this->actingAs($this->owner)
            ->post(route('users.request-more'), ['plan_id' => $starter->id])
            ->assertSessionHasErrors('plan_id');

        $this->assertSame(0, UserLimitRequest::count());
    }

    public function test_only_one_pending_request_at_a_time(): void
    {
        $business = Plan::where('key', 'business')->first();
        $this->actingAs($this->owner)->post(route('users.request-more'), ['plan_id' => $business->id]);
        $this->actingAs($this->owner)->post(route('users.request-more'), ['plan_id' => $business->id])
            ->assertSessionHas('error');

        $this->assertSame(1, UserLimitRequest::count());
    }

    public function test_users_page_shows_plans_without_any_money_wording(): void
    {
        $html = $this->actingAs($this->owner)->get(route('users.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Business', $html);
        $this->assertStringContainsString('Up to 5 users', $html);

        $visible = strtolower(strip_tags(preg_replace('/<script.*?<\/script>/s', '', $html)));
        foreach (['tzs', 'price', 'pay ', 'payment', 'subscription', 'upgrade', 'per month', '/ month'] as $word) {
            $this->assertStringNotContainsString($word, $visible, "found \"{$word}\" on the Users page");
        }
    }

    public function test_creation_respects_the_plan_limit_when_enforced(): void
    {
        config(['billing.enforced' => true]);
        Plan::where('key', 'starter')->update(['max_users' => 1]);
        $company = $this->company->fresh();

        // The owner already fills the single slot.
        $this->assertSame(1, $company->effectiveUserLimit());
        $this->assertFalse($company->canAddUser());

        $this->actingAs($this->owner)->get(route('users.create'))->assertRedirect(route('users.index'));
    }

    public function test_admin_sees_the_requested_plan(): void
    {
        $business = Plan::where('key', 'business')->first();
        $request = UserLimitRequest::create([
            'company_id' => $this->company->id, 'requested_by' => $this->owner->id,
            'current_limit' => 3, 'requested_limit' => 5, 'requested_plan_id' => $business->id,
            'status' => 'pending',
        ]);
        $admin = User::factory()->create(['role' => User::ROLE_PLATFORM_ADMIN, 'company_id' => null, 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.user-limit-requests.show', $request))
            ->assertOk()->assertSee('Requested Plan')->assertSee('Business');
        $this->actingAs($admin)->get(route('admin.user-limit-requests.index'))->assertOk()->assertSee('Business');
    }
}
