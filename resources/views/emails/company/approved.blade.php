<x-mail::message>
# Your Business is Ready

{{ $company->name }} has been approved on Sasampa.

Your account is now active. Log in to access your dashboard and begin managing your business operations.

<x-mail::panel>
**Business Name:** {{ $company->name }}

**Account Status:** Active
</x-mail::panel>

## Getting Started

Set up your store preferences, add your product catalog, and configure payment options from your dashboard. Our system is designed to help you track sales, manage inventory, and generate reports.

<x-mail::button :url="$loginUrl" color="primary">
Access Dashboard
</x-mail::button>

For assistance, contact our support team at any time.

{{ config('app.name') }}
</x-mail::message>
