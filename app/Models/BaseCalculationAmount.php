<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseCalculationAmount extends Model
{
    protected $fillable = ['amount', 'effective_from', 'effective_to', 'is_active'];
    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean'
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'base_amount_id');
    }
}
