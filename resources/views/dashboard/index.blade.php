<x-app-layout>
    <div class="fade-in">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}</p>
        </div>

        <!-- Stats Cards -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div class="stat-value">{{ number_format($todaySales, 0) }}</div>
                    <div class="stat-label">Today's Sales</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="stat-value">{{ $todayTransactions }}</div>
                    <div class="stat-label">Transactions</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-value">{{ $totalProducts }}</div>
                    <div class="stat-label">Products</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon {{ $lowStockCount > 0 ? 'red' : 'green' }}">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">{{ $lowStockCount }}</div>
                    <div class="stat-label">Low Stock</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Quick Actions</div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-6 col-lg-12">
                                <a href="{{ route('pos.index') }}" class="btn btn-primary w-100">
                                    <i class="bi bi-cart3 me-1"></i>Open POS
                                </a>
                            </div>
                            <div class="col-6 col-lg-12">
                                <a href="{{ route('products.create') }}" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-plus-circle me-1"></i>Add Product
                                </a>
                            </div>
                            <div class="col-6 col-lg-12">
                                <a href="{{ route('inventory.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-clipboard-data me-1"></i>Manage Stock
                                </a>
                            </div>
                            <div class="col-6 col-lg-12">
                                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-bar-chart me-1"></i>View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="col-lg-8">
                <div class="card">
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
