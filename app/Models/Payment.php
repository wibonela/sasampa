<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'company_id',
        'subscription_id',
        'plan_id',
        'amount_tzs',
        'months',
        'msisdn',
        'gateway',
        'gateway_ref',
        'status',
        'raw_payload',
        'note',
        'recorded_by',
        'paid_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
