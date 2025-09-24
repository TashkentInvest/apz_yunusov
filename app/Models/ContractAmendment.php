<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractAmendment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_id',
        'amendment_number',
        'amendment_date',
        'sequential_number',
        'new_total_amount',
        'new_initial_payment_percent',
        'new_quarters_count',
        'new_completion_date',
        'reason',
        'description',
        'is_approved',
        'approved_at',
        'approved_by',
        'impact_summary',
        'applied_changes',
        'changes_summary',
        'amendment_type',
        'financial_impact',
        'schedule_impact_days',
        'parent_amendment_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'amendment_date' => 'date',
        'new_completion_date' => 'date',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean',
        'impact_summary' => 'array',
        'applied_changes' => 'array',
        'new_total_amount' => 'decimal:2',
        'new_initial_payment_percent' => 'decimal:2',
        'financial_impact' => 'decimal:2'
    ];

    // Relationships
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function parentAmendment()
    {
        return $this->belongsTo(ContractAmendment::class, 'parent_amendment_id');
    }

    public function childAmendments()
    {
        return $this->hasMany(ContractAmendment::class, 'parent_amendment_id');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class, 'amendment_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeForContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->is_approved ? 'Tasdiqlangan' : 'Kutilmoqda';
    }

    public function getStatusClassAttribute()
    {
        return $this->is_approved ? 'completed' : 'warning';
    }

    public function getFormattedNewTotalAmountAttribute()
    {
        return $this->new_total_amount ? number_format($this->new_total_amount, 0, '.', ' ') . ' so\'m' : null;
    }

    public function getHasChangesAttribute()
    {
        return $this->new_total_amount !== null ||
               $this->new_initial_payment_percent !== null ||
               $this->new_quarters_count !== null ||
               $this->new_completion_date !== null;
    }

    // Boot method for automatic sequential numbering
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($amendment) {
            if (!$amendment->sequential_number) {
                $amendment->sequential_number = static::where('contract_id', $amendment->contract_id)->count() + 1;
            }
        });
    }

    // Helper methods
    public function calculateFinancialImpact()
    {
        if ($this->new_total_amount && $this->contract) {
            return $this->new_total_amount - $this->contract->total_amount;
        }
        return null;
    }

    public function canBeEdited()
    {
        return !$this->is_approved;
    }

    public function canBeDeleted()
    {
        return !$this->is_approved && $this->paymentSchedules()->count() === 0;
    }

    public function canBeApproved()
    {
        return !$this->is_approved && $this->has_changes;
    }
}
