<x-mail::message>
# Registration Status Update

We have reviewed your business registration for **{{ $company->name }}** on Sasampa.

After careful consideration, we are unable to approve your application at this time.

@if($reason)
<x-mail::panel>
**Details:** {{ $reason }}
</x-mail::panel>
@endif

## Next Steps

You may submit a new registration with updated documentation or contact our support team for clarification on the requirements.

We review applications to ensure platform quality and compliance with our terms of service.

<x-mail::button :url="$contactUrl" color="primary">
Contact Support
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
