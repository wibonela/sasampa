<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    // Role constants
    const ROLE_PLATFORM_ADMIN = 'platform_admin';
    const ROLE_COMPANY_OWNER = 'company_owner';
    const ROLE_CASHIER = 'cashier';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'pin',
        'company_id',
        'is_active',
        'invitation_token',
        'invitation_sent_at',
        'invitation_accepted_at',
        'invitation_method',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pin',
        'invitation_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'invitation_sent_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class)
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withTimestamps();
    }

    public function pinSessions(): HasMany
    {
        return $this->hasMany(PinSession::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Role Check Methods
    |--------------------------------------------------------------------------
    */

    public function isPlatformAdmin(): bool
    {
        return $this->role === self::ROLE_PLATFORM_ADMIN && $this->company_id === null;
    }

    public function isCompanyOwner(): bool
    {
        return $this->role === self::ROLE_COMPANY_OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->isPlatformAdmin() || $this->isCompanyOwner();
    }

    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    public function hasApprovedCompany(): bool
    {
        return $this->company && $this->company->isApproved();
    }

    /*
    |--------------------------------------------------------------------------
    | Branch Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user's default branch
     */
    public function defaultBranch(): ?Branch
    {
        return $this->branches()
            ->wherePivot('is_default', true)
            ->first();
    }

    /**
     * Get the user's current active branch (from session or default)
     */
    public function currentBranch(): ?Branch
    {
        $branchId = session('current_branch_id');

        if ($branchId) {
            // If company owner, can access any branch
            if ($this->isCompanyOwner()) {
                return Branch::find($branchId);
            }
            // Otherwise, must be assigned to the branch
            return $this->branches()->find($branchId);
        }

        return $this->defaultBranch() ?? $this->branches()->first();
    }

    /**
     * Check if user can access a specific branch
     */
    public function canAccessBranch(Branch $branch): bool
    {
        // Company owners can access all branches in their company
        if ($this->isCompanyOwner()) {
            return $branch->company_id === $this->company_id;
        }

        // Other users must be assigned to the branch
        return $this->branches()->where('branches.id', $branch->id)->exists();
    }

    /**
     * Get all branches the user can access
     */
    public function accessibleBranches()
    {
        if ($this->isCompanyOwner()) {
            return $this->company->branches()->active();
        }

        return $this->branches()->active();
    }

    /**
     * Set the user's current branch in session
     */
    public function setCurrentBranch(Branch $branch): bool
    {
        if (!$this->canAccessBranch($branch)) {
            return false;
        }

        session(['current_branch_id' => $branch->id]);
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Company owners have all permissions
        if ($this->isCompanyOwner()) {
            return true;
        }

        // Platform admins have all permissions
        if ($this->isPlatformAdmin()) {
            return true;
        }

        return $this->permissions()
            ->where('slug', $permissionSlug)
            ->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        if ($this->isCompanyOwner() || $this->isPlatformAdmin()) {
            return true;
        }

        return $this->permissions()
            ->whereIn('slug', $permissionSlugs)
            ->exists();
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissionSlugs): bool
    {
        if ($this->isCompanyOwner() || $this->isPlatformAdmin()) {
            return true;
        }

        return $this->permissions()
            ->whereIn('slug', $permissionSlugs)
            ->count() === count($permissionSlugs);
    }

    /**
     * Grant a permission to the user
     */
    public function grantPermission(Permission $permission): void
    {
        if (!$this->permissions()->where('permission_id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id, [
                'company_id' => $this->company_id,
            ]);
        }
    }

    /**
     * Revoke a permission from the user
     */
    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    /**
     * Sync user permissions
     */
    public function syncPermissions(array $permissionIds): void
    {
        $syncData = [];
        foreach ($permissionIds as $permissionId) {
            $syncData[$permissionId] = ['company_id' => $this->company_id];
        }
        $this->permissions()->sync($syncData);
    }

    /*
    |--------------------------------------------------------------------------
    | PIN Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Set the user's PIN (hashed)
     */
    public function setPin(string $pin): void
    {
        $this->update([
            'pin' => Hash::make($pin),
        ]);
    }

    /**
     * Verify the user's PIN
     */
    public function verifyPin(string $pin): bool
    {
        if (!$this->pin) {
            return false;
        }

        return Hash::check($pin, $this->pin);
    }

    /**
     * Check if user has a PIN set
     */
    public function hasPin(): bool
    {
        return !empty($this->pin);
    }

    /**
     * Clear the user's PIN
     */
    public function clearPin(): void
    {
        $this->update(['pin' => null]);
    }

    /*
    |--------------------------------------------------------------------------
    | Invitation Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Generate a new invitation token
     */
    public function generateInvitationToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'invitation_token' => $token,
            'invitation_sent_at' => now(),
        ]);
        return $token;
    }

    /**
     * Accept the invitation
     */
    public function acceptInvitation(): void
    {
        $this->update([
            'invitation_token' => null,
            'invitation_accepted_at' => now(),
        ]);
    }

    /**
     * Check if user has a pending invitation
     */
    public function hasPendingInvitation(): bool
    {
        return $this->invitation_token !== null
            && $this->invitation_accepted_at === null;
    }

    /**
     * Check if invitation is expired (older than 7 days)
     */
    public function isInvitationExpired(): bool
    {
        if (!$this->invitation_sent_at) {
            return true;
        }

        return $this->invitation_sent_at->addDays(7)->isPast();
    }

    /**
     * Find user by invitation token
     */
    public static function findByInvitationToken(string $token): ?self
    {
        return static::where('invitation_token', $token)->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopePendingInvitation($query)
    {
        return $query->whereNotNull('invitation_token')
            ->whereNull('invitation_accepted_at');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeCashiers($query)
    {
        return $query->where('role', self::ROLE_CASHIER);
    }
}
