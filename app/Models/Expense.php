<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Expense extends Model
{
    use HasFactory, BelongsToCompany;

    public const FREQUENCY_ONE_TIME = 'one_time';
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_QUARTERLY = 'quarterly';
    public const FREQUENCY_YEARLY = 'yearly';

    public const FREQUENCIES = [
        self::FREQUENCY_ONE_TIME,
        self::FREQUENCY_DAILY,
        self::FREQUENCY_WEEKLY,
        self::FREQUENCY_MONTHLY,
        self::FREQUENCY_QUARTERLY,
        self::FREQUENCY_YEARLY,
    ];

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
        'frequency',
        'period_start',
        'period_end',
        'reference_number',
        'supplier',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'decimal:2',
        'expense_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
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

    public function getLineTotalAttribute(): float
    {
        return (float) $this->amount * (float) $this->quantity;
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

    public function isRecurring(): bool
    {
        return $this->frequency && $this->frequency !== self::FREQUENCY_ONE_TIME;
    }

    public function effectivePeriodStart(): Carbon
    {
        return Carbon::parse($this->period_start ?? $this->expense_date);
    }

    /**
     * Effective period end. NULL period_end means "ongoing" — extend to today.
     * Explicit period_end is honored as-is (no clamping to the report window).
     */
    public function effectivePeriodEnd(): Carbon
    {
        return $this->period_end
            ? Carbon::parse($this->period_end)
            : Carbon::today();
    }

    /**
     * Number of days the recurring rate covers (e.g., yearly = 365).
     * The amount represents what's owed per this many days.
     */
    public function frequencyDaysPerPeriod(): int
    {
        return match ($this->frequency) {
            self::FREQUENCY_DAILY => 1,
            self::FREQUENCY_WEEKLY => 7,
            self::FREQUENCY_MONTHLY => 30,
            self::FREQUENCY_QUARTERLY => 91,
            self::FREQUENCY_YEARLY => 365,
            default => 1,
        };
    }

    /**
     * Amount allocated to the window [from, to] using calendar-day proration.
     *
     * One-time: full line total iff expense_date falls inside window.
     * Recurring: per-day rate (= amount / days-per-frequency-period) times
     *   the number of days where both the window AND the active period
     *   (period_start..period_end-or-today) overlap.
     */
    public function proratedAmount(Carbon $from, Carbon $to): float
    {
        $lineTotal = $this->line_total;

        if (!$this->isRecurring()) {
            $date = Carbon::parse($this->expense_date)->startOfDay();
            return $date->betweenIncluded($from->copy()->startOfDay(), $to->copy()->endOfDay())
                ? $lineTotal
                : 0.0;
        }

        $periodStart = $this->effectivePeriodStart()->startOfDay();
        $periodEnd = $this->effectivePeriodEnd()->startOfDay();

        if ($periodEnd->lt($periodStart)) {
            return 0.0;
        }

        $windowStart = $from->copy()->startOfDay();
        $windowEnd = $to->copy()->startOfDay();

        $overlapStart = $windowStart->max($periodStart);
        $overlapEnd = $windowEnd->min($periodEnd);

        if ($overlapEnd->lt($overlapStart)) {
            return 0.0;
        }

        // Inclusive day count: diff at midnight + 1 day for the start day itself.
        $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
        $perDayRate = $lineTotal / $this->frequencyDaysPerPeriod();

        return $perDayRate * $overlapDays;
    }

    /**
     * Query: expenses whose effective period overlaps [from, to].
     * Includes one-time expenses dated within the window AND recurring
     * expenses whose period intersects the window (period_end NULL = ongoing).
     */
    public function scopeOverlappingPeriod($query, $from, $to)
    {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        return $query->where(function ($q) use ($from, $to) {
            $q->where(function ($q1) use ($from, $to) {
                $q1->where('frequency', self::FREQUENCY_ONE_TIME)
                    ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]);
            })->orWhere(function ($q2) use ($from, $to) {
                $q2->where('frequency', '!=', self::FREQUENCY_ONE_TIME)
                    ->where(function ($q3) use ($to) {
                        $q3->whereNotNull('period_start')
                            ->where('period_start', '<=', $to->toDateString());
                    })
                    ->where(function ($q4) use ($from) {
                        $q4->whereNull('period_end')
                            ->orWhere('period_end', '>=', $from->toDateString());
                    });
            });
        });
    }

    /**
     * Sum the amount allocated to the window [from, to] across all matching
     * expenses, with calendar-day proration for recurring entries.
     */
    public static function proratedSum($from, $to, ?\Closure $scope = null): float
    {
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);

        $query = static::query()->overlappingPeriod($from, $to);

        if ($scope) {
            $scope($query);
        }

        $total = 0.0;
        $query->cursor()->each(function (Expense $expense) use (&$total, $from, $to) {
            $total += $expense->proratedAmount($from, $to);
        });

        return round($total, 2);
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
