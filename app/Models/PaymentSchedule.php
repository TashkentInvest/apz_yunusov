<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'contract_id',
        'amendment_id', // Yangi maydon
        'year',
        'quarter',
        'quarter_amount',
        'custom_percent',
        'is_active'
    ];

    protected $casts = [
        'quarter_amount' => 'decimal:2',
        'custom_percent' => 'decimal:2',
        'year' => 'integer',
        'quarter' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship with contract
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relationship with amendment (agar qo'shimcha kelishuv orqali yaratilgan bo'lsa)
     */


    /**
     * Get actual payments for this quarter
     */
    public function actualPayments(): HasMany
    {
        return $this->hasMany(ActualPayment::class, 'contract_id', 'contract_id')
            ->where('year', $this->year)
            ->where('quarter', $this->quarter);
    }

    /**
     * Get paid amount for this quarter
     */
    public function getPaidAmountAttribute(): float
    {
        return $this->contract->actualPayments()
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->sum('amount');
    }

    /**
     * Get remaining amount to be paid
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->quarter_amount - $this->paid_amount);
    }

    /**
     * Get payment completion percentage
     */
    public function getPaymentPercentAttribute(): float
    {
        if ($this->quarter_amount <= 0) return 0;
        return min(100, ($this->paid_amount / $this->quarter_amount) * 100);
    }

    /**
     * Check if this quarter is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        $currentYear = now()->year;
        $currentQuarter = ceil(now()->month / 3);

        // If it's a past quarter and not fully paid
        if ($this->year < $currentYear) {
            return $this->remaining_amount > 0;
        } elseif ($this->year == $currentYear && $this->quarter < $currentQuarter) {
            return $this->remaining_amount > 0;
        }

        return false;
    }

    /**
     * Get payment status
     */
    public function getStatusAttribute(): string
    {
        if ($this->remaining_amount <= 0) {
            return 'paid';
        } elseif ($this->is_overdue) {
            return 'overdue';
        } else {
            return 'pending';
        }
    }

    /**
     * Get quarter display name
     */
    public function getQuarterDisplayAttribute(): string
    {
        return "{$this->quarter}-chorak {$this->year}";
    }

    /**
     * Check if created by amendment
     */
    public function getIsAmendmentBasedAttribute(): bool
    {
        return !is_null($this->amendment_id);
    }

    /**
     * Get source description (original contract or amendment)
     */
    public function getSourceDescriptionAttribute(): string
    {
        if ($this->is_amendment_based) {
            return "Qo'shimcha kelishuv #{$this->amendment->amendment_number}";
        }
        return "Asosiy shartnoma";
    }

    /**
     * Scope for active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for original contract schedules (not from amendments)
     */
    public function scopeOriginal($query)
    {
        return $query->whereNull('amendment_id');
    }

    /**
     * Scope for amendment-based schedules
     */
    public function scopeAmendmentBased($query)
    {
        return $query->whereNotNull('amendment_id');
    }

    /**
     * Scope for overdue payments
     */
    public function scopeOverdue($query)
    {
        $currentYear = now()->year;
        $currentQuarter = ceil(now()->month / 3);

        return $query->where(function ($q) use ($currentYear, $currentQuarter) {
            $q->where('year', '<', $currentYear)
                ->orWhere(function ($subQ) use ($currentYear, $currentQuarter) {
                    $subQ->where('year', $currentYear)
                        ->where('quarter', '<', $currentQuarter);
                });
        });
    }

    /**
     * Scope for current quarter
     */
    public function scopeCurrentQuarter($query)
    {
        $currentYear = now()->year;
        $currentQuarter = ceil(now()->month / 3);

        return $query->where('year', $currentYear)
            ->where('quarter', $currentQuarter);
    }

    /**
     * Scope for specific amendment
     */
    public function scopeForAmendment($query, $amendmentId)
    {
        return $query->where('amendment_id', $amendmentId);
    }

    /**
     * Get overdue days
     */
    public function getOverdueDaysAttribute(): int
    {
        if (!$this->is_overdue) return 0;

        $quarterEndDate = now()->setYear($this->year)
            ->setMonth($this->quarter * 3)
            ->endOfMonth();

        return max(0, now()->diffInDays($quarterEndDate));
    }

    /**
     * Get formatted amounts
     */
    public function getFormattedAmountsAttribute(): array
    {
        return [
            'quarter_amount' => number_format($this->quarter_amount, 0, '.', ' ') . ' so\'m',
            'paid_amount' => number_format($this->paid_amount, 0, '.', ' ') . ' so\'m',
            'remaining_amount' => number_format($this->remaining_amount, 0, '.', ' ') . ' so\'m'
        ];
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        switch ($this->status) {
            case 'paid':
                return 'green';
            case 'overdue':
                return 'red';
            case 'pending':
                return 'yellow';
            default:
                return 'gray';
        }
    }

    /**
     * Get CSS classes for status display
     */
    public function getStatusClassesAttribute(): string
    {
        switch ($this->status) {
            case 'paid':
                return 'bg-green-50 text-green-800 border-green-200';
            case 'overdue':
                return 'bg-red-50 text-red-800 border-red-200';
            case 'pending':
                return 'bg-yellow-50 text-yellow-800 border-yellow-200';
            default:
                return 'bg-gray-50 text-gray-800 border-gray-200';
        }
    }

    /**
     * Check if schedule can be modified
     */
    public function getCanModifyAttribute(): bool
    {
        // Amendment-based schedules generally shouldn't be modified directly
        if ($this->is_amendment_based) {
            return false;
        }

        // Don't allow modification if there are payments
        if ($this->paid_amount > 0) {
            return false;
        }

        return true;
    }

    /**
     * Export format
     */
    public function toExportArray(): array
    {
        return [
            'contract_number' => $this->contract->contract_number,
            'year' => $this->year,
            'quarter' => $this->quarter,
            'quarter_display' => $this->quarter_display,
            'quarter_amount' => $this->quarter_amount,
            'paid_amount' => $this->paid_amount,
            'remaining_amount' => $this->remaining_amount,
            'payment_percent' => round($this->payment_percent, 2),
            'status' => $this->status,
            'is_overdue' => $this->is_overdue,
            'overdue_days' => $this->overdue_days,
            'source' => $this->source_description,
            'amendment_number' => $this->amendment ? $this->amendment->amendment_number : null,
            'created_at' => $this->created_at->format('d.m.Y H:i')
        ];
    }

public function amendment()
{
    return $this->belongsTo(ContractAmendment::class, 'amendment_id');
}
}
