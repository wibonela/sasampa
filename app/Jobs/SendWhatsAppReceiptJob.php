<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\WhatsappReceiptLog;
use App\Services\Messaging\MessagingProviderInterface;
use App\Services\WhatsappReceiptService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(
        private int $logId,
    ) {}

    public function handle(MessagingProviderInterface $provider, WhatsappReceiptService $receiptService): void
    {
        $log = WhatsappReceiptLog::withoutGlobalScope('company')->find($this->logId);

        if (!$log || $log->status === 'sent' || $log->status === 'delivered') {
            return;
        }

        $transaction = $log->transaction;
        if (!$transaction) {
            $log->update(['status' => 'failed', 'error_message' => 'Transaction not found.']);
            return;
        }

        $message = $receiptService->formatReceiptMessage($transaction);
        $providerName = config('messaging.provider', 'stub');

        // Try WhatsApp first
        $result = $provider->sendWhatsApp($log->phone_number, $message);

        if ($result['success']) {
            $log->update([
                'status' => 'sent',
                'provider' => $providerName,
                'provider_message_id' => $result['provider_message_id'],
                'attempts' => $log->attempts + 1,
                'sent_at' => now(),
            ]);

            Log::channel('messaging')->info('WhatsApp receipt sent', [
                'transaction_id' => $transaction->id,
                'phone' => $log->phone_number,
            ]);

            return;
        }

        // SMS fallback
        $smsFallback = Setting::withoutGlobalScope('company')
            ->where('key', 'whatsapp_receipts_sms_fallback')
            ->where('company_id', $log->company_id)
            ->first();

        $fallbackEnabled = $smsFallback
            ? filter_var($smsFallback->value, FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($fallbackEnabled) {
            $smsResult = $provider->sendSms($log->phone_number, $message);

            if ($smsResult['success']) {
                $log->update([
                    'channel' => 'sms',
                    'status' => 'sent',
                    'provider' => $providerName,
                    'provider_message_id' => $smsResult['provider_message_id'],
                    'attempts' => $log->attempts + 1,
                    'sent_at' => now(),
                ]);

                Log::channel('messaging')->info('SMS fallback receipt sent', [
                    'transaction_id' => $transaction->id,
                    'phone' => $log->phone_number,
                ]);

                return;
            }
        }

        // Both failed
        $log->update([
            'attempts' => $log->attempts + 1,
            'error_message' => $result['error'] ?? 'Send failed.',
        ]);

        // If we've exhausted retries, mark as failed
        if ($this->attempts() >= $this->tries) {
            $log->update(['status' => 'failed']);

            Log::channel('messaging')->warning('Receipt delivery failed permanently', [
                'transaction_id' => $transaction->id,
                'phone' => $log->phone_number,
                'error' => $result['error'],
            ]);
        }
    }
}
