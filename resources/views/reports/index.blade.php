<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="page-title">Reports</h1>
            <p class="page-subtitle">Analytics and insights</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-5">
                        <div style="width: 64px; height: 64px; border-radius: 16px; background: rgba(0, 122, 255, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <i class="bi bi-graph-up" style="font-size: 28px; color: var(--apple-blue);"></i>
                        </div>
                        <h5 style="font-weight: 600; margin-bottom: 8px;">Sales Report</h5>
                        <p class="text-secondary mb-4" style="font-size: 13px;">View daily, weekly, and monthly sales with payment breakdown.</p>
                        <a href="{{ route('reports.sales') }}" class="btn btn-primary">
                            View Report
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-5">
                        <div style="width: 64px; height: 64px; border-radius: 16px; background: rgba(52, 199, 89, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <i class="bi bi-box-seam" style="font-size: 28px; color: var(--apple-green);"></i>
                        </div>
                        <h5 style="font-weight: 600; margin-bottom: 8px;">Product Performance</h5>
                        <p class="text-secondary mb-4" style="font-size: 13px;">See top selling products and revenue by product.</p>
                        <a href="{{ route('reports.products') }}" class="btn btn-success">
                            View Report
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-5">
                        <div style="width: 64px; height: 64px; border-radius: 16px; background: rgba(255, 149, 0, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <i class="bi bi-clipboard-data" style="font-size: 28px; color: var(--apple-orange);"></i>
                        </div>
                        <h5 style="font-weight: 600; margin-bottom: 8px;">Inventory Report</h5>
                        <p class="text-secondary mb-4" style="font-size: 13px;">Stock levels, valuation, and low stock items.</p>
                        <a href="{{ route('reports.inventory') }}" class="btn btn-warning text-white">
                            View Report
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-5">
                        <div style="width: 64px; height: 64px; border-radius: 16px; background: rgba(175, 82, 222, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <i class="bi bi-calculator" style="font-size: 28px; color: #AF52DE;"></i>
                        </div>
                        <h5 style="font-weight: 600; margin-bottom: 8px;">Profit Report</h5>
                        <p class="text-secondary mb-4" style="font-size: 13px;">Sales minus expenses equals profit/loss analysis.</p>
                        <a href="{{ route('reports.profit') }}" class="btn text-white" style="background: #AF52DE;">
                            View Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
