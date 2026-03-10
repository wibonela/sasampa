<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Transaction;

class EfdmsXmlBuilder
{
    public static function buildRegistrationXml(Company $company): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' .
            '<EFDMS>' .
            '<REGDATA>' .
            '<TIN>' . htmlspecialchars($company->tin ?? '') . '</TIN>' .
            '<CERTKEY>' . htmlspecialchars($company->efd_serial_number ?? '') . '</CERTKEY>' .
            '</REGDATA>' .
            '</EFDMS>';
    }

    public static function buildReceiptXml(Transaction $transaction): string
    {
        $company = $transaction->user?->company;
        $items = $transaction->items;

        $itemsXml = '';
        foreach ($items as $index => $item) {
            $taxCode = match ($item->tax_category ?? 'standard') {
                'zero_rated' => '2',
                'exempt' => '3',
                default => '1', // standard 18%
            };

            $itemsXml .= '<ITEM>' .
                '<ID>' . ($index + 1) . '</ID>' .
                '<DESC>' . htmlspecialchars($item->product_name) . '</DESC>' .
                '<QTY>' . $item->quantity . '</QTY>' .
                '<TAXCODE>' . $taxCode . '</TAXCODE>' .
                '<AMT>' . number_format((float) $item->subtotal, 2, '.', '') . '</AMT>' .
                '</ITEM>';
        }

        $paymentType = match ($transaction->payment_method) {
            'cash' => 'CASH',
            'card' => 'CCARD',
            'mobile' => 'EMONEY',
            'bank_transfer' => 'CHEQUE',
            'credit' => 'INVOICE',
            default => 'CASH',
        };

        return '<?xml version="1.0" encoding="UTF-8"?>' .
            '<EFDMS>' .
            '<RCT>' .
            '<DATE>' . $transaction->created_at->format('Y-m-d') . '</DATE>' .
            '<TIME>' . $transaction->created_at->format('H:i:s') . '</TIME>' .
            '<TIN>' . htmlspecialchars($company->tin ?? '') . '</TIN>' .
            '<REGID>' . htmlspecialchars($company->efd_serial_number ?? '') . '</REGID>' .
            '<EFDSERIAL>' . htmlspecialchars($company->efd_uin ?? '') . '</EFDSERIAL>' .
            '<CUSTIDTYPE>6</CUSTIDTYPE>' .
            '<CUSTID>' . htmlspecialchars($transaction->customer_tin ?? '') . '</CUSTID>' .
            '<CUSTNAME>' . htmlspecialchars($transaction->customer_name ?? 'Walk-in Customer') . '</CUSTNAME>' .
            '<MESSION>' . htmlspecialchars($transaction->transaction_number) . '</MESSION>' .
            '<TOTALTAXEXCL>' . number_format((float) $transaction->subtotal, 2, '.', '') . '</TOTALTAXEXCL>' .
            '<TOTALTAXINCL>' . number_format((float) $transaction->total, 2, '.', '') . '</TOTALTAXINCL>' .
            '<DISCOUNT>' . number_format((float) $transaction->discount_amount, 2, '.', '') . '</DISCOUNT>' .
            '<ITEMS>' . $itemsXml . '</ITEMS>' .
            '<PAYMENTS>' .
            '<PMTTYPE>' . $paymentType . '</PMTTYPE>' .
            '<PMTAMOUNT>' . number_format((float) $transaction->total, 2, '.', '') . '</PMTAMOUNT>' .
            '</PAYMENTS>' .
            '<VATTOTALS>' .
            '<VATRATE>A</VATRATE>' .
            '<NETTAMOUNT>' . number_format((float) $transaction->subtotal, 2, '.', '') . '</NETTAMOUNT>' .
            '<TAXAMOUNT>' . number_format((float) $transaction->tax_amount, 2, '.', '') . '</TAXAMOUNT>' .
            '</VATTOTALS>' .
            '</RCT>' .
            '</EFDMS>';
    }

    public static function buildZReportXml(Company $company, array $dailySummary): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' .
            '<EFDMS>' .
            '<ZREPORT>' .
            '<DATE>' . ($dailySummary['date'] ?? now()->format('Y-m-d')) . '</DATE>' .
            '<TIME>' . ($dailySummary['time'] ?? now()->format('H:i:s')) . '</TIME>' .
            '<TIN>' . htmlspecialchars($company->tin ?? '') . '</TIN>' .
            '<REGID>' . htmlspecialchars($company->efd_serial_number ?? '') . '</REGID>' .
            '<EFDSERIAL>' . htmlspecialchars($company->efd_uin ?? '') . '</EFDSERIAL>' .
            '<ZNUMBER>' . ($dailySummary['z_number'] ?? '1') . '</ZNUMBER>' .
            '<DAILYTOTALAMOUNT>' . number_format($dailySummary['total_amount'] ?? 0, 2, '.', '') . '</DAILYTOTALAMOUNT>' .
            '<GROSS>' . number_format($dailySummary['gross'] ?? 0, 2, '.', '') . '</GROSS>' .
            '<CORRECTIONS>' . number_format($dailySummary['corrections'] ?? 0, 2, '.', '') . '</CORRECTIONS>' .
            '<DISCOUNTS>' . number_format($dailySummary['discounts'] ?? 0, 2, '.', '') . '</DISCOUNTS>' .
            '<SURCHARGES>0.00</SURCHARGES>' .
            '<TICKETSFISCAL>' . ($dailySummary['receipt_count'] ?? 0) . '</TICKETSFISCAL>' .
            '<TICKETSNONFISCAL>0</TICKETSNONFISCAL>' .
            '</ZREPORT>' .
            '</EFDMS>';
    }
}
