<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $user = User::findByInvitationToken($token);

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired invitation link.');
        }

        if ($user->isInvitationExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired. Please contact your administrator for a new invitation.');
        }

        return view('auth.invitation', [
            'user' => $user,
            'token' => $token,
            'requiresPin' => in_array($user->invitation_method, ['pin', 'both']),
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $user = User::findByInvitationToken($token);

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired invitation link.');
        }

        if ($user->isInvitationExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired. Please contact your administrator for a new invitation.');
        }

        $rules = [
            'password' => ['required', 'confirmed', Password::defaults()],
        ];

        // If invitation method includes PIN and user doesn't have one, require it
        if (in_array($user->invitation_method, ['pin', 'both']) && !$user->hasPin()) {
            $rules['pin'] = ['required', 'digits:4', 'confirmed'];
        }

        $validated = $request->validate($rules);

        // Update password
        $user->password = Hash::make($validated['password']);

        // Set PIN if provided
        if (!empty($validated['pin'])) {
            $user->setPin($validated['pin']);
        }

        // Mark email as verified and accept invitation
        $user->email_verified_at = now();
        $user->save();
        $user->acceptInvitation();

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your account has been activated.');
    }
}
