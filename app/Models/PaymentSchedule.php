<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'amendment_id', 'year', 'quarter',
        'quarter_amount', 'custom_percent', 'is_active'
    ];

    protected $casts = [
        'quarter_amount' => 'decimal:2',
        'custom_percent' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function amendment()
    {
        return $this->belongsTo(ContractAmendment::class);
    }

    public function getQuarterEndDateAttribute()
    {
        return Carbon::createFromDate($this->year, $this->quarter * 3, 1)->endOfQuarter();
    }

    public function getPaidAmountAttribute()
    {
        return $this->contract->actualPayments()
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->quarter_amount - $this->paid_amount;
    }

    public function getIsOverdueAttribute()
    {
        return $this->quarter_end_date->lt(now()) && $this->remaining_amount > 0;
    }
}
