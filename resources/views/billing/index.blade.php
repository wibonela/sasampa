<x-app-layout>
    <div class="fade-in">
        <div class="mb-4">
            <h1 class="page-title mb-1">Billing</h1>
            <p class="page-subtitle">Your plan, renewal date and payment history</p>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                @if($subscription)
                    @php $lapsed = !$subscription->isCurrent(); @endphp
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="text-secondary small">Current plan</div>
                            <div class="fs-4 fw-bold">{{ $subscription->plan->name }}</div>
                        </div>
                        <div class="text-end">
                            <div class="text-secondary small">{{ $lapsed ? 'Ended' : 'Renews / ends' }}</div>
                            <div class="fw-medium {{ $lapsed ? 'text-danger' : '' }}">
                                {{ $subscription->current_period_end?->format('d M Y') ?? '-' }}
                            </div>
                            <span class="badge bg-{{ $lapsed ? 'danger' : ($subscription->status === 'trialing' ? 'info' : 'success') }}">
                                {{ $lapsed ? 'Expired' : ucfirst($subscription->status) }}
                            </span>
                        </div>
                    </div>
                @else
                    <p class="mb-0 text-secondary">No plan on this account yet.</p>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach($plans as $plan)
                <div class="col-md-4">
                    <div class="card h-100 {{ $subscription && $subscription->plan_id === $plan->id ? 'border-primary' : '' }}">
                        <div class="card-body">
                            <h5 class="mb-1">{{ $plan->name }}</h5>
                            <div class="fs-4 fw-bold mb-3">TZS {{ number_format($plan->price_tzs) }}<span class="fs-6 text-secondary"> / month</span></div>
                            <ul class="list-unstyled small mb-0">
                                <li>{{ $plan->max_users ?? 'Unlimited' }} users</li>
                                <li>{{ $plan->max_branches ?? 'Unlimited' }} {{ ($plan->max_branches ?? 2) === 1 ? 'branch' : 'branches' }}</li>
                                <li>{{ $plan->max_products ? number_format($plan->max_products) : 'Unlimited' }} products</li>
                                @foreach(['full_reports' => 'Profit reports & analytics', 'expenses' => 'Expenses', 'export' => 'PDF / CSV export', 'whatsapp_receipts' => 'WhatsApp receipts'] as $key => $label)
                                    <li class="{{ $plan->hasFeature($key) ? '' : 'text-secondary text-decoration-line-through' }}">{{ $label }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-credit-card me-2"></i>How to pay</div>
            <div class="card-body">
                @if($onlinePayment)
                    <form action="{{ route('billing.checkout') }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Plan</label>
                            <select name="plan_id" class="form-select" required>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected($subscription?->plan_id === $plan->id)>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Period</label>
                            <select name="months" class="form-select" required>
                                @foreach($discounts as $m => $pct)
                                    <option value="{{ $m }}">{{ $m }} month{{ $m > 1 ? 's' : '' }}{{ $pct ? " ({$pct}% off)" : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Mobile money number</label>
                            <input type="tel" name="msisdn" class="form-control" placeholder="0712345678" value="{{ old('msisdn') }}" required>
                            @error('msisdn')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100">Pay now</button>
                        </div>
                    </form>
                    <p class="text-secondary small mt-2 mb-0">You'll get a prompt on your phone to approve the payment.</p>
                @else
                    <p class="mb-0">{{ $instructions }}</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-receipt me-2"></i>Payment history</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Date</th><th>Plan</th><th>Months</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ ($payment->paid_at ?? $payment->created_at)->format('d M Y') }}</td>
                                    <td>{{ $payment->plan?->name ?? '-' }}</td>
                                    <td>{{ $payment->months }}</td>
                                    <td>TZS {{ number_format($payment->amount_tzs) }}</td>
                                    <td><span class="badge bg-{{ $payment->status === 'paid' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($payment->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary py-3">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
