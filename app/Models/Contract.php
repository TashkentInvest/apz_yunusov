<?php

// ================================
// Enhanced Contract Model
// ================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'subject_id',
        'object_id',
        'contract_date',
        'completion_date',
        'status_id',
        'base_amount_id',
        'contract_volume',
        'coefficient',
        'total_amount',
        'payment_type',
        'initial_payment_percent',
        'construction_period_years',
        'quarters_count',
        'formula',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'contract_date' => 'date',
        'completion_date' => 'date',
        'total_amount' => 'decimal:2',
        'contract_volume' => 'decimal:2',
        'coefficient' => 'decimal:4',
        'initial_payment_percent' => 'decimal:2',
        'construction_period_years' => 'integer',
        'quarters_count' => 'integer',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(Objectt::class, 'object_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ContractStatus::class, 'status_id');
    }

    public function baseAmount(): BelongsTo
    {
        return $this->belongsTo(BaseCalculationAmount::class, 'base_amount_id');
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function actualPayments(): HasMany
    {
        return $this->hasMany(ActualPayment::class);
    }

    public function amendments(): HasMany
    {
        return $this->hasMany(ContractAmendment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors & Mutators
    public function getInitialPaymentAmountAttribute(): float
    {
        return $this->total_amount * (($this->initial_payment_percent ?? 0) / 100);
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->initial_payment_amount;
    }

    public function getQuarterlyAmountAttribute(): float
    {
        return $this->quarters_count > 0 ? $this->remaining_amount / $this->quarters_count : 0;
    }

    public function getTotalPaidAmountAttribute(): float
    {
        return $this->actualPayments()->sum('amount');
    }

    public function getInitialPaymentsPaidAttribute(): float
    {
        return $this->actualPayments()->where('is_initial_payment', true)->sum('amount');
    }

    public function getQuarterlyPaymentsPaidAttribute(): float
    {
        return $this->actualPayments()->where('is_initial_payment', false)->sum('amount');
    }

    public function getRemainingDebtAttribute(): float
    {
        return max(0, $this->total_amount - $this->total_paid_amount);
    }

    public function getPaymentPercentAttribute(): float
    {
        return $this->total_amount > 0 ? ($this->total_paid_amount / $this->total_amount) * 100 : 0;
    }

    // Helper Methods
    public function hasActiveSchedule(): bool
    {
        return $this->paymentSchedules()->where('is_active', true)->exists();
    }

    public function hasAmendments(): bool
    {
        return $this->amendments()->exists();
    }

    public function getApprovedAmendments()
    {
        return $this->amendments()->approved()->get();
    }

    public function canCreateSchedule(): bool
    {
        return $this->payment_type === 'installment' && $this->quarters_count > 0;
    }

    public function isCompleted(): bool
    {
        return $this->payment_percent >= 100;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithDebt($query)
    {
        return $query->whereRaw('total_amount > (SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id)');
    }

    public function scopeCompleted($query)
    {
        return $query->whereRaw('total_amount <= (SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id)');
    }
}

// ================================
// Enhanced PaymentSchedule Model
// ================================

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'amendment_id',
        'year',
        'quarter',
        'quarter_amount',
        'is_initial_payment',
        'custom_percent',
        'is_active'
    ];

    protected $casts = [
        'quarter_amount' => 'decimal:2',
        'custom_percent' => 'decimal:2',
        'year' => 'integer',
        'quarter' => 'integer',
        'is_initial_payment' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
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
            ->where('quarter', $this->quarter)
            ->where('is_initial_payment', $this->is_initial_payment);
    }

    // Accessors
    public function getTotalPaidAttribute(): float
    {
        if ($this->is_initial_payment) {
            return $this->contract->actualPayments()
                ->where('is_initial_payment', true)
                ->sum('amount');
        }

        return $this->contract->actualPayments()
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->where('is_initial_payment', false)
            ->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->quarter_amount - $this->total_paid);
    }

    public function getPaymentPercentAttribute(): float
    {
        return $this->quarter_amount > 0 ? ($this->total_paid / $this->quarter_amount) * 100 : 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->is_initial_payment) return false;
        
        $quarterEnd = now()->create($this->year, $this->quarter * 3, 1)->endOfMonth();
        return $quarterEnd->isPast() && $this->remaining_amount > 0;
    }

    public function getQuarterNameAttribute(): string
    {
        if ($this->is_initial_payment) {
            return 'Boshlang\'ich to\'lov';
        }
        
        return "{$this->quarter}-chorak {$this->year}";
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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

    public function scopeOverdue($query)
    {
        $currentDate = now();
        return $query->where('is_initial_payment', false)
            ->whereRaw("DATE(CONCAT(year, '-', quarter * 3, '-01')) + INTERVAL 1 MONTH - INTERVAL 1 DAY < ?", [$currentDate]);
    }
}

// ================================
// Enhanced ActualPayment Model
// ================================

class ActualPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'payment_date',
        'amount',
        'year',
        'quarter',
        'is_initial_payment',
        'amendment_id',
        'payment_category',
        'payment_number',
        'notes',
        'exchange_rate',
        'currency',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'year' => 'integer',
        'quarter' => 'integer',
        'is_initial_payment' => 'boolean',
        'exchange_rate' => 'decimal:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function amendment(): BelongsTo
    {
        return $this->belongsTo(ContractAmendment::class, 'amendment_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors
    public function getQuarterInfoAttribute(): string
    {
        if ($this->is_initial_payment) {
            return 'Boshlang\'ich to\'lov';
        }
        
        return "{$this->quarter}-chorak {$this->year}";
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, '.', ' ') . ' so\'m';
    }

    public function getCanEditAttribute(): bool
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }

    public function getCanDeleteAttribute(): bool
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }

    public function getPaymentTypeAttribute(): string
    {
        return $this->is_initial_payment ? 'initial' : 'quarterly';
    }

    // Helper Methods
    public function isEditable(): bool
    {
        return $this->can_edit;
    }

    public function isDeletable(): bool
    {
        return $this->can_delete;
    }

    public function getRelatedSchedule()
    {
        return PaymentSchedule::where('contract_id', $this->contract_id)
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->where('is_initial_payment', $this->is_initial_payment)
            ->where('is_active', true)
            ->first();
    }

    // Scopes
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

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeEditable($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('payment_category', $category);
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->created_by) {
                $payment->created_by = auth()->id();
            }
            
            // Auto-determine quarter if not set
            if (!$payment->year || !$payment->quarter) {
                $payment->year = $payment->payment_date->year;
                $payment->quarter = ceil($payment->payment_date->month / 3);
            }
        });

        static::updating(function ($payment) {
            $payment->updated_by = auth()->id();
        });
    }
}