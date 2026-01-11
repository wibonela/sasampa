<x-app-layout>
    <x-slot name="header">Inventory Report</x-slot>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small">Total Stock Value</div>
                    <div class="h3 mb-0">TZS {{ number_format($totalValue, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small">Total Items in Stock</div>
                    <div class="h3 mb-0">{{ number_format($totalItems) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small">Low Stock Items</div>
                    <div class="h3 mb-0 text-warning">{{ $lowStockCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Inventory Details</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th class="text-end">Quantity</th>
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
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($item['is_low_stock'])
                                        <span class="badge bg-warning">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
