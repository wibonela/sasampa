<x-mail::message>
# {{ $daysLeft > 0 ? 'Your plan is ending soon' : 'Your plan has ended' }}

@if($daysLeft > 0)
The plan for {{ $company->name }} ends in {{ $daysLeft }} day{{ $daysLeft > 1 ? 's' : '' }}.
Renew to keep all features available.
@else
The plan for {{ $company->name }} has ended. You can still make sales, but some features are hidden until you renew.
@endif

<x-mail::button :url="$billingUrl" color="primary">
View Billing
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
