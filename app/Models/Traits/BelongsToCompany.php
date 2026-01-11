<?php

namespace App\Models\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        // Auto-scope queries to current company
        static::addGlobalScope('company', function (Builder $builder) {
            if ($companyId = static::getCurrentCompanyId()) {
                $builder->where($builder->getModel()->getTable() . '.company_id', $companyId);
            }
        });

        // Auto-assign company_id on creation
        static::creating(function ($model) {
            if (!$model->company_id && $companyId = static::getCurrentCompanyId()) {
                $model->company_id = $companyId;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected static function getCurrentCompanyId(): ?int
    {
        $user = auth()->user();

        // Not logged in
        if (!$user) {
            return null;
        }

        // Platform admins don't have company scope
        if ($user->isPlatformAdmin()) {
            return null;
        }

        return $user->company_id;
    }
}
