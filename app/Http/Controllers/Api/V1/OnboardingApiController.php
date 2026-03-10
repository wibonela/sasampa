<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class OnboardingApiController extends Controller
{
    /**
     * Register a new account (Step 1)
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'device_name' => 'required|string',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $company = Company::create([
                'name' => 'Pending Setup',
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => Company::STATUS_PENDING,
                'onboarding_step' => 2,
            ]);

            return User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'company_id' => $company->id,
                'role' => 'company_owner',
            ]);
        });

        event(new Registered($user));

        $token = $user->createToken($validated['device_name'])->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully. Please verify your email.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'has_pin' => false,
                'email_verified' => false,
                'company' => [
                    'id' => $user->company->id,
                    'name' => $user->company->name,
                    'status' => $user->company->status,
                    'onboarding_step' => $user->company->onboarding_step,
                    'onboarding_completed' => false,
                ],
            ],
        ], 201);
    }

    /**
     * Resend verification email (Step 2)
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent.']);
    }

    /**
     * Check email verification status (Step 2)
     */
    public function verifyStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'verified' => $user->hasVerifiedEmail(),
        ]);
    }

    /**
     * Save business details (Step 3)
     */
    public function saveBusiness(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:500',
        ]);

        $company = $request->user()->company;

        $company->update([
            'name' => $validated['company_name'],
            'phone' => $validated['company_phone'] ?? null,
            'address' => $validated['company_address'] ?? null,
            'onboarding_step' => 4,
        ]);

        return response()->json([
            'message' => 'Business details saved.',
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'phone' => $company->phone,
                'address' => $company->address,
            ],
        ]);
    }

    /**
     * Verify email from mobile app deep link (no auth required, hash provides security)
     */
    public function verifyEmailFromApp(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json(['error' => 'Invalid verification link.'], 403);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));

            $company = $user->company;
            if ($company && $company->needsOnboarding()) {
                $company->update(['onboarding_step' => 3]);
            }
        }

        return response()->json([
            'verified' => true,
            'message' => 'Email verified successfully.',
        ]);
    }

    /**
     * Complete onboarding - auto-approve company (Step 4)
     */
    public function complete(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        $company->update([
            'status' => Company::STATUS_APPROVED,
            'approved_at' => now(),
            'onboarding_completed' => true,
            'onboarding_step' => 4,
        ]);

        return response()->json([
            'message' => 'Onboarding complete! Your account is ready.',
        ]);
    }
}
