<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const BRANCH_MODE_SHARED = 'shared';
    const BRANCH_MODE_INDEPENDENT = 'independent';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'logo',
        'status',
        'is_suspended',
        'user_limit',
        'approved_at',
        'onboarding_step',
        'onboarding_completed',
        'branches_enabled',
        'branch_sharing_mode',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'onboarding_completed' => 'boolean',
        'branches_enabled' => 'boolean',
        'is_suspended' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function owner(): HasOne
    {
        return $this->hasOne(User::class)->where('role', 'company_owner');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function mainBranch(): HasOne
    {
        return $this->hasOne(Branch::class)->where('is_main', true);
    }

    public function activeBranches(): HasMany
    {
        return $this->branches()->where('is_active', true);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function userLimitRequests(): HasMany
    {
        return $this->hasMany(UserLimitRequest::class);
    }

    /*
    |--------------------------------------------------------------------------
    | User Limit Helpers
    |--------------------------------------------------------------------------
    */

    public function getUserLimit(): int
    {
        return $this->user_limit ?? 3;
    }

    public function getUserCount(): int
    {
        return $this->users()->count();
    }

    public function canCreateMoreUsers(): bool
    {
        return $this->getUserCount() < $this->getUserLimit();
    }

    public function getRemainingUserSlots(): int
    {
        return max(0, $this->getUserLimit() - $this->getUserCount());
    }

    public function hasPendingLimitRequest(): bool
    {
        return $this->userLimitRequests()->pending()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /*
    |--------------------------------------------------------------------------
    | Branch Helpers
    |--------------------------------------------------------------------------
    */

    public function hasBranchesEnabled(): bool
    {
        return $this->branches_enabled ?? false;
    }

    public function hasSharedProducts(): bool
    {
        return $this->branch_sharing_mode === self::BRANCH_MODE_SHARED;
    }

    public function hasIndependentProducts(): bool
    {
        return $this->branch_sharing_mode === self::BRANCH_MODE_INDEPENDENT;
    }

    /**
     * Enable branches for this company
     */
    public function enableBranches(string $mode = self::BRANCH_MODE_SHARED): void
    {
        $this->update([
            'branches_enabled' => true,
            'branch_sharing_mode' => $mode,
        ]);

        // Create main branch if none exists
        if (!$this->branches()->exists()) {
            $this->branches()->create([
                'name' => 'Main Branch',
                'code' => 'HQ',
                'is_main' => true,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Disable branches for this company
     */
    public function disableBranches(): void
    {
        $this->update(['branches_enabled' => false]);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /*
    |--------------------------------------------------------------------------
    | Onboarding Helpers
    |--------------------------------------------------------------------------
    */

    public function needsOnboarding(): bool
    {
        return !$this->onboarding_completed;
    }
}
