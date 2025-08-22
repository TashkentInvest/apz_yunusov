<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'payment_number', 'payment_date',
        'amount', 'year', 'quarter', 'notes'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
