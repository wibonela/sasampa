<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PinSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PinLoginController extends Controller
{
    /**
     * Show PIN login form (for POS terminal mode)
     */
    public function showForm(): View
    {
        $deviceId = $this->getDeviceIdentifier();
        $activeSession = PinSession::getActiveSessionForDevice($deviceId);

        return view('auth.pin-login', [
            'activeSession' => $activeSession,
            'currentUser' => $activeSession?->user,
        ]);
    }

    /**
     * Full PIN login (email + PIN as alternative to password)
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'pin' => 'required|digits:4',
        ]);

        // Rate limiting
        $key = 'pin-login:' . Str::lower($validated['email']);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $user = User::where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if (!$user || !$user->verifyPin($validated['pin'])) {
            RateLimiter::hit($key, 60);
            return back()->withErrors([
                'pin' => 'The provided credentials are incorrect.',
            ]);
        }

        // Check if company is approved
        if (!$user->hasApprovedCompany()) {
            return back()->withErrors([
                'email' => 'Your company account is pending approval.',
            ]);
        }

        RateLimiter::clear($key);
        Auth::login($user, $request->boolean('remember'));

        // Start PIN session for device
        $deviceId = $this->getDeviceIdentifier();
        $branchId = $user->currentBranch()?->id;
        PinSession::startSession($user, $deviceId, $branchId);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Quick switch between users on same device (POS terminal mode)
     * This allows cashiers to switch shifts without full logout
     */
    public function quickSwitch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|digits:4',
            'company_id' => 'required|exists:companies,id',
        ]);

        $deviceId = $this->getDeviceIdentifier();

        // Rate limiting per device
        $key = 'pin-switch:' . $deviceId;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        // Find user by PIN in the company
        $user = User::where('company_id', $validated['company_id'])
            ->where('is_active', true)
            ->get()
            ->first(function ($u) use ($validated) {
                return $u->verifyPin($validated['pin']);
            });

        if (!$user) {
            RateLimiter::hit($key, 60);
            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN.',
            ], 401);
        }

        RateLimiter::clear($key);

        // Start new PIN session
        $branchId = $user->currentBranch()?->id;
        $session = PinSession::startSession($user, $deviceId, $branchId);

        // If there's an authenticated user, we update the session
        // For POS terminal mode, we might want to use session-based switching
        // without full auth change
        session(['pos_active_user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
            'session_id' => $session->id,
        ]);
    }

    /**
     * End current PIN session
     */
    public function endSession(Request $request): RedirectResponse|JsonResponse
    {
        $deviceId = $this->getDeviceIdentifier();
        $session = PinSession::getActiveSessionForDevice($deviceId);

        if ($session) {
            $session->end();
        }

        session()->forget('pos_active_user_id');

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('pos.pin-login')
            ->with('success', 'Session ended successfully.');
    }

    /**
     * Get or generate device identifier
     */
    protected function getDeviceIdentifier(): string
    {
        $deviceId = session('device_identifier');

        if (!$deviceId) {
            $deviceId = Str::uuid()->toString();
            session(['device_identifier' => $deviceId]);
        }

        return $deviceId;
    }

    /**
     * Get current active user for POS mode
     */
    public function getCurrentUser(): JsonResponse
    {
        $deviceId = $this->getDeviceIdentifier();
        $session = PinSession::getActiveSessionForDevice($deviceId);

        if (!$session) {
            return response()->json(['user' => null]);
        }

        return response()->json([
            'user' => [
                'id' => $session->user->id,
                'name' => $session->user->name,
                'role' => $session->user->role,
            ],
            'session_started' => $session->started_at->toISOString(),
        ]);
    }
}
