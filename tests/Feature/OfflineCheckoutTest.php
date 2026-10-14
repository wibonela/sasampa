<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OfflineCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function setUpShop(int $stock): array
    {
        $company = Company::create([
            'name' => 'Duka', 'email' => 'duka@example.com',
            'status' => Company::STATUS_APPROVED, 'onboarding_completed' => true,
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id, 'role' => User::ROLE_COMPANY_OWNER, 'is_active' => true,
        ]);
        $product = Product::create([
            'company_id' => $company->id, 'name' => 'Soda', 'cost_price' => 500,
            'selling_price' => 1000, 'tax_rate' => 0, 'is_active' => true,
        ]);
        Inventory::create(['company_id' => $company->id, 'product_id' => $product->id, 'quantity' => $stock, 'low_stock_threshold' => 2]);
        Sanctum::actingAs($user);

        return [$company, $product];
    }

    private function skipDeviceChecks(): void
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureMobileAccessApproved::class,
            \App\Http\Middleware\EnsureDeviceRegistered::class,
            \App\Http\Middleware\TrackDeviceActivity::class,
        ]);
    }

    private function payload(Product $product, array $extra = []): array
    {
        return array_merge([
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payment_method' => 'cash',
            'amount_paid' => 3000,
        ], $extra);
    }

    public function test_resending_an_offline_sale_does_not_duplicate_it(): void
    {
        [$company, $product] = $this->setUpShop(10);
        $this->skipDeviceChecks();
        $body = $this->payload($product, ['offline_id' => 'off-1']);

        $first = $this->postJson('/api/v1/pos/checkout', $body);
        $this->assertSame(201, $first->status(), (string) $first->getContent());
        $second = $this->postJson('/api/v1/pos/checkout', $body)->assertOk()->assertJsonPath('duplicate', true);

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertSame(1, Transaction::withoutGlobalScopes()->where('offline_id', 'off-1')->count());
        $this->assertSame(7, (int) Inventory::where('product_id', $product->id)->value('quantity'));
    }

    public function test_offline_sale_is_accepted_even_without_stock_and_keeps_its_time(): void
    {
        [$company, $product] = $this->setUpShop(1);
        $this->skipDeviceChecks();
        $when = now()->subHours(5)->startOfMinute();

        $this->postJson('/api/v1/pos/checkout', $this->payload($product, [
            'offline_id' => 'off-2', 'created_at' => $when->toIso8601String(),
        ]))->assertStatus(201);

        $tx = Transaction::withoutGlobalScopes()->where('offline_id', 'off-2')->first();
        $this->assertTrue($tx->created_at->equalTo($when));
    }

    public function test_online_sale_still_rejects_insufficient_stock(): void
    {
        [$company, $product] = $this->setUpShop(1);
        $this->skipDeviceChecks();

        $this->postJson('/api/v1/pos/checkout', $this->payload($product))->assertStatus(422);
    }
}
