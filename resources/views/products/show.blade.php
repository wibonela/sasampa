<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('products.index') }}" class="text-secondary" style="text-decoration: none;">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h1 class="page-title mb-0">{{ $product->name }}</h1>
                    @if($product->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
                <p class="page-subtitle">Product details</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST"
                      data-confirm='{"title":"Delete Product","message":"Are you sure you want to delete {{ $product->name }}? This action cannot be undone.","type":"danger","confirmText":"Delete"}'>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    @if($product->image_path)
                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                             class="rounded mb-3" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto mb-3"
                             style="width: 200px; height: 200px;">
                            <i class="bi bi-image display-1 text-muted"></i>
                        </div>
                    @endif
                    <h5>{{ $product->name }}</h5>
                    <p class="text-muted mb-0">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Stock Information</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Current Stock:</span>
                        <strong>
                            @if($product->isLowStock())
                                <span class="text-warning">{{ $product->stock_quantity }}</span>
                            @else
                                <span class="text-success">{{ $product->stock_quantity }}</span>
                            @endif
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Low Stock Threshold:</span>
                        <strong>{{ $product->inventory?->low_stock_threshold ?? 10 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Status:</span>
                        @if($product->isLowStock())
                            <span class="badge bg-warning">Low Stock</span>
                        @else
                            <span class="badge bg-success">In Stock</span>
                        @endif
                    </div>
                    <hr>
                    <a href="{{ route('inventory.adjust', $product) }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-plus-slash-minus me-1"></i>Adjust Stock
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Product Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">SKU</label>
                            <div>{{ $product->sku ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Barcode</label>
                            <div>{{ $product->barcode ?? '-' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Cost Price</label>
                            <div>TZS {{ number_format($product->cost_price, 0) }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Selling Price</label>
                            <div class="fw-bold">TZS {{ number_format($product->selling_price, 0) }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Tax Rate</label>
                            <div>{{ $product->tax_rate }}%</div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="text-muted small">Description</label>
                            <div>{{ $product->description ?? 'No description' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <div>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Created</label>
                            <div>{{ $product->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Recent Stock Adjustments</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Change</th>
                                    <th>Before/After</th>
                                    <th>By</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->stockAdjustments as $adjustment)
                                    <tr>
                                        <td>{{ $adjustment->created_at->format('M d, H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $adjustment->type_color }}">
                                                {{ ucfirst($adjustment->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($adjustment->quantity_change > 0)
                                                <span class="text-success">+{{ $adjustment->quantity_change }}</span>
                                            @else
                                                <span class="text-danger">{{ $adjustment->quantity_change }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $adjustment->quantity_before }} → {{ $adjustment->quantity_after }}</td>
                                        <td>{{ $adjustment->user->name }}</td>
                                        <td>{{ Str::limit($adjustment->reason, 30) ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">
                                            No stock adjustments yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
