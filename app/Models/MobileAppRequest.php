<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileAppRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'company_id',
        'status',
        'request_reason',
        'expected_devices',
        'is_suspicious',
        'suspicious_reason',
        'scheduled_approval_at',
        'auto_approved',
        'approved_at',
        'rejected_at',
        'revoked_at',
        'rejection_reason',
        'revocation_reason',
        'reviewed_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revoked_at' => 'datetime',
        'scheduled_approval_at' => 'datetime',
        'expected_devices' => 'integer',
        'is_suspicious' => 'boolean',
        'auto_approved' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function canAccessMobile(): bool
    {
        return $this->isApproved();
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function approve(User $reviewer): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'reviewed_by' => $reviewer->id,
        ]);
    }

    public function autoApprove(): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'auto_approved' => true,
            'reviewed_by' => null,
        ]);
    }

    public function reject(User $reviewer, string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewer->id,
        ]);
    }

    public function revoke(User $reviewer, string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REVOKED,
            'revoked_at' => now(),
            'revocation_reason' => $reason,
            'reviewed_by' => $reviewer->id,
        ]);
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

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeRevoked($query)
    {
        return $query->where('status', self::STATUS_REVOKED);
    }

    public function scopeDueForAutoApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('is_suspicious', false)
            ->whereNotNull('scheduled_approval_at')
            ->where('scheduled_approval_at', '<=', now());
    }
}
