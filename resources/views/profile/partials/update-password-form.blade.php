<p class="text-secondary mb-4" style="font-size: 13px;">
    Ensure your account is using a long, random password to stay secure.
</p>

<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="mb-3">
        <label for="update_password_current_password" class="form-label">Current Password</label>
        <input type="password" class="form-control" id="update_password_current_password" name="current_password" autocomplete="current-password">
        <x-input-error :messages="$errors->updatePassword->get('current_password')" />
    </div>

    <div class="mb-3">
        <label for="update_password_password" class="form-label">New Password</label>
        <input type="password" class="form-control" id="update_password_password" name="password" autocomplete="new-password">
        <x-input-error :messages="$errors->updatePassword->get('password')" />
    </div>

    <div class="mb-3">
        <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password">
        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
    </div>

    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">Update Password</button>

        @if (session('status') === 'password-updated')
            <span class="text-success" style="font-size: 13px;">
                <i class="bi bi-check-circle me-1"></i>Saved
            </span>
        @endif
    </div>
</form>
