<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_number',
        'object_id',
        'subject_id',
        'contract_date',
        'completion_date',
        'status_id',
        'base_amount_id',
        'contract_volume',
        'coefficient',
        'total_amount',
        'formula',
        'payment_type',
        'initial_payment_percent',
        'construction_period_years',
        'quarters_count',
        'is_active'
    ];

    protected $casts = [
        'contract_date' => 'date',
        'completion_date' => 'date',
        'total_amount' => 'decimal:2',
        'contract_volume' => 'decimal:2',
        'coefficient' => 'decimal:2',
        'is_active' => 'boolean'
    ];

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

    // Accessor for display number
    public function getDisplayNumberAttribute(): string
    {
        return $this->contract_number;
    }

    // Calculate initial payment amount
    public function getInitialPaymentAmountAttribute(): float
    {
        return $this->total_amount * ($this->initial_payment_percent / 100);
    }

    // Calculate remaining amount after initial payment
    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->initial_payment_amount;
    }

    // Calculate quarterly payment amount
    public function getQuarterlyPaymentAmountAttribute(): float
    {
        return $this->quarters_count > 0 ? $this->remaining_amount / $this->quarters_count : 0;
    }
}
