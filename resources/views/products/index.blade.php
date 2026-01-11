<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Products</h1>
                <p class="page-subtitle">Manage your product catalog</p>
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Add Product
            </a>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search"
                               placeholder="Search products..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;"></th>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    @if($product->image_path)
                                        <img src="{{ Storage::url($product->image_path) }}"
                                             alt="{{ $product->name }}"
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                                    @else
                                        <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--apple-gray-5); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-image text-secondary"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('products.show', $product) }}" style="text-decoration: none; color: var(--apple-text); font-weight: 500;">
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td class="text-secondary">{{ $product->sku ?? '—' }}</td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td style="font-weight: 500;">{{ number_format($product->selling_price, 0) }} TZS</td>
                                <td>
                                    @if($product->isLowStock())
                                        <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST"
                                              data-confirm='{"title":"Delete Product","message":"Are you sure you want to delete {{ $product->name }}? This action cannot be undone.","type":"danger","confirmText":"Delete"}'>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-secondary">
                                    <i class="bi bi-box-seam" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                                    <p class="mt-3 mb-0">No products found</p>
                                    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm mt-2">Add Product</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="card-footer">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
