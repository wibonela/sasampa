<x-app-layout>
    {{-- ============================================= --}}
    {{-- MOBILE DASHBOARD (matches native Flutter app) --}}
    {{-- ============================================= --}}
    <div class="mobile-dashboard d-lg-none">
        {{-- Greeting --}}
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--apple-text); margin: 0;">
                    Hello, {{ strtoupper(explode(' ', auth()->user()->name)[0]) }}
                </h1>
                @if(auth()->user()->company)
                    <p style="font-size: 14px; color: var(--apple-text-secondary); margin: 4px 0 0;">
                        {{ strtoupper(auth()->user()->company->name) }}
                    </p>
                @endif
            </div>
            <div style="width: 48px; height: 48px; border-radius: 50%; border: 1px solid var(--apple-border); display: flex; align-items: center; justify-content: center; background: #fff;">
                <span style="font-size: 20px; font-weight: 600; color: var(--apple-text);">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
        </div>

        {{-- Today Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div style="background: #fff; border: 1px solid var(--apple-border); border-radius: 12px; padding: 16px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: #fff; border: 1px solid var(--apple-border); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                        <i class="bi bi-graph-up" style="font-size: 16px; color: var(--apple-green);"></i>
                    </div>
                    <div style="font-size: 22px; font-weight: 700; color: var(--apple-text);">TZS {{ number_format($todaySales) }}</div>
                    <div style="font-size: 13px; color: var(--apple-text-secondary);">Today's Sales</div>
                </div>
            </div>
            <div class="col-6">
                <div style="background: #fff; border: 1px solid var(--apple-border); border-radius: 12px; padding: 16px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: #fff; border: 1px solid var(--apple-border); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                        <i class="bi bi-receipt" style="font-size: 16px; color: var(--apple-blue);"></i>
                    </div>
                    <div style="font-size: 22px; font-weight: 700; color: var(--apple-text);">{{ $todayTransactions }}</div>
                    <div style="font-size: 13px; color: var(--apple-text-secondary);">Transactions</div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <h3 style="font-size: 20px; font-weight: 700; color: var(--apple-text); margin: 0 0 12px;">Quick Actions</h3>
        <div class="d-flex gap-3 mb-4">
            <a href="{{ route('pos.index') }}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; background: #1a1a1a; color: #fff; border-radius: 10px; padding: 16px; font-size: 16px; font-weight: 600; text-decoration: none;">
                <i class="bi bi-cart3" style="font-size: 20px;"></i>
                New Sale
            </a>
            <a href="{{ route('transactions.index') }}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; background: #fff; color: var(--apple-text); border: 1px solid var(--apple-border); border-radius: 10px; padding: 16px; font-size: 16px; font-weight: 600; text-decoration: none;">
                <i class="bi bi-clock-history" style="font-size: 20px;"></i>
                History
            </a>
        </div>

        {{-- Today's Profit (compact) --}}
        <div style="background: #fff; border: 1px solid var(--apple-border); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size: 13px; color: var(--apple-text-secondary);">Today's {{ $todayNetProfit >= 0 ? 'Profit' : 'Loss' }}</div>
                    <div style="font-size: 20px; font-weight: 700; color: {{ $todayNetProfit >= 0 ? 'var(--apple-green)' : 'var(--apple-red)' }};">
                        TZS {{ number_format(abs($todayNetProfit)) }}
                    </div>
                </div>
                <a href="{{ route('analytics.profit') }}" style="font-size: 13px; color: var(--apple-text-secondary); text-decoration: none;">
                    Details <i class="bi bi-chevron-right" style="font-size: 11px;"></i>
                </a>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 style="font-size: 20px; font-weight: 700; color: var(--apple-text); margin: 0;">Recent Transactions</h3>
            <a href="{{ route('transactions.index') }}" style="font-size: 15px; color: var(--apple-text-secondary); text-decoration: none; font-weight: 500;">See All</a>
        </div>

        @forelse($recentTransactions->take(5) as $transaction)
            <a href="{{ route('transactions.show', $transaction->id) }}" style="display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid var(--apple-border); border-radius: 10px; padding: 14px 16px; margin-bottom: 10px; text-decoration: none;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #fff; border: 1px solid var(--apple-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    @if($transaction->status === 'completed')
                        <i class="bi bi-check-circle-fill" style="font-size: 20px; color: var(--apple-green);"></i>
                    @elseif($transaction->status === 'pending')
                        <i class="bi bi-clock-fill" style="font-size: 20px; color: var(--apple-orange);"></i>
                    @else
                        <i class="bi bi-x-circle-fill" style="font-size: 20px; color: var(--apple-red);"></i>
                    @endif
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 15px; font-weight: 600; color: var(--apple-text);">{{ $transaction->transaction_number }}</div>
                    <div style="font-size: 12px; color: var(--apple-text-secondary);">{{ $transaction->created_at->diffForHumans() }}</div>
                </div>
                <div style="font-size: 15px; font-weight: 600; color: var(--apple-text); white-space: nowrap;">
                    TZS {{ number_format($transaction->total) }}
                </div>
            </a>
        @empty
            <div style="text-align: center; padding: 32px 16px; background: #fff; border: 1px solid var(--apple-border); border-radius: 10px;">
                <i class="bi bi-receipt" style="font-size: 32px; color: var(--apple-text-secondary);"></i>
                <p style="font-size: 14px; color: var(--apple-text-secondary); margin: 8px 0 0;">No transactions yet</p>
            </div>
        @endforelse

        @if($lowStockCount > 0)
            <a href="{{ route('inventory.index') }}" style="display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid var(--apple-border); border-radius: 10px; padding: 14px 16px; margin-top: 16px; text-decoration: none;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #fff; border: 1px solid var(--apple-border); display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 18px; color: var(--apple-red);"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 15px; font-weight: 600; color: var(--apple-red);">{{ $lowStockCount }} Low Stock Items</div>
                    <div style="font-size: 12px; color: var(--apple-text-secondary);">Tap to view inventory</div>
                </div>
                <i class="bi bi-chevron-right" style="color: var(--apple-text-secondary);"></i>
            </a>
        @endif
    </div>

    {{-- ============================================= --}}
    {{-- DESKTOP DASHBOARD (original web layout)       --}}
    {{-- ============================================= --}}
    <div class="desktop-dashboard d-none d-lg-block fade-in">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}</p>
            </div>
            <a href="{{ route('analytics.profit') }}" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up me-1"></i>Full Analytics
            </a>
        </div>

        <!-- Today's Profit Highlight -->
        <div class="card mb-4" style="border: 1px solid var(--apple-border);">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center py-3">
                        <div style="width: 64px; height: 64px; border-radius: 16px; background: #fff; border: 1px solid var(--apple-border); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px;">
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
                                <div class="p-2 rounded" style="background: #fff; border: 1px solid var(--apple-border);">
                                    <div class="small text-secondary">Sales</div>
                                    <h5 class="mb-0">{{ number_format($todaySales / 1000) }}K</h5>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="p-2 rounded" style="background: #fff; border: 1px solid var(--apple-border);">
                                    <div class="small text-secondary">Txns</div>
                                    <h5 class="mb-0">{{ $todayTransactions }}</h5>
                                </div>
                            </div>
                            <div class="col-4 col-md-3">
                                <div class="p-2 rounded" style="background: #fff; border: 1px solid var(--apple-border);">
                                    <div class="small text-secondary">Gross</div>
                                    <h5 class="mb-0 {{ $todayGrossProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($todayGrossProfit / 1000) }}K</h5>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-2 rounded" style="background: #fff; border: 1px solid var(--apple-border);">
                                    <div class="small text-secondary">Expenses</div>
                                    <h5 class="mb-0 text-danger">{{ number_format($todayExpenses / 1000) }}K</h5>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-2 rounded" style="background: #fff; border: 1px solid var(--apple-border);">
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
                        <h5 class="mb-0">TZS {{ number_format($monthSales) }}</h5>
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
                        <div class="p-3 rounded" style="background: #fff; border: 1px solid var(--apple-border);">
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
                                <a href="{{ route('pos.index') }}" class="btn btn-dark w-100">
                                    <i class="bi bi-cart3 me-1"></i>Open POS
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('expenses.create') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-wallet2 me-1"></i>Add Expense
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('products.create') }}" class="btn btn-outline-secondary w-100">
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
                                <span class="fw-bold">{{ number_format($product->revenue) }}</span>
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
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
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
                                            <a href="{{ route('transactions.show', $transaction->id) }}" style="text-decoration: none; color: var(--apple-text); font-weight: 500;">
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
