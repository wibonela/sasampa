<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmartVerifyController extends Controller
{
    /**
     * Verify email without requiring an active session.
     * The signed URL provides authentication via HMAC.
     */
    public function __invoke(Request $request, int $id, string $hash)
    {
        $user = User::find($id);

        if (!$user) {
            if ($this->isMobileDevice($request)) {
                return view('auth.verify-success', [
                    'user' => null,
                    'alreadyVerified' => false,
                    'needsOnboarding' => false,
                    'error' => 'This verification link is no longer valid. Please register again or resend the verification email from the app.',
                ]);
            }
            return redirect()->route('login')->with('error', 'This verification link is no longer valid. Please register again.');
        }

        // Validate the hash matches the user's email
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Invalid verification link.');
        }

        // Mark as verified if not already
        $alreadyVerified = $user->hasVerifiedEmail();
        if (! $alreadyVerified) {
            $user->markEmailAsVerified();
            event(new Verified($user));

            // Update onboarding step
            $company = $user->company;
            if ($company && $company->needsOnboarding()) {
                $company->update(['onboarding_step' => 3]);
            }
        }

        // Log the user in (fresh session)
        Auth::login($user);
        $request->session()->regenerate();

        // Detect if request is from a mobile device
        $isMobile = $this->isMobileDevice($request);

        if ($isMobile) {
            return view('auth.verify-success', [
                'user' => $user,
                'alreadyVerified' => $alreadyVerified,
                'needsOnboarding' => $user->company?->needsOnboarding(),
            ]);
        }

        // Desktop: redirect directly to next step
        if ($user->company?->needsOnboarding()) {
            return redirect()->route('onboarding.step3')->with('verified', true);
        }

        return redirect()->route('dashboard')->with('verified', true);
    }

    private function isMobileDevice(Request $request): bool
    {
        $ua = strtolower($request->userAgent() ?? '');
        return str_contains($ua, 'mobile')
            || str_contains($ua, 'android')
            || str_contains($ua, 'iphone')
            || str_contains($ua, 'ipad');
    }
}
