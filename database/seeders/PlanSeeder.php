<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Placeholder prices and limits; adjust before enabling billing.
     */
    public function run(): void
    {
        $paid = ['full_reports', 'expenses', 'export', 'whatsapp_receipts'];

        $plans = [
            ['key' => 'starter', 'name' => 'Starter', 'price_tzs' => 15000, 'max_users' => 2, 'max_branches' => 1, 'max_products' => 500, 'features' => [], 'sort_order' => 1],
            ['key' => 'business', 'name' => 'Business', 'price_tzs' => 35000, 'max_users' => 5, 'max_branches' => 1, 'max_products' => null, 'features' => $paid, 'sort_order' => 2],
            ['key' => 'multi_branch', 'name' => 'Multi-branch', 'price_tzs' => 75000, 'max_users' => 15, 'max_branches' => 5, 'max_products' => null, 'features' => $paid, 'sort_order' => 3],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['key' => $plan['key']], $plan);
        }
    }
}
