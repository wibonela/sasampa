<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'key',
        'value',
        'type',
        'company_id',
    ];

    public static function get(string $key, $default = null)
    {
        $companyId = auth()->user()?->company_id;

        if (!$companyId) {
            return $default;
        }

        $cacheKey = "setting.{$companyId}.{$key}";

        $setting = Cache::remember($cacheKey, 3600, function () use ($key, $companyId) {
            return static::withoutGlobalScope('company')
                ->where('key', $key)
                ->where('company_id', $companyId)
                ->first();
        });

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    public static function set(string $key, $value, string $type = 'string'): void
    {
        $companyId = auth()->user()->company_id;

        static::withoutGlobalScope('company')->updateOrCreate(
            ['key' => $key, 'company_id' => $companyId],
            ['value' => $value, 'type' => $type]
        );

        Cache::forget("setting.{$companyId}.{$key}");
    }

    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'array', 'json' => json_decode($value, true),
            default => $value,
        };
    }

    public static function getDefaults(): array
    {
        return [
            'store_name' => 'My Store',
            'store_logo' => '',
            'store_address' => '',
            'store_phone' => '',
            'store_email' => '',
            'currency_symbol' => 'TZS',
            'default_tax_rate' => 18,
            'low_stock_threshold' => 10,
            'receipt_header' => '',
            'receipt_footer' => 'Thank you for your business!',
        ];
    }
}
