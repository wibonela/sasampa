<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PinSession extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'device_identifier',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeForDevice($query, string $deviceIdentifier)
    {
        return $query->where('device_identifier', $deviceIdentifier);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }

    public function end(): void
    {
        $this->update(['ended_at' => now()]);
    }

    public static function getActiveSessionForDevice(string $deviceIdentifier): ?self
    {
        return static::active()
            ->forDevice($deviceIdentifier)
            ->latest('started_at')
            ->first();
    }

    public static function startSession(User $user, string $deviceIdentifier, ?int $branchId = null): self
    {
        // End any existing active session for this device
        static::active()
            ->forDevice($deviceIdentifier)
            ->update(['ended_at' => now()]);

        return static::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'branch_id' => $branchId,
            'device_identifier' => $deviceIdentifier,
            'started_at' => now(),
        ]);
    }
}
