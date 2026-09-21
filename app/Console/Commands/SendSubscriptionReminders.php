<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionReminder;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:remind';

    protected $description = 'Email company owners 7, 3 and 1 days before their plan ends, and on the day it ends';

    /** Days before the end date on which a reminder goes out (0 = the end date itself). */
    private const OFFSETS = [7, 3, 1, 0];

    public function handle(): int
    {
        // Runs once a day, so matching on the calendar date sends each reminder once.
        if (!config('billing.enforced')) {
            $this->info('Billing is not enforced; no reminders sent.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach (self::OFFSETS as $days) {
            Subscription::with('company.owner')
                ->whereDate('current_period_end', now()->addDays($days)->toDateString())
                ->each(function (Subscription $subscription) use ($days, &$sent) {
                    $owner = $subscription->company?->owner;

                    if ($owner?->email) {
                        Mail::to($owner->email)->queue(new SubscriptionReminder($subscription->company, $days));
                        $sent++;
                    }
                });
        }

        $this->info("Queued {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
