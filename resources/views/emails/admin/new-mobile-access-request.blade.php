<x-mail::message>
# New Mobile Access Request

A business has requested mobile app access on Sasampa.

<x-mail::panel>
**Business:** {{ $company->name }}

**Reason:** {{ $mobileAppRequest->request_reason }}

**Expected Devices:** {{ $mobileAppRequest->expected_devices }}

**Submitted:** {{ $mobileAppRequest->created_at->format('d M Y, H:i') }}
</x-mail::panel>

Please review this request and approve or reject it promptly.

<x-mail::button :url="$reviewUrl" color="primary">
Review Request
</x-mail::button>

Sasampa Admin
</x-mail::message>
