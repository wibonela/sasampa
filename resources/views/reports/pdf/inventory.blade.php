<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Inventory Report</title>
    <style>
        @page { margin: 15mm; size: landscape; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: normal; color: #666; }
        .header .period { margin-top: 5px; font-size: 11px; color: #888; }
        .summary { margin-bottom: 20px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 8px 12px; border: 1px solid #ddd; }
        .summary .label { background: #f5f5f5; font-weight: bold; width: 30%; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data th { background: #333; color: #fff; padding: 6px; text-align: left; font-size: 9px; }
        table.data td { padding: 5px 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        table.data tr:nth-child(even) { background: #f9f9f9; }
        .text-end { text-align: right; }
        .low-stock { color: #e67e22; font-weight: bold; }
        .out-of-stock { color: #e74c3c; font-weight: bold; }
        .ok { color: #27ae60; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>Inventory Report</h2>
        <div class="period">As of {{ now()->format('M d, Y H:i') }}</div>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Stock Value</td>
                <td class="text-end">TZS {{ number_format($totalValue, 0) }}</td>
                <td class="label">Total Items in Stock</td>
                <td class="text-end">{{ number_format($totalItems) }}</td>
                <td class="label">Low Stock Items</td>
                <td class="text-end">{{ $lowStockCount }}</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>SKU</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Cost Price</th>
                <th class="text-end">Selling Price</th>
                <th class="text-end">Stock Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventory as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['category'] }}</td>
                    <td>{{ $item['sku'] ?? '-' }}</td>
                    <td class="text-end">{{ $item['quantity'] }}</td>
                    <td class="text-end">TZS {{ number_format($item['cost_price'], 0) }}</td>
                    <td class="text-end">TZS {{ number_format($item['selling_price'], 0) }}</td>
                    <td class="text-end">TZS {{ number_format($item['stock_value'], 0) }}</td>
                    <td>
                        @if($item['quantity'] == 0)
                            <span class="out-of-stock">Out of Stock</span>
                        @elseif($item['is_low_stock'])
                            <span class="low-stock">Low Stock</span>
                        @else
                            <span class="ok">OK</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y H:i') }} | {{ $companyName }}
    </div>
</body>
</html>
