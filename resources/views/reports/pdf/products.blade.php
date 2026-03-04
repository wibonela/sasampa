<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Product Performance Report</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: normal; color: #666; }
        .header .period { margin-top: 5px; font-size: 11px; color: #888; }
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
        <h2>Product Performance Report</h2>
        <div class="period">{{ $dateFrom }} to {{ $dateTo }}</div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th class="text-end">Quantity Sold</th>
                <th class="text-end">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->product_name }}</td>
                    <td class="text-end">{{ $product->total_quantity }}</td>
                    <td class="text-end">TZS {{ number_format($product->total_revenue, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y H:i') }} | {{ $companyName }}
    </div>
</body>
</html>
