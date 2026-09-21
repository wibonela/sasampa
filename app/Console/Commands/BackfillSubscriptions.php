<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Console\Command;

class BackfillSubscriptions extends Command
{
    protected $signature = 'billing:backfill {--days= : Days of full access (defaults to billing.grace_days)}';

    protected $description = 'Give every approved company without a subscription a grace period on the default plan';

    public function handle(): int
    {
        $plan = Plan::where('key', config('billing.default_plan'))->first();

        if (!$plan) {
            $this->error('Default plan not found. Run: php artisan db:seed --class=PlanSeeder');
            return self::FAILURE;
        }

        $days = (int) ($this->option('days') ?? config('billing.grace_days'));
        $created = 0;

        Company::approved()->doesntHave('subscription')->each(function (Company $company) use ($plan, $days, &$created) {
            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_ACTIVE,
                'current_period_end' => now()->addDays($days),
            ]);
            $created++;
        });

        $this->info("Created {$created} subscription(s) with {$days} days of access.");

        return self::SUCCESS;
    }
}
