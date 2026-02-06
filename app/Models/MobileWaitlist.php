<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileWaitlist extends Model
{
    protected $table = 'mobile_waitlist';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'business_name',
        'business_type',
        'platform',
        'referral_source',
        'status',
        'notes',
        'contacted_at',
        'converted_at',
        'ip_address',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public const BUSINESS_TYPES = [
        'restaurant' => 'Restaurant',
        'retail' => 'Retail Shop',
        'pharmacy' => 'Pharmacy',
        'supermarket' => 'Supermarket',
        'salon' => 'Salon',
        'other' => 'Other',
    ];

    public const PLATFORMS = [
        'ios' => 'iOS',
        'android' => 'Android',
        'both' => 'Both',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'contacted' => 'Contacted',
        'converted' => 'Converted',
        'cancelled' => 'Cancelled',
    ];

    public function getBusinessTypeLabelAttribute(): string
    {
        return self::BUSINESS_TYPES[$this->business_type] ?? $this->business_type;
    }

    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeThisWeek($query)
    {
        return $query->where('created_at', '>=', now()->startOfWeek());
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeByBusinessType($query, string $type)
    {
        return $query->where('business_type', $type);
    }
}
