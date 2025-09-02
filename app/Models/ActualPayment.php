<?php

namespace App\Models;

use Carbon\Carbon;
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



//



//===


public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function calculateQuarterFromDate($date)
    {
        $month = Carbon::parse($date)->month;
        return ceil($month / 3);
    }

    public function getQuarterNameAttribute()
    {
        return $this->quarter . ' квартал ' . $this->year;
    }

    public function setPaymentDateAttribute($value)
    {
        $this->attributes['payment_date'] = $value;
        $date = Carbon::parse($value);
        $this->attributes['year'] = $date->year;
        $this->attributes['quarter'] = self::calculateQuarterFromDate($value);
    }
}
