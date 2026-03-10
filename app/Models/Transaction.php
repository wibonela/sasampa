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
        'customer_id',
        'transaction_number',
        'type',
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
        'valid_until',
        'fiscal_receipt_number',
        'fiscal_verification_code',
        'fiscal_qr_code',
        'fiscal_receipt_time',
        'fiscal_submitted',
        'fiscal_submission_error',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_given' => 'decimal:2',
        'valid_until' => 'datetime',
        'fiscal_receipt_time' => 'datetime',
        'fiscal_submitted' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -5));

        $maxAttempts = 10;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $number = sprintf('%s-%s-%s%s', $prefix, $date, $random, $i > 0 ? $i : '');

            if (!static::where('transaction_number', $number)->exists()) {
                return $number;
            }

            $random = strtoupper(substr(uniqid(), -5));
        }

        return sprintf('%s-%s-%s', $prefix, $date, substr(md5(uniqid(mt_rand(), true)), 0, 8));
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
            'pending' => 'info',
            'refunded' => 'warning',
            'voided' => 'danger',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getIsOrderAttribute(): bool
    {
        return $this->type === 'order';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'card' => 'Card',
            'mobile' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'credit' => 'Credit',
            default => ucfirst($this->payment_method),
        };
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOrders($query)
    {
        return $query->where('type', 'order');
    }

    public function scopeSales($query)
    {
        return $query->where(fn ($q) => $q->where('type', 'sale')->orWhereNull('type'));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeFiscalPending($query)
    {
        return $query->where('status', 'completed')
            ->where('fiscal_submitted', false)
            ->whereNull('fiscal_receipt_number');
    }
}
