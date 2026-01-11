<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('transactions.index') }}" class="text-secondary" style="text-decoration: none;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="page-title mb-0">{{ $transaction->transaction_number }}</h1>
                    <p class="page-subtitle mb-0">Transaction details</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pos.receipt', $transaction) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                    <i class="bi bi-printer me-1"></i>Print
                </a>
                <a href="{{ route('pos.receipt.pdf', $transaction) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-pdf me-1"></i>Download PDF
                </a>
                @if($transaction->status === 'completed' && auth()->user()->isAdmin())
                    <form action="{{ route('transactions.void', $transaction) }}" method="POST"
                          onsubmit="return confirm('Void this transaction? Stock will be restored.')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Void
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Items</div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Unit Price</th>
                                    <th>Qty</th>
                                    <th>Tax</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaction->items as $item)
                                    <tr>
                                        <td>
                                            @if($item->product)
                                                <a href="{{ route('products.show', $item->product) }}" style="text-decoration: none; color: var(--apple-blue);">
                                                    {{ $item->product_name }}
                                                </a>
                                            @else
                                                {{ $item->product_name }}
                                            @endif
                                        </td>
                                        <td>{{ number_format($item->unit_price, 0) }} TZS</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="text-secondary">{{ $item->tax_rate }}%</td>
                                        <td class="text-end" style="font-weight: 500;">{{ number_format($item->subtotal, 0) }} TZS</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer" style="background: var(--apple-gray-6);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Subtotal</span>
                            <span>{{ number_format($transaction->subtotal, 0) }} TZS</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Tax</span>
                            <span>{{ number_format($transaction->tax_amount, 0) }} TZS</span>
                        </div>
                        @if($transaction->discount_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-secondary">Discount</span>
                                <span style="color: var(--apple-red);">-{{ number_format($transaction->discount_amount, 0) }} TZS</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between pt-2 border-top" style="border-color: var(--apple-border) !important;">
                            <span style="font-weight: 600;">Total</span>
                            <span style="font-weight: 700; font-size: 18px;">{{ number_format($transaction->total, 0) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Details</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Status</div>
                            @if($transaction->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($transaction->status === 'refunded')
                                <span class="badge bg-warning">Refunded</span>
                            @else
                                <span class="badge bg-danger">Voided</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Date & Time</div>
                            <div style="font-weight: 500;">{{ $transaction->created_at->format('M d, Y H:i:s') }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Cashier</div>
                            <div style="font-weight: 500;">{{ $transaction->user->name }}</div>
                        </div>
                        @if($transaction->customer_name)
                            <div class="mb-3">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Customer</div>
                                <div style="font-weight: 500;">{{ $transaction->customer_name }}</div>
                            </div>
                        @endif
                        @if($transaction->customer_phone)
                            <div class="mb-3">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Phone</div>
                                <div style="font-weight: 500;">{{ $transaction->customer_phone }}</div>
                            </div>
                        @endif
                        @if($transaction->customer_tin)
                            <div class="mb-3">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">TIN Number</div>
                                <div style="font-weight: 500;">{{ $transaction->customer_tin }}</div>
                            </div>
                        @endif

                        <div class="border-top pt-3 mt-3" style="border-color: var(--apple-border) !important;">
                            <div class="mb-3">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Payment Method</div>
                                <div style="font-weight: 500;">{{ $transaction->payment_method_label }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Amount Paid</div>
                                <div style="font-weight: 500;">{{ number_format($transaction->amount_paid, 0) }} TZS</div>
                            </div>
                            @if($transaction->change_given > 0)
                                <div class="mb-0">
                                    <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Change Given</div>
                                    <div style="font-weight: 500; color: var(--apple-green);">{{ number_format($transaction->change_given, 0) }} TZS</div>
                                </div>
                            @endif
                        </div>

                        @if($transaction->notes)
                            <div class="border-top pt-3 mt-3" style="border-color: var(--apple-border) !important;">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Notes</div>
                                <div>{{ $transaction->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
