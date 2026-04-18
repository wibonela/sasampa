<?php

namespace App\Console\Commands;

use App\Models\MobileAppRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoApproveMobileAccessRequests extends Command
{
    protected $signature = 'mobile-access:auto-approve
                            {--dry-run : List what would be auto-approved without changing anything}';

    protected $description = 'Auto-approve pending, non-suspicious mobile access requests whose 10-minute hold has elapsed';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $requests = MobileAppRequest::dueForAutoApproval()->with('company')->get();

        if ($requests->isEmpty()) {
            $this->info('No mobile access requests are due for auto-approval.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d request(s) due for auto-approval%s', $requests->count(), $dryRun ? ' (dry run)' : ''));

        foreach ($requests as $request) {
            $companyName = $request->company?->name ?? "company#{$request->company_id}";

            if ($dryRun) {
                $this->line(" - would approve #{$request->id} ({$companyName}) scheduled at {$request->scheduled_approval_at}");
                continue;
            }

            try {
                $request->autoApprove();
                $this->line(" - approved #{$request->id} ({$companyName})");

                Log::info('Mobile access request auto-approved', [
                    'request_id' => $request->id,
                    'company_id' => $request->company_id,
                    'scheduled_approval_at' => $request->scheduled_approval_at?->toIso8601String(),
                ]);
            } catch (\Throwable $e) {
                $this->error(" - failed to approve #{$request->id}: {$e->getMessage()}");
                Log::error('Mobile access auto-approval failed', [
                    'request_id' => $request->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}
