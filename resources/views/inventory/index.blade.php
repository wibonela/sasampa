<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="page-title">Inventory</h1>
            <p class="page-subtitle">Manage your stock levels</p>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">{{ $lowStockCount }}</div>
                    <div class="stat-label">Low Stock Items</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="stat-value">{{ $outOfStockCount }}</div>
                    <div class="stat-label">Out of Stock</div>
                </div>
            </div>
            <div class="col-md-4">
                <a href="{{ route('inventory.history') }}" class="text-decoration-none">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-value" style="font-size: 18px;">View History</div>
                        <div class="stat-label">Stock Adjustments</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form action="{{ route('inventory.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="search"
                               placeholder="Search products..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">All Stock Levels</option>
                            <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out" {{ request('status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Threshold</th>
                            <th>Status</th>
                            <th>Last Restocked</th>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('products.show', $item->product) }}" style="text-decoration: none; color: var(--apple-text); font-weight: 500;">
                                        {{ $item->product->name }}
                                    </a>
                                </td>
                                <td class="text-secondary">{{ $item->product->category?->name ?? '—' }}</td>
                                <td>
                                    <span style="font-weight: 600; color: var(--apple-{{ $item->quantity == 0 ? 'red' : ($item->isLowStock() ? 'orange' : 'green') }});">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td class="text-secondary">{{ $item->low_stock_threshold }}</td>
                                <td>
                                    @if($item->quantity == 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($item->isLowStock())
                                        <span class="badge bg-warning">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td class="text-secondary">{{ $item->last_restocked_at?->diffForHumans() ?? 'Never' }}</td>
                                <td>
                                    <a href="{{ route('inventory.adjust', $item->product) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-plus-slash-minus"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="bi bi-clipboard-data" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                                    <p class="mt-3 mb-0">No inventory records found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($inventory->hasPages())
                <div class="card-footer">
                    {{ $inventory->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
