<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory, BelongsToCompany;

    protected static function booted(): void
    {
        static::created(function (Transaction $transaction) {
            if ($transaction->company_id) {
                app(CacheService::class)->invalidateDashboard(
                    $transaction->company_id,
                    $transaction->branch_id
                );
            }
        });

        static::updated(function (Transaction $transaction) {
            if ($transaction->company_id) {
                app(CacheService::class)->invalidateDashboard(
                    $transaction->company_id,
                    $transaction->branch_id
                );
            }
        });
    }

    protected $fillable = [
        'company_id',
        'branch_id',
        'transaction_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_tin',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'payment_method',
        'amount_paid',
        'change_given',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_given' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public static function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));

        // Generate unique transaction number with retry
        $maxAttempts = 10;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $number = sprintf('%s-%s-%s%s', $prefix, $date, $random, $i > 0 ? $i : '');

            if (!static::where('transaction_number', $number)->exists()) {
                return $number;
            }

            // Generate new random for next attempt
            $random = strtoupper(substr(uniqid(), -4));
        }

        // Fallback with timestamp for guaranteed uniqueness
        return sprintf('%s-%s-%s', $prefix, $date, substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'refunded' => 'warning',
            'voided' => 'danger',
            default => 'secondary',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'card' => 'Card',
            'mobile' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            default => ucfirst($this->payment_method),
        };
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
