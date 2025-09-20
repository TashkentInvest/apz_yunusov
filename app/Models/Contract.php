<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'payment_type',
        'initial_payment_percent',
        'construction_period_years',
        'quarters_count',
        'formula',
        'is_active',
        'created_by',
        'updated_by',
        'last_amendment_id',
        'amendment_count'
    ];

  protected $casts = [
    'total_amount' => 'decimal:2',
    'initial_payment_percent' => 'decimal:2',
    'contract_volume' => 'decimal:2',
    'coefficient' => 'decimal:4',
    'construction_period_years' => 'integer',
    'quarters_count' => 'integer',
    'contract_date' => 'date',
    'completion_date' => 'date',
    'is_active' => 'boolean',
];
    /**
     * Relationship with subject (property owner)
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relationship with object (construction object)
     */
    public function object()
    {
        return $this->belongsTo(Objectt::class, 'object_id');
    }

    /**
     * Relationship with contract status
     */
    public function status()
    {
        return $this->belongsTo(ContractStatus::class, 'status_id');
    }

    /**
     * Relationship with base calculation amount
     */
    public function baseAmount()
    {
        return $this->belongsTo(BaseCalculationAmount::class, 'base_amount_id');
    }

    /**
     * Relationship with payment schedules
     */
    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    /**
     * Relationship with actual payments
     */
    public function actualPayments()
    {
        return $this->hasMany(ActualPayment::class);
    }

    /**
     * Relationship with contract amendments
     */
    public function amendments()
    {
        return $this->hasMany(ContractAmendment::class);
    }

    /**
     * Relationship with payment history
     */
    public function paymentHistory()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    /**
     * Scope for active contracts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for contracts by payment type
     */
    public function scopeByPaymentType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    /**
     * Get remaining debt amount
     */
    public function getRemainingDebtAttribute()
    {
        $totalPaid = $this->actualPayments()->sum('amount');
        return $this->total_amount - $totalPaid;
    }

    /**
     * Get payment completion percentage
     */
    public function getPaymentCompletionPercentageAttribute()
    {
        if ($this->total_amount <= 0) return 0;

        $totalPaid = $this->actualPayments()->sum('amount');
        return min(100, ($totalPaid / $this->total_amount) * 100);
    }

    /**
     * Get initial payment amount
     */
    public function getInitialPaymentAmountAttribute()
    {
        return ($this->total_amount * $this->initial_payment_percent) / 100;
    }

    /**
     * Get remaining amount after initial payment
     */
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->initial_payment_amount;
    }

    /**
     * Check if contract is overdue
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->completion_date) return false;

        return now() > $this->completion_date && $this->remaining_debt > 0;
    }

    /**
     * Get contract age in days
     */
    public function getAgeInDaysAttribute()
    {
        return $this->contract_date ? $this->contract_date->diffInDays(now()) : 0;
    }

    /**
     * Format contract number for display
     */
    public function getFormattedContractNumberAttribute()
    {
        return $this->contract_number;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        return $this->status ? $this->status->color : 'gray';
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set created_by and updated_by if auth is available
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

//


 // Relationships


    public function lastAmendment()
    {
        return $this->belongsTo(ContractAmendment::class, 'last_amendment_id');
    }

    // Methods
    public function getExistingPayments()
    {
        return ActualPayment::where('contract_id', $this->id)->get();
    }

    public function getTotalPaidAmount()
    {
        return $this->getExistingPayments()->sum('amount');
    }

    public function getInitialPaymentsMade()
    {
        return ActualPayment::where('contract_id', $this->id)
            ->where('is_initial_payment', true)
            ->sum('amount');
    }

    public function getQuarterlyPaymentsMade()
    {
        return ActualPayment::where('contract_id', $this->id)
            ->where('is_initial_payment', false)
            ->sum('amount');
    }

    public function hasAmendments()
    {
        return $this->amendments()->count() > 0;
    }

    public function getCurrentStructure()
    {
        $initialPayment = $this->total_amount * ($this->initial_payment_percent / 100);
        $remainingAmount = $this->total_amount - $initialPayment;
        $quarterlyAmount = $this->quarters_count > 0 ? $remainingAmount / $this->quarters_count : 0;

        return [
            'total_amount' => $this->total_amount,
            'initial_payment' => $initialPayment,
            'remaining_amount' => $remainingAmount,
            'quarterly_amount' => $quarterlyAmount,
            'quarters_count' => $this->quarters_count
        ];
    }
}
