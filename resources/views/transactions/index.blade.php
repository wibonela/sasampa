<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="page-title">Sales</h1>
            <p class="page-subtitle">Insights & transaction history</p>
        </div>

        <!-- ═══════════════ INSIGHTS SECTION ═══════════════ -->

        <!-- Period Comparison Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="text-secondary" style="font-size: 12px;">Today</div>
                        <div class="fw-bold mt-1" style="font-size: 18px;">{{ number_format($insights['today_total'], 0) }} <small class="text-secondary fw-normal" style="font-size: 12px;">TZS</small></div>
                        <div class="text-secondary" style="font-size: 12px;">{{ $insights['today_count'] }} sales</div>
                        @if($insights['today_change'] != 0)
                            <div class="mt-1" style="font-size: 11px;">
                                <span class="{{ $insights['today_change'] > 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bi bi-arrow-{{ $insights['today_change'] > 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($insights['today_change']) }}% vs yesterday
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="text-secondary" style="font-size: 12px;">Avg. Sale</div>
                        <div class="fw-bold mt-1" style="font-size: 18px;">{{ number_format($insights['avg_today'], 0) }} <small class="text-secondary fw-normal" style="font-size: 12px;">TZS</small></div>
                        <div class="text-secondary" style="font-size: 12px;">This month: {{ number_format($insights['avg_month'], 0) }} TZS</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="text-secondary" style="font-size: 12px;">This Week</div>
                        <div class="fw-bold mt-1" style="font-size: 18px;">{{ number_format($insights['week_total'], 0) }} <small class="text-secondary fw-normal" style="font-size: 12px;">TZS</small></div>
                        <div class="text-secondary" style="font-size: 12px;">{{ $insights['week_count'] }} sales</div>
                        @if($insights['week_change'] != 0)
                            <div class="mt-1" style="font-size: 11px;">
                                <span class="{{ $insights['week_change'] > 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bi bi-arrow-{{ $insights['week_change'] > 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($insights['week_change']) }}% vs last week
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="text-secondary" style="font-size: 12px;">This Month</div>
                        <div class="fw-bold mt-1" style="font-size: 18px;">{{ number_format($insights['month_total'], 0) }} <small class="text-secondary fw-normal" style="font-size: 12px;">TZS</small></div>
                        <div class="text-secondary" style="font-size: 12px;">{{ $insights['month_count'] }} sales</div>
                        @if($insights['month_change'] != 0)
                            <div class="mt-1" style="font-size: 11px;">
                                <span class="{{ $insights['month_change'] > 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bi bi-arrow-{{ $insights['month_change'] > 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($insights['month_change']) }}% vs last month
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products + Payment Breakdown -->
        <div class="row g-3 mb-4">
            <!-- Top Products -->
            @if($insights['top_products']->isNotEmpty())
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1">Top Products</h6>
                        <p class="text-secondary mb-3" style="font-size: 12px;">This month by revenue</p>
                        @php $maxRevenue = $insights['top_products']->max('total_revenue') ?: 1; @endphp
                        @foreach($insights['top_products'] as $i => $product)
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2 fw-semibold {{ $i < 3 ? 'text-primary' : 'text-secondary' }}" style="width: 18px; font-size: 12px;">{{ $i + 1 }}</span>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span style="font-size: 13px;" class="text-truncate me-2">{{ $product->product_name }}</span>
                                        <span style="font-size: 12px;" class="fw-semibold text-nowrap">{{ number_format($product->total_revenue, 0) }} TZS</span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar" style="width: {{ ($product->total_revenue / $maxRevenue) * 100 }}%; background-color: var(--apple-blue);"></div>
                                    </div>
                                    <div class="text-secondary mt-1" style="font-size: 10px;">{{ $product->total_quantity }} sold</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment Breakdown -->
            @if($insights['payment_breakdown']->isNotEmpty())
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1">Payment Breakdown</h6>
                        <p class="text-secondary mb-3" style="font-size: 12px;">This month</p>
                        @php
                            $methodColors = ['cash' => '#34C759', 'card' => '#007AFF', 'mobile' => '#5856D6', 'bank_transfer' => '#FF9500', 'credit' => '#FF3B30'];
                            $methodLabels = ['cash' => 'Cash', 'card' => 'Card', 'mobile' => 'Mobile Money', 'bank_transfer' => 'Bank Transfer', 'credit' => 'Credit'];
                        @endphp
                        @foreach($insights['payment_breakdown'] as $method)
                            @php
                                $pct = $insights['payment_total'] > 0 ? round(($method->total / $insights['payment_total']) * 100, 1) : 0;
                                $color = $methodColors[$method->payment_method] ?? '#8E8E93';
                                $label = $methodLabels[$method->payment_method] ?? ucfirst($method->payment_method);
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="font-size: 13px;">
                                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $color }}; margin-right: 6px;"></span>
                                        {{ $label }}
                                    </span>
                                    <span style="font-size: 12px;" class="fw-semibold">{{ number_format($method->total, 0) }} TZS <span class="text-secondary fw-normal">({{ $pct }}%)</span></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ $pct }}%; background-color: {{ $color }};"></div>
                                </div>
                                <div class="text-secondary" style="font-size: 10px;">{{ $method->count }} transactions</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Discounts + Peak Hours + Customer Insights -->
        <div class="row g-3 mb-4">
            <!-- Discounts -->
            @if($insights['discounted_count'] > 0)
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1">Discounts</h6>
                        <p class="text-secondary mb-3" style="font-size: 12px;">This month</p>
                        <div class="mb-2">
                            <span class="text-secondary" style="font-size: 12px;">Total given away</span>
                            <div class="fw-bold" style="font-size: 20px; color: #FF9500;">{{ number_format($insights['total_discounts'], 0) }} TZS</div>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size: 13px;">
                            <span class="text-secondary">Discounted sales</span>
                            <span class="fw-semibold">{{ $insights['discounted_count'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size: 13px;">
                            <span class="text-secondary">Discount rate</span>
                            <span class="fw-semibold">{{ $insights['discount_rate'] }}%</span>
                        </div>
                        @if($insights['discounted_count'] > 0)
                        <div class="d-flex justify-content-between" style="font-size: 13px;">
                            <span class="text-secondary">Avg. per sale</span>
                            <span class="fw-semibold">{{ number_format($insights['total_discounts'] / $insights['discounted_count'], 0) }} TZS</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Peak Hours -->
            @if($insights['peak_hours']->isNotEmpty())
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1">Peak Hours</h6>
                        <p class="text-secondary mb-3" style="font-size: 12px;">Today's busiest times</p>
                        @php $maxHourCount = $insights['peak_hours']->max('count') ?: 1; @endphp
                        @foreach($insights['peak_hours']->take(5) as $hour)
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-secondary me-2" style="font-size: 12px; width: 75px;">{{ sprintf('%02d:00-%02d:59', $hour->hour, $hour->hour) }}</span>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" style="width: {{ ($hour->count / $maxHourCount) * 100 }}%; background-color: var(--apple-blue);"></div>
                                    </div>
                                </div>
                                <span class="ms-2 fw-semibold" style="font-size: 12px; width: 45px; text-align: right;">{{ $hour->count }} sales</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Customer Insights -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1">Customer Insights</h6>
                        <p class="text-secondary mb-3" style="font-size: 12px;">This month</p>
                        <div class="d-flex justify-content-between mb-2" style="font-size: 13px;">
                            <span class="text-secondary">Returning customers</span>
                            <span class="fw-semibold text-success">{{ $insights['registered_sales'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3" style="font-size: 13px;">
                            <span class="text-secondary">Walk-ins</span>
                            <span class="fw-semibold">{{ $insights['walk_in_sales'] }}</span>
                        </div>
                        @if($insights['top_customers']->isNotEmpty())
                            <div class="text-secondary mb-2" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Top Customers</div>
                            @foreach($insights['top_customers'] as $customer)
                                <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                                    <span class="text-truncate me-2">{{ $customer->customer_name }}</span>
                                    <span class="fw-semibold text-nowrap">{{ number_format($customer->total_spent, 0) }} TZS</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Margin Alerts -->
        @if($insights['low_margin_alerts']->isNotEmpty())
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle text-warning me-1"></i> Margin Alerts</h6>
                <p class="text-secondary mb-3" style="font-size: 12px;">Products sold below cost this month</p>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="font-size: 12px;">Product</th>
                                <th style="font-size: 12px;">Qty Sold</th>
                                <th style="font-size: 12px;">Avg. Price</th>
                                <th style="font-size: 12px;">Avg. Cost</th>
                                <th style="font-size: 12px;" class="text-danger">Loss</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($insights['low_margin_alerts'] as $alert)
                            <tr>
                                <td style="font-size: 13px;">{{ $alert->product_name }}</td>
                                <td style="font-size: 13px;">{{ $alert->total_quantity }}</td>
                                <td style="font-size: 13px;">{{ number_format($alert->avg_price, 0) }} TZS</td>
                                <td style="font-size: 13px;">{{ number_format($alert->avg_cost, 0) }} TZS</td>
                                <td style="font-size: 13px;" class="text-danger fw-semibold">{{ number_format(abs($alert->total_loss), 0) }} TZS</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- ═══════════════ TRANSACTIONS TABLE ═══════════════ -->

        <h6 class="fw-semibold mb-3">All Transactions</h6>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form action="{{ route('transactions.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search"
                               placeholder="Transaction #" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="payment_method">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="mobile" {{ request('payment_method') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="From">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="To">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Date</th>
                            <th>Cashier</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    <a href="{{ route('transactions.show', $transaction) }}" style="text-decoration: none; color: var(--apple-blue); font-weight: 500;">
                                        {{ $transaction->transaction_number }}
                                    </a>
                                </td>
                                <td class="text-secondary">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $transaction->user->name }}</td>
                                <td class="text-secondary">{{ $transaction->customer_name ?? '—' }}</td>
                                <td>{{ $transaction->items->count() }}</td>
                                <td style="font-weight: 600;">{{ number_format($transaction->total, 0) }} TZS</td>
                                <td>
                                    @if($transaction->payment_method === 'cash')
                                        <span class="badge bg-success">Cash</span>
                                    @elseif($transaction->payment_method === 'card')
                                        <span class="badge bg-primary">Card</span>
                                    @elseif($transaction->payment_method === 'mobile')
                                        <span class="badge" style="background-color: #5856D6;">Mobile</span>
                                    @elseif($transaction->payment_method === 'bank_transfer')
                                        <span class="badge" style="background-color: #FF9500;">Bank</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($transaction->payment_method) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($transaction->status === 'refunded')
                                        <span class="badge bg-warning">Refunded</span>
                                    @else
                                        <span class="badge bg-danger">Voided</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('transactions.show', $transaction) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('pos.receipt', $transaction) }}"
                                           class="btn btn-sm btn-outline-secondary" target="_blank" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="{{ route('pos.receipt.pdf', $transaction) }}"
                                           class="btn btn-sm btn-outline-success" title="Download PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-secondary">
                                    <i class="bi bi-receipt" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                                    <p class="mt-3 mb-0">No transactions found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="card-footer">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
