<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'contract_id',
        'year',
        'quarter',
        'quarter_amount',
        'is_active',
    ];

    protected $casts = [
        'quarter_amount' => 'decimal:2',
        'year' => 'integer',
        'quarter' => 'integer',
        'is_active' => 'boolean',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
    public function amendment(): BelongsTo
    {
        return $this->belongsTo(ContractAmendment::class, 'amendment_id');
    }

    public function actualPayments(): HasMany
    {
        return $this->hasMany(ActualPayment::class, 'contract_id', 'contract_id')
            ->where('year', $this->year)
            ->where('quarter', $this->quarter);
    }

    // CORRECTED: Fix the paid_amount accessor to return actual amount, not percentage
    public function getPaidAmountAttribute(): float
    {
        // Get actual payments for this specific quarter and year
        $paidAmount = ActualPayment::where('contract_id', $this->contract_id)
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->sum('amount');

        return (float) $paidAmount;
    }

    // CORRECTED: Fix the remaining_amount accessor
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->quarter_amount - $this->paid_amount);
    }

    // Check if payment is overdue
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

    // Get payment status
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

    // Get payment percentage
    public function getPaymentPercentAttribute(): float
    {
        if ($this->quarter_amount <= 0) return 0;

        return min(100, ($this->paid_amount / $this->quarter_amount) * 100);
    }

    // Scope for active schedules
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for overdue payments
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

    // Scope for current quarter
    public function scopeCurrentQuarter($query)
    {
        $currentYear = now()->year;
        $currentQuarter = ceil(now()->month / 3);

        return $query->where('year', $currentYear)
            ->where('quarter', $currentQuarter);
    }

    // Format quarter display
    public function getQuarterDisplayAttribute(): string
    {
        return "{$this->quarter} кв. {$this->year}";
    }

    // Get overdue days
    public function getOverdueDaysAttribute(): int
    {
        if (!$this->is_overdue) return 0;

        $quarterEndDate = now()->setYear($this->year)
            ->setMonth($this->quarter * 3)
            ->endOfMonth();

        return max(0, now()->diffInDays($quarterEndDate));
    }
}
