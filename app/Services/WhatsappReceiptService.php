<?php

namespace App\Services;

use App\Jobs\SendWhatsAppReceiptJob;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\WhatsappReceiptLog;

class WhatsappReceiptService
{
    /**
     * Send a receipt via WhatsApp (with deduplication).
     */
    public function sendReceipt(Transaction $transaction, ?string $phone = null): array
    {
        $phone = $this->resolvePhone($transaction, $phone);

        if (!$phone) {
            return ['success' => false, 'message' => 'No phone number available.'];
        }

        // Dedup: check for existing log within 60 seconds
        $recent = WhatsappReceiptLog::withoutGlobalScope('company')
            ->where('transaction_id', $transaction->id)
            ->where('channel', 'whatsapp')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->whereIn('status', ['pending', 'sent', 'delivered'])
            ->first();

        if ($recent) {
            return ['success' => true, 'message' => 'Receipt already being sent.', 'log_id' => $recent->id];
        }

        $log = WhatsappReceiptLog::create([
            'company_id' => $transaction->company_id,
            'transaction_id' => $transaction->id,
            'phone_number' => $phone,
            'channel' => 'whatsapp',
            'status' => 'pending',
        ]);

        SendWhatsAppReceiptJob::dispatch($log->id);

        return ['success' => true, 'message' => 'Receipt queued for delivery.', 'log_id' => $log->id];
    }

    /**
     * Resend a receipt (bypasses deduplication).
     */
    public function resendReceipt(Transaction $transaction, string $phone): array
    {
        $log = WhatsappReceiptLog::create([
            'company_id' => $transaction->company_id,
            'transaction_id' => $transaction->id,
            'phone_number' => $phone,
            'channel' => 'whatsapp',
            'status' => 'pending',
        ]);

        SendWhatsAppReceiptJob::dispatch($log->id);

        return ['success' => true, 'message' => 'Receipt queued for delivery.', 'log_id' => $log->id];
    }

    /**
     * Get the latest receipt status for a transaction.
     */
    public function getReceiptStatus(int $transactionId): ?array
    {
        $log = WhatsappReceiptLog::withoutGlobalScope('company')
            ->where('transaction_id', $transactionId)
            ->latest()
            ->first();

        if (!$log) {
            return null;
        }

        return [
            'id' => $log->id,
            'channel' => $log->channel,
            'status' => $log->status,
            'phone_number' => $log->phone_number,
            'error_message' => $log->error_message,
            'attempts' => $log->attempts,
            'sent_at' => $log->sent_at?->toIso8601String(),
            'delivered_at' => $log->delivered_at?->toIso8601String(),
        ];
    }

    /**
     * Format a transaction as a text receipt message.
     */
    public function formatReceiptMessage(Transaction $transaction): string
    {
        $transaction->loadMissing(['items', 'user']);
        $company = $transaction->user?->company;

        $lines = [];
        $lines[] = strtoupper($company?->name ?? 'SASAMPA POS');
        if ($company?->phone) {
            $lines[] = "Tel: {$company->phone}";
        }
        $lines[] = str_repeat('-', 30);
        $lines[] = "Receipt: {$transaction->transaction_number}";
        $lines[] = "Date: {$transaction->created_at->format('d/m/Y H:i')}";
        if ($transaction->customer_name) {
            $lines[] = "Customer: {$transaction->customer_name}";
        }
        $lines[] = str_repeat('-', 30);

        foreach ($transaction->items as $item) {
            $lines[] = "{$item->product_name}";
            $lines[] = "  {$item->quantity} x " . number_format($item->unit_price, 0) . " = " . number_format($item->subtotal, 0);
        }

        $lines[] = str_repeat('-', 30);
        $lines[] = "Subtotal: TZS " . number_format($transaction->subtotal, 0);
        if ($transaction->tax_amount > 0) {
            $lines[] = "Tax: TZS " . number_format($transaction->tax_amount, 0);
        }
        if ($transaction->discount_amount > 0) {
            $lines[] = "Discount: -TZS " . number_format($transaction->discount_amount, 0);
        }
        $lines[] = "TOTAL: TZS " . number_format($transaction->total, 0);
        $lines[] = "Paid: TZS " . number_format($transaction->amount_paid, 0);
        if ($transaction->change_given > 0) {
            $lines[] = "Change: TZS " . number_format($transaction->change_given, 0);
        }

        // Fiscal data
        if ($transaction->fiscal_receipt_number) {
            $lines[] = str_repeat('-', 30);
            $lines[] = "Fiscal #: {$transaction->fiscal_receipt_number}";
            $lines[] = "VFD Code: {$transaction->fiscal_verification_code}";
        }

        $lines[] = str_repeat('-', 30);
        $lines[] = "Thank you for your purchase!";

        // Marketing footer
        $footer = Setting::withoutGlobalScope('company')
            ->where('key', 'whatsapp_receipts_marketing_footer')
            ->where('company_id', $transaction->company_id)
            ->value('value');

        if ($footer) {
            $lines[] = '';
            $lines[] = $footer;
        }

        return implode("\n", $lines);
    }

    private function resolvePhone(Transaction $transaction, ?string $phone): ?string
    {
        if ($phone) {
            return $phone;
        }

        if ($transaction->customer_phone) {
            return $transaction->customer_phone;
        }

        if ($transaction->customer_id) {
            $transaction->loadMissing('customer');
            return $transaction->customer?->phone;
        }

        return null;
    }
}
