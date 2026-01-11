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
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pin',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
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
}
