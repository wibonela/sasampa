<x-app-layout>
    <div class="fade-in">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Page Header -->
                <div class="mb-4">
                    <h1 class="page-title">Edit Expense</h1>
                    <p class="page-subtitle">Update expense details</p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('expenses.update', $expense) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expense_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('expense_category_id') is-invalid @enderror"
                                            id="expense_category_id" name="expense_category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('expense_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expense_date') is-invalid @enderror"
                                           id="expense_date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                                    @error('expense_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('description') is-invalid @enderror"
                                       id="description" name="description" value="{{ old('description', $expense->description) }}"
                                       placeholder="e.g., Sugar, Flour, Oil, Packaging" required>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="amount" class="form-label">Unit Price (TZS) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror"
                                           id="amount" name="amount" value="{{ old('amount', $expense->amount) }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" class="form-control @error('quantity') is-invalid @enderror"
                                           id="quantity" name="quantity" value="{{ old('quantity', $expense->quantity) }}" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="unit" class="form-label">Unit</label>
                                    <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit">
                                        <option value="">Select Unit</option>
                                        <option value="kg" {{ old('unit', $expense->unit) == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                        <option value="g" {{ old('unit', $expense->unit) == 'g' ? 'selected' : '' }}>Grams (g)</option>
                                        <option value="L" {{ old('unit', $expense->unit) == 'L' ? 'selected' : '' }}>Litres (L)</option>
                                        <option value="ml" {{ old('unit', $expense->unit) == 'ml' ? 'selected' : '' }}>Millilitres (ml)</option>
                                        <option value="pcs" {{ old('unit', $expense->unit) == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                        <option value="bags" {{ old('unit', $expense->unit) == 'bags' ? 'selected' : '' }}>Bags</option>
                                        <option value="boxes" {{ old('unit', $expense->unit) == 'boxes' ? 'selected' : '' }}>Boxes</option>
                                        <option value="rolls" {{ old('unit', $expense->unit) == 'rolls' ? 'selected' : '' }}>Rolls</option>
                                    </select>
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="alert alert-light mb-3">
                                <strong>Total:</strong> TZS <span id="total-display">0</span>
                            </div>

                            <div class="card border-info mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="frequency" class="form-label">Frequency <span class="text-danger">*</span></label>
                                            <select class="form-select @error('frequency') is-invalid @enderror"
                                                    id="frequency" name="frequency" required>
                                                @foreach(['one_time' => 'One-time (default)', 'daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly (e.g., annual rent)'] as $val => $label)
                                                    <option value="{{ $val }}" {{ old('frequency', $expense->frequency) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('frequency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-secondary">Recurring expenses are spread across the period in profit reports.</small>
                                        </div>
                                    </div>
                                    <div class="row" id="period-fields" style="display: none;">
                                        <div class="col-md-6 mb-3">
                                            <label for="period_start" class="form-label">Period Starts</label>
                                            <input type="date" class="form-control @error('period_start') is-invalid @enderror"
                                                   id="period_start" name="period_start"
                                                   value="{{ old('period_start', $expense->period_start?->format('Y-m-d')) }}">
                                            @error('period_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="period_end" class="form-label">Period Ends (optional)</label>
                                            <input type="date" class="form-control @error('period_end') is-invalid @enderror"
                                                   id="period_end" name="period_end"
                                                   value="{{ old('period_end', $expense->period_end?->format('Y-m-d')) }}">
                                            <small class="text-secondary">Leave empty if still ongoing.</small>
                                            @error('period_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier" class="form-label">Supplier</label>
                                    <input type="text" class="form-control @error('supplier') is-invalid @enderror"
                                           id="supplier" name="supplier" value="{{ old('supplier', $expense->supplier) }}"
                                           placeholder="Vendor or supplier name">
                                    @error('supplier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                                           id="reference_number" name="reference_number" value="{{ old('reference_number', $expense->reference_number) }}"
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
                                    <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="mobile" {{ old('payment_method', $expense->payment_method) == 'mobile' ? 'selected' : '' }}>Mobile Money</option>
                                    <option value="card" {{ old('payment_method', $expense->payment_method) == 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="bank" {{ old('payment_method', $expense->payment_method) == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2" placeholder="Additional notes...">{{ old('notes', $expense->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Update Expense
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

        const frequencySelect = document.getElementById('frequency');
        const periodFields = document.getElementById('period-fields');
        function togglePeriodFields() {
            periodFields.style.display = frequencySelect.value === 'one_time' ? 'none' : '';
        }
        frequencySelect.addEventListener('change', togglePeriodFields);
        togglePeriodFields();
    </script>
    @endpush
</x-app-layout>
