<p class="text-secondary mb-4" style="font-size: 13px;">
    Update your account's profile information and email address.
</p>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
        <x-input-error :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2">
                <p class="text-secondary" style="font-size: 13px;">
                    Your email address is unverified.
                    <button form="send-verification" class="btn btn-link p-0" style="font-size: 13px;">
                        Click here to re-send the verification email.
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="text-success" style="font-size: 13px;">
                        A new verification link has been sent to your email address.
                    </p>
                @endif
            </div>
        @endif
    </div>

    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">Save Changes</button>

        @if (session('status') === 'profile-updated')
            <span class="text-success" style="font-size: 13px;">
                <i class="bi bi-check-circle me-1"></i>Saved
            </span>
        @endif
    </div>
</form>
