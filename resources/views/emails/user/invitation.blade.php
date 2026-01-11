<x-mail::message>
# You've Been Invited

Hello {{ $user->name }},

{{ $inviterName }} has invited you to join **{{ $companyName }}** on {{ config('app.name') }}.

<x-mail::panel>
**Your Account Details:**

- **Email:** {{ $user->email }}
- **Role:** {{ ucfirst(str_replace('_', ' ', $user->role)) }}
</x-mail::panel>

## Getting Started

Click the button below to set up your password and activate your account. This invitation link will expire in 7 days.

<x-mail::button :url="$invitationUrl" color="primary">
Accept Invitation
</x-mail::button>

@if($user->invitation_method === 'both')
**Note:** Your administrator has also provided a PIN for quick access. Ask them for your PIN if you haven't received it.
@endif

If you did not expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
