<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Creating things through the web forms must work on SQLite and PostgreSQL alike
 * (PostgreSQL is stricter about types, lengths and unique/foreign keys).
 */
class WebCrudSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $company = Company::create([
            'name' => 'Duka', 'email' => 'duka@example.com',
            'status' => Company::STATUS_APPROVED, 'onboarding_completed' => true,
        ]);
        $this->owner = User::factory()->create([
            'company_id' => $company->id, 'role' => User::ROLE_COMPANY_OWNER, 'is_active' => true,
        ]);
    }

    public function test_owner_can_create_category_and_product_with_stock(): void
    {
        $this->actingAs($this->owner)
            ->post(route('categories.store'), ['name' => 'Vinywaji'])
            ->assertSessionHasNoErrors();
        $category = Category::where('name', 'Vinywaji')->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('products.store'), [
                'name' => 'Soda 500ml', 'sku' => 'SODA-1', 'barcode' => '6001234567890',
                'category_id' => $category->id, 'cost_price' => 500, 'selling_price' => 800,
                'tax_rate' => 0, 'is_active' => 1, 'initial_stock' => 24, 'low_stock_threshold' => 5,
            ])
            ->assertSessionHasNoErrors();

        $product = Product::where('sku', 'SODA-1')->firstOrFail();
        $this->assertSame(24, (int) $product->inventory->quantity);
    }
}
