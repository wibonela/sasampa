<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    const STATUS_TRIALING = 'trialing';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAST_DUE = 'past_due';
    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'current_period_end',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_end' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Whether access continues: the period is running or is inside the past-due grace window.
     */
    public function hasAccess(): bool
    {
        return $this->current_period_end !== null
            && $this->current_period_end->copy()->addDays((int) config('billing.past_due_days'))->isFuture();
    }

    /**
     * Whether the paid/trial period still covers today.
     */
    public function isCurrent(): bool
    {
        return $this->current_period_end !== null && $this->current_period_end->isFuture();
    }
}
