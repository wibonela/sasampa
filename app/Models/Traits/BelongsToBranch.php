<?php

namespace App\Models\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch(): void
    {
        // Auto-scope queries to current branch when company uses independent mode
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = auth()->user();

            if (!$user || $user->isPlatformAdmin()) {
                return;
            }

            $company = $user->company;

            if (!$company || !$company->hasBranchesEnabled()) {
                return;
            }

            // Only apply branch scoping for independent mode
            // In shared mode, products/categories are shared across all branches
            if ($company->hasIndependentProducts()) {
                $branchId = static::getCurrentBranchId();
                if ($branchId) {
                    $builder->where($builder->getModel()->getTable() . '.branch_id', $branchId);
                }
            }
        });

        // Auto-assign branch_id on creation
        static::creating(function ($model) {
            $user = auth()->user();

            if (!$user || $user->isPlatformAdmin()) {
                return;
            }

            $company = $user->company;

            if (!$company || !$company->hasBranchesEnabled()) {
                return;
            }

            // Always assign branch_id when branches are enabled
            if (!$model->branch_id) {
                $model->branch_id = static::getCurrentBranchId();
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function getCurrentBranchId(): ?int
    {
        return session('current_branch_id') ?? auth()->user()?->defaultBranch()?->id;
    }

    /**
     * Scope to filter by specific branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to include all branches (bypass branch filtering)
     */
    public function scopeAllBranches($query)
    {
        return $query->withoutGlobalScope('branch');
    }
}
