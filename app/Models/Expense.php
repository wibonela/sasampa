<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'branch_id',
        'expense_category_id',
        'user_id',
        'description',
        'amount',
        'quantity',
        'unit',
        'expense_date',
        'reference_number',
        'supplier',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->amount * $this->quantity;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'card' => 'Card',
            'mobile' => 'Mobile Money',
            'bank' => 'Bank Transfer',
            default => ucfirst($this->payment_method),
        };
    }

    public function scopeInDateRange($query, $from, $to)
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }
}
