<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Platform Admin (no company)
        User::create([
            'name' => 'Platform Admin',
            'email' => 'admin@sasampapos.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PLATFORM_ADMIN,
            'company_id' => null,
        ]);

        // Create a sample approved company
        $company = Company::create([
            'name' => 'Sasampa Supermarket',
            'email' => 'info@sasampasupermarket.com',
            'phone' => '+255 123 456 789',
            'address' => 'Dar es Salaam, Tanzania',
            'status' => Company::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        // Create company owner
        User::create([
            'name' => 'John Owner',
            'email' => 'owner@sasampasupermarket.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_COMPANY_OWNER,
            'company_id' => $company->id,
        ]);

        // Create cashier for the company
        User::create([
            'name' => 'Mary Cashier',
            'email' => 'cashier@sasampasupermarket.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CASHIER,
            'company_id' => $company->id,
        ]);

        // Create company settings
        $settings = [
            'store_name' => 'Sasampa Supermarket',
            'store_address' => 'Dar es Salaam, Tanzania',
            'store_phone' => '+255 123 456 789',
            'currency_symbol' => 'TZS',
            'default_tax_rate' => 18,
            'low_stock_threshold' => 10,
            'receipt_header' => 'Welcome to Sasampa Supermarket',
            'receipt_footer' => 'Thank you for shopping with us!',
        ];
        foreach ($settings as $key => $value) {
            Setting::withoutGlobalScope('company')->create([
                'key' => $key,
                'value' => $value,
                'type' => is_numeric($value) ? 'integer' : 'string',
                'company_id' => $company->id,
            ]);
        }

        // Create categories for the company
        $beverages = Category::withoutGlobalScope('company')->create(['name' => 'Beverages', 'description' => 'Drinks', 'company_id' => $company->id]);
        $snacks = Category::withoutGlobalScope('company')->create(['name' => 'Snacks', 'description' => 'Chips, cookies', 'company_id' => $company->id]);
        $groceries = Category::withoutGlobalScope('company')->create(['name' => 'Groceries', 'description' => 'Rice, sugar, flour', 'company_id' => $company->id]);
        $dairy = Category::withoutGlobalScope('company')->create(['name' => 'Dairy', 'description' => 'Milk, eggs', 'company_id' => $company->id]);
        $bakery = Category::withoutGlobalScope('company')->create(['name' => 'Bakery', 'description' => 'Bread, cakes', 'company_id' => $company->id]);
        $household = Category::withoutGlobalScope('company')->create(['name' => 'Household', 'description' => 'Cleaning supplies', 'company_id' => $company->id]);

        // Create products for the company
        $products = [
            // Beverages
            ['name' => 'Coca Cola 500ml', 'sku' => 'BEV001', 'category_id' => $beverages->id, 'cost_price' => 500, 'selling_price' => 800, 'tax_rate' => 18, 'stock' => 120],
            ['name' => 'Pepsi 500ml', 'sku' => 'BEV002', 'category_id' => $beverages->id, 'cost_price' => 500, 'selling_price' => 800, 'tax_rate' => 18, 'stock' => 100],
            ['name' => 'Water 500ml', 'sku' => 'BEV003', 'category_id' => $beverages->id, 'cost_price' => 200, 'selling_price' => 500, 'tax_rate' => 0, 'stock' => 200],
            ['name' => 'Orange Juice 1L', 'sku' => 'BEV004', 'category_id' => $beverages->id, 'cost_price' => 1200, 'selling_price' => 2000, 'tax_rate' => 18, 'stock' => 60],

            // Snacks
            ['name' => 'Lay\'s Chips', 'sku' => 'SNK001', 'category_id' => $snacks->id, 'cost_price' => 800, 'selling_price' => 1500, 'tax_rate' => 18, 'stock' => 60],
            ['name' => 'Oreo Cookies', 'sku' => 'SNK002', 'category_id' => $snacks->id, 'cost_price' => 1200, 'selling_price' => 2000, 'tax_rate' => 18, 'stock' => 50],
            ['name' => 'KitKat Bar', 'sku' => 'SNK003', 'category_id' => $snacks->id, 'cost_price' => 600, 'selling_price' => 1200, 'tax_rate' => 18, 'stock' => 80],

            // Groceries
            ['name' => 'Rice 1kg', 'sku' => 'GRO001', 'category_id' => $groceries->id, 'cost_price' => 2500, 'selling_price' => 3500, 'tax_rate' => 0, 'stock' => 100],
            ['name' => 'Sugar 1kg', 'sku' => 'GRO002', 'category_id' => $groceries->id, 'cost_price' => 2800, 'selling_price' => 3800, 'tax_rate' => 0, 'stock' => 90],
            ['name' => 'Cooking Oil 1L', 'sku' => 'GRO003', 'category_id' => $groceries->id, 'cost_price' => 4500, 'selling_price' => 6000, 'tax_rate' => 0, 'stock' => 80],

            // Dairy
            ['name' => 'Fresh Milk 1L', 'sku' => 'DAI001', 'category_id' => $dairy->id, 'cost_price' => 1500, 'selling_price' => 2500, 'tax_rate' => 0, 'stock' => 40],
            ['name' => 'Eggs (Tray of 30)', 'sku' => 'DAI002', 'category_id' => $dairy->id, 'cost_price' => 8000, 'selling_price' => 12000, 'tax_rate' => 0, 'stock' => 25],

            // Bakery
            ['name' => 'White Bread', 'sku' => 'BAK001', 'category_id' => $bakery->id, 'cost_price' => 1200, 'selling_price' => 2000, 'tax_rate' => 0, 'stock' => 30],
            ['name' => 'Croissant Pack', 'sku' => 'BAK002', 'category_id' => $bakery->id, 'cost_price' => 2000, 'selling_price' => 3500, 'tax_rate' => 0, 'stock' => 20],

            // Household
            ['name' => 'Toilet Paper 4-Pack', 'sku' => 'HOU001', 'category_id' => $household->id, 'cost_price' => 3000, 'selling_price' => 4500, 'tax_rate' => 18, 'stock' => 60],
            ['name' => 'Dish Soap 500ml', 'sku' => 'HOU002', 'category_id' => $household->id, 'cost_price' => 1500, 'selling_price' => 2500, 'tax_rate' => 18, 'stock' => 50],
        ];

        foreach ($products as $productData) {
            $stock = $productData['stock'];
            unset($productData['stock']);
            $productData['company_id'] = $company->id;

            $product = Product::withoutGlobalScope('company')->create($productData);

            Inventory::withoutGlobalScope('company')->create([
                'product_id' => $product->id,
                'quantity' => $stock,
                'low_stock_threshold' => 10,
                'company_id' => $company->id,
            ]);
        }

        // Create a pending company for demo
        $pendingCompany = Company::create([
            'name' => 'Safari Traders',
            'email' => 'info@safaritraders.com',
            'phone' => '+255 987 654 321',
            'address' => 'Arusha, Tanzania',
            'status' => Company::STATUS_PENDING,
        ]);

        User::create([
            'name' => 'Safari Owner',
            'email' => 'owner@safaritraders.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_COMPANY_OWNER,
            'company_id' => $pendingCompany->id,
        ]);

        // Seed documentation
        $this->call(DocumentationSeeder::class);
    }
}
