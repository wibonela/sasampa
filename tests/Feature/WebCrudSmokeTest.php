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

    public function test_owner_can_import_products_from_csv(): void
    {
        $csv = "name,category,selling_price,cost_price,opening_stock,barcode\n"
            . "Soda,Vinywaji,\"1,500\",800,12,111\n"
            . "Water,Vinywaji,500,300,5,111\n"
            . ",Vinywaji,abc,,,\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('p.csv', $csv);

        $template = $this->actingAs($this->owner)->get(route('products.import.template'));
        $template->assertOk();
        $this->assertStringContainsString('Selling Price (required)', $template->streamedContent());

        $friendly = "Product Name (required),Category,Selling Price (required),Cost Price,Opening Stock,Active (yes/no)\nTea,Drinks,700,400,9,yes\n";
        $this->actingAs($this->owner)->post(route('products.import.store'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('t.csv', $friendly),
        ]);
        $this->assertSame(9, (int) Product::where('name', 'Tea')->firstOrFail()->inventory->quantity);
        $this->actingAs($this->owner)
            ->post(route('products.import.store'), ['file' => $file])
            ->assertSessionHas('import_errors');

        $soda = Product::where('name', 'Soda')->firstOrFail();
        $this->assertSame(12, (int) $soda->inventory->quantity);
        $this->assertSame('1500.00', (string) $soda->selling_price);
        $this->assertSame(1, Category::where('name', 'Vinywaji')->count());
        $this->assertNull(Product::where('name', 'Water')->first());
        $this->assertSame(2, Product::count());
    }
}
