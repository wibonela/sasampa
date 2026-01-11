<x-app-layout>
    <x-slot name="header">Product Performance</x-slot>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <form action="{{ route('reports.products') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label mb-0 small">From</label>
                    <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0 small">To</label>
                    <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0 small">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Top Selling Products</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th class="text-end">Quantity Sold</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $index => $product)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $product->product_name }}</td>
                                <td class="text-end">{{ $product->total_quantity }}</td>
                                <td class="text-end">TZS {{ number_format($product->total_revenue, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No sales data for this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
