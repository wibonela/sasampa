<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCustomersFromTransactions extends Command
{
    protected $signature = 'customers:backfill';
    protected $description = 'Create Customer records from existing transaction data (customer_name + customer_phone)';

    public function handle(): int
    {
        $this->info('Scanning transactions for customers to backfill...');

        // Find all unique company_id + customer_phone combos from transactions
        // that have a phone but no linked customer_id
        $rows = DB::table('transactions')
            ->select('company_id', 'customer_name', 'customer_phone', 'customer_tin')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->groupBy('company_id', 'customer_phone')
            ->get();

        $created = 0;
        $linked = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            // Check if customer already exists for this company + phone
            $existing = Customer::withoutGlobalScope('company')
                ->where('company_id', $row->company_id)
                ->where('phone', $row->customer_phone)
                ->first();

            if (!$existing) {
                // Create the customer
                $existing = Customer::withoutGlobalScope('company')->create([
                    'company_id' => $row->company_id,
                    'name' => $row->customer_name,
                    'phone' => $row->customer_phone,
                    'tin' => $row->customer_tin,
                    'credit_limit' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                ]);
                $created++;
                $this->line("  Created: {$row->customer_name} ({$row->customer_phone}) for company #{$row->company_id}");
            } else {
                $skipped++;
            }

            // Link all unlinked transactions for this company + phone to the customer
            $updated = DB::table('transactions')
                ->where('company_id', $row->company_id)
                ->where('customer_phone', $row->customer_phone)
                ->whereNull('customer_id')
                ->update(['customer_id' => $existing->id]);

            if ($updated > 0) {
                $linked += $updated;
            }
        }

        // Also handle transactions with customer_name only (no phone)
        $nameOnlyRows = DB::table('transactions')
            ->select('company_id', 'customer_name', 'customer_tin')
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->where(function ($q) {
                $q->whereNull('customer_phone')->orWhere('customer_phone', '');
            })
            ->whereNull('customer_id')
            ->groupBy('company_id', 'customer_name')
            ->get();

        foreach ($nameOnlyRows as $row) {
            // Check if customer with this name exists for this company
            $existing = Customer::withoutGlobalScope('company')
                ->where('company_id', $row->company_id)
                ->where('name', $row->customer_name)
                ->first();

            if (!$existing) {
                $existing = Customer::withoutGlobalScope('company')->create([
                    'company_id' => $row->company_id,
                    'name' => $row->customer_name,
                    'tin' => $row->customer_tin,
                    'credit_limit' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                ]);
                $created++;
                $this->line("  Created (name only): {$row->customer_name} for company #{$row->company_id}");
            }

            // Link transactions
            $updated = DB::table('transactions')
                ->where('company_id', $row->company_id)
                ->where('customer_name', $row->customer_name)
                ->where(function ($q) {
                    $q->whereNull('customer_phone')->orWhere('customer_phone', '');
                })
                ->whereNull('customer_id')
                ->update(['customer_id' => $existing->id]);

            if ($updated > 0) {
                $linked += $updated;
            }
        }

        $this->info("Done! Created {$created} customers, linked {$linked} transactions, skipped {$skipped} (already existed).");

        return self::SUCCESS;
    }
}
