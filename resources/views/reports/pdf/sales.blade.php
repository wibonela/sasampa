<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sales Report</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: normal; color: #666; }
        .header .period { margin-top: 5px; font-size: 11px; color: #888; }
        .summary { margin-bottom: 20px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 8px 12px; border: 1px solid #ddd; }
        .summary .label { background: #f5f5f5; font-weight: bold; width: 40%; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data th { background: #333; color: #fff; padding: 8px; text-align: left; font-size: 10px; }
        table.data td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        table.data tr:nth-child(even) { background: #f9f9f9; }
        .text-end { text-align: right; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>Sales Report</h2>
        <div class="period">{{ $dateFrom }} to {{ $dateTo }}</div>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Sales</td>
                <td class="text-end">TZS {{ number_format($totalSales, 0) }}</td>
            </tr>
            <tr>
                <td class="label">Total Transactions</td>
                <td class="text-end">{{ $totalTransactions }}</td>
            </tr>
            <tr>
                <td class="label">Average Transaction</td>
                <td class="text-end">TZS {{ number_format($averageTransaction, 0) }}</td>
            </tr>
        </table>
    </div>

    <h3 style="font-size: 13px; margin-bottom: 8px;">Daily Sales</h3>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th class="text-end">Transactions</th>
                <th class="text-end">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesData as $day)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                    <td class="text-end">{{ $day->count }}</td>
                    <td class="text-end">TZS {{ number_format($day->total, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($paymentBreakdown->count() > 0)
        <h3 style="font-size: 13px; margin-bottom: 8px;">Payment Methods</h3>
        <table class="data">
            <thead>
                <tr>
                    <th>Method</th>
                    <th class="text-end">Count</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paymentBreakdown as $method)
                    <tr>
                        <td>{{ ucfirst($method->payment_method) }}</td>
                        <td class="text-end">{{ $method->count }}</td>
                        <td class="text-end">TZS {{ number_format($method->total, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Generated on {{ now()->format('M d, Y H:i') }} | {{ $companyName }}
    </div>
</body>
</html>
