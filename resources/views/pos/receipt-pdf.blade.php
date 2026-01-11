<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt - {{ $transaction->transaction_number }}</title>
    <style>
        @page {
            margin: 8mm 12mm;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
        }
        body {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #1a2942;
        }

        .receipt {
            width: 100%;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 8px;
            display: block;
            border-radius: 50%;
        }
        .store-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a2942;
            margin-bottom: 2px;
        }
        .store-tagline {
            font-size: 9px;
            color: #6b7280;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .store-address {
            font-size: 10px;
            color: #1a2942;
            line-height: 1.4;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px dashed #1a2942;
            margin: 6px 0;
        }

        /* Info rows */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }
        .info-label {
            display: table-cell;
            width: 35%;
            font-size: 10px;
            color: #1a2942;
        }
        .info-value {
            display: table-cell;
            width: 65%;
            font-size: 10px;
            color: #1a2942;
            text-align: right;
        }

        /* Items header */
        .items-header {
            display: table;
            width: 100%;
            border-bottom: 1px dashed #1a2942;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .items-header span {
            display: table-cell;
            font-size: 10px;
            font-weight: bold;
            color: #1a2942;
        }
        .items-header .col-item { width: 50%; }
        .items-header .col-qty { width: 20%; text-align: center; }
        .items-header .col-amount { width: 30%; text-align: right; }

        /* Item */
        .item {
            margin-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
        .item:last-child {
            border-bottom: none;
        }
        .item-name {
            font-size: 10px;
            font-weight: bold;
            color: #1a2942;
            margin-bottom: 1px;
        }
        .item-details {
            display: table;
            width: 100%;
        }
        .item-details span {
            display: table-cell;
            font-size: 10px;
            color: #1a2942;
        }
        .item-price { width: 50%; color: #6b7280; }
        .item-qty { width: 20%; text-align: center; }
        .item-amount { width: 30%; text-align: right; }

        /* Totals */
        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }
        .totals-label {
            display: table-cell;
            font-size: 10px;
            color: #1a2942;
        }
        .totals-value {
            display: table-cell;
            font-size: 10px;
            color: #1a2942;
            text-align: right;
        }
        .total-final {
            margin: 5px 0;
        }
        .total-final .totals-label,
        .total-final .totals-value {
            font-size: 12px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 8px;
        }
        .thank-you {
            font-size: 10px;
            color: #1a2942;
            margin-bottom: 2px;
        }
        .welcome {
            font-size: 10px;
            color: #1a2942;
            margin-bottom: 5px;
        }
        .enquiries {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .receipt-box {
            display: inline-block;
            border: 1px solid #1a2942;
            padding: 4px 10px;
            font-size: 10px;
            color: #1a2942;
            margin: 5px 0;
        }
        .powered {
            font-size: 9px;
            color: #6b7280;
            margin-top: 5px;
        }
        .powered .brand-link {
            color: #667eea;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $company = $transaction->company;
        $logo = \App\Models\Setting::get('store_logo');
        $storeName = \App\Models\Setting::get('store_name') ?: $company->name;
        $storeAddress = \App\Models\Setting::get('store_address') ?: $company->address;
        $storePhone = \App\Models\Setting::get('store_phone') ?: $company->phone;
        $receiptHeader = \App\Models\Setting::get('receipt_header');
        $receiptFooter = \App\Models\Setting::get('receipt_footer', 'Thank you for your purchase!');
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
        <div class="info-row">
            <span class="info-label">Receipt #:</span>
            <span class="info-value">{{ $transaction->transaction_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span class="info-value">{{ $transaction->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Time:</span>
            <span class="info-value">{{ $transaction->created_at->format('H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cashier:</span>
            <span class="info-value">{{ $transaction->user->name }}</span>
        </div>
        @if($transaction->customer_name)
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span class="info-value">{{ $transaction->customer_name }}</span>
        </div>
        @endif

        <hr class="divider">

        <!-- Items Header -->
        <div class="items-header">
            <span class="col-item">Item</span>
            <span class="col-qty">Qty</span>
            <span class="col-amount">Amount</span>
        </div>

        <!-- Items -->
        @foreach($transaction->items as $item)
        <div class="item">
            <div class="item-name">{{ $item->product_name }}</div>
            <div class="item-details">
                <span class="item-price">@ {{ $currency }} {{ number_format($item->unit_price, 2) }}</span>
                <span class="item-qty">{{ $item->quantity }}</span>
                <span class="item-amount">{{ $currency }} {{ number_format($item->subtotal, 0) }}</span>
            </div>
        </div>
        @endforeach

        <hr class="divider">

        <!-- Totals -->
        <div class="totals-row">
            <span class="totals-label">Subtotal:</span>
            <span class="totals-value">{{ $currency }} {{ number_format($transaction->subtotal, 0) }}</span>
        </div>
        @if($transaction->tax_amount > 0)
        <div class="totals-row">
            <span class="totals-label">VAT:</span>
            <span class="totals-value">{{ $currency }} {{ number_format($transaction->tax_amount, 0) }}</span>
        </div>
        @endif
        @if($transaction->discount_amount > 0)
        <div class="totals-row">
            <span class="totals-label">Discount:</span>
            <span class="totals-value">-{{ $currency }} {{ number_format($transaction->discount_amount, 0) }}</span>
        </div>
        @endif

        <div class="totals-row total-final">
            <span class="totals-label">TOTAL:</span>
            <span class="totals-value">{{ $currency }} {{ number_format($transaction->total, 0) }}</span>
        </div>

        <hr class="divider">

        <!-- Payment -->
        <div class="info-row">
            <span class="info-label">Payment:</span>
            <span class="info-value">{{ ucfirst($transaction->payment_method) }}</span>
        </div>
        @if($transaction->change_given > 0)
        <div class="info-row">
            <span class="info-label">Change:</span>
            <span class="info-value">{{ $currency }} {{ number_format($transaction->change_given, 0) }}</span>
        </div>
        @endif

        <hr class="divider">

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">{{ $receiptFooter }}</div>
            <div class="welcome">Karibu tena / Welcome again</div>
            @if($storePhone)
                <div class="enquiries">For enquiries: {{ $storePhone }}</div>
            @endif
            <div class="receipt-box">{{ $transaction->transaction_number }}</div>
            <div class="powered">Powered by Sasampa POS | <span class="brand-link">sasampa.com</span></div>
        </div>
    </div>
</body>
</html>
