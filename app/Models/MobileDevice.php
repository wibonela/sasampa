<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'device_identifier',
        'device_name',
        'device_model',
        'os_version',
        'app_version',
        'push_token',
        'is_active',
        'last_active_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function updateActivity(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    public function updatePushToken(string $token): void
    {
        $this->update(['push_token' => $token]);
    }

    public function updateAppVersion(string $version): void
    {
        $this->update(['app_version' => $version]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Find or create a device by its unique identifier
     */
    public static function findOrCreateByIdentifier(
        string $identifier,
        int $userId,
        int $companyId,
        array $attributes = []
    ): self {
        return self::updateOrCreate(
            ['device_identifier' => $identifier],
            array_merge([
                'user_id' => $userId,
                'company_id' => $companyId,
                'is_active' => true,
                'last_active_at' => now(),
            ], $attributes)
        );
    }
}
