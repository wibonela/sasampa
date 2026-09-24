<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'tin',
        'vrn',
        'efd_serial_number',
        'efd_uin',
        'efd_enabled',
        'efd_environment',
        'efd_registered_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'onboarding_completed' => 'boolean',
        'branches_enabled' => 'boolean',
        'is_suspended' => 'boolean',
        'efd_enabled' => 'boolean',
        'efd_registered_at' => 'datetime',
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

    public function mobileAppRequest(): HasOne
    {
        return $this->hasOne(MobileAppRequest::class)->latest();
    }

    public function mobileDevices(): HasMany
    {
        return $this->hasMany(MobileDevice::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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

    /**
     * The user limit that actually applies: the company's own limit, further capped
     * by its plan while billing is enforced.
     */
    public function effectiveUserLimit(): int
    {
        $limit = $this->getUserLimit();

        if ($this->billingEnforced()) {
            $planLimit = $this->effectivePlan()?->limitFor('users');
            if ($planLimit !== null) {
                $limit = min($limit, $planLimit);
            }
        }

        return $limit;
    }

    public function canAddUser(): bool
    {
        return $this->getUserCount() < $this->effectiveUserLimit();
    }

    /**
     * Plans that allow more users than the company can have today.
     */
    public function plansWithMoreUsers()
    {
        $current = $this->effectiveUserLimit();

        return Plan::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('max_users')->orWhere('max_users', '>', $current))
            ->orderBy('sort_order')
            ->get();
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
    | Billing Helpers
    |--------------------------------------------------------------------------
    */

    // Per-instance memo: the layout asks several times per page render.
    private ?Plan $effectivePlanCache = null;
    private bool $effectivePlanResolved = false;

    public function billingEnforced(): bool
    {
        return (bool) config('billing.enforced');
    }

    /**
     * The plan whose features and limits apply right now. When the paid or trial
     * period has lapsed (or there is no subscription) the company drops to the
     * fallback plan instead of being locked out.
     */
    /**
     * Banner for the company owner when renewal is near or the period has lapsed.
     * Null when billing is off, the viewer isn't the owner, or nothing needs saying.
     */
    public function billingNotice(User $viewer, int $warnDays = 7): ?array
    {
        if (!$this->billingEnforced() || !$viewer->isCompanyOwner() || !$this->subscription?->current_period_end) {
            return null;
        }

        $end = $this->subscription->current_period_end;

        if ($end->isPast()) {
            return ['type' => 'danger', 'message' => 'Your plan has ended. Some features are hidden until you renew.'];
        }

        if ($end->lte(now()->addDays($warnDays))) {
            $days = max(1, (int) ceil(now()->diffInDays($end, false)));
            return ['type' => 'warning', 'message' => "Your plan ends in {$days} day" . ($days > 1 ? 's' : '') . '.'];
        }

        return null;
    }

    public function effectivePlan(): ?Plan
    {
        if ($this->effectivePlanResolved) {
            return $this->effectivePlanCache;
        }

        $subscription = $this->subscription;

        $plan = $subscription && $subscription->hasAccess()
            ? $subscription->plan
            : Plan::where('key', config('billing.fallback_plan'))->first();

        $this->effectivePlanResolved = true;

        return $this->effectivePlanCache = $plan;
    }

    public function hasFeature(string $feature): bool
    {
        if (!$this->billingEnforced()) {
            return true;
        }

        return $this->effectivePlan()?->hasFeature($feature) ?? false;
    }

    /**
     * Whether one more of $resource (users, branches, products) may be created.
     */
    public function withinLimit(string $resource): bool
    {
        if (!$this->billingEnforced()) {
            return true;
        }

        $limit = $this->effectivePlan()?->limitFor($resource);

        if ($limit === null) {
            return true;
        }

        return $this->resourceCount($resource) < $limit;
    }

    public function resourceCount(string $resource): int
    {
        return match ($resource) {
            'users' => $this->getUserCount(),
            'branches' => $this->branches()->count(),
            'products' => $this->products()->count(),
        };
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
    | EFD Helpers
    |--------------------------------------------------------------------------
    */

    public function isEfdEnabled(): bool
    {
        return $this->efd_enabled && $this->efd_serial_number && $this->efd_uin;
    }

    public function isEfdProduction(): bool
    {
        return $this->efd_environment === 'production';
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

    /*
    |--------------------------------------------------------------------------
    | Mobile Access Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if company has approved mobile access
     */
    public function hasMobileAccess(): bool
    {
        $request = $this->mobileAppRequest;
        return $request && $request->isApproved();
    }

    /**
     * Check if company has a pending mobile access request
     */
    public function hasPendingMobileRequest(): bool
    {
        $request = $this->mobileAppRequest;
        return $request && $request->isPending();
    }

    /**
     * Get mobile access status
     */
    public function getMobileAccessStatus(): ?string
    {
        $request = $this->mobileAppRequest;
        return $request?->status;
    }

    /**
     * Get active mobile devices count
     */
    public function getActiveMobileDevicesCount(): int
    {
        return $this->mobileDevices()->active()->count();
    }
}
