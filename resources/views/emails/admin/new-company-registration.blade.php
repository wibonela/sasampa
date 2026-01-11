<x-mail::message>
# New Registration

A business has submitted a registration request on Sasampa.

<x-mail::panel>
**Business:** {{ $company->name }}

**Email:** {{ $company->email }}

**Phone:** {{ $company->phone ?? 'Not provided' }}

**Location:** {{ $company->address ?? 'Not provided' }}

**Submitted:** {{ $company->created_at->format('d M Y, H:i') }}
</x-mail::panel>

Review the application details and verify the business information before making a decision.

<x-mail::button :url="$reviewUrl" color="primary">
Review Application
</x-mail::button>

Sasampa Admin
</x-mail::message>
