<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $transaction->transaction_number }}</title>
    <style>
        @page {
            margin: 10mm 8mm;
            size: 76mm auto;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.5;
            color: #1a2942;
            background: #fff;
        }

        .receipt {
            width: 76mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 10mm 8mm;
            background: #fff;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 10px;
            display: block;
            border-radius: 50%;
            object-fit: cover;
        }
        .store-name {
            font-size: 18px;
            font-weight: bold;
            color: #1a2942;
            margin-bottom: 2px;
        }
        .store-tagline {
            font-size: 11px;
            color: #6b7280;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        .store-address {
            font-size: 12px;
            color: #1a2942;
            line-height: 1.6;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px dashed #1a2942;
            margin: 12px 0;
        }

        /* Info rows */
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .info-label {
            font-size: 12px;
            color: #1a2942;
        }
        .info-value {
            font-size: 12px;
            color: #1a2942;
            text-align: right;
        }

        /* Items header */
        .items-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #1a2942;
            padding-bottom: 5px;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 12px;
        }
        .items-header .col-item { flex: 1; }
        .items-header .col-qty { width: 50px; text-align: center; }
        .items-header .col-amount { width: 80px; text-align: right; }

        /* Item */
        .item {
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }
        .item:last-child {
            border-bottom: none;
        }
        .item-name {
            font-size: 12px;
            font-weight: bold;
            color: #1a2942;
            margin-bottom: 2px;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        .item-price { flex: 1; color: #6b7280; }
        .item-qty { width: 50px; text-align: center; }
        .item-amount { width: 80px; text-align: right; }

        /* Totals */
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 12px;
        }
        .total-final {
            margin: 8px 0;
            font-size: 14px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 15px;
        }
        .thank-you {
            font-size: 13px;
            color: #1a2942;
            margin-bottom: 3px;
        }
        .welcome {
            font-size: 12px;
            color: #1a2942;
            margin-bottom: 8px;
        }
        .enquiries {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .receipt-box {
            display: inline-block;
            border: 1px solid #1a2942;
            padding: 6px 15px;
            font-size: 12px;
            color: #1a2942;
            margin: 8px 0;
        }
        .powered {
            font-size: 11px;
            color: #6b7280;
            margin-top: 10px;
        }
        .powered .brand-link {
            color: #667eea;
            font-weight: bold;
        }

        /* Print button */
        .no-print {
            text-align: center;
            padding: 20px;
            background: #f5f5f5;
            margin-top: 20px;
        }
        .no-print button {
            padding: 12px 24px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            margin: 5px;
        }
        .btn-print {
            background: #1a2942;
            color: #fff;
        }
        .btn-close {
            background: #e5e7eb;
            color: #1a2942;
        }

        @media print {
            body {
                background: #fff;
            }
            .receipt {
                width: 76mm;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }

        @media screen {
            body {
                background: #e5e7eb;
                padding: 20px;
            }
            .receipt {
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                border-radius: 4px;
            }
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
    @endphp

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            @if($logo)
                <img src="{{ Storage::url($logo) }}" alt="{{ $storeName }}" class="logo">
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
            <span>Subtotal:</span>
            <span>{{ $currency }} {{ number_format($transaction->subtotal, 0) }}</span>
        </div>
        @if($transaction->tax_amount > 0)
        <div class="totals-row">
            <span>VAT:</span>
            <span>{{ $currency }} {{ number_format($transaction->tax_amount, 0) }}</span>
        </div>
        @endif
        @if($transaction->discount_amount > 0)
        <div class="totals-row">
            <span>Discount:</span>
            <span>-{{ $currency }} {{ number_format($transaction->discount_amount, 0) }}</span>
        </div>
        @endif

        <div class="totals-row total-final">
            <span>TOTAL:</span>
            <span>{{ $currency }} {{ number_format($transaction->total, 0) }}</span>
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

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Print Receipt</button>
        <button onclick="window.close()" class="btn-close">Close</button>
    </div>
</body>
</html>
