<?php

namespace App\Console\Commands;

use App\Services\EfdmsService;
use Illuminate\Console\Command;

class RetryEfdSubmissions extends Command
{
    protected $signature = 'efd:retry {--company= : Retry for a specific company ID}';
    protected $description = 'Retry failed EFDMS fiscal receipt submissions';

    public function handle(EfdmsService $efdmsService): int
    {
        $companyId = $this->option('company');
        $company = null;

        if ($companyId) {
            $company = \App\Models\Company::find($companyId);
            if (!$company) {
                $this->error("Company #{$companyId} not found.");
                return 1;
            }
        }

        $this->info('Retrying failed EFDMS submissions...');
        $results = $efdmsService->retryFailedReceipts($company);

        $this->info("Total: {$results['total']}, Success: {$results['success']}, Failed: {$results['failed']}");

        return 0;
    }
}
