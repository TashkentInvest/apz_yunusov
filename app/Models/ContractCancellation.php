<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'cancellation_reason_id', 'cancellation_date',
        'paid_amount', 'refund_amount', 'refund_date', 'notes'
    ];

    protected $casts = [
        'cancellation_date' => 'date',
        'refund_date' => 'date',
        'paid_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function reason()
    {
        return $this->belongsTo(CancellationReason::class, 'cancellation_reason_id');
    }
}
