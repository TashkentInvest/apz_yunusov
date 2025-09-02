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

    // Calculate quarter from date (1-4)
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

    // Get quarter name in different languages
    public function getQuarterNameAttribute()
    {
        return $this->quarter . '-чорак ' . $this->year . ' йил';
    }

    public function getQuarterNameRuAttribute()
    {
        return $this->quarter . ' квартал ' . $this->year . ' года';
    }

    // Formatted amount accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, '.', ' ') . ' сум';
    }

    public function getAmountInMillionsAttribute()
    {
        return number_format($this->amount / 1000000, 2) . 'М';
    }

    public function getAmountInThousandsAttribute()
    {
        return number_format($this->amount / 1000, 0) . 'К';
    }

    // Validation methods
    public function validateAmount()
    {
        $contract = $this->contract;
        $totalPaid = $contract->actualPayments()
            ->where('id', '!=', $this->id ?? 0)
            ->sum('amount');
        $newTotal = $totalPaid + $this->amount;

        return [
            'valid' => $newTotal <= $contract->total_amount,
            'total_paid' => $newTotal,
            'contract_total' => $contract->total_amount,
            'remaining' => $contract->total_amount - $newTotal,
            'exceeds_by' => max(0, $newTotal - $contract->total_amount),
            'message' => $newTotal > $contract->total_amount
                ? 'Тўлов суммаси шартнома суммасидан ошиб кетмоқда'
                : 'Тўлов суммаси тўғри'
        ];
    }

    public function validateQuarterPayment()
    {
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
                : 0,
            'remaining_in_quarter' => $planPayment ? max(0, $planPayment->quarter_amount - $newQuarterTotal) : 0
        ];
    }

    // Scopes for querying
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

    public function scopeWithMinAmount($query, $minAmount)
    {
        return $query->where('amount', '>=', $minAmount);
    }

    // Static helper methods
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
                'largest_payment' => 0,
                'smallest_payment' => 0,
                'first_payment_date' => null,
                'last_payment_date' => null,
                'quarters_with_payments' => 0,
                'years_with_payments' => 0
            ];
        }

        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'average_payment' => $payments->avg('amount'),
            'largest_payment' => $payments->max('amount'),
            'smallest_payment' => $payments->min('amount'),
            'first_payment_date' => $payments->min('payment_date'),
            'last_payment_date' => $payments->max('payment_date'),
            'quarters_with_payments' => $payments->groupBy(function($payment) {
                return $payment->year . '-' . $payment->quarter;
            })->count(),
            'years_with_payments' => $payments->pluck('year')->unique()->count()
        ];
    }

    public static function getMonthlyBreakdown($contractId, $year)
    {
        $payments = static::where('contract_id', $contractId)
            ->where('year', $year)
            ->get()
            ->groupBy(function($payment) {
                return $payment->payment_date->month;
            });

        $breakdown = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthPayments = $payments->get($month, collect([]));
            $breakdown[$month] = [
                'month' => $month,
                'month_name' => Carbon::create($year, $month, 1)->format('F'),
                'total_amount' => $monthPayments->sum('amount'),
                'payment_count' => $monthPayments->count(),
                'payments' => $monthPayments->values()
            ];
        }

        return $breakdown;
    }

    public static function getQuarterlyComparison($contractId)
    {
        $payments = static::where('contract_id', $contractId)->get();
        $quarters = $payments->groupBy(function($payment) {
            return $payment->year . '-Q' . $payment->quarter;
        });

        $comparison = [];
        foreach ($quarters as $quarter => $quarterPayments) {
            $comparison[$quarter] = [
                'quarter' => $quarter,
                'total_amount' => $quarterPayments->sum('amount'),
                'payment_count' => $quarterPayments->count(),
                'average_payment' => $quarterPayments->avg('amount'),
                'first_payment' => $quarterPayments->min('payment_date'),
                'last_payment' => $quarterPayments->max('payment_date')
            ];
        }

        return collect($comparison)->sortKeys();
    }

    // Export helpers
    public function toExportArray()
    {
        return [
            'contract_number' => $this->contract->contract_number,
            'payment_date' => $this->payment_date->format('d.m.Y'),
            'year' => $this->year,
            'quarter' => $this->quarter,
            'quarter_name' => $this->quarter_name,
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'amount_millions' => $this->amount_in_millions,
            'payment_number' => $this->payment_number,
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('d.m.Y H:i')
        ];
    }

    // Search functionality
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
}
