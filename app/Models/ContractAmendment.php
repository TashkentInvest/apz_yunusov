<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractAmendment extends Model
{
    use HasFactory;

    protected $table = 'contract_amendments';

    protected $fillable = [
        'contract_id',
        'amendment_number',
        'amendment_date',
        'new_total_amount',
        'new_completion_date',
        'new_initial_payment_percent',
        'new_quarters_count',
        'reason',
        'description',
        'is_approved',
        'approved_at',
        'approved_by',
        'created_by'
    ];

    protected $casts = [
        'amendment_date' => 'date',
        'new_completion_date' => 'date',
        'new_total_amount' => 'decimal:2',
        'new_initial_payment_percent' => 'decimal:2',
        'new_quarters_count' => 'integer',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the contract that owns the amendment
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get the user who created the amendment
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the amendment
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the payment schedules created for this amendment
     */
    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class, 'amendment_id');
    }

    /**
     * Get the actual payments linked to this amendment
     */
    public function actualPayments(): HasMany
    {
        return $this->hasMany(ActualPayment::class, 'amendment_id');
    }

    /**
     * Check if amendment can be edited
     */
    public function canBeEdited(): bool
    {
        return !$this->is_approved;
    }

    /**
     * Check if amendment can be approved
     */
    public function canBeApproved(): bool
    {
        return !$this->is_approved;
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_approved ? 'Tasdiqlangan' : 'Kutilmoqda';
    }

    /**
     * Get status class for styling
     */
    public function getStatusClassAttribute(): string
    {
        return $this->is_approved ? 'completed' : 'warning';
    }

    /**
     * Scope for approved amendments
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for pending amendments
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope for specific contract
     */
    public function scopeForContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }
}