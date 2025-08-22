<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\Subject;
use App\Models\Objectt;
use App\Models\ContractStatus;
use App\Models\BaseCalculationAmount;
use App\Models\District;
use App\Models\PaymentSchedule;
use App\Models\ContractAmendment;
use App\Models\ContractCancellation;
use App\Models\CancellationReason;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with(['subject', 'object.district', 'status']);

        if ($request->contract_number) {
            $query->where('contract_number', 'like', '%' . $request->contract_number . '%');
        }

        if ($request->district_id) {
            $query->whereHas('object', function($q) use ($request) {
                $q->where('district_id', $request->district_id);
            });
        }

        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        $contracts = $query->orderBy('created_at', 'desc')->paginate(20);

        $districts = District::where('is_active', true)->get();
        $statuses = ContractStatus::where('is_active', true)->get();

        return view('contracts.index', compact('contracts', 'districts', 'statuses'));
    }

    public function create()
    {
        $subjects = Subject::where('is_active', true)->get();
        $objects = Objectt::where('is_active', true)->with(['subject', 'district'])->get();
        $statuses = ContractStatus::where('is_active', true)->get();
        $baseAmounts = BaseCalculationAmount::where('is_current', true)->get();
        $districts = District::where('is_active', true)->get();

        return view('contracts.create', compact('subjects', 'objects', 'statuses', 'baseAmounts', 'districts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_number' => 'required|unique:contracts',
            'object_id' => 'required|exists:objects,id',
            'subject_id' => 'required|exists:subjects,id',
            'contract_date' => 'required|date',
            'completion_date' => 'nullable|date|after:contract_date',
            'status_id' => 'required|exists:contract_statuses,id',
            'base_amount_id' => 'required|exists:base_calculation_amounts,id',
            'contract_volume' => 'required|numeric|min:0',
            'coefficient' => 'required|numeric|min:0',
            'payment_type' => 'required|in:full,installment',
            'initial_payment_percent' => 'required|integer|min:0|max:100',
            'construction_period_years' => 'required|integer|min:1|max:10',
        ]);

        DB::beginTransaction();
        try {
            $baseAmount = BaseCalculationAmount::find($validated['base_amount_id']);
            $totalAmount = $validated['contract_volume'] * $baseAmount->amount * $validated['coefficient'];

            $contract = Contract::create([
                ...$validated,
                'total_amount' => $totalAmount,
                'formula' => "Ҳажм: {$validated['contract_volume']} м³ × Базавий миқдор: {$baseAmount->amount} сўм × Коэффициент: {$validated['coefficient']} = {$totalAmount} сўм",
                'quarters_count' => $validated['construction_period_years'] * 4,
                'is_active' => true
            ]);

            $this->generatePaymentSchedule($contract);

            DB::commit();
            return redirect()->route('contracts.show', $contract)->with('success', 'Договор успешно создан');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    public function show(Contract $contract)
    {
        $contract->load(['subject', 'object.district', 'status', 'baseAmount', 'amendments', 'paymentSchedules' => function($q) {
            $q->where('is_active', true)->orderBy('year')->orderBy('quarter');
        }, 'actualPayments' => function($q) {
            $q->orderBy('payment_date', 'desc');
        }]);

        $penalties = $this->calculatePenalties($contract);

        return view('contracts.show', compact('contract', 'penalties'));
    }

    public function edit(Contract $contract)
    {
        $subjects = Subject::where('is_active', true)->get();
        $objects = Object::where('is_active', true)->with(['subject', 'district'])->get();
        $statuses = ContractStatus::where('is_active', true)->get();
        $baseAmounts = BaseCalculationAmount::where('is_current', true)->get();

        return view('contracts.edit', compact('contract', 'subjects', 'objects', 'statuses', 'baseAmounts'));
    }

    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'contract_number' => 'required|unique:contracts,contract_number,' . $contract->id,
            'object_id' => 'required|exists:objects,id',
            'subject_id' => 'required|exists:subjects,id',
            'contract_date' => 'required|date',
            'completion_date' => 'nullable|date|after:contract_date',
            'status_id' => 'required|exists:contract_statuses,id',
            'base_amount_id' => 'required|exists:base_calculation_amounts,id',
            'contract_volume' => 'required|numeric|min:0',
            'coefficient' => 'required|numeric|min:0',
            'payment_type' => 'required|in:full,installment',
            'initial_payment_percent' => 'required|integer|min:0|max:100',
            'construction_period_years' => 'required|integer|min:1|max:10',
        ]);

        DB::beginTransaction();
        try {
            $baseAmount = BaseCalculationAmount::find($validated['base_amount_id']);
            $totalAmount = $validated['contract_volume'] * $baseAmount->amount * $validated['coefficient'];

            $contract->update([
                ...$validated,
                'total_amount' => $totalAmount,
                'formula' => "Ҳажм: {$validated['contract_volume']} м³ × Базавий миқдор: {$baseAmount->amount} сўм × Коэффициент: {$validated['coefficient']} = {$totalAmount} сўм",
                'quarters_count' => $validated['construction_period_years'] * 4,
            ]);

            DB::commit();
            return redirect()->route('contracts.show', $contract)->with('success', 'Договор успешно обновлен');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    public function destroy(Contract $contract)
    {
        $contract->update(['is_active' => false]);
        return redirect()->route('contracts.index')->with('success', 'Договор успешно удален');
    }

    public function debtors()
    {
        $debtors = Contract::with(['subject', 'object.district', 'status'])
            ->whereHas('paymentSchedules', function($q) {
                $q->where('is_active', true)
                  ->whereRaw('quarter_amount > (SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id AND year = payment_schedules.year AND quarter = payment_schedules.quarter)');
            })
            ->where('is_active', true)
            ->paginate(20);

        return view('contracts.debtors', compact('debtors'));
    }

    public function createAmendment(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'new_volume' => 'nullable|numeric|min:0',
            'new_coefficient' => 'nullable|numeric|min:0',
            'new_base_amount_id' => 'nullable|exists:base_calculation_amounts,id',
            'bank_changes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $amendmentNumber = $contract->amendments()->count() + 1;

            $amendment = ContractAmendment::create([
                'contract_id' => $contract->id,
                'amendment_number' => $amendmentNumber,
                'amendment_date' => now(),
                'reason' => $validated['reason'],
                'old_volume' => $contract->contract_volume,
                'old_coefficient' => $contract->coefficient,
                'old_amount' => $contract->total_amount,
                'old_base_amount_id' => $contract->base_amount_id,
                'new_volume' => $validated['new_volume'] ?? $contract->contract_volume,
                'new_coefficient' => $validated['new_coefficient'] ?? $contract->coefficient,
                'new_base_amount_id' => $validated['new_base_amount_id'] ?? $contract->base_amount_id,
                'bank_changes' => $validated['bank_changes']
            ]);

            $newBaseAmount = BaseCalculationAmount::find($amendment->new_base_amount_id);
            $newTotalAmount = $amendment->new_volume * $newBaseAmount->amount * $amendment->new_coefficient;
            $amendment->update(['new_amount' => $newTotalAmount]);

            $contract->update([
                'contract_volume' => $amendment->new_volume,
                'coefficient' => $amendment->new_coefficient,
                'total_amount' => $newTotalAmount,
                'base_amount_id' => $amendment->new_base_amount_id
            ]);

            $contract->paymentSchedules()->update(['is_active' => false]);
            $this->generatePaymentScheduleForAmendment($contract, $amendment);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Дополнительное соглашение успешно создано']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
        }
    }

    public function cancel(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'cancellation_reason_id' => 'required|exists:cancellation_reasons,id',
            'notes' => 'nullable|string',
            'refund_amount' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $paidAmount = $contract->total_paid;

            ContractCancellation::create([
                'contract_id' => $contract->id,
                'cancellation_reason_id' => $validated['cancellation_reason_id'],
                'cancellation_date' => now(),
                'paid_amount' => $paidAmount,
                'refund_amount' => $validated['refund_amount'] ?? 0,
                'notes' => $validated['notes']
            ]);

            $cancelledStatus = ContractStatus::where('code', 'CANCELLED')->first();
            $contract->update(['status_id' => $cancelledStatus->id]);

            DB::commit();
            return redirect()->route('contracts.show', $contract)->with('success', 'Договор отменен');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    private function generatePaymentSchedule(Contract $contract)
    {
        $remainingAmount = $contract->total_amount * (100 - $contract->initial_payment_percent) / 100;
        $quarterAmount = $remainingAmount / $contract->quarters_count;

        $startYear = $contract->contract_date->year;
        $startQuarter = ceil($contract->contract_date->month / 3);

        for ($i = 0; $i < $contract->quarters_count; $i++) {
            $year = $startYear + floor(($startQuarter + $i - 1) / 4);
            $quarter = (($startQuarter + $i - 1) % 4) + 1;

            PaymentSchedule::create([
                'contract_id' => $contract->id,
                'year' => $year,
                'quarter' => $quarter,
                'quarter_amount' => $quarterAmount,
                'is_active' => true
            ]);
        }
    }

    private function generatePaymentScheduleForAmendment(Contract $contract, ContractAmendment $amendment)
    {
        $remainingAmount = $amendment->new_amount * (100 - $contract->initial_payment_percent) / 100;
        $quarterAmount = $remainingAmount / $contract->quarters_count;

        $startYear = $contract->contract_date->year;
        $startQuarter = ceil($contract->contract_date->month / 3);

        for ($i = 0; $i < $contract->quarters_count; $i++) {
            $year = $startYear + floor(($startQuarter + $i - 1) / 4);
            $quarter = (($startQuarter + $i - 1) % 4) + 1;

            PaymentSchedule::create([
                'contract_id' => $contract->id,
                'amendment_id' => $amendment->id,
                'year' => $year,
                'quarter' => $quarter,
                'quarter_amount' => $quarterAmount,
                'is_active' => true
            ]);
        }
    }

    private function calculatePenalties(Contract $contract)
    {
        $penalties = [];
        $totalPenalty = 0;

        $overdueSchedules = $contract->paymentSchedules()
            ->where('is_active', true)
            ->get()
            ->filter(function ($schedule) {
                $quarterEndDate = Carbon::createFromDate($schedule->year, $schedule->quarter * 3, 1)->endOfQuarter();
                return $quarterEndDate->lt(now()) && $schedule->remaining_amount > 0;
            });

        foreach ($overdueSchedules as $schedule) {
            $quarterEndDate = Carbon::createFromDate($schedule->year, $schedule->quarter * 3, 1)->endOfQuarter();
            $unpaidAmount = $schedule->remaining_amount;
            $overdueDays = $quarterEndDate->diffInDays(now());

            $penaltyAmount = $unpaidAmount * 0.0001 * $overdueDays;
            $maxPenalty = $unpaidAmount * 0.15;
            $penaltyAmount = min($penaltyAmount, $maxPenalty);

            $penalties[] = [
                'year' => $schedule->year,
                'quarter' => $schedule->quarter,
                'scheduled_amount' => $schedule->quarter_amount,
                'paid_amount' => $schedule->paid_amount,
                'unpaid_amount' => $unpaidAmount,
                'overdue_days' => $overdueDays,
                'penalty_amount' => $penaltyAmount
            ];

            $totalPenalty += $penaltyAmount;
        }

        return [
            'penalties' => $penalties,
            'total_penalty' => $totalPenalty,
            'total_debt' => $contract->remaining_debt + $totalPenalty
        ];
    }
}
