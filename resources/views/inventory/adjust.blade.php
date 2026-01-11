<x-app-layout>
    <x-slot name="header">Adjust Stock: {{ $product->name }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Current Stock</div>
                            <div class="h3 mb-0">{{ $product->stock_quantity }}</div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Low Stock Threshold</div>
                            <div class="h5 mb-0">{{ $product->inventory?->low_stock_threshold ?? 10 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('inventory.adjust.store', $product) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="type" class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="received" {{ old('type') == 'received' ? 'selected' : '' }}>
                                    Stock Received (Add)
                                </option>
                                <option value="damaged" {{ old('type') == 'damaged' ? 'selected' : '' }}>
                                    Damaged/Lost (Remove)
                                </option>
                                <option value="returned" {{ old('type') == 'returned' ? 'selected' : '' }}>
                                    Customer Return (Add)
                                </option>
                                <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>
                                    Manual Adjustment (Remove)
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                   id="quantity" name="quantity" value="{{ old('quantity') }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror"
                                      id="reason" name="reason" rows="3">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Save Adjustment
                            </button>
                            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
