<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\PaymentSchedule;
use App\Models\ActualPayment;
use App\Models\ContractAmendment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractPaymentService
{
    /**
     * Get comprehensive contract data with all payment information
     */
    public function getContractPaymentData(Contract $contract): array
    {
        if (!$contract->relationLoaded('status')) {
            $contract->load('status');
        }
        return [
            'contract' => $this->formatContractData($contract),
            'quarterly_breakdown' => $this->getQuarterlyBreakdown($contract),
            'summary_cards' => $this->getSummaryCards($contract),
            'payment_history' => $this->getPaymentHistory($contract),
            'initial_payments' => $this->getInitialPayments($contract),
            'amendments' => $this->getAmendments($contract),
            'available_years' => $this->getAvailableYears($contract),
            'quarter_options' => $this->getQuarterOptions(),
            'status_id' => $contract->status_id, // ADD THIS LINE
            'status' => $contract->status ? [ // ADD THIS BLOCK
                'id' => $contract->status->id,
                'name_uz' => $contract->status->name_uz,
                'name_ru' => $contract->status->name_ru,
                'code' => $contract->status->code,
                'color' => $contract->status->color,
            ] : null,
        ];
    }

    /**
     * Format contract data for frontend consumption
     */
    private function formatContractData(Contract $contract): array
    {
        $contractDate = Carbon::parse($contract->contract_date);

        // Use current amounts after amendments
        $currentTotalAmount = $this->getCurrentContractAmount($contract);
        $currentInitialPercent = $this->getCurrentInitialPercent($contract);

        $initialPaymentAmount = $currentTotalAmount * ($currentInitialPercent / 100);
        $remainingAmount = $currentTotalAmount - $initialPaymentAmount;

        return [
            'id' => $contract->id,
            'contract_number' => $contract->contract_number,
            'contract_date' => $contractDate->format('Y-m-d'),
            'contract_date_formatted' => $contractDate->format('d.m.Y'),
            'completion_date' => $contract->completion_date?->format('Y-m-d'),
            'total_amount' => $currentTotalAmount, // Use current amount
            'total_amount_formatted' => $this->formatCurrency($currentTotalAmount),
            'payment_type' => $contract->payment_type,
            'initial_payment_percent' => $currentInitialPercent,
            'construction_period_years' => $contract->construction_period_years ?? 2,
            'quarters_count' => $contract->quarters_count ?? 8,
            'initial_payment_amount' => $initialPaymentAmount,
            'initial_payment_formatted' => $this->formatCurrency($initialPaymentAmount),
            'remaining_amount' => $remainingAmount,
            'remaining_amount_formatted' => $this->formatCurrency($remainingAmount),
            'quarterly_amount' => $contract->quarters_count > 0 ? $remainingAmount / $contract->quarters_count : 0,
            'quarterly_amount_formatted' => $contract->quarters_count > 0 ?
                $this->formatCurrency($remainingAmount / $contract->quarters_count) : $this->formatCurrency(0),
            'contract_year' => $contractDate->year,
            'contract_quarter' => ceil($contractDate->month / 3),
            'status' => $this->calculateContractStatus($contract),
            'has_amendments' => $contract->amendments()->count() > 0,
            'amendment_count' => $contract->amendments()->count()
        ];
    }

    /**
     * Get quarterly breakdown with payment status including amendments and initial payments
     */
    public function getQuarterlyBreakdown(Contract $contract): array
    {
        // Get payment schedules (excluding initial payment schedule)
        $schedules = PaymentSchedule::where('contract_id', $contract->id)
            ->where('is_active', true)
            ->where('is_initial_payment', false)
            ->with(['actualPayments', 'amendment'])
            ->orderBy('year')
            ->orderBy('quarter')
            ->get();

        $breakdown = [];

        foreach ($schedules as $schedule) {
            $year = $schedule->year;
            $quarter = $schedule->quarter;

            if (!isset($breakdown[$year])) {
                $breakdown[$year] = [];
            }

            // Get actual payments for this quarter (only quarterly payments)
            $actualPayments = ActualPayment::where('contract_id', $contract->id)
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->where('is_initial_payment', false)
                ->where('payment_category', 'quarterly')
                ->get();

            $factTotal = $actualPayments->sum('amount');
            $planAmount = $schedule->quarter_amount;
            $debt = $planAmount - $factTotal;
            $paymentPercent = $planAmount > 0 ? ($factTotal / $planAmount) * 100 : 0;
            $isOverdue = $this->isQuarterOverdue($year, $quarter);

            $breakdown[$year][$quarter] = [
                'id' => $schedule->id,
                'plan_amount' => $planAmount,
                'plan_amount_formatted' => $this->formatCurrency($planAmount),
                'fact_total' => $factTotal,
                'fact_total_formatted' => $this->formatCurrency($factTotal),
                'debt' => $debt,
                'debt_formatted' => $this->formatCurrency(abs($debt)),
                'payment_percent' => round($paymentPercent, 1),
                'is_overdue' => $isOverdue,
                'status' => $this->getQuarterStatus($paymentPercent, $debt, $isOverdue),
                'status_class' => $this->getQuarterStatusClass($paymentPercent, $debt, $isOverdue),
                'progress_color' => $this->getProgressColor($paymentPercent),
                'payments' => $this->formatPayments($actualPayments),
                'is_amendment_based' => !is_null($schedule->amendment_id),
                'amendment_info' => $schedule->amendment ? [
                    'id' => $schedule->amendment->id,
                    'number' => $schedule->amendment->amendment_number,
                    'date' => $schedule->amendment->amendment_date->format('d.m.Y')
                ] : null,
                'quarter_info' => [
                    'year' => $year,
                    'quarter' => $quarter,
                    'start_date' => Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->format('d.m.Y'),
                    'end_date' => Carbon::create($year, $quarter * 3, 1)->endOfMonth()->format('d.m.Y')
                ]
            ];
        }

        // Group by years and add year totals
        $yearlyBreakdown = [];
        foreach ($breakdown as $year => $quarters) {
            $yearTotals = $this->calculateYearTotals($quarters);
            $yearlyBreakdown[$year] = [
                'quarters' => $quarters,
                'totals' => $yearTotals
            ];
        }

        return $yearlyBreakdown;
    }

    /**
     * Get initial payments data
     */
    /**
     * Get initial payments data
     */
    public function getInitialPayments(Contract $contract): ?array
    {
        if ($contract->payment_type === 'full') {
            return null;
        }

        // Use current amounts after amendments
        $currentTotalAmount = $this->getCurrentContractAmount($contract);
        $currentInitialPercent = $this->getCurrentInitialPercent($contract);
        $initialPaymentAmount = $currentTotalAmount * ($currentInitialPercent / 100);

        $initialPayments = ActualPayment::where('contract_id', $contract->id)
            ->where('is_initial_payment', true)
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalPaid = $initialPayments->sum('amount');
        $remaining = max(0, $initialPaymentAmount - $totalPaid);
        $paymentPercent = $initialPaymentAmount > 0 ? ($totalPaid / $initialPaymentAmount) * 100 : 0;

        return [
            'plan_amount' => $initialPaymentAmount,
            'plan_amount_formatted' => $this->formatCurrency($initialPaymentAmount),
            'total_paid' => $totalPaid,
            'total_paid_formatted' => $this->formatCurrency($totalPaid),
            'remaining' => $remaining,
            'remaining_formatted' => $this->formatCurrency($remaining),
            'payment_percent' => round($paymentPercent, 1),
            'is_completed' => $remaining <= 0,
            'status' => $remaining <= 0 ? 'Yakunlangan' : ($totalPaid > 0 ? 'Qisman to\'langan' : 'To\'lanmagan'),
            'status_class' => $remaining <= 0 ? 'completed' : ($totalPaid > 0 ? 'partial' : 'pending'),
            'payments' => $this->formatPayments($initialPayments),
            'payments_count' => $initialPayments->count()
        ];
    }
    /**
     * Calculate current debt (unpaid scheduled amounts)
     */
    private function calculateCurrentDebt(Contract $contract): float
    {
        $currentTotalAmount = $this->getCurrentContractAmount($contract);

        if ($contract->payment_type === 'full') {
            return $currentTotalAmount - $contract->payments()->sum('amount');
        }

        $scheduledAmount = PaymentSchedule::where('contract_id', $contract->id)
            ->where('is_active', true)
            ->sum('quarter_amount');

        $paidAmount = $contract->payments()->sum('amount');

        return max(0, $scheduledAmount - $paidAmount);
    }

    /**
     * Calculate overdue debt
     */
    private function calculateOverdueDebt(Contract $contract): float
    {
        $currentTotalAmount = $this->getCurrentContractAmount($contract);

        if ($contract->payment_type === 'full') {
            if ($contract->completion_date && Carbon::parse($contract->completion_date)->isPast()) {
                $remainingDebt = $currentTotalAmount - $contract->payments()->sum('amount');
                return max(0, $remainingDebt);
            }
            return 0;
        }

        $now = now();
        $overdueSchedules = PaymentSchedule::where('contract_id', $contract->id)
            ->where('is_active', true)
            ->where('is_initial_payment', false)
            ->where(function ($query) use ($now) {
                $query->where('year', '<', $now->year)
                    ->orWhere(function ($q) use ($now) {
                        $q->where('year', '=', $now->year)
                            ->where('quarter', '<', ceil($now->month / 3));
                    });
            })
            ->get();

        $totalOverdue = 0;
        foreach ($overdueSchedules as $schedule) {
            $paid = ActualPayment::where('contract_id', $contract->id)
                ->where('year', $schedule->year)
                ->where('quarter', $schedule->quarter)
                ->where('is_initial_payment', false)
                ->sum('amount');

            $debt = max(0, $schedule->quarter_amount - $paid);
            $totalOverdue += $debt;
        }

        return $totalOverdue;
    }
    /**
     * Get summary cards data with separate initial payment tracking
     */
public function getSummaryCards(Contract $contract): array
{
    // Get the current total amount (after amendments)
    $currentTotalAmount = $this->getCurrentContractAmount($contract);
    $totalPaid = $contract->payments()->sum('amount');

    // For full payment type
    if ($contract->payment_type === 'full') {
        $planAmount = $currentTotalAmount;
        $remainingDebt = $currentTotalAmount - $totalPaid;

        // Calculate overdue for full payment if completion date has passed
        $overdueDebt = 0;
        if ($contract->completion_date && Carbon::parse($contract->completion_date)->isPast() && $remainingDebt > 0) {
            $overdueDebt = $remainingDebt;
        }

        return [
            'total_plan_formatted' => number_format($planAmount, 0, '.', ' ') . ' so\'m',
            'total_paid_formatted' => number_format($totalPaid, 0, '.', ' ') . ' so\'m',
            'initial_payment_plan_formatted' => '0 so\'m',
            'initial_payment_paid_formatted' => '0 so\'m',
            'quarterly_plan_formatted' => '0 so\'m',
            'quarterly_paid_formatted' => '0 so\'m',
            'current_debt_formatted' => number_format($remainingDebt, 0, '.', ' ') . ' so\'m',
            'overdue_debt_formatted' => number_format($overdueDebt, 0, '.', ' ') . ' so\'m',
            'completion_percent' => $currentTotalAmount > 0 ? round(($totalPaid / $currentTotalAmount) * 100, 1) : 0,
        ];
    }

    // For installment payment type - use current amounts after amendments
    $currentInitialPercent = $this->getCurrentInitialPercent($contract);
    $currentQuartersCount = $this->getCurrentQuartersCount($contract);

    $initialPaymentPlan = $currentTotalAmount * ($currentInitialPercent / 100);
    $remainingAmountAfterInitial = $currentTotalAmount - $initialPaymentPlan;

    // Calculate quarterly plan based on current amounts
    $quarterlyPlan = $currentQuartersCount > 0 ? $remainingAmountAfterInitial : 0;

    $initialPaymentPaid = $contract->payments()
        ->where('is_initial_payment', true)
        ->sum('amount');

    $quarterlyPaid = $contract->payments()
        ->where('is_initial_payment', false)
        ->sum('amount');

    $totalPlanAmount = $initialPaymentPlan + $quarterlyPlan;

    // Calculate debts using current amounts
    $currentDebt = max(0, $totalPlanAmount - $totalPaid);
    $overdueDebt = $this->calculateOverdueDebt($contract);

    return [
        'total_plan_formatted' => number_format($totalPlanAmount, 0, '.', ' ') . ' so\'m',
        'total_paid_formatted' => number_format($totalPaid, 0, '.', ' ') . ' so\'m',
        'initial_payment_plan_formatted' => number_format($initialPaymentPlan, 0, '.', ' ') . ' so\'m',
        'initial_payment_paid_formatted' => number_format($initialPaymentPaid, 0, '.', ' ') . ' so\'m',
        'quarterly_plan_formatted' => number_format($quarterlyPlan, 0, '.', ' ') . ' so\'m',
        'quarterly_paid_formatted' => number_format($quarterlyPaid, 0, '.', ' ') . ' so\'m',
        'current_debt_formatted' => number_format($currentDebt, 0, '.', ' ') . ' so\'m',
        'overdue_debt_formatted' => number_format($overdueDebt, 0, '.', ' ') . ' so\'m',
        'completion_percent' => $totalPlanAmount > 0 ? round(($totalPaid / $totalPlanAmount) * 100, 1) : 0,
    ];
}

private function getCurrentQuartersCount(Contract $contract): int
{
    $latestApprovedAmendment = $contract->amendments()
        ->where('is_approved', true)
        ->whereNotNull('new_quarters_count')
        ->orderBy('amendment_date', 'desc')
        ->first();

    return $latestApprovedAmendment ? $latestApprovedAmendment->new_quarters_count : ($contract->quarters_count ?? 8);
}

    private function getCurrentInitialPercent(Contract $contract): float
    {
        $latestApprovedAmendment = $contract->amendments()
            ->where('is_approved', true)
            ->whereNotNull('new_initial_payment_percent')
            ->orderBy('amendment_date', 'desc')
            ->first();

        return $latestApprovedAmendment ? $latestApprovedAmendment->new_initial_payment_percent : ($contract->initial_payment_percent ?? 20);
    }

    private function getCurrentContractAmount(Contract $contract): float
    {
        $latestApprovedAmendment = $contract->amendments()
            ->where('is_approved', true)
            ->whereNotNull('new_total_amount')
            ->orderBy('amendment_date', 'desc')
            ->first();

        return $latestApprovedAmendment ? $latestApprovedAmendment->new_total_amount : $contract->total_amount;
    }

    /**
     * Create payment schedule - Enhanced version with initial payment logic
     */
    /**
     * Create payment schedule - Enhanced version with initial payment logic
     */
    public function createPaymentSchedule(Contract $contract, array $data): array
    {
        try {
            DB::beginTransaction();

            // Clear existing active schedules if not amendment-based
            if (!isset($data['amendment_id'])) {
                PaymentSchedule::where('contract_id', $contract->id)
                    ->where('is_active', true)
                    ->whereNull('amendment_id')
                    ->update(['is_active' => false]);
            }

            $scheduleType = $data['schedule_type'];
            $quartersCount = (int) $data['quarters_count'];
            $totalAmount = (float) $data['total_schedule_amount'];
            $amendmentId = $data['amendment_id'] ?? null;

            $contractDate = Carbon::parse($contract->contract_date);
            $currentYear = $contractDate->year;
            $currentQuarter = ceil($contractDate->month / 3);

            // Create initial payment schedule first
            if (!$amendmentId) {
                $initialPaymentAmount = $contract->total_amount * (($contract->initial_payment_percent ?? 20) / 100);

                PaymentSchedule::create([
                    'contract_id' => $contract->id,
                    'year' => $currentYear,
                    'quarter' => 0, // Special quarter for initial payment
                    'quarter_amount' => $initialPaymentAmount,
                    'is_initial_payment' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Validate custom percentages if needed
            if ($scheduleType === 'custom') {
                $totalPercent = 0;
                for ($i = 1; $i <= $quartersCount; $i++) {
                    $percent = (float) ($data["quarter_{$i}_percent"] ?? 0);
                    $totalPercent += $percent;
                }

                if (abs($totalPercent - 100) > 0.1) {
                    throw new \Exception("Foizlar yig'indisi 100% bo'lishi kerak. Joriy: {$totalPercent}%");
                }
            }

            // Create quarterly schedules
            $quarterlySchedule = [];

            for ($i = 0; $i < $quartersCount; $i++) {
                $quarterAmount = 0;

                if ($scheduleType === 'auto') {
                    $quarterAmount = $totalAmount / $quartersCount;
                } else {
                    // FIXED: Use the amount directly from the form instead of calculating from percent
                    $quarterAmount = (float) ($data["quarter_" . ($i + 1) . "_amount"] ?? 0);

                    // Fallback to percent calculation if amount is not provided
                    if ($quarterAmount <= 0) {
                        $percent = (float) ($data["quarter_" . ($i + 1) . "_percent"] ?? 0);
                        $quarterAmount = $totalAmount * ($percent / 100);
                    }
                }

                $scheduleData = [
                    'contract_id' => $contract->id,
                    'year' => $currentYear,
                    'quarter' => $currentQuarter,
                    'quarter_amount' => (float) $quarterAmount, // Ensure it stays as float
                    'is_initial_payment' => false,
                    'custom_percent' => $scheduleType === 'custom' ?
                        (float) ($data["quarter_" . ($i + 1) . "_percent"] ?? 0) : null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                // Add amendment_id if provided
                if ($amendmentId) {
                    $scheduleData['amendment_id'] = $amendmentId;
                }

                $quarterlySchedule[] = $scheduleData;

                $currentQuarter++;
                if ($currentQuarter > 4) {
                    $currentQuarter = 1;
                    $currentYear++;
                }
            }

            PaymentSchedule::insert($quarterlySchedule);

            // Update contract quarters count if not amendment
            if (!$amendmentId) {
                $contract->update(['quarters_count' => $quartersCount]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => $amendmentId ?
                    'Qo\'shimcha kelishuv jadvali muvaffaqiyatli yaratildi' :
                    'To\'lov jadvali muvaffaqiyatli yaratildi',
                'schedule_count' => count($quarterlySchedule) + ($amendmentId ? 0 : 1)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment schedule creation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Jadval yaratishda xatolik: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add payment to contract - Enhanced version with initial payment logic
     */
    public function addPayment(Contract $contract, array $data): array
    {
        try {
            DB::beginTransaction();

            $paymentDate = Carbon::parse($data['payment_date']);
            $paymentAmount = (float) $data['payment_amount'];
            $paymentCategory = $data['payment_category'] ?? 'quarterly';

            // Validate payment amount
            if ($paymentAmount <= 0) {
                throw new \Exception('To\'lov summasi 0 dan katta bo\'lishi kerak');
            }

            // Validate payment date
            if ($paymentDate->lt($contract->contract_date)) {
                throw new \Exception('To\'lov sanasi shartnoma sanasidan oldin bo\'lishi mumkin emas');
            }

            $isInitialPayment = $paymentCategory === 'initial';

            // Additional validation for initial payment
            if ($isInitialPayment) {
                $initialPaymentLimit = $contract->total_amount * (($contract->initial_payment_percent ?? 20) / 100);
                $alreadyPaidInitial = ActualPayment::where('contract_id', $contract->id)
                    ->where('is_initial_payment', true)
                    ->sum('amount');

                $remainingInitialPayment = $initialPaymentLimit - $alreadyPaidInitial;

                if ($paymentAmount > $remainingInitialPayment) {
                    throw new \Exception(
                        'Boshlang\'ich to\'lov summasi qolgan miqdordan (' .
                            $this->formatCurrency($remainingInitialPayment) . ') oshmasligi kerak'
                    );
                }

                if ($remainingInitialPayment <= 0) {
                    throw new \Exception('Boshlang\'ich to\'lov allaqachon to\'liq to\'langan');
                }
            }

            $targetQuarter = null;

            if ($isInitialPayment) {
                // For initial payments, use special quarter 0
                $targetQuarter = [
                    'year' => $paymentDate->year,
                    'quarter' => 0
                ];
            } else {
                // Determine target quarter for regular payments
                $targetQuarter = $this->determineTargetQuarter($contract, $paymentDate, $data);

                if (!$targetQuarter) {
                    throw new \Exception('Ushbu sana uchun to\'lov jadvali topilmadi');
                }
            }

            // Check for duplicate payment numbers
            if (!empty($data['payment_number'])) {
                $existingPayment = ActualPayment::where('contract_id', $contract->id)
                    ->where('payment_number', $data['payment_number'])
                    ->first();

                if ($existingPayment) {
                    throw new \Exception('Bu hujjat raqami bilan to\'lov allaqachon mavjud');
                }
            }

            // Create payment
            $payment = ActualPayment::create([
                'contract_id' => $contract->id,
                'payment_date' => $paymentDate,
                'amount' => $paymentAmount,
                'year' => $targetQuarter['year'],
                'quarter' => $targetQuarter['quarter'],
                'is_initial_payment' => $isInitialPayment,
                'payment_category' => $paymentCategory,
                'payment_number' => $data['payment_number'] ?? null,
                'notes' => $data['payment_notes'] ?? null,
                'created_by' => auth()->id() ?? 1
            ]);

            DB::commit();

            $message = $isInitialPayment ?
                "Boshlang'ich to'lov muvaffaqiyatli qo'shildi" :
                "To'lov muvaffaqiyatli qo'shildi: {$targetQuarter['quarter']}-chorak {$targetQuarter['year']}";

            return [
                'success' => true,
                'message' => $message,
                'payment' => [
                    'id' => $payment->id,
                    'amount' => $paymentAmount,
                    'amount_formatted' => $this->formatCurrency($paymentAmount),
                    'date' => $paymentDate->format('d.m.Y'),
                    'quarter' => $targetQuarter['quarter'],
                    'year' => $targetQuarter['year'],
                    'is_initial_payment' => $isInitialPayment
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment addition failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage() // Remove the prefix to show clean error message
            ];
        }
    }

    /**
     * Update existing payment - Enhanced version
     */
    /**
     * Update existing payment - Enhanced version
     */
    public function updatePayment(ActualPayment $payment, array $data): array
    {
        try {
            DB::beginTransaction();

            $paymentDate = Carbon::parse($data['payment_date']);
            $paymentAmount = (float) $data['payment_amount'];

            // Validate payment amount
            if ($paymentAmount <= 0) {
                throw new \Exception('To\'lov summasi 0 dan katta bo\'lishi kerak');
            }

            // Determine new target quarter if date changed and not initial payment
            if ($payment->is_initial_payment) {
                $targetQuarter = [
                    'year' => $paymentDate->year,
                    'quarter' => 0
                ];
            } else {
                if ($paymentDate->format('Y-m-d') !== $payment->payment_date->format('Y-m-d')) {
                    $targetQuarter = $this->determineTargetQuarter($payment->contract, $paymentDate, $data);
                    if (!$targetQuarter) {
                        throw new \Exception('Yangi sana uchun to\'lov jadvali topilmadi');
                    }
                } else {
                    $targetQuarter = ['year' => $payment->year, 'quarter' => $payment->quarter];
                }
            }

            // Check for duplicate payment numbers (excluding current payment)
            if (!empty($data['payment_number']) && $data['payment_number'] !== $payment->payment_number) {
                $existingPayment = ActualPayment::where('contract_id', $payment->contract_id)
                    ->where('payment_number', $data['payment_number'])
                    ->where('id', '!=', $payment->id)
                    ->first();

                if ($existingPayment) {
                    throw new \Exception('Bu hujjat raqami bilan to\'lov allaqachon mavjud');
                }
            }

            // Update payment
            $payment->update([
                'payment_date' => $paymentDate,
                'amount' => $paymentAmount,
                'year' => $targetQuarter['year'],
                'quarter' => $targetQuarter['quarter'],
                'payment_number' => $data['payment_number'] ?? null,
                'notes' => $data['payment_notes'] ?? null,
                'updated_by' => auth()->id() ?? 1
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'To\'lov muvaffaqiyatli yangilandi',
                'payment' => [
                    'id' => $payment->id,
                    'amount' => $paymentAmount,
                    'amount_formatted' => $this->formatCurrency($paymentAmount),
                    'date' => $paymentDate->format('d.m.Y'),
                    'quarter' => $targetQuarter['quarter'],
                    'year' => $targetQuarter['year']
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment update failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'To\'lov yangilashda xatolik: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete payment
     */
    public function deletePayment(ActualPayment $payment): array
    {
        try {
            DB::beginTransaction();

            $paymentInfo = [
                'amount' => $this->formatCurrency($payment->amount),
                'date' => $payment->payment_date->format('d.m.Y'),
                'type' => $payment->is_initial_payment ? 'Boshlang\'ich to\'lov' : "{$payment->quarter}-chorak {$payment->year}"
            ];

            $payment->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => "To'lov o'chirildi: {$paymentInfo['amount']} ({$paymentInfo['date']}) - {$paymentInfo['type']}"
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment deletion failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'To\'lov o\'chirishda xatolik: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create contract amendment
     */
    public function createAmendment(Contract $contract, array $data): array
    {
        try {
            DB::beginTransaction();

            // Enhanced validation for amendment number format
            $baseContractNumber = $contract->contract_number;
            if (strpos($baseContractNumber, '(') !== false) {
                $baseContractNumber = preg_replace('/\(\d+\)$/', '', $baseContractNumber);
            }

            // Validate amendment number format
            if (!preg_match('/^.+\(\d+\)$/', $data['amendment_number'])) {
                throw new \Exception('Kelishuv raqami formati noto\'g\'ri. Misol: ' . $baseContractNumber . '(1)');
            }

            // Check for duplicate amendment numbers
            $existingAmendment = ContractAmendment::where('contract_id', $contract->id)
                ->where('amendment_number', $data['amendment_number'])
                ->first();

            if ($existingAmendment) {
                throw new \Exception('Bu raqamli qo\'shimcha kelishuv allaqachon mavjud: ' . $data['amendment_number']);
            }

            // Advanced financial validation
            if (isset($data['new_total_amount'])) {
                $totalPaid = ActualPayment::where('contract_id', $contract->id)->sum('amount');
                if ($data['new_total_amount'] < $totalPaid) {
                    throw new \Exception(
                        "Yangi shartnoma summasi ({$this->formatCurrency($data['new_total_amount'])}) " .
                            "allaqachon to'langan summadan ({$this->formatCurrency($totalPaid)}) kam bo'lishi mumkin emas"
                    );
                }

                // Check if the change is significant (less than 1% change might be a mistake)
                $percentChange = abs(($data['new_total_amount'] - $contract->total_amount) / $contract->total_amount) * 100;
                if ($percentChange < 0.1) {
                    Log::warning('Amendment with minimal amount change created', [
                        'contract_id' => $contract->id,
                        'old_amount' => $contract->total_amount,
                        'new_amount' => $data['new_total_amount'],
                        'percent_change' => $percentChange
                    ]);
                }
            }

            // Validate quarters count
            if (isset($data['new_quarters_count'])) {
                if ($data['new_quarters_count'] > 40) {
                    throw new \Exception('Choraklar soni 40 tadan ko\'p bo\'lishi mumkin emas');
                }
            }

            // Calculate impact summary
            $impactSummary = $this->calculateAmendmentImpact($contract, $data);

            $amendment = ContractAmendment::create([
                'contract_id' => $contract->id,
                'amendment_number' => $data['amendment_number'],
                'amendment_date' => $data['amendment_date'],
                'new_total_amount' => $data['new_total_amount'] ?? null,
                'new_completion_date' => $data['new_completion_date'] ?? null,
                'new_initial_payment_percent' => $data['new_initial_payment_percent'] ?? null,
                'new_quarters_count' => $data['new_quarters_count'] ?? null,
                'reason' => $data['reason'],
                'description' => $data['description'] ?? null,
                'impact_summary' => json_encode($impactSummary),
                'is_approved' => false,
                'created_by' => auth()->id() ?? 1
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "Qo'shimcha kelishuv '{$amendment->amendment_number}' muvaffaqiyatli yaratildi",
                'amendment' => $amendment,
                'impact_summary' => $impactSummary
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Amendment creation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function calculateAmendmentImpact(Contract $contract, array $data): array
    {
        $impact = [];

        // Financial impact
        if (isset($data['new_total_amount'])) {
            $oldAmount = $contract->total_amount;
            $newAmount = (float) $data['new_total_amount'];
            $difference = $newAmount - $oldAmount;
            $percentChange = ($difference / $oldAmount) * 100;

            $impact['financial'] = [
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
                'difference' => $difference,
                'percent_change' => round($percentChange, 2),
                'impact_level' => $this->getImpactLevel($percentChange)
            ];

            // Calculate new payment breakdown
            $newInitialPercent = $data['new_initial_payment_percent'] ?? $contract->initial_payment_percent ?? 20;
            $newQuarters = $data['new_quarters_count'] ?? $contract->quarters_count ?? 8;

            $newInitialAmount = $newAmount * ($newInitialPercent / 100);
            $newRemainingAmount = $newAmount - $newInitialAmount;
            $newQuarterlyAmount = $newQuarters > 0 ? $newRemainingAmount / $newQuarters : 0;

            $impact['payment_structure'] = [
                'old_initial_amount' => $contract->total_amount * (($contract->initial_payment_percent ?? 20) / 100),
                'new_initial_amount' => $newInitialAmount,
                'old_quarterly_amount' => $contract->quarters_count > 0 ?
                    ($contract->total_amount * (100 - ($contract->initial_payment_percent ?? 20)) / 100) / $contract->quarters_count : 0,
                'new_quarterly_amount' => $newQuarterlyAmount
            ];
        }

        // Schedule impact
        if (isset($data['new_quarters_count'])) {
            $oldQuarters = $contract->quarters_count ?? 8;
            $newQuarters = (int) $data['new_quarters_count'];

            $impact['schedule'] = [
                'old_quarters' => $oldQuarters,
                'new_quarters' => $newQuarters,
                'quarters_difference' => $newQuarters - $oldQuarters,
                'duration_change_months' => ($newQuarters - $oldQuarters) * 3
            ];
        }

        // Timeline impact
        if (isset($data['new_completion_date'])) {
            $oldDate = $contract->completion_date;
            $newDate = Carbon::parse($data['new_completion_date']);

            if ($oldDate) {
                $daysDifference = $newDate->diffInDays($oldDate, false);
                $impact['timeline'] = [
                    'old_date' => $oldDate,
                    'new_date' => $newDate,
                    'days_difference' => $daysDifference,
                    'extended' => $daysDifference > 0
                ];
            }
        }

        return $impact;
    }

    private function getImpactLevel(float $percentChange): string
    {
        $absChange = abs($percentChange);

        if ($absChange < 5) return 'minimal';
        if ($absChange < 15) return 'moderate';
        if ($absChange < 30) return 'significant';
        return 'major';
    }
    /**
     * Approve amendment and apply changes
     */
    public function approveAmendment(ContractAmendment $amendment): array
    {
        try {
            DB::beginTransaction();

            $contract = $amendment->contract;

            // Re-validate against current payments
            $totalPaid = ActualPayment::where('contract_id', $contract->id)->sum('amount');

            if ($amendment->new_total_amount && $amendment->new_total_amount < $totalPaid) {
                throw new \Exception(
                    "Yangi shartnoma summasi ({$this->formatCurrency($amendment->new_total_amount)}) " .
                        "allaqachon to'langan summadan ({$this->formatCurrency($totalPaid)}) kam bo'lishi mumkin emas"
                );
            }

            // Apply changes to contract
            $appliedChanges = [];
            $updateData = [];

            if ($amendment->new_total_amount !== null) {
                $oldAmount = $contract->total_amount;
                $updateData['total_amount'] = $amendment->new_total_amount;
                $appliedChanges['total_amount'] = [
                    'old' => $oldAmount,
                    'new' => $amendment->new_total_amount,
                    'difference' => $amendment->new_total_amount - $oldAmount
                ];
            }

            if ($amendment->new_completion_date !== null) {
                $oldDate = $contract->completion_date;
                $updateData['completion_date'] = $amendment->new_completion_date;
                $appliedChanges['completion_date'] = [
                    'old' => $oldDate,
                    'new' => $amendment->new_completion_date
                ];
            }

            if ($amendment->new_initial_payment_percent !== null) {
                $oldPercent = $contract->initial_payment_percent;
                $updateData['initial_payment_percent'] = $amendment->new_initial_payment_percent;
                $appliedChanges['initial_payment_percent'] = [
                    'old' => $oldPercent,
                    'new' => $amendment->new_initial_payment_percent,
                    'difference' => $amendment->new_initial_payment_percent - $oldPercent
                ];
            }

            if ($amendment->new_quarters_count !== null) {
                $oldQuarters = $contract->quarters_count;
                $updateData['quarters_count'] = $amendment->new_quarters_count;
                $appliedChanges['quarters_count'] = [
                    'old' => $oldQuarters,
                    'new' => $amendment->new_quarters_count,
                    'difference' => $amendment->new_quarters_count - $oldQuarters
                ];
            }

            // Update contract
            if (!empty($updateData)) {
                $updateData['updated_by'] = auth()->id() ?? 1;
                $contract->update($updateData);
            }

            // Update payment schedules
            $this->updateSchedulesAfterAmendmentApproval($contract, $amendment, $appliedChanges);

            // Approve amendment
            $amendment->update([
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => auth()->id() ?? 1,
                'applied_changes' => json_encode($appliedChanges)
            ]);

            // Create audit log
            $this->createAmendmentAuditLog($contract, $amendment, $appliedChanges);

            DB::commit();

            return [
                'success' => true,
                'message' => "Qo'shimcha kelishuv '{$amendment->amendment_number}' muvaffaqiyatli tasdiqlandi",
                'applied_changes' => $appliedChanges
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Amendment approval failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Qo\'shimcha kelishuvni tasdiqlashda xatolik: ' . $e->getMessage()
            ];
        }
    }

    private function updateSchedulesAfterAmendmentApproval(Contract $contract, ContractAmendment $amendment, array $appliedChanges): void
    {
        try {
            // Update initial payment schedule if needed
            if (isset($appliedChanges['total_amount']) || isset($appliedChanges['initial_payment_percent'])) {
                $newTotalAmount = $appliedChanges['total_amount']['new'] ?? $contract->total_amount;
                $newInitialPercent = $appliedChanges['initial_payment_percent']['new'] ?? $contract->initial_payment_percent ?? 20;
                $newInitialAmount = $newTotalAmount * ($newInitialPercent / 100);

                PaymentSchedule::where('contract_id', $contract->id)
                    ->where('is_initial_payment', true)
                    ->where('is_active', true)
                    ->update([
                        'quarter_amount' => $newInitialAmount,
                        'updated_by' => auth()->id() ?? 1,
                        'amendment_impact' => "Updated by amendment {$amendment->amendment_number}"
                    ]);
            }

            // Mark old quarterly schedules as inactive if major structural changes
            if (isset($appliedChanges['total_amount']) || isset($appliedChanges['quarters_count'])) {
                PaymentSchedule::where('contract_id', $contract->id)
                    ->where('is_initial_payment', false)
                    ->whereNull('amendment_id')
                    ->update([
                        'is_active' => false,
                        'deactivated_reason' => "Superseded by amendment {$amendment->amendment_number}",
                        'deactivated_at' => now(),
                        'updated_by' => auth()->id() ?? 1
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update schedules after amendment approval', [
                'amendment_id' => $amendment->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw - main approval should still succeed
        }
    }

    private function createAmendmentAuditLog(Contract $contract, ContractAmendment $amendment, array $appliedChanges): void
    {
        try {
            $auditData = [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'amendment_id' => $amendment->id,
                'amendment_number' => $amendment->amendment_number,
                'action' => 'amendment_approved',
                'applied_changes' => $appliedChanges,
                'reason' => $amendment->reason,
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'previous_state' => [
                    'total_amount' => $appliedChanges['total_amount']['old'] ?? $contract->total_amount,
                    'initial_payment_percent' => $appliedChanges['initial_payment_percent']['old'] ?? $contract->initial_payment_percent,
                    'quarters_count' => $appliedChanges['quarters_count']['old'] ?? $contract->quarters_count,
                    'completion_date' => $appliedChanges['completion_date']['old'] ?? $contract->completion_date
                ]
            ];

            Log::info('Contract amendment approved - audit log', $auditData);

            // You can also store this in a dedicated audit table if you have one
            // ContractAuditLog::create($auditData);

        } catch (\Exception $e) {
            Log::error('Failed to create amendment audit log', [
                'amendment_id' => $amendment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    public function getAmendmentHistory(Contract $contract): array
    {
        $amendments = $contract->amendments()
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('amendment_date', 'desc')
            ->get();

        $history = [];

        foreach ($amendments as $amendment) {
            $changes = [];

            if ($amendment->new_total_amount) {
                $changes[] = "Summa: " . $this->formatCurrency($amendment->new_total_amount);
            }

            if ($amendment->new_initial_payment_percent) {
                $changes[] = "Boshlang'ich: {$amendment->new_initial_payment_percent}%";
            }

            if ($amendment->new_quarters_count) {
                $changes[] = "Choraklar: {$amendment->new_quarters_count}";
            }

            if ($amendment->new_completion_date) {
                $changes[] = "Muddat: " . $amendment->new_completion_date->format('d.m.Y');
            }

            $history[] = [
                'id' => $amendment->id,
                'amendment_number' => $amendment->amendment_number,
                'date' => $amendment->amendment_date->format('d.m.Y'),
                'reason' => $amendment->reason,
                'changes' => $changes,
                'changes_text' => implode(', ', $changes),
                'is_approved' => $amendment->is_approved,
                'status' => $amendment->is_approved ? 'Tasdiqlangan' : 'Kutilmoqda',
                'created_by' => $amendment->createdBy?->name ?? 'Noma\'lum',
                'approved_by' => $amendment->approvedBy?->name,
                'created_at' => $amendment->created_at->format('d.m.Y H:i'),
                'approved_at' => $amendment->approved_at?->format('d.m.Y H:i')
            ];
        }

        return [
            'amendments' => $history,
            'total_count' => count($history),
            'approved_count' => $amendments->where('is_approved', true)->count(),
            'pending_count' => $amendments->where('is_approved', false)->count()
        ];
    }


    public function generateAmendmentSuggestions(Contract $contract): array
    {
        $suggestions = [];

        // Analyze payment patterns
        $totalPaid = ActualPayment::where('contract_id', $contract->id)->sum('amount');
        $paymentPercent = $contract->total_amount > 0 ? ($totalPaid / $contract->total_amount) * 100 : 0;

        // Check if contract is significantly behind schedule
        if ($contract->completion_date && $contract->completion_date->isPast()) {
            if ($paymentPercent < 90) {
                $suggestions[] = [
                    'type' => 'extend_deadline',
                    'priority' => 'high',
                    'title' => 'Muddatni uzaytirish',
                    'description' => 'Shartnoma muddati o\'tgan, lekin to\'lovlar yakunlanmagan',
                    'suggested_date' => now()->addMonths(6)->format('Y-m-d')
                ];
            }
        }

        // Check for payment difficulties
        $recentPayments = ActualPayment::where('contract_id', $contract->id)
            ->where('payment_date', '>=', now()->subMonths(3))
            ->count();

        if ($recentPayments == 0 && $paymentPercent < 100) {
            $suggestions[] = [
                'type' => 'restructure_payments',
                'priority' => 'medium',
                'title' => 'To\'lov tuzilmasini o\'zgartirish',
                'description' => 'So\'nggi 3 oyda to\'lovlar amalga oshirilmagan',
                'suggested_quarters' => $contract->quarters_count + 4
            ];
        }

        // Check for overpayment
        if ($paymentPercent > 100) {
            $overpayment = $totalPaid - $contract->total_amount;
            $suggestions[] = [
                'type' => 'increase_amount',
                'priority' => 'low',
                'title' => 'Shartnoma summasini oshirish',
                'description' => 'Ortiqcha to\'lov amalga oshirilgan',
                'suggested_amount' => $totalPaid,
                'overpayment' => $overpayment
            ];
        }

        return $suggestions;
    }

    public function validateAmendmentData(Contract $contract, array $data): array
    {
        $errors = [];
        $warnings = [];

        // Validate total amount
        if (isset($data['new_total_amount'])) {
            $newAmount = (float) $data['new_total_amount'];
            $totalPaid = ActualPayment::where('contract_id', $contract->id)->sum('amount');

            if ($newAmount < $totalPaid) {
                $errors[] = "Yangi summa allaqachon to'langan summadan kam: " .
                    $this->formatCurrency($totalPaid);
            }

            $percentChange = abs(($newAmount - $contract->total_amount) / $contract->total_amount) * 100;
            if ($percentChange > 50) {
                $warnings[] = "Katta o'zgarish ({$percentChange}% dan ortiq)";
            }

            if ($percentChange < 1) {
                $warnings[] = "Juda kichik o'zgarish ({$percentChange}%)";
            }
        }

        // Validate quarters count
        if (isset($data['new_quarters_count'])) {
            $newQuarters = (int) $data['new_quarters_count'];

            if ($newQuarters < 1) {
                $errors[] = "Choraklar soni kamida 1 bo'lishi kerak";
            }

            if ($newQuarters > 40) {
                $errors[] = "Choraklar soni 40 dan ortiq bo'lmasligi kerak";
            }

            $existingPayments = ActualPayment::where('contract_id', $contract->id)
                ->where('is_initial_payment', false)
                ->distinct('year', 'quarter')
                ->count();

            if ($newQuarters < $existingPayments) {
                $warnings[] = "Yangi choraklar soni mavjud to'lovlarga mos emas";
            }
        }

        // Validate completion date
        if (isset($data['new_completion_date'])) {
            $newDate = Carbon::parse($data['new_completion_date']);

            if ($newDate->lt($contract->contract_date)) {
                $errors[] = "Yakunlash sanasi shartnoma sanasidan oldin bo'lishi mumkin emas";
            }

            if ($newDate->lt(now())) {
                $warnings[] = "Yakunlash sanasi o'tmishda";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }


    public function getAmendmentImpactPreview(Contract $contract, array $data): array
    {
        $preview = [];

        // Current state
        $currentState = [
            'total_amount' => $contract->total_amount,
            'initial_payment_percent' => $contract->initial_payment_percent ?? 20,
            'quarters_count' => $contract->quarters_count ?? 8,
            'completion_date' => $contract->completion_date
        ];

        // New state
        $newState = [
            'total_amount' => $data['new_total_amount'] ?? $currentState['total_amount'],
            'initial_payment_percent' => $data['new_initial_payment_percent'] ?? $currentState['initial_payment_percent'],
            'quarters_count' => $data['new_quarters_count'] ?? $currentState['quarters_count'],
            'completion_date' => isset($data['new_completion_date']) ?
                Carbon::parse($data['new_completion_date']) : $currentState['completion_date']
        ];

        // Calculate payment structure changes
        $currentInitialAmount = $currentState['total_amount'] * ($currentState['initial_payment_percent'] / 100);
        $currentRemainingAmount = $currentState['total_amount'] - $currentInitialAmount;
        $currentQuarterlyAmount = $currentState['quarters_count'] > 0 ?
            $currentRemainingAmount / $currentState['quarters_count'] : 0;

        $newInitialAmount = $newState['total_amount'] * ($newState['initial_payment_percent'] / 100);
        $newRemainingAmount = $newState['total_amount'] - $newInitialAmount;
        $newQuarterlyAmount = $newState['quarters_count'] > 0 ?
            $newRemainingAmount / $newState['quarters_count'] : 0;

        $preview['payment_structure'] = [
            'initial_payment' => [
                'current' => $currentInitialAmount,
                'new' => $newInitialAmount,
                'difference' => $newInitialAmount - $currentInitialAmount,
                'current_formatted' => $this->formatCurrency($currentInitialAmount),
                'new_formatted' => $this->formatCurrency($newInitialAmount)
            ],
            'quarterly_payment' => [
                'current' => $currentQuarterlyAmount,
                'new' => $newQuarterlyAmount,
                'difference' => $newQuarterlyAmount - $currentQuarterlyAmount,
                'current_formatted' => $this->formatCurrency($currentQuarterlyAmount),
                'new_formatted' => $this->formatCurrency($newQuarterlyAmount)
            ]
        ];

        // Payment impact on existing payments
        $totalPaid = ActualPayment::where('contract_id', $contract->id)->sum('amount');
        $currentDebt = $currentState['total_amount'] - $totalPaid;
        $newDebt = $newState['total_amount'] - $totalPaid;

        $preview['payment_impact'] = [
            'total_paid' => $totalPaid,
            'current_debt' => $currentDebt,
            'new_debt' => $newDebt,
            'debt_change' => $newDebt - $currentDebt,
            'total_paid_formatted' => $this->formatCurrency($totalPaid),
            'current_debt_formatted' => $this->formatCurrency($currentDebt),
            'new_debt_formatted' => $this->formatCurrency($newDebt)
        ];

        return $preview;
    }

    /**
     * Create payment schedule for amendment
     */
    public function createAmendmentSchedule(ContractAmendment $amendment, array $data): array
    {
        if (!$amendment->is_approved) {
            return [
                'success' => false,
                'message' => 'Faqat tasdiqlangan qo\'shimcha kelishuvlar uchun jadval yaratish mumkin'
            ];
        }

        $data['amendment_id'] = $amendment->id;
        return $this->createPaymentSchedule($amendment->contract, $data);
    }

    /**
     * Get contract amendments
     */
    public function getAmendments(Contract $contract): array
    {
        $amendments = ContractAmendment::where('contract_id', $contract->id)
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('amendment_date', 'desc')
            ->get();

        $amendmentData = [];
        $originalValues = [
            'total_amount' => $contract->total_amount,
            'initial_payment_percent' => $contract->initial_payment_percent ?? 20,
            'quarters_count' => $contract->quarters_count ?? 8,
            'completion_date' => $contract->completion_date
        ];

        foreach ($amendments as $index => $amendment) {
            // Calculate cumulative changes up to this amendment
            $cumulativeValues = $originalValues;
            $previousAmendments = $amendments->slice($index + 1)->reverse();

            foreach ($previousAmendments as $prevAmendment) {
                if ($prevAmendment->is_approved) {
                    if ($prevAmendment->new_total_amount !== null) {
                        $cumulativeValues['total_amount'] = $prevAmendment->new_total_amount;
                    }
                    if ($prevAmendment->new_initial_payment_percent !== null) {
                        $cumulativeValues['initial_payment_percent'] = $prevAmendment->new_initial_payment_percent;
                    }
                    if ($prevAmendment->new_quarters_count !== null) {
                        $cumulativeValues['quarters_count'] = $prevAmendment->new_quarters_count;
                    }
                    if ($prevAmendment->new_completion_date !== null) {
                        $cumulativeValues['completion_date'] = $prevAmendment->new_completion_date;
                    }
                }
            }

            // Calculate changes made by this amendment
            $changes = [];
            if ($amendment->new_total_amount !== null) {
                $diff = $amendment->new_total_amount - $cumulativeValues['total_amount'];
                $changes['total_amount'] = [
                    'old' => $cumulativeValues['total_amount'],
                    'new' => $amendment->new_total_amount,
                    'diff' => $diff,
                    'diff_formatted' => ($diff > 0 ? '+' : '') . $this->formatCurrency(abs($diff)),
                    'diff_percent' => $cumulativeValues['total_amount'] > 0 ? round(($diff / $cumulativeValues['total_amount']) * 100, 2) : 0
                ];
            }

            if ($amendment->new_initial_payment_percent !== null) {
                $diff = $amendment->new_initial_payment_percent - $cumulativeValues['initial_payment_percent'];
                $changes['initial_payment_percent'] = [
                    'old' => $cumulativeValues['initial_payment_percent'],
                    'new' => $amendment->new_initial_payment_percent,
                    'diff' => $diff,
                    'diff_formatted' => ($diff > 0 ? '+' : '') . number_format(abs($diff), 1) . '%'
                ];
            }

            if ($amendment->new_quarters_count !== null) {
                $diff = $amendment->new_quarters_count - $cumulativeValues['quarters_count'];
                $changes['quarters_count'] = [
                    'old' => $cumulativeValues['quarters_count'],
                    'new' => $amendment->new_quarters_count,
                    'diff' => $diff,
                    'diff_formatted' => ($diff > 0 ? '+' : '') . $diff
                ];
            }

            if ($amendment->new_completion_date !== null) {
                $changes['completion_date'] = [
                    'old' => $cumulativeValues['completion_date'],
                    'new' => $amendment->new_completion_date,
                    'old_formatted' => $cumulativeValues['completion_date']?->format('d.m.Y') ?? 'Yo\'q',
                    'new_formatted' => $amendment->new_completion_date->format('d.m.Y')
                ];
            }

            $amendmentData[] = [
                'id' => $amendment->id,
                'amendment_number' => $amendment->amendment_number,
                'amendment_date' => $amendment->amendment_date->format('d.m.Y'),
                'amendment_date_iso' => $amendment->amendment_date->format('Y-m-d'),
                'reason' => $amendment->reason,
                'description' => $amendment->description,
                'is_approved' => $amendment->is_approved,
                'approved_at' => $amendment->approved_at?->format('d.m.Y H:i'),
                'created_at' => $amendment->created_at->format('d.m.Y H:i'),
                'created_by' => $amendment->createdBy?->name ?? 'Noma\'lum',
                'approved_by' => $amendment->approvedBy?->name,
                'status_text' => $amendment->is_approved ? 'Tasdiqlangan' : 'Kutilmoqda',
                'status_class' => $amendment->is_approved ? 'completed' : 'warning',
                'changes' => $changes,
                'has_changes' => !empty($changes),
                'sequential_number' => $amendments->count() - $index,
                'new_total_amount' => $amendment->new_total_amount,
                'new_total_amount_formatted' => $amendment->new_total_amount ? $this->formatCurrency($amendment->new_total_amount) : null,
                'new_initial_payment_percent' => $amendment->new_initial_payment_percent,
                'new_quarters_count' => $amendment->new_quarters_count,
                'new_completion_date' => $amendment->new_completion_date?->format('d.m.Y'),
                'cumulative_state' => $cumulativeValues
            ];
        }

        return $amendmentData;
    }

    /**
     * Get payment history including initial payments
     */
    public function getPaymentHistory(Contract $contract): array
    {
        $payments = ActualPayment::where('contract_id', $contract->id)
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $history = [];

        foreach ($payments as $payment) {
            $quarterInfo = $payment->is_initial_payment ?
                'Boshlang\'ich to\'lov' :
                "{$payment->quarter}-chorak {$payment->year}";

            $history[] = [
                'id' => $payment->id,
                'payment_date' => $payment->payment_date->format('d.m.Y'),
                'payment_date_iso' => $payment->payment_date->format('Y-m-d'),
                'amount' => $payment->amount,
                'amount_formatted' => $this->formatCurrency($payment->amount),
                'payment_number' => $payment->payment_number,
                'notes' => $payment->notes,
                'quarter' => $payment->quarter,
                'year' => $payment->year,
                'is_initial_payment' => $payment->is_initial_payment,
                'payment_category' => $payment->payment_category,
                'quarter_info' => $quarterInfo,
                'created_at' => $payment->created_at->format('d.m.Y H:i'),
                'created_at_human' => $payment->created_at->diffForHumans(),
                'can_edit' => $this->canEditPayment($payment),
                'actions' => $this->getPaymentActions($payment)
            ];
        }

        return [
            'payments' => $history,
            'total_count' => count($history),
            'initial_payments_count' => $payments->where('is_initial_payment', true)->count(),
            'quarterly_payments_count' => $payments->where('is_initial_payment', false)->count(),
            'total_amount' => $payments->sum('amount'),
            'total_amount_formatted' => $this->formatCurrency($payments->sum('amount')),
            'initial_payments_total' => $payments->where('is_initial_payment', true)->sum('amount'),
            'initial_payments_total_formatted' => $this->formatCurrency($payments->where('is_initial_payment', true)->sum('amount')),
            'quarterly_payments_total' => $payments->where('is_initial_payment', false)->sum('amount'),
            'quarterly_payments_total_formatted' => $this->formatCurrency($payments->where('is_initial_payment', false)->sum('amount'))
        ];
    }

    /**
     * Determine target quarter for payment - Enhanced version
     */
    private function determineTargetQuarter(Contract $contract, Carbon $paymentDate, array $data): ?array
    {
        $paymentYear = $paymentDate->year;
        $paymentQuarter = ceil($paymentDate->month / 3);

        // If explicit target provided, validate and use it
        if (isset($data['target_year']) && isset($data['target_quarter'])) {
            $targetYear = (int) $data['target_year'];
            $targetQuarter = (int) $data['target_quarter'];

            $schedule = PaymentSchedule::where('contract_id', $contract->id)
                ->where('year', $targetYear)
                ->where('quarter', $targetQuarter)
                ->where('is_active', true)
                ->where('is_initial_payment', false)
                ->first();

            if ($schedule) {
                return ['year' => $targetYear, 'quarter' => $targetQuarter];
            }
        }

        // Try to use calculated quarter first
        $directSchedule = PaymentSchedule::where('contract_id', $contract->id)
            ->where('year', $paymentYear)
            ->where('quarter', $paymentQuarter)
            ->where('is_active', true)
            ->where('is_initial_payment', false)
            ->first();

        if ($directSchedule) {
            return ['year' => $paymentYear, 'quarter' => $paymentQuarter];
        }

        // Find the closest available quarter
        $availableSchedules = PaymentSchedule::where('contract_id', $contract->id)
            ->where('is_active', true)
            ->where('is_initial_payment', false)
            ->orderBy('year')
            ->orderBy('quarter')
            ->get();

        if ($availableSchedules->isEmpty()) {
            return null;
        }

        $closest = null;
        $minDiff = PHP_INT_MAX;

        foreach ($availableSchedules as $schedule) {
            $quarterMiddle = Carbon::create($schedule->year, ($schedule->quarter - 1) * 3 + 2, 15);
            $diff = abs($quarterMiddle->diffInDays($paymentDate));

            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = ['year' => $schedule->year, 'quarter' => $schedule->quarter];
            }
        }

        return $closest;
    }

    // Helper methods
    private function calculateYearTotals(array $quarters): array
    {
        $yearPlan = 0;
        $yearPaid = 0;
        $yearDebt = 0;

        foreach ($quarters as $quarter) {
            $yearPlan += $quarter['plan_amount'];
            $yearPaid += $quarter['fact_total'];
            $yearDebt += max(0, $quarter['debt']);
        }

        return [
            'plan' => $yearPlan,
            'plan_formatted' => $this->formatCurrency($yearPlan),
            'paid' => $yearPaid,
            'paid_formatted' => $this->formatCurrency($yearPaid),
            'debt' => $yearDebt,
            'debt_formatted' => $this->formatCurrency($yearDebt),
            'percent' => $yearPlan > 0 ? round(($yearPaid / $yearPlan) * 100, 1) : 0
        ];
    }

    private function isQuarterOverdue(int $year, int $quarter): bool
    {
        if ($quarter === 0) return false; // Initial payments are never overdue based on quarter
        $quarterEnd = Carbon::create($year, $quarter * 3, 1)->endOfMonth();
        return $quarterEnd->isPast();
    }

    private function getQuarterStatus(float $percent, float $debt, bool $isOverdue): string
    {
        if ($isOverdue && $debt > 0) return 'Muddati o\'tgan';
        if ($percent >= 100) return 'To\'liq to\'langan';
        if ($percent > 0) return 'Qisman to\'langan';
        return 'To\'lanmagan';
    }

    private function getQuarterStatusClass(float $percent, float $debt, bool $isOverdue): string
    {
        if ($isOverdue && $debt > 0) return 'overdue';
        if ($percent >= 100) return 'completed';
        if ($percent > 0) return 'partial';
        return 'pending';
    }

    private function getProgressColor(float $percent): string
    {
        if ($percent >= 100) return 'bg-green-500';
        if ($percent >= 50) return 'bg-yellow-500';
        if ($percent > 0) return 'bg-blue-500';
        return 'bg-gray-300';
    }

    private function formatPayments($payments): array
    {
        return $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'date' => $payment->payment_date->format('d.m.Y'),
                'amount' => $payment->amount,
                'amount_formatted' => $this->formatCurrency($payment->amount),
                'payment_number' => $payment->payment_number,
                'notes' => $payment->notes,
                'is_initial_payment' => $payment->is_initial_payment,
                'payment_category' => $payment->payment_category
            ];
        })->toArray();
    }

    private function calculateContractStatus(Contract $contract): array
    {
        $totalPlan = $contract->total_amount;
        $totalPaid = ActualPayment::where('contract_id', $contract->id)->sum('amount');

        $percent = $totalPlan > 0 ? ($totalPaid / $totalPlan) * 100 : 0;

        return [
            'percent' => round($percent, 1),
            'total_plan' => $totalPlan,
            'total_paid' => $totalPaid,
            'remaining' => $totalPlan - $totalPaid,
            'status_text' => $percent >= 100 ? 'Yakunlangan' : ($percent > 50 ? 'Faol' : 'Boshlang\'ich')
        ];
    }

    private function getAvailableYears(Contract $contract): array
    {
        $contractDate = Carbon::parse($contract->contract_date);
        $contractYear = $contractDate->year;
        $contractQuarter = ceil($contractDate->month / 3);
        $quartersCount = $contract->quarters_count ?? 8;

        $years = [];
        $remainingQuartersInYear = 5 - $contractQuarter;
        $additionalYears = ceil(max(0, $quartersCount - $remainingQuartersInYear) / 4);

        for ($i = 0; $i <= $additionalYears; $i++) {
            $year = $contractYear + $i;
            $years[] = [
                'value' => $year,
                'label' => $year === $contractYear ?
                    "{$year} yil ({$contractQuarter}-chorakdan)" :
                    "{$year} yil"
            ];
        }

        return $years;
    }

    private function getQuarterOptions(): array
    {
        return [
            ['value' => 1, 'label' => '1-chorak (Yanvar-Mart)'],
            ['value' => 2, 'label' => '2-chorak (Aprel-Iyun)'],
            ['value' => 3, 'label' => '3-chorak (Iyul-Sentyabr)'],
            ['value' => 4, 'label' => '4-chorak (Oktyabr-Dekabr)']
        ];
    }

    private function canEditPayment(ActualPayment $payment): bool
    {
        return $payment->created_at->diffInDays(now()) <= 30;
    }

    private function getPaymentActions(ActualPayment $payment): array
    {
        $actions = ['view'];

        if ($this->canEditPayment($payment)) {
            $actions[] = 'edit';
            $actions[] = 'delete';
        }

        return $actions;
    }

    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' so\'m';
    }

    /**
     * Export contract report
     */
    public function exportReport(Contract $contract): array
    {
        $data = $this->getContractPaymentData($contract);

        return [
            'contract_info' => $data['contract'],
            'summary' => $data['summary_cards'],
            'quarterly_breakdown' => $data['quarterly_breakdown'],
            'initial_payments' => $data['initial_payments'],
            'payment_history' => $data['payment_history'],
            'amendments' => $data['amendments'],
            'export_date' => now()->format('d.m.Y H:i'),
            'export_timestamp' => now()->toISOString()
        ];
    }

    /**
     * Calculate payment breakdown preview
     */
    public function calculatePaymentBreakdown(array $contractData): array
    {
        $totalAmount = (float) ($contractData['total_amount'] ?? 0);
        $initialPercent = (float) ($contractData['initial_payment_percent'] ?? 20);
        $quartersCount = (int) ($contractData['quarters_count'] ?? 8);

        $initialAmount = $totalAmount * ($initialPercent / 100);
        $remainingAmount = $totalAmount - $initialAmount;
        $quarterlyAmount = $quartersCount > 0 ? $remainingAmount / $quartersCount : 0;

        return [
            'total_amount' => $totalAmount,
            'total_amount_formatted' => $this->formatCurrency($totalAmount),
            'initial_amount' => $initialAmount,
            'initial_amount_formatted' => $this->formatCurrency($initialAmount),
            'remaining_amount' => $remainingAmount,
            'remaining_amount_formatted' => $this->formatCurrency($remainingAmount),
            'quarterly_amount' => $quarterlyAmount,
            'quarterly_amount_formatted' => $this->formatCurrency($quarterlyAmount),
            'initial_percent' => $initialPercent,
            'quarters_count' => $quartersCount
        ];
    }
}
