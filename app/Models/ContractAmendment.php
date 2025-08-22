<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAmendment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'amendment_number', 'amendment_date', 'reason',
        'old_volume', 'new_volume', 'old_coefficient', 'new_coefficient',
        'old_amount', 'new_amount', 'old_base_amount_id', 'new_base_amount_id',
        'bank_changes'
    ];

    protected $casts = [
        'amendment_date' => 'date',
        'old_volume' => 'decimal:2',
        'new_volume' => 'decimal:2',
        'old_coefficient' => 'decimal:2',
        'new_coefficient' => 'decimal:2',
        'old_amount' => 'decimal:2',
        'new_amount' => 'decimal:2'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function oldBaseAmount()
    {
        return $this->belongsTo(BaseCalculationAmount::class, 'old_base_amount_id');
    }

    public function newBaseAmount()
    {
        return $this->belongsTo(BaseCalculationAmount::class, 'new_base_amount_id');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class, 'amendment_id');
    }
}
