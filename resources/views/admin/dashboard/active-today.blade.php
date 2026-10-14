<x-app-layout>
    <div class="fade-in">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-secondary" style="font-size: 13px;"><i class="bi bi-chevron-left"></i> Dashboard</a>
                <h1 class="page-title mt-1">Active Today</h1>
                <p class="page-subtitle">Companies that recorded transactions today, and what they did</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-4"><div class="stat-card"><div class="stat-value">{{ $totals['companies'] }}</div><div class="stat-label">Companies</div></div></div>
            <div class="col-4"><div class="stat-card"><div class="stat-value">{{ number_format($totals['sales']) }}</div><div class="stat-label">Sales</div></div></div>
            <div class="col-4"><div class="stat-card"><div class="stat-value">{{ number_format($totals['revenue']) }}</div><div class="stat-label">Revenue (TZS)</div></div></div>
        </div>

        @forelse($rows as $r)
            @php
                $c = $r['company'];
                $phone = $c->owner?->phone ?: $c->phone;
                $wa = \App\Models\Company::whatsappNumber($phone);
            @endphp
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <a href="{{ route('admin.companies.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->name }}</a>
                            <div class="text-secondary" style="font-size: 12px;">
                                First activity {{ $r['first_at']->format('H:i') }} · last {{ $r['last_at']->diffForHumans() }}
                                @if($r['staff']) · {{ implode(', ', $r['staff']) }} @endif
                            </div>
                        </div>
                        @if($wa)
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
                        @endif
                    </div>

                    <div class="row g-2 mt-2" style="font-size: 14px;">
                        <div class="col-6 col-md-3"><span class="text-secondary">Sales</span><div class="fw-semibold">{{ $r['sales_count'] }}</div></div>
                        <div class="col-6 col-md-3"><span class="text-secondary">Revenue</span><div class="fw-semibold">TZS {{ number_format($r['revenue']) }}</div></div>
                        <div class="col-6 col-md-3"><span class="text-secondary">Expenses</span><div class="fw-semibold">{{ $r['expenses_count'] }}@if($r['expenses_count']) · TZS {{ number_format($r['expenses_total']) }}@endif</div></div>
                        <div class="col-6 col-md-3"><span class="text-secondary">Other</span>
                            <div class="fw-semibold">
                                @forelse($r['other'] as $label => $n){{ $n }} {{ $label }}@if(! $loop->last), @endif @empty — @endforelse
                            </div>
                        </div>
                    </div>

                    @if($r['top_products'])
                        <div class="mt-2" style="font-size: 13px;">
                            <span class="text-secondary">Selling:</span>
                            @foreach($r['top_products'] as $name => $qty)
                                <span class="badge text-bg-light border">{{ $name }} × {{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center text-secondary">No company has recorded activity today yet.</div></div>
        @endforelse
    </div>
</x-app-layout>
