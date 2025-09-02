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
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2'
    ];

    // Relationships
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Automatically calculate quarter from date
    protected static function booted()
    {
        static::creating(function ($payment) {
            if (!$payment->year || !$payment->quarter) {
                $date = Carbon::parse($payment->payment_date);
                $payment->year = $date->year;
                $payment->quarter = static::calculateQuarterFromDate($payment->payment_date);
            }
        });

        static::updating(function ($payment) {
            if ($payment->isDirty('payment_date')) {
                $date = Carbon::parse($payment->payment_date);
                $payment->year = $date->year;
                $payment->quarter = static::calculateQuarterFromDate($payment->payment_date);
            }
        });
    }

    // Calculate quarter from date
    public static function calculateQuarterFromDate($date)
    {
        $month = Carbon::parse($date)->month;
        return ceil($month / 3);
    }

    // Get quarter date range
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

    // Get quarter name in Uzbek
    public function getQuarterNameAttribute()
    {
        return $this->quarter . '-чорак ' . $this->year . ' йил';
    }

    // Get formatted amount
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, '.', ' ') . ' сум';
    }

    // Get amount in millions
    public function getAmountInMillionsAttribute()
    {
        return number_format($this->amount / 1000000, 1) . 'М';
    }

    // Validation methods
    public function validateAmount()
    {
        // Check if payment doesn't exceed contract total
        $totalPaid = $this->contract->actualPayments()->sum('amount');
        $newTotal = $totalPaid + $this->amount;

        if ($this->exists) {
            $newTotal -= $this->getOriginal('amount');
        }

        return [
            'valid' => $newTotal <= $this->contract->total_amount,
            'total_paid' => $newTotal,
            'contract_total' => $this->contract->total_amount,
            'message' => $newTotal > $this->contract->total_amount
                ? 'Тўлов суммаси шартнома суммасидан ошиб кетмоқда'
                : 'Тўлов суммаси тўғри'
        ];
    }

    public function validateQuarterPayment()
    {
        // Check if quarter has planned payment and validate against it
        $planPayment = PaymentSchedule::where('contract_id', $this->contract_id)
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->where('is_active', true)
            ->first();

        $quarterPaid = $this->contract->actualPayments()
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->where('id', '!=', $this->id ?? 0)
            ->sum('amount');

        $newQuarterTotal = $quarterPaid + $this->amount;

        return [
            'has_plan' => (bool) $planPayment,
            'plan_amount' => $planPayment ? $planPayment->quarter_amount : 0,
            'quarter_paid' => $newQuarterTotal,
            'exceeds_plan' => $planPayment && $newQuarterTotal > $planPayment->quarter_amount,
            'completion_percent' => $planPayment && $planPayment->quarter_amount > 0
                ? ($newQuarterTotal / $planPayment->quarter_amount) * 100
                : 0
        ];
    }

    // Scopes
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

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('payment_date', '>=', Carbon::now()->subDays($days));
    }

    public function scopeOrderByDate($query, $direction = 'desc')
    {
        return $query->orderBy('payment_date', $direction);
    }

    // Helper methods for reporting
    public static function getPaymentsByQuarter($contractId, $year = null)
    {
        $query = static::where('contract_id', $contractId);

        if ($year) {
            $query->where('year', $year);
        }

        return $query->get()->groupBy(function($payment) {
            return $payment->year . '-' . $payment->quarter;
        });
    }

    public static function getTotalByQuarter($contractId, $year, $quarter)
    {
        return static::where('contract_id', $contractId)
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->sum('amount');
    }

    public static function getPaymentStatistics($contractId)
    {
        $payments = static::where('contract_id', $contractId)->get();

        if ($payments->isEmpty()) {
            return [
                'total_payments' => 0,
                'total_amount' => 0,
                'average_payment' => 0,
                'first_payment_date' => null,
                'last_payment_date' => null,
                'quarters_with_payments' => 0
            ];
        }

        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'average_payment' => $payments->avg('amount'),
            'first_payment_date' => $payments->min('payment_date'),
            'last_payment_date' => $payments->max('payment_date'),
            'quarters_with_payments' => $payments->groupBy(function($payment) {
                return $payment->year . '-' . $payment->quarter;
            })->count()
        ];
    }
}
