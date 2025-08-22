<?php

// app/Models/Contract.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number', 'object_id', 'subject_id', 'contract_date',
        'completion_date', 'status_id', 'base_amount_id', 'contract_volume',
        'coefficient', 'total_amount', 'formula', 'payment_type',
        'initial_payment_percent', 'construction_period_years', 'quarters_count'
    ];

    protected $casts = [
        'contract_date' => 'date',
        'completion_date' => 'date',
        'contract_volume' => 'decimal:2',
        'coefficient' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    public function object()
    {
        return $this->belongsTo(Objectt::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function status()
    {
        return $this->belongsTo(ContractStatus::class, 'status_id');
    }

    public function baseAmount()
    {
        return $this->belongsTo(BaseCalculationAmount::class, 'base_amount_id');
    }

    public function amendments()
    {
        return $this->hasMany(ContractAmendment::class);
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function actualPayments()
    {
        return $this->hasMany(ActualPayment::class);
    }

    public function cancellation()
    {
        return $this->hasOne(ContractCancellation::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->actualPayments->sum('amount');
    }

    public function getRemainingDebtAttribute()
    {
        return $this->total_amount - $this->total_paid;
    }

    public function getPaymentPercentAttribute()
    {
        if ($this->total_amount == 0) return 0;
        return round(($this->total_paid / $this->total_amount) * 100, 2);
    }
}
