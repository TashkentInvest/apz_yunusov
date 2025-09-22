<?php

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

    // ========== RELATIONSHIPS ==========

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

    // Alias methods for backward compatibility
    public function payments()
    {
        return $this->hasMany(ActualPayment::class);
    }

    public function schedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    // ========== ACCESSORS ==========

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

    // ========== HELPER METHODS ==========

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

    // ========== SCOPES ==========

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

    public function scopeByStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeCurrent($query)
    {
        return $query->whereHas('status', function($q) {
            $q->where('code', 'current');
        });
    }

    public function scopeInProcess($query)
    {
        return $query->whereHas('status', function($q) {
            $q->where('code', 'process');
        });
    }
}
