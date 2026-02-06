<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteStalePendingCompanies extends Command
{
    protected $signature = 'companies:delete-stale-pending
                            {--days=3 : Number of days a company can stay pending}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Delete companies that have been pending for more than the specified days';

    public function handle()
    {
        $days = $this->option('days');
        $dryRun = $this->option('dry-run');

        $cutoffDate = now()->subDays($days);

        $stalePendingCompanies = Company::where('status', Company::STATUS_PENDING)
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($stalePendingCompanies->isEmpty()) {
            $this->info('No stale pending companies found.');
            return 0;
        }

        $this->info("Found {$stalePendingCompanies->count()} pending companies older than {$days} days:");
        $this->newLine();

        $tableData = $stalePendingCompanies->map(function ($company) {
            return [
                'ID' => $company->id,
                'Name' => $company->name,
                'Email' => $company->email,
                'Created' => $company->created_at->format('Y-m-d H:i'),
                'Days Pending' => $company->created_at->diffInDays(now()),
            ];
        })->toArray();

        $this->table(['ID', 'Name', 'Email', 'Created', 'Days Pending'], $tableData);
        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN - No companies were deleted.');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to delete these companies and all their related data?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $deletedCount = 0;

        foreach ($stalePendingCompanies as $company) {
            try {
                DB::beginTransaction();

                // Delete related data
                $company->users()->delete();
                $company->branches()->delete();
                $company->categories()->delete();

                // Delete products if relationship exists
                if (method_exists($company, 'products')) {
                    $company->products()->delete();
                }

                // Delete transactions if relationship exists
                if (method_exists($company, 'transactions')) {
                    $company->transactions()->delete();
                }

                // Delete the company
                $company->delete();

                DB::commit();

                $deletedCount++;
                $this->line("Deleted: {$company->name} (ID: {$company->id})");

                Log::info("Deleted stale pending company", [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'days_pending' => $company->created_at->diffInDays(now()),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to delete {$company->name}: {$e->getMessage()}");
                Log::error("Failed to delete stale pending company", [
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Successfully deleted {$deletedCount} stale pending companies.");

        return 0;
    }
}
