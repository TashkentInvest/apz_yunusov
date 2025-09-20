<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContractAmendment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'amendment_number',
        'amendment_date',
        'reason',
        'description',
        'old_total_amount',
        'new_total_amount',
        'old_initial_payment_percent',
        'new_initial_payment_percent',
        'old_quarters_count',
        'new_quarters_count',
        'old_construction_period_years',
        'new_construction_period_years',
        'effective_date',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'calculation_data',
        'payment_recalculation_summary'
    ];

    protected $casts = [
        'amendment_date' => 'date',
        'effective_date' => 'date',
        'approved_at' => 'datetime',
        'old_total_amount' => 'decimal:2',
        'new_total_amount' => 'decimal:2',
        'old_initial_payment_percent' => 'decimal:2',
        'new_initial_payment_percent' => 'decimal:2',
        'calculation_data' => 'array',
        'payment_recalculation_summary' => 'array'
    ];

    // Relationships
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getAmountDifferenceAttribute()
    {
        return $this->new_total_amount - $this->old_total_amount;
    }

    public function getFormattedAmountDifferenceAttribute()
    {
        $diff = $this->amount_difference;
        $symbol = $diff >= 0 ? '+' : '';
        return $symbol . number_format($diff, 0, '.', ' ') . ' so\'m';
    }

    // Methods
    public function generateAmendmentNumber()
    {
        $contract = $this->contract;
        $lastAmendment = self::where('contract_id', $contract->id)
            ->orderBy('amendment_number', 'desc')
            ->first();

        $nextNumber = $lastAmendment ? $lastAmendment->amendment_number + 1 : 1;
        return $nextNumber;
    }

    public function calculatePaymentRecalculation()
    {
        $contract = $this->contract;

        // Get existing payments
        $existingPayments = ActualPayment::where('contract_id', $contract->id)->get();

        // Calculate old contract structure
        $oldInitialPayment = $this->old_total_amount * ($this->old_initial_payment_percent / 100);
        $oldRemainingAmount = $this->old_total_amount - $oldInitialPayment;
        $oldQuarterlyAmount = $this->old_quarters_count > 0 ? $oldRemainingAmount / $this->old_quarters_count : 0;

        // Calculate new contract structure
        $newInitialPayment = $this->new_total_amount * ($this->new_initial_payment_percent / 100);
        $newRemainingAmount = $this->new_total_amount - $newInitialPayment;
        $newQuarterlyAmount = $this->new_quarters_count > 0 ? $newRemainingAmount / $this->new_quarters_count : 0;

        // Calculate paid amounts
        $totalPaidAmount = $existingPayments->sum('amount');
        $initialPaymentsMade = $existingPayments->where('is_initial_payment', true)->sum('amount');
        $quarterlyPaymentsMade = $existingPayments->where('is_initial_payment', false)->sum('amount');

        // Determine adjustments needed
        $initialPaymentAdjustment = $newInitialPayment - $initialPaymentsMade;
        $remainingAmountForQuarters = $newRemainingAmount - $quarterlyPaymentsMade;

        return [
            'old_structure' => [
                'total_amount' => $this->old_total_amount,
                'initial_payment' => $oldInitialPayment,
                'remaining_amount' => $oldRemainingAmount,
                'quarterly_amount' => $oldQuarterlyAmount,
                'quarters_count' => $this->old_quarters_count
            ],
            'new_structure' => [
                'total_amount' => $this->new_total_amount,
                'initial_payment' => $newInitialPayment,
                'remaining_amount' => $newRemainingAmount,
                'quarterly_amount' => $newQuarterlyAmount,
                'quarters_count' => $this->new_quarters_count
            ],
            'payments_made' => [
                'total_paid' => $totalPaidAmount,
                'initial_payments' => $initialPaymentsMade,
                'quarterly_payments' => $quarterlyPaymentsMade
            ],
            'adjustments' => [
                'initial_payment_adjustment' => $initialPaymentAdjustment,
                'remaining_for_quarters' => $remainingAmountForQuarters,
                'quarters_remaining' => $this->new_quarters_count,
                'new_quarterly_amount' => $this->new_quarters_count > 0 ? $remainingAmountForQuarters / $this->new_quarters_count : 0
            ],
            'calculated_at' => now()->toISOString()
        ];
    }

    public function approve($userId = null)
    {
        $this->status = 'approved';
        $this->approved_by = $userId ?? auth()->id();
        $this->approved_at = now();
        $this->save();

        // Apply changes to contract
        $this->applyToContract();

        return true;
    }

    public function applyToContract()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Amendment must be approved before applying to contract');
        }

        $contract = $this->contract;

        // Update contract with new values
        $contract->update([
            'total_amount' => $this->new_total_amount,
            'initial_payment_percent' => $this->new_initial_payment_percent,
            'quarters_count' => $this->new_quarters_count,
            'construction_period_years' => $this->new_construction_period_years,
            'last_amendment_id' => $this->id,
            'updated_at' => now()
        ]);

        // Increment amendment count
        $contract->increment('amendment_count');

        // Recalculate payment schedule
        $this->recalculatePaymentSchedule();

        return true;
    }

    private function recalculatePaymentSchedule()
    {
        $contract = $this->contract;
        $recalculation = $this->calculatePaymentRecalculation();

        // Store recalculation summary
        $this->payment_recalculation_summary = $recalculation;
        $this->save();

        // Create new payment schedule entries for remaining quarters
        $this->createNewPaymentSchedule($recalculation);

        return $recalculation;
    }

    private function createNewPaymentSchedule($recalculation)
    {
        $contract = $this->contract;
        $adjustments = $recalculation['adjustments'];

        if ($adjustments['quarters_remaining'] > 0 && $adjustments['new_quarterly_amount'] > 0) {
            // Calculate starting quarter based on amendment effective date
            $effectiveDate = $this->effective_date ?: $this->amendment_date;
            $startYear = $effectiveDate->year;
            $startMonth = $effectiveDate->month;
            $startQuarter = ceil($startMonth / 3);

            // If we're past the middle of a quarter, start from next quarter
            $dayOfMonth = $effectiveDate->day;
            if ($dayOfMonth > 15) {
                $startQuarter++;
                if ($startQuarter > 4) {
                    $startQuarter = 1;
                    $startYear++;
                }
            }

            // Create new quarterly payment schedules
            $currentYear = $startYear;
            $currentQuarter = $startQuarter;
            $quarterlyAmount = $adjustments['new_quarterly_amount'];

            for ($i = 0; $i < $adjustments['quarters_remaining']; $i++) {
                PaymentSchedule::create([
                    'contract_id' => $contract->id,
                    'year' => $currentYear,
                    'quarter' => $currentQuarter,
                    'quarter_amount' => $quarterlyAmount,
                    'sequence_number' => $i + 1,
                    'is_amendment_created' => true,
                    'amendment_id' => $this->id,
                    'is_active' => true,
                    'created_at' => now()
                ]);

                // Move to next quarter
                $currentQuarter++;
                if ($currentQuarter > 4) {
                    $currentQuarter = 1;
                    $currentYear++;
                }
            }
        }

        // Handle initial payment adjustment if needed
        if ($adjustments['initial_payment_adjustment'] != 0) {
            $this->handleInitialPaymentAdjustment($adjustments['initial_payment_adjustment']);
        }
    }



    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class, 'amendment_id');
    }

    private function handleInitialPaymentAdjustment($adjustment)
    {
        $contract = $this->contract;

        if ($adjustment > 0) {
            // Additional initial payment needed
            ActualPayment::create([
                'contract_id' => $contract->id,
                'payment_date' => $this->effective_date ?: $this->amendment_date,
                'amount' => $adjustment,
                'payment_number' => 'AMD-' . $this->amendment_number . '-INITIAL',
                'notes' => "Amendment #{$this->amendment_number} qo'shimcha boshlang'ich to'lov",
                'is_initial_payment' => true,
                'amendment_id' => $this->id,
                'year' => $this->effective_date ? $this->effective_date->year : $this->amendment_date->year,
                'quarter' => $this->effective_date ? ceil($this->effective_date->month / 3) : ceil($this->amendment_date->month / 3),
                'created_at' => now()
            ]);
        }
        // Note: Overpayment handling would require specific business logic
    }
}
