<x-app-layout>
    <div class="fade-in">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}</p>
            </div>
            <a href="{{ route('analytics.profit') }}" class="btn btn-outline-primary">
                <i class="bi bi-graph-up me-1"></i>Full Analytics
            </a>
        </div>

        <!-- Today's Profit Highlight -->
        <div class="card mb-4 {{ $todayNetProfit >= 0 ? 'border-success' : 'border-danger' }}" style="border-width: 2px;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center py-3">
                        <div style="width: 64px; height: 64px; border-radius: 16px; background: {{ $todayNetProfit >= 0 ? 'rgba(52, 199, 89, 0.15)' : 'rgba(255, 59, 48, 0.15)' }}; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                            <i class="bi {{ $todayNetProfit >= 0 ? 'bi-trophy' : 'bi-exclamation-triangle' }}" style="font-size: 28px; color: {{ $todayNetProfit >= 0 ? 'var(--apple-green)' : 'var(--apple-red)' }};"></i>
                        </div>
                        <h2 class="mb-0 {{ $todayNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            TZS {{ number_format(abs($todayNetProfit)) }}
                        </h2>
                        <p class="text-secondary mb-0">Today's {{ $todayNetProfit >= 0 ? 'Profit' : 'Loss' }}</p>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3 text-center">
                            <div class="col-4 col-md-3">
                                <div class="p-2 rounded" style="background: rgba(0, 122, 255, 0.1);">
                                    <div class="small text-secondary">Sales</div>
                                    <h5 class="mb-0 text-primary">{{ number_format($todaySales / 1000) }}K</h5>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="p-2 rounded" style="background: var(--apple-gray-6);">
                                    <div class="small text-secondary">Txns</div>
                                    <h5 class="mb-0">{{ $todayTransactions }}</h5>
                                </div>
                            </div>
                            <div class="col-4 col-md-3">
                                <div class="p-2 rounded" style="background: rgba(52, 199, 89, 0.1);">
                                    <div class="small text-secondary">Gross</div>
                                    <h5 class="mb-0 {{ $todayGrossProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($todayGrossProfit / 1000) }}K</h5>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-2 rounded" style="background: rgba(255, 59, 48, 0.1);">
                                    <div class="small text-secondary">Expenses</div>
                                    <h5 class="mb-0 text-danger">{{ number_format($todayExpenses / 1000) }}K</h5>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-2 rounded" style="background: var(--apple-gray-6);">
                                    <div class="small text-secondary">vs Yesterday</div>
                                    <h5 class="mb-0 {{ $salesGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                        <i class="bi {{ $salesGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                                        {{ number_format(abs($salesGrowth), 0) }}%
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Stats -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div class="stat-value">{{ number_format($monthSales / 1000) }}K</div>
                    <div class="stat-label">This Month Sales</div>
                    @if($monthSalesGrowth != 0)
                        <small class="{{ $monthSalesGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi {{ $monthSalesGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                            {{ number_format(abs($monthSalesGrowth), 0) }}% vs last month
                        </small>
                    @endif
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon {{ $monthNetProfit >= 0 ? 'green' : 'red' }}">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="stat-value {{ $monthNetProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($monthNetProfit / 1000) }}K</div>
                    <div class="stat-label">Month Net Profit</div>
                    <small class="{{ $monthProfitMargin >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($monthProfitMargin, 1) }}% margin
                    </small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-value">{{ $totalProducts }}</div>
                    <div class="stat-label">Active Products</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon {{ $lowStockCount > 0 ? 'red' : 'green' }}">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">{{ $lowStockCount }}</div>
                    <div class="stat-label">Low Stock Items</div>
                    @if($lowStockCount > 0)
                        <a href="{{ route('inventory.index') }}" class="small text-danger">View items</a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Month Profit Breakdown -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ now()->format('F Y') }} Profit Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center align-items-center">
                    <div class="col-6 col-md-2">
                        <div class="text-secondary small">Revenue</div>
                        <h5 class="text-primary mb-0">TZS {{ number_format($monthSales) }}</h5>
                    </div>
                    <div class="col-auto d-none d-md-block">
                        <i class="bi bi-arrow-right text-secondary"></i>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="text-secondary small">Gross Profit</div>
                        <h5 class="{{ $monthGrossProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">TZS {{ number_format($monthGrossProfit) }}</h5>
                    </div>
                    <div class="col-auto d-none d-md-block">
                        <i class="bi bi-dash text-secondary"></i>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="text-secondary small">Expenses</div>
                        <h5 class="text-danger mb-0">TZS {{ number_format($monthExpenses) }}</h5>
                    </div>
                    <div class="col-auto d-none d-md-block">
                        <i class="bi bi-arrow-right text-secondary"></i>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded" style="background: {{ $monthNetProfit >= 0 ? 'rgba(52, 199, 89, 0.15)' : 'rgba(255, 59, 48, 0.15)' }};">
                            <div class="text-secondary small">Net Profit</div>
                            <h4 class="{{ $monthNetProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">TZS {{ number_format($monthNetProfit) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Quick Actions & Top Products -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card mb-3">
                    <div class="card-header">Quick Actions</div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('pos.index') }}" class="btn btn-primary w-100">
                                    <i class="bi bi-cart3 me-1"></i>Open POS
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('expenses.create') }}" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-wallet2 me-1"></i>Add Expense
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('products.create') }}" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-plus-circle me-1"></i>Add Product
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-clipboard-data me-1"></i>Stock
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products Today -->
                <div class="card">
                    <div class="card-header">Top Selling Today</div>
                    <div class="card-body p-0">
                        @forelse($topProducts as $product)
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <div style="font-weight: 500;">{{ $product->product_name }}</div>
                                    <small class="text-secondary">{{ $product->qty_sold }} sold</small>
                                </div>
                                <span class="text-primary fw-bold">{{ number_format($product->revenue) }}</span>
                            </div>
                        @empty
                            <div class="text-center text-secondary py-4">
                                <i class="bi bi-box-seam" style="font-size: 24px;"></i>
                                <p class="mb-0 small mt-2">No sales yet today</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Recent Transactions</span>
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Transaction</th>
                                    <th>Cashier</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <a href="{{ route('transactions.show', $transaction->id) }}" style="text-decoration: none; color: var(--apple-blue);">
                                                {{ $transaction->transaction_number }}
                                            </a>
                                        </td>
                                        <td>{{ $transaction->user->name ?? 'Unknown' }}</td>
                                        <td style="font-weight: 500;">{{ number_format($transaction->total, 0) }} TZS</td>
                                        <td>
                                            @if($transaction->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="text-secondary">{{ $transaction->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-4">
                                            No transactions yet today
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
</x-app-layout>
