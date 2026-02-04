<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt - {{ $transaction->transaction_number }}</title>
    <style>
        @page {
            margin: 5mm;
            size: 80mm 297mm; /* Will be overridden by setPaper */
        }
        * {
            page-break-inside: avoid !important;
            page-break-before: avoid !important;
            page-break-after: avoid !important;
        }
        html, body {
            margin: 0;
            padding: 0;
            height: auto !important;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1a2942;
        }

        .receipt {
            width: 100%;
            max-width: 70mm;
            margin: 0 auto;
            page-break-inside: avoid;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-bottom: 8px;
        }
        .store-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .store-tagline {
            font-size: 8px;
            color: #666;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .store-address {
            font-size: 9px;
            line-height: 1.4;
        }

        /* Divider */
        .divider {
            border: 0;
            border-top: 1px dashed #333;
            margin: 8px 0;
        }

        /* Info table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            page-break-inside: avoid;
        }
        .info-table td {
            padding: 2px 0;
            font-size: 9px;
        }
        .info-table .label {
            width: 30%;
            color: #1a2942;
        }
        .info-table .value {
            width: 70%;
            text-align: right;
            color: #1a2942;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .items-table th {
            font-size: 9px;
            font-weight: bold;
            text-align: left;
            padding: 3px 0;
            border-bottom: 1px dashed #333;
        }
        .items-table th.qty {
            text-align: center;
            width: 15%;
        }
        .items-table th.amount {
            text-align: right;
            width: 30%;
        }
        .items-table td {
            padding: 4px 0;
            font-size: 9px;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }
        .items-table td.qty {
            text-align: center;
        }
        .items-table td.amount {
            text-align: right;
        }
        .item-name {
            font-weight: bold;
            font-size: 9px;
        }
        .item-price {
            color: #666;
            font-size: 8px;
        }

        /* Totals table */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            page-break-inside: avoid;
        }
        .totals-table td {
            padding: 2px 0;
            font-size: 9px;
        }
        .totals-table .value {
            text-align: right;
        }
        .totals-table .total-row td {
            font-size: 11px;
            font-weight: bold;
            padding: 5px 0;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .thank-you {
            font-size: 9px;
            margin-bottom: 3px;
        }
        .welcome {
            font-size: 9px;
            margin-bottom: 5px;
        }
        .enquiries {
            font-size: 8px;
            color: #666;
            margin-bottom: 8px;
        }
        .receipt-box {
            display: inline-block;
            border: 1px solid #333;
            padding: 4px 12px;
            font-size: 9px;
            margin: 5px 0;
        }
        .powered {
            font-size: 8px;
            color: #666;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    @php
        // Helper function to strip emojis (DomPDF doesn't support them)
        $stripEmojis = function($text) {
            if (empty($text)) return $text;
            // Remove emojis and other special unicode characters
            return preg_replace('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]|[\x{FE00}-\x{FE0F}]|[\x{1F900}-\x{1F9FF}]|[\x{1FA00}-\x{1FA6F}]|[\x{1FA70}-\x{1FAFF}]|[\x{231A}-\x{231B}]|[\x{23E9}-\x{23F3}]|[\x{23F8}-\x{23FA}]|[\x{25AA}-\x{25AB}]|[\x{25B6}]|[\x{25C0}]|[\x{25FB}-\x{25FE}]|[\x{2614}-\x{2615}]|[\x{2648}-\x{2653}]|[\x{267F}]|[\x{2693}]|[\x{26A1}]|[\x{26AA}-\x{26AB}]|[\x{26BD}-\x{26BE}]|[\x{26C4}-\x{26C5}]|[\x{26CE}]|[\x{26D4}]|[\x{26EA}]|[\x{26F2}-\x{26F3}]|[\x{26F5}]|[\x{26FA}]|[\x{26FD}]|[\x{2702}]|[\x{2705}]|[\x{2708}-\x{270D}]|[\x{270F}]|[\x{2712}]|[\x{2714}]|[\x{2716}]|[\x{271D}]|[\x{2721}]|[\x{2728}]|[\x{2733}-\x{2734}]|[\x{2744}]|[\x{2747}]|[\x{274C}]|[\x{274E}]|[\x{2753}-\x{2755}]|[\x{2757}]|[\x{2763}-\x{2764}]|[\x{2795}-\x{2797}]|[\x{27A1}]|[\x{27B0}]|[\x{27BF}]|[\x{2934}-\x{2935}]|[\x{2B05}-\x{2B07}]|[\x{2B1B}-\x{2B1C}]|[\x{2B50}]|[\x{2B55}]|[\x{3030}]|[\x{303D}]|[\x{3297}]|[\x{3299}]/u', '', $text);
        };

        $company = $transaction->company;
        $logo = \App\Models\Setting::get('store_logo');
        $storeName = $stripEmojis(\App\Models\Setting::get('store_name') ?: $company->name);
        $storeAddress = $stripEmojis(\App\Models\Setting::get('store_address') ?: $company->address);
        $storePhone = $stripEmojis(\App\Models\Setting::get('store_phone') ?: $company->phone);
        $receiptHeader = $stripEmojis(\App\Models\Setting::get('receipt_header'));
        $receiptFooter = $stripEmojis(\App\Models\Setting::get('receipt_footer', 'Thank you for your purchase!'));
        $currency = \App\Models\Setting::get('currency_symbol', 'TZS');

        $logoBase64 = null;
        if ($logo) {
            $logoPath = storage_path('app/public/' . $logo);
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }
    @endphp

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="{{ $storeName }}" class="logo">
            @endif
            <div class="store-name">{{ $storeName }}</div>
            @if($receiptHeader)
                <div class="store-tagline">{{ strtoupper($receiptHeader) }}</div>
            @endif
            <div class="store-address">
                @if($storeAddress){{ $storeAddress }}<br>@endif
                @if($storePhone)Tel: {{ $storePhone }}@endif
            </div>
        </div>

        <hr class="divider">

        <!-- Transaction Info -->
        <table class="info-table">
            <tr>
                <td class="label">Receipt #:</td>
                <td class="value">{{ $transaction->transaction_number }}</td>
            </tr>
            <tr>
                <td class="label">Date:</td>
                <td class="value">{{ $transaction->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Time:</td>
                <td class="value">{{ $transaction->created_at->format('H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Cashier:</td>
                <td class="value">{{ $stripEmojis($transaction->user->name) }}</td>
            </tr>
            @if($transaction->customer_name)
            <tr>
                <td class="label">Customer:</td>
                <td class="value">{{ $stripEmojis($transaction->customer_name) }}</td>
            </tr>
            @endif
        </table>

        <hr class="divider">

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="qty">Qty</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $stripEmojis($item->product_name) }}</div>
                        <div class="item-price">@ {{ $currency }} {{ number_format($item->unit_price, 0) }}</div>
                    </td>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td class="amount">{{ $currency }} {{ number_format($item->subtotal, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="divider">

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="value">{{ $currency }} {{ number_format($transaction->subtotal, 0) }}</td>
            </tr>
            @if($transaction->tax_amount > 0)
            <tr>
                <td>VAT:</td>
                <td class="value">{{ $currency }} {{ number_format($transaction->tax_amount, 0) }}</td>
            </tr>
            @endif
            @if($transaction->discount_amount > 0)
            <tr>
                <td>Discount:</td>
                <td class="value">-{{ $currency }} {{ number_format($transaction->discount_amount, 0) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL:</td>
                <td class="value">{{ $currency }} {{ number_format($transaction->total, 0) }}</td>
            </tr>
        </table>

        <hr class="divider">

        <!-- Payment -->
        <table class="info-table">
            <tr>
                <td class="label">Payment:</td>
                <td class="value">{{ ucfirst($transaction->payment_method) }}</td>
            </tr>
            @if($transaction->change_given > 0)
            <tr>
                <td class="label">Change:</td>
                <td class="value">{{ $currency }} {{ number_format($transaction->change_given, 0) }}</td>
            </tr>
            @endif
        </table>

        <hr class="divider">

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">{{ $receiptFooter }}</div>
            <div class="welcome">Karibu tena / Welcome again</div>
            @if($storePhone)
                <div class="enquiries">For enquiries: {{ $storePhone }}</div>
            @endif
            <div class="receipt-box">{{ $transaction->transaction_number }}</div>
            <div class="powered">Powered by Sasampa POS | sasampa.com</div>
        </div>
    </div>
</body>
</html>
