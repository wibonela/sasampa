<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptPdfRenderer
{
    public function render(Transaction $transaction): string
    {
        $transaction->loadMissing(['items.product', 'user', 'company']);

        $heightMm = $this->computeHeightMm($transaction);

        $widthPoints = 80 * 2.83465;
        $heightPoints = $heightMm * 2.83465;

        $pdf = Pdf::loadView('pos.receipt-pdf', ['transaction' => $transaction]);
        $pdf->setPaper([0, 0, $widthPoints, $heightPoints], 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('dpi', 96);

        return $pdf->output();
    }

    public function filename(Transaction $transaction): string
    {
        return 'receipt-' . $transaction->transaction_number . '.pdf';
    }

    private function computeHeightMm(Transaction $transaction): int
    {
        $totalHeight = 195 + ($transaction->items->count() * 14);

        if ($transaction->discount_amount > 0) $totalHeight += 8;
        if ($transaction->tax_amount > 0) $totalHeight += 8;
        if ($transaction->customer_name) $totalHeight += 8;
        if ($transaction->change_given > 0) $totalHeight += 8;
        if ($transaction->company?->tin) $totalHeight += 5;
        if ($transaction->company?->vrn) $totalHeight += 5;

        $logo = Setting::get('store_logo') ?: $transaction->company?->logo;
        if ($logo) $totalHeight += 25;

        if ($transaction->fiscal_receipt_number) {
            $totalHeight += 30;
            if ($transaction->fiscal_qr_code) $totalHeight += 45;
        }

        return $totalHeight + 12;
    }
}
