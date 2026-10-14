<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ProfitBreakdownService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitPeriodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_time_and_custom_reports_work_with_expenses_and_old_sales(): void
    {
        $company = Company::create(['name' => 'Duka', 'email' => 'd@example.com', 'status' => Company::STATUS_APPROVED]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => User::ROLE_COMPANY_OWNER]);
        $this->actingAs($user);

        foreach ([800, 400, 5] as $daysAgo) {
            $t = Transaction::create([
                'company_id' => $company->id, 'transaction_number' => "T$daysAgo", 'user_id' => $user->id,
                'type' => 'sale', 'status' => 'completed', 'subtotal' => 1000, 'total' => 1000,
                'payment_method' => 'cash', 'amount_paid' => 1000,
            ]);
            $t->forceFill(['created_at' => now()->subDays($daysAgo)])->save();
        }
        $cat = ExpenseCategory::create(['company_id' => $company->id, 'name' => 'Rent', 'is_active' => true]);
        Expense::create([
            'company_id' => $company->id, 'expense_category_id' => $cat->id, 'user_id' => $user->id,
            'description' => 'Rent', 'amount' => 300, 'quantity' => 1, 'expense_date' => now()->subDays(10)->toDateString(),
            'payment_method' => 'cash',
        ]);

        $svc = app(ProfitBreakdownService::class);

        $all = $svc->fullReport('all', [], null, true);
        $this->assertSame(3000.0, $all['summary']['revenue']);
        $this->assertSame(300.0, $all['summary']['operating_expenses']);
        $this->assertNotEmpty($all['trend']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $all['trend'][0]['bucket'], 'multi-year window is bucketed by month');

        $custom = $svc->fullReport('custom', ['date_from' => now()->subDays(30)->toDateString(), 'date_to' => now()->toDateString()], null, true);
        $this->assertSame(1000.0, $custom['summary']['revenue']);
        $this->assertSame(300.0, $custom['summary']['operating_expenses']);

        // admin company page: All view spans the full history, grouped by month
        $sales = app(\App\Services\CompanyActivityService::class)->companySales($company, 'all');
        $this->assertTrue($sales['is_all'] && $sales['monthly']);
        $this->assertSame(3, (int) $sales['current']->sales);
        $this->assertEquals(3000, $sales['current']->revenue);
        $this->assertEquals(3000, array_sum(array_column($sales['series'], 'revenue')));

        $last = $svc->fullReport('last_month', [], null, true);
        $this->assertSame('Last Month', $last['period']['label']);
    }
}
