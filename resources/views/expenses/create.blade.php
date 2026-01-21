<x-app-layout>
    <div class="fade-in">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Page Header -->
                <div class="mb-4">
                    <h1 class="page-title">Add Expense</h1>
                    <p class="page-subtitle">Record a new expense or raw material purchase</p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('expenses.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expense_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('expense_category_id') is-invalid @enderror"
                                            id="expense_category_id" name="expense_category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('expense_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-secondary">
                                        <a href="{{ route('expense-categories.create') }}">+ Add new category</a>
                                    </small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expense_date') is-invalid @enderror"
                                           id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                    @error('expense_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('description') is-invalid @enderror"
                                       id="description" name="description" value="{{ old('description') }}"
                                       placeholder="e.g., Sugar, Flour, Oil, Packaging" required>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="amount" class="form-label">Unit Price (TZS) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror"
                                           id="amount" name="amount" value="{{ old('amount') }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" class="form-control @error('quantity') is-invalid @enderror"
                                           id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="unit" class="form-label">Unit</label>
                                    <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit">
                                        <option value="">Select Unit</option>
                                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                        <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>Grams (g)</option>
                                        <option value="L" {{ old('unit') == 'L' ? 'selected' : '' }}>Litres (L)</option>
                                        <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>Millilitres (ml)</option>
                                        <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                        <option value="bags" {{ old('unit') == 'bags' ? 'selected' : '' }}>Bags</option>
                                        <option value="boxes" {{ old('unit') == 'boxes' ? 'selected' : '' }}>Boxes</option>
                                        <option value="rolls" {{ old('unit') == 'rolls' ? 'selected' : '' }}>Rolls</option>
                                    </select>
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="alert alert-light mb-3">
                                <strong>Total:</strong> TZS <span id="total-display">0</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier" class="form-label">Supplier</label>
                                    <input type="text" class="form-control @error('supplier') is-invalid @enderror"
                                           id="supplier" name="supplier" value="{{ old('supplier') }}"
                                           placeholder="Vendor or supplier name">
                                    @error('supplier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                                           id="reference_number" name="reference_number" value="{{ old('reference_number') }}"
                                           placeholder="Receipt or invoice number">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror"
                                        id="payment_method" name="payment_method" required>
                                    <option value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="mobile" {{ old('payment_method') == 'mobile' ? 'selected' : '' }}>Mobile Money</option>
                                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Save Expense
                                </button>
                                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateTotal() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const total = amount * quantity;
            document.getElementById('total-display').textContent = total.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        }

        document.getElementById('amount').addEventListener('input', updateTotal);
        document.getElementById('quantity').addEventListener('input', updateTotal);
        updateTotal();
    </script>
    @endpush
</x-app-layout>
