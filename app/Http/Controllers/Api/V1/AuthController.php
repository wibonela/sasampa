<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login with email and password.
     *
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact support.'],
            ]);
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Please verify your email address before logging in.'],
            ]);
        }

        // Check if user has a pending invitation
        if ($user->hasPendingInvitation()) {
            throw ValidationException::withMessages([
                'email' => ['Please accept your invitation to complete account setup.'],
            ]);
        }

        // Create token with abilities based on role
        $abilities = $this->getAbilitiesForUser($user);
        $token = $user->createToken($request->device_name, $abilities);

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'mobile_access' => $this->getMobileAccessInfo($user),
        ]);
    }

    /**
     * Login with email and PIN (for quick cashier access).
     *
     * POST /api/v1/auth/login/pin
     */
    public function loginWithPin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'pin' => 'required|string|size:4',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasPin() || !$user->verifyPin($request->pin)) {
            throw ValidationException::withMessages([
                'pin' => ['The provided PIN is incorrect.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact support.'],
            ]);
        }

        // Create token
        $abilities = $this->getAbilitiesForUser($user);
        $token = $user->createToken($request->device_name, $abilities);

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'mobile_access' => $this->getMobileAccessInfo($user),
        ]);
    }

    /**
     * Logout (revoke current token).
     *
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens).
     *
     * POST /api/v1/auth/logout-all
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out from all devices.',
        ]);
    }

    /**
     * Get current authenticated user.
     *
     * GET /api/v1/auth/user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $this->formatUser($user),
            'mobile_access' => $this->getMobileAccessInfo($user),
        ]);
    }

    /**
     * Set or update user PIN.
     *
     * POST /api/v1/auth/pin
     */
    public function setPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|string|size:4|regex:/^[0-9]+$/',
            'current_password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password is incorrect.'],
            ]);
        }

        $user->setPin($request->pin);

        return response()->json([
            'message' => 'PIN has been set successfully.',
            'has_pin' => true,
        ]);
    }

    /**
     * Change PIN using current PIN.
     *
     * POST /api/v1/auth/pin/change
     */
    public function changePin(Request $request): JsonResponse
    {
        $request->validate([
            'current_pin' => 'required|string|size:4|regex:/^[0-9]+$/',
            'new_pin' => 'required|string|min:4|max:6|regex:/^[0-9]+$/',
        ]);

        $user = $request->user();

        if (!$user->hasPin() || !$user->verifyPin($request->current_pin)) {
            throw ValidationException::withMessages([
                'current_pin' => ['The provided PIN is incorrect.'],
            ]);
        }

        $user->setPin($request->new_pin);

        return response()->json([
            'message' => 'PIN has been changed successfully.',
        ]);
    }

    /**
     * Remove user PIN.
     *
     * DELETE /api/v1/auth/pin
     */
    public function removePin(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password is incorrect.'],
            ]);
        }

        $user->clearPin();

        return response()->json([
            'message' => 'PIN has been removed.',
            'has_pin' => false,
        ]);
    }

    /**
     * Change password.
     *
     * POST /api/v1/auth/password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all other tokens
        $currentTokenId = $request->user()->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return response()->json([
            'message' => 'Password changed successfully. Other sessions have been logged out.',
        ]);
    }

    /**
     * Get token abilities based on user role.
     */
    protected function getAbilitiesForUser(User $user): array
    {
        if ($user->isPlatformAdmin()) {
            return ['*'];
        }

        if ($user->isCompanyOwner()) {
            return ['*'];
        }

        // Cashier abilities based on permissions
        $abilities = ['pos:read', 'pos:checkout'];

        if ($user->hasPermission('void_transactions')) {
            $abilities[] = 'pos:void';
        }
        if ($user->hasPermission('apply_discounts')) {
            $abilities[] = 'pos:discount';
        }
        if ($user->hasPermission('view_reports')) {
            $abilities[] = 'reports:read';
        }
        if ($user->hasPermission('manage_inventory')) {
            $abilities[] = 'inventory:write';
        }
        if ($user->hasPermission('manage_products')) {
            $abilities[] = 'products:write';
        }

        return $abilities;
    }

    /**
     * Format user data for API response.
     */
    protected function formatUser(User $user): array
    {
        $user->load('company');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'has_pin' => $user->hasPin(),
            'company' => $user->company ? [
                'id' => $user->company->id,
                'name' => $user->company->name,
                'logo' => $user->company->logo ? asset('storage/' . $user->company->logo) : null,
                'status' => $user->company->status,
                'branches_enabled' => $user->company->branches_enabled,
            ] : null,
            'current_branch' => $user->currentBranch() ? [
                'id' => $user->currentBranch()->id,
                'name' => $user->currentBranch()->name,
                'code' => $user->currentBranch()->code,
            ] : null,
            'permissions' => $user->isCompanyOwner() ? ['*'] : $user->permissions->pluck('slug')->toArray(),
        ];
    }

    /**
     * Get mobile access information for user's company.
     */
    protected function getMobileAccessInfo(User $user): array
    {
        if ($user->isPlatformAdmin()) {
            return [
                'status' => 'approved',
                'can_use_mobile' => true,
            ];
        }

        if (!$user->company) {
            return [
                'status' => null,
                'can_use_mobile' => false,
                'message' => 'No company associated with this account.',
            ];
        }

        $mobileRequest = $user->company->mobileAppRequest;

        if (!$mobileRequest) {
            return [
                'status' => null,
                'can_use_mobile' => false,
                'message' => 'Mobile access has not been requested.',
            ];
        }

        return [
            'status' => $mobileRequest->status,
            'can_use_mobile' => $mobileRequest->isApproved(),
            'requested_at' => $mobileRequest->created_at->toIso8601String(),
            'approved_at' => $mobileRequest->approved_at?->toIso8601String(),
            'rejection_reason' => $mobileRequest->rejection_reason,
            'revocation_reason' => $mobileRequest->revocation_reason,
        ];
    }
}
