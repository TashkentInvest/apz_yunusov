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
        $initialPaymentPercent = $contract->initial_payment_percent ?? 20;
        $initialPaymentAmount = $contract->total_amount * ($initialPaymentPercent / 100);
        $remainingAmount = $contract->total_amount - $initialPaymentAmount;

        return [
            'id' => $contract->id,
            'contract_number' => $contract->contract_number,
            'contract_date' => $contractDate->format('Y-m-d'),
            'contract_date_formatted' => $contractDate->format('d.m.Y'),
            'completion_date' => $contract->completion_date?->format('Y-m-d'),
            'total_amount' => $contract->total_amount,
            'total_amount_formatted' => $this->formatCurrency($contract->total_amount),
            'payment_type' => $contract->payment_type,
            'initial_payment_percent' => $initialPaymentPercent,
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
        // Return null for full payment contracts - no initial payment concept
        if ($contract->payment_type === 'full') {
            return null;
        }

        $initialPaymentAmount = $contract->total_amount * (($contract->initial_payment_percent ?? 20) / 100);

        // Get all initial payments
        $initialPayments = ActualPayment::where('contract_id', $contract->id)
            ->where('is_initial_payment', true)
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalPaid = $initialPayments->sum('amount');
        $remaining = $initialPaymentAmount - $totalPaid;
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
        if ($contract->payment_type === 'full') {
            return $contract->total_amount - $contract->payments()->sum('amount');
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
        if ($contract->payment_type === 'full') {
            // For full payment, check if completion date passed and there's unpaid balance
            if ($contract->completion_date && Carbon::parse($contract->completion_date)->isPast()) {
                $remainingDebt = $contract->total_amount - $contract->payments()->sum('amount');
                return max(0, $remainingDebt);
            }
            return 0;
        }

        $now = now();
        $overdueSchedules = PaymentSchedule::where('contract_id', $contract->id)
            ->where('is_active', true)
            ->where('is_initial_payment', false) // EXCLUDE initial payment from overdue calculation
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
            // Get actual payments for this schedule
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
        $totalAmount = $contract->total_amount;
        $totalPaid = $contract->payments()->sum('amount');

        // For full payment type
        if ($contract->payment_type === 'full') {
            $planAmount = $totalAmount;
            $remainingDebt = $totalAmount - $totalPaid;

            // Calculate overdue for full payment if completion date has passed
            $overdueDebt = 0;
            if ($contract->completion_date && Carbon::parse($contract->completion_date)->isPast() && $remainingDebt > 0) {
                $overdueDebt = $remainingDebt; // All unpaid amount is overdue after completion date
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
                'completion_percent' => $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 1) : 0,
            ];
        }

        // For installment payment type (existing logic)
        $initialPaymentPlan = $contract->initial_payment_amount;
        $initialPaymentPaid = $contract->payments()
            ->where('is_initial_payment', true)
            ->sum('amount');

        $quarterlyPlan = $contract->schedules()
            ->where('is_initial_payment', false)
            ->where('is_active', true)
            ->sum('quarter_amount');

        $quarterlyPaid = $contract->payments()
            ->where('is_initial_payment', false)
            ->sum('amount');

        $totalPlanAmount = $initialPaymentPlan + $quarterlyPlan;

        // Calculate debts
        $currentDebt = $this->calculateCurrentDebt($contract);
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
                    $percent = (float) ($data["quarter_" . ($i + 1) . "_percent"] ?? 0);
                    $quarterAmount = $totalAmount * ($percent / 100);
                }

                $scheduleData = [
                    'contract_id' => $contract->id,
                    'year' => $currentYear,
                    'quarter' => $currentQuarter,
                    'quarter_amount' => $quarterAmount,
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
                'message' => 'To\'lov qo\'shishda xatolik: ' . $e->getMessage()
            ];
        }
    }

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

            // Check for duplicate amendment numbers
            $existingAmendment = ContractAmendment::where('contract_id', $contract->id)
                ->where('amendment_number', $data['amendment_number'])
                ->first();

            if ($existingAmendment) {
                throw new \Exception('Bu raqamli qo\'shimcha kelishuv allaqachon mavjud');
            }

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
                'is_approved' => false,
                'created_by' => auth()->id() ?? 1
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Qo\'shimcha kelishuv muvaffaqiyatli yaratildi',
                'amendment' => $amendment
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Amendment creation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Qo\'shimcha kelishuv yaratishda xatolik: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Approve amendment and apply changes
     */
    public function approveAmendment(ContractAmendment $amendment): array
    {
        try {
            DB::beginTransaction();

            $contract = $amendment->contract;

            // Calculate current paid amount
            $totalPaid = ActualPayment::where('contract_id', $contract->id)->sum('amount');

            // Prepare update data
            $updateData = [];

            if ($amendment->new_total_amount !== null) {
                // Validate that new amount is not less than already paid
                if ($amendment->new_total_amount < $totalPaid) {
                    throw new \Exception("Yangi shartnoma summasi ({$this->formatCurrency($amendment->new_total_amount)}) allaqachon to'langan summadan ({$this->formatCurrency($totalPaid)}) kam bo'lishi mumkin emas");
                }
                $updateData['total_amount'] = $amendment->new_total_amount;
            }

            if ($amendment->new_completion_date !== null) {
                $updateData['completion_date'] = $amendment->new_completion_date;
            }

            if ($amendment->new_initial_payment_percent !== null) {
                $updateData['initial_payment_percent'] = $amendment->new_initial_payment_percent;
            }

            if ($amendment->new_quarters_count !== null) {
                $updateData['quarters_count'] = $amendment->new_quarters_count;
            }

            // Update contract with new values
            $contract->update($updateData);

            // Approve amendment
            $amendment->update([
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => auth()->id() ?? 1
            ]);

            // Update initial payment schedule if initial payment percent changed
            if (isset($updateData['initial_payment_percent']) || isset($updateData['total_amount'])) {
                $newInitialAmount = $contract->fresh()->total_amount * (($contract->fresh()->initial_payment_percent ?? 20) / 100);

                PaymentSchedule::where('contract_id', $contract->id)
                    ->where('is_initial_payment', true)
                    ->where('is_active', true)
                    ->update(['quarter_amount' => $newInitialAmount]);
            }

            // Deactivate old quarterly payment schedules if major changes were made
            if (isset($updateData['total_amount']) || isset($updateData['quarters_count'])) {
                PaymentSchedule::where('contract_id', $contract->id)
                    ->where('is_initial_payment', false)
                    ->whereNull('amendment_id')
                    ->update(['is_active' => false]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Qo\'shimcha kelishuv tasdiqlandi va o\'zgarishlar qo\'llanildi'
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
            ->orderBy('amendment_date', 'desc')
            ->get();

        return $amendments->map(function ($amendment) {
            return [
                'id' => $amendment->id,
                'amendment_number' => $amendment->amendment_number,
                'amendment_date' => $amendment->amendment_date->format('d.m.Y'),
                'amendment_date_iso' => $amendment->amendment_date->format('Y-m-d'),
                'new_total_amount' => $amendment->new_total_amount,
                'new_total_amount_formatted' => $amendment->new_total_amount ? $this->formatCurrency($amendment->new_total_amount) : null,
                'new_completion_date' => $amendment->new_completion_date?->format('d.m.Y'),
                'new_initial_payment_percent' => $amendment->new_initial_payment_percent,
                'new_quarters_count' => $amendment->new_quarters_count,
                'reason' => $amendment->reason,
                'description' => $amendment->description,
                'is_approved' => $amendment->is_approved,
                'approved_at' => $amendment->approved_at?->format('d.m.Y H:i'),
                'created_at' => $amendment->created_at->format('d.m.Y H:i'),
                'status_text' => $amendment->is_approved ? 'Tasdiqlangan' : 'Kutilmoqda',
                'status_class' => $amendment->is_approved ? 'completed' : 'warning'
            ];
        })->toArray();
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
