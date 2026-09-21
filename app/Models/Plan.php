<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'key',
        'name',
        'price_tzs',
        'max_users',
        'max_branches',
        'max_products',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }

    /**
     * Limit for a resource (users, branches, products); null means unlimited.
     */
    public function limitFor(string $resource): ?int
    {
        return $this->{'max_' . $resource};
    }
}
