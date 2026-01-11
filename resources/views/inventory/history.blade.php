<x-app-layout>
    <x-slot name="header">Stock Adjustment History</x-slot>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('inventory.history') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select class="form-select" name="product">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="received" {{ request('type') == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="sold" {{ request('type') == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="damaged" {{ request('type') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        <option value="returned" {{ request('type') == 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('inventory.history') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Change</th>
                            <th>Before → After</th>
                            <th>By</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adjustment)
                            <tr>
                                <td>{{ $adjustment->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('products.show', $adjustment->product) }}">
                                        {{ $adjustment->product->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $adjustment->type_color }}">
                                        {{ ucfirst($adjustment->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($adjustment->quantity_change > 0)
                                        <span class="text-success fw-bold">+{{ $adjustment->quantity_change }}</span>
                                    @else
                                        <span class="text-danger fw-bold">{{ $adjustment->quantity_change }}</span>
                                    @endif
                                </td>
                                <td>{{ $adjustment->quantity_before }} → {{ $adjustment->quantity_after }}</td>
                                <td>{{ $adjustment->user->name }}</td>
                                <td class="text-muted">{{ Str::limit($adjustment->reason, 40) ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No stock adjustments found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $adjustments->withQueryString()->links() }}
    </div>
</x-app-layout>
