<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseCalculationAmount extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount', 'effective_from', 'effective_to', 'is_current'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_current' => 'boolean'
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'base_amount_id');
    }

    public static function getCurrentAmount()
    {
        return static::where('is_current', true)->first();
    }
}
