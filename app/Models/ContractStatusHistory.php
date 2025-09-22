<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'old_status_id',
        'new_status_id',
        'changed_by',
        'reason',
        'changed_at'
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Get the contract
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get the old status
     */
    public function oldStatus()
    {
        return $this->belongsTo(ContractStatus::class, 'old_status_id');
    }

    /**
     * Get the new status
     */
    public function newStatus()
    {
        return $this->belongsTo(ContractStatus::class, 'new_status_id');
    }

    /**
     * Get the user who changed the status
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get formatted change description
     */
    public function getChangeDescriptionAttribute()
    {
        return "{$this->oldStatus->name_uz} → {$this->newStatus->name_uz}";
    }

    /**
     * Scope for recent changes
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific contract
     */
    public function scopeForContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }
}
