<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLimitRequest extends Model
{
    protected $fillable = [
        'company_id',
        'requested_by',
        'current_limit',
        'requested_limit',
        'reason',
        'status',
        'handled_by',
        'admin_notes',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
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

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function approve(User $admin, ?string $notes = null, ?int $approvedLimit = null): void
    {
        $newLimit = $approvedLimit ?? $this->requested_limit;

        $this->update([
            'status' => 'approved',
            'handled_by' => $admin->id,
            'admin_notes' => $notes,
            'handled_at' => now(),
        ]);

        // Update the company's user limit
        $this->company->update(['user_limit' => $newLimit]);
    }

    public function reject(User $admin, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'handled_by' => $admin->id,
            'admin_notes' => $notes,
            'handled_at' => now(),
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
