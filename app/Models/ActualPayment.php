<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class ActualPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'payment_number',
        'payment_date',
        'amount',
        'year',
        'quarter',
        'notes',
        'is_initial_payment',
        'payment_category',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'is_initial_payment' => 'boolean',
    ];

    // ========== RELATIONSHIPS ==========

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ========== ACCESSORS ==========

    public function getQuarterNameAttribute()
    {
        return $this->quarter . '-chorak ' . $this->year . ' yil';
    }

    public function getQuarterNameRuAttribute()
    {
        return $this->quarter . ' квартал ' . $this->year . ' года';
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, '.', ' ') . ' so\'m';
    }

    public function getAmountInMillionsAttribute()
    {
        return number_format($this->amount / 1000000, 2) . 'M';
    }

    public function getAmountInThousandsAttribute()
    {
        return number_format($this->amount / 1000, 0) . 'K';
    }

    public function getQuarterInfoAttribute(): string
    {
        if ($this->is_initial_payment) {
            return 'Boshlang\'ich to\'lov';
        }
        return "{$this->quarter}-chorak {$this->year}";
    }

    public function getCanEditAttribute(): bool
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }

    public function getCanDeleteAttribute(): bool
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }

    // ========== HELPER METHODS ==========

    public static function calculateQuarterFromDate($date)
    {
        $month = Carbon::parse($date)->month;
        return ceil($month / 3);
    }

    public static function getQuarterDateRange($year, $quarter)
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $start = Carbon::create($year, $startMonth, 1)->startOfDay();
        $end = Carbon::create($year, $endMonth, 1)->endOfMonth()->endOfDay();

        return [
            'start' => $start,
            'end' => $end,
            'start_formatted' => $start->format('d.m.Y'),
            'end_formatted' => $end->format('d.m.Y')
        ];
    }

    // ========== SCOPES ==========

    public function scopeForContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }

    public function scopeForQuarter($query, $year, $quarter)
    {
        return $query->where('year', $year)->where('quarter', $quarter);
    }

    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('payment_date', '>=', Carbon::now()->subDays($days));
    }

    public function scopeOrderByDate($query, $direction = 'desc')
    {
        return $query->orderBy('payment_date', $direction);
    }

    public function scopeInitialPayments($query)
    {
        return $query->where('is_initial_payment', true);
    }

    public function scopeQuarterlyPayments($query)
    {
        return $query->where('is_initial_payment', false);
    }

    public function scopeEditable($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('payment_number', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%")
              ->orWhere('amount', 'like', "%{$search}%")
              ->orWhereHas('contract', function($contractQuery) use ($search) {
                  $contractQuery->where('contract_number', 'like', "%{$search}%");
              });
        });
    }

    // ========== EVENTS ==========

    protected static function booted()
    {
        static::creating(function ($payment) {
            if (!$payment->year || !$payment->quarter) {
                $date = Carbon::parse($payment->payment_date);
                $payment->year = $date->year;
                $payment->quarter = static::calculateQuarterFromDate($payment->payment_date);
            }
            if (!$payment->created_by) {
                $payment->created_by = auth()->id();
            }
        });

        static::updating(function ($payment) {
            if ($payment->isDirty('payment_date')) {
                $date = Carbon::parse($payment->payment_date);
                $payment->year = $date->year;
                $payment->quarter = static::calculateQuarterFromDate($payment->payment_date);
            }
            $payment->updated_by = auth()->id();
        });
    }
}
