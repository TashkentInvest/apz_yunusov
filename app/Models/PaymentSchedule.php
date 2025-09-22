<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'amendment_id',
        'year',
        'quarter',
        'quarter_amount',
        'custom_percent',
        'is_initial_payment',
        'is_active'
    ];

    protected $casts = [
        'quarter_amount' => 'decimal:2',
        'custom_percent' => 'decimal:2',
        'year' => 'integer',
        'quarter' => 'integer',
        'is_initial_payment' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ========== RELATIONSHIPS ==========

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

    // ========== ACCESSORS ==========

    public function getPaidAmountAttribute(): float
    {
        return $this->contract->actualPayments()
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->quarter_amount - $this->paid_amount);
    }

    public function getPaymentPercentAttribute(): float
    {
        if ($this->quarter_amount <= 0) return 0;
        return min(100, ($this->paid_amount / $this->quarter_amount) * 100);
    }

    public function getIsOverdueAttribute(): bool
    {
        $currentYear = now()->year;
        $currentQuarter = ceil(now()->month / 3);

        if ($this->year < $currentYear) {
            return $this->remaining_amount > 0;
        } elseif ($this->year == $currentYear && $this->quarter < $currentQuarter) {
            return $this->remaining_amount > 0;
        }

        return false;
    }

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

    public function getQuarterDisplayAttribute(): string
    {
        if ($this->is_initial_payment) {
            return 'Boshlang\'ich to\'lov';
        }
        return "{$this->quarter}-chorak {$this->year}";
    }

    public function getIsAmendmentBasedAttribute(): bool
    {
        return !is_null($this->amendment_id);
    }

    public function getSourceDescriptionAttribute(): string
    {
        if ($this->is_amendment_based) {
            return "Qo'shimcha kelishuv #{$this->amendment->amendment_number}";
        }
        return "Asosiy shartnoma";
    }

    public function getOverdueDaysAttribute(): int
    {
        if (!$this->is_overdue) return 0;

        $quarterEndDate = now()->setYear($this->year)
            ->setMonth($this->quarter * 3)
            ->endOfMonth();

        return max(0, now()->diffInDays($quarterEndDate));
    }

    public function getFormattedAmountsAttribute(): array
    {
        return [
            'quarter_amount' => number_format($this->quarter_amount, 0, '.', ' ') . ' so\'m',
            'paid_amount' => number_format($this->paid_amount, 0, '.', ' ') . ' so\'m',
            'remaining_amount' => number_format($this->remaining_amount, 0, '.', ' ') . ' so\'m'
        ];
    }

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

    public function getCanModifyAttribute(): bool
    {
        if ($this->is_amendment_based) {
            return false;
        }

        if ($this->paid_amount > 0) {
            return false;
        }

        return true;
    }

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOriginal($query)
    {
        return $query->whereNull('amendment_id');
    }

    public function scopeAmendmentBased($query)
    {
        return $query->whereNotNull('amendment_id');
    }

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

    public function scopeCurrentQuarter($query)
    {
        $currentYear = now()->year;
        $currentQuarter = ceil(now()->month / 3);

        return $query->where('year', $currentYear)
            ->where('quarter', $currentQuarter);
    }

    public function scopeForAmendment($query, $amendmentId)
    {
        return $query->where('amendment_id', $amendmentId);
    }

    public function scopeInitialPayments($query)
    {
        return $query->where('is_initial_payment', true);
    }

    public function scopeQuarterlyPayments($query)
    {
        return $query->where('is_initial_payment', false);
    }

    public function scopeForContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }

    public function scopeForQuarter($query, $year, $quarter)
    {
        return $query->where('year', $year)->where('quarter', $quarter);
    }

    // ========== HELPER METHODS ==========

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
}
