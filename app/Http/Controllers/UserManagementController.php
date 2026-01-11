<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::where('company_id', auth()->user()->company_id)
            ->where('id', '!=', auth()->id())
            ->with(['branches'])
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $branches = auth()->user()->company->branches()->active()->get();
        $permissions = Permission::all()->groupBy('group');

        return view('users.create', compact('branches', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'role' => ['required', Rule::in([User::ROLE_CASHIER])],
            'invitation_method' => ['required', Rule::in(['email', 'pin', 'both'])],
            'pin' => 'nullable|digits:4',
            'branches' => 'required|array|min:1',
            'branches.*' => 'exists:branches,id',
            'default_branch' => 'required|exists:branches,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Verify branches belong to company
        $companyBranchIds = auth()->user()->company->branches()->pluck('id')->toArray();
        foreach ($validated['branches'] as $branchId) {
            if (!in_array($branchId, $companyBranchIds)) {
                return back()->withErrors(['branches' => 'Invalid branch selected.']);
            }
        }

        // PIN is required for 'pin' or 'both' methods
        if (in_array($validated['invitation_method'], ['pin', 'both']) && empty($validated['pin'])) {
            return back()->withErrors(['pin' => 'PIN is required for this invitation method.']);
        }

        DB::transaction(function () use ($validated) {
            // Create user with temporary password for email-only, or actual password for PIN
            $password = $validated['invitation_method'] === 'pin'
                ? Str::random(32) // Random password - they'll use PIN
                : Str::random(32); // Random password - they'll set via invitation

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => $validated['role'],
                'company_id' => auth()->user()->company_id,
                'is_active' => true,
                'invitation_method' => $validated['invitation_method'],
            ]);

            // Set PIN if provided
            if (!empty($validated['pin'])) {
                $user->setPin($validated['pin']);
            }

            // Assign branches
            foreach ($validated['branches'] as $branchId) {
                $isDefault = $branchId == $validated['default_branch'];
                $user->branches()->attach($branchId, ['is_default' => $isDefault]);
            }

            // Assign permissions
            if (!empty($validated['permissions'])) {
                $user->syncPermissions($validated['permissions']);
            }

            // Send invitation based on method
            if (in_array($validated['invitation_method'], ['email', 'both'])) {
                $token = $user->generateInvitationToken();
                Mail::to($user->email)->queue(new UserInvitation($user, route('invitation.show', $token)));
            } elseif ($validated['invitation_method'] === 'pin') {
                // Mark as verified for PIN-only users (they don't need email verification)
                $user->email_verified_at = now();
                $user->invitation_accepted_at = now();
                $user->save();
            }
        });

        $message = match ($validated['invitation_method']) {
            'email' => 'Staff member created. Invitation email has been sent.',
            'pin' => 'Staff member created with PIN. Share the PIN securely with them.',
            'both' => 'Staff member created. Invitation email sent. Also share the PIN with them.',
        };

        return redirect()->route('users.index')->with('success', $message);
    }

    public function edit(User $user): View
    {
        $this->authorizeUser($user);

        $branches = auth()->user()->company->branches()->active()->get();
        $permissions = Permission::all()->groupBy('group');
        $userPermissionIds = $user->permissions->pluck('id')->toArray();
        $userBranchIds = $user->branches->pluck('id')->toArray();
        $defaultBranchId = $user->defaultBranch()?->id;

        return view('users.edit', compact(
            'user',
            'branches',
            'permissions',
            'userPermissionIds',
            'userBranchIds',
            'defaultBranchId'
        ));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUser($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'is_active' => 'boolean',
            'branches' => 'required|array|min:1',
            'branches.*' => 'exists:branches,id',
            'default_branch' => 'required|exists:branches,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Sync branches
            $branchSync = [];
            foreach ($validated['branches'] as $branchId) {
                $branchSync[$branchId] = [
                    'is_default' => $branchId == $validated['default_branch'],
                ];
            }
            $user->branches()->sync($branchSync);

            // Sync permissions
            $user->syncPermissions($validated['permissions'] ?? []);
        });

        return redirect()->route('users.index')->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeUser($user);

        // Check if user has transactions
        if ($user->transactions()->exists()) {
            return back()->with('error', 'Cannot delete user with existing transactions. Please deactivate them instead.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Staff member deleted successfully.');
    }

    public function permissions(User $user): View
    {
        $this->authorizeUser($user);

        $permissions = Permission::all()->groupBy('group');
        $userPermissionIds = $user->permissions->pluck('id')->toArray();

        return view('users.permissions', compact('user', 'permissions', 'userPermissionIds'));
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUser($user);

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('users.index')->with('success', 'Permissions updated successfully.');
    }

    public function sendInvitation(User $user): RedirectResponse
    {
        $this->authorizeUser($user);

        if ($user->invitation_accepted_at) {
            return back()->with('error', 'This user has already accepted their invitation.');
        }

        $token = $user->generateInvitationToken();
        Mail::to($user->email)->queue(new UserInvitation($user, route('invitation.show', $token)));

        return back()->with('success', 'Invitation email has been sent.');
    }

    public function resendInvitation(User $user): RedirectResponse
    {
        return $this->sendInvitation($user);
    }

    public function resetPin(User $user): RedirectResponse
    {
        $this->authorizeUser($user);

        // Generate new PIN
        $newPin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $user->setPin($newPin);

        return back()->with('success', "PIN has been reset. New PIN: {$newPin}");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorizeUser($user);

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Staff member has been {$status}.");
    }

    protected function authorizeUser(User $user): void
    {
        if ($user->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            abort(403, 'You cannot modify your own account here.');
        }

        if ($user->isCompanyOwner()) {
            abort(403, 'You cannot modify another company owner.');
        }
    }
}
