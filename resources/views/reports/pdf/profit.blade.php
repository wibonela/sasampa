<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Profit Report</title>
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
        .profit { color: #27ae60; }
        .loss { color: #e74c3c; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data th { background: #333; color: #fff; padding: 8px; text-align: left; font-size: 10px; }
        table.data td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        table.data tr:nth-child(even) { background: #f9f9f9; }
        table.data tfoot td { font-weight: bold; border-top: 2px solid #333; background: #f5f5f5; }
        .text-end { text-align: right; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>Profit Report</h2>
        <div class="period">{{ $dateFrom }} to {{ $dateTo }}</div>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Sales</td>
                <td class="text-end">TZS {{ number_format($totalSales) }}</td>
            </tr>
            <tr>
                <td class="label">Total Expenses</td>
                <td class="text-end">TZS {{ number_format($totalExpenses) }}</td>
            </tr>
            <tr>
                <td class="label">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</td>
                <td class="text-end {{ $netProfit >= 0 ? 'profit' : 'loss' }}"><strong>TZS {{ number_format(abs($netProfit)) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Profit Margin</td>
                <td class="text-end">{{ number_format($profitMargin, 1) }}%</td>
            </tr>
        </table>
    </div>

    <h3 style="font-size: 13px; margin-bottom: 8px;">Daily Profit/Loss</h3>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th class="text-end">Sales</th>
                <th class="text-end">Expenses</th>
                <th class="text-end">Profit/Loss</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyProfit as $day)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($day['date'])->format('D, d M Y') }}</td>
                    <td class="text-end">TZS {{ number_format($day['sales']) }}</td>
                    <td class="text-end">TZS {{ number_format($day['expenses']) }}</td>
                    <td class="text-end {{ $day['profit'] >= 0 ? 'profit' : 'loss' }}">TZS {{ number_format($day['profit']) }}</td>
                </tr>
            @endforeach
        </tbody>
        @if(count($dailyProfit) > 0)
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="text-end">TZS {{ number_format($dailyProfit->sum('sales')) }}</td>
                    <td class="text-end">TZS {{ number_format($dailyProfit->sum('expenses')) }}</td>
                    <td class="text-end">TZS {{ number_format($dailyProfit->sum('profit')) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    @if($expensesByCategory->count() > 0)
        <h3 style="font-size: 13px; margin-bottom: 8px;">Expense Categories</h3>
        <table class="data">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">% of Expenses</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByCategory as $category)
                    <tr>
                        <td>{{ $category->category_name }}</td>
                        <td class="text-end">TZS {{ number_format($category->total) }}</td>
                        <td class="text-end">{{ $totalExpenses > 0 ? number_format(($category->total / $totalExpenses) * 100, 1) : 0 }}%</td>
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
