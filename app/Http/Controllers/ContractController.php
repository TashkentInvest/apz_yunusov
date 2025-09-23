<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ActualPayment;
use App\Models\ContractAmendment;
use App\Models\PaymentSchedule;
use App\Models\Subject;
use App\Models\Objectt;
use App\Services\ContractPaymentService;
use App\Services\NumberToTextService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContractController extends Controller
{
    protected ContractPaymentService $paymentService;
    protected $numberToText;

    public function __construct(ContractPaymentService $paymentService, NumberToTextService $numberToText)
    {
        $this->paymentService = $paymentService;
        $this->numberToText = $numberToText;
    }

    /**
     * Display a listing of contracts
     */
    public function index(Request $request): View
    {
        $query = Contract::with(['subject', 'object.district', 'status', 'updatedBy'])
            ->where('is_active', true);

        // Existing filters...
        if ($request->contract_number) {
            $query->where('contract_number', 'like', "%{$request->contract_number}%");
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                    ->orWhereHas('subject', function ($sq) use ($search) {
                        $sq->where('company_name', 'like', "%{$search}%")
                            ->orWhere('inn', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->district_id) {
            $query->whereHas('object', function ($q) use ($request) {
                $q->where('district_id', $request->district_id);
            });
        }

        // Completion year filter
        if ($request->completion_year) {
            $query->whereYear('completion_date', $request->completion_year);
        }

        // Calculate total amount
        $totalAmount = (clone $query)
            ->whereHas('status', function ($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->sum('total_amount');

        $activeCount = (clone $query)->whereHas('status', function ($q) {
            $q->where('code', 'ACTIVE');
        })->count();

        // Calculate quarterly amounts if year is selected
        $quarterlyAmounts = null;
        if ($request->completion_year) {
            $quarterlyAmounts = [];
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $quarter * 3;

                $amount = (clone $query)
                    ->whereHas('status', function ($q) {
                        $q->where('name_uz', '!=', 'Бекор қилинган');
                    })
                    ->whereBetween('completion_date', [
                        "{$request->completion_year}-{$startMonth}-01",
                        date('Y-m-t', strtotime("{$request->completion_year}-{$endMonth}-01"))
                    ])
                    ->sum('total_amount');

                $quarterlyAmounts[$quarter] = $amount;
            }
        }

        $contracts = $query->paginate(20)->appends($request->query());

        $statuses = \App\Models\ContractStatus::where('is_active', true)->get();
        $districts = \App\Models\District::where('is_active', true)
            ->where('name_uz', 'REGEXP', '^[А-Яа-яЎўҚқҒғҲҳ]')
            ->get();

        return view('contracts.index', compact('contracts', 'statuses', 'districts', 'totalAmount', 'activeCount', 'quarterlyAmounts'));
    }
    /**
     * Show the form for creating a new contract
     */
    public function create(): View
    {
        $subjects = Subject::where('is_active', true)->get();
        $objects = Objectt::where('is_active', true)->with('district')->get();
        $statuses = \App\Models\ContractStatus::where('is_active', true)->get();
        $baseAmounts = \App\Models\BaseCalculationAmount::where('is_active', true)->get();
        $districts = \App\Models\District::where('is_active', true)->get();

        // For payment management, use empty data structure
        $paymentData = [
            'contract' => null,
            'quarterly_breakdown' => [],
            'summary_cards' => [
                'total_plan_formatted' => '0 so\'m',
                'total_paid_formatted' => '0 so\'m',
                'current_debt_formatted' => '0 so\'m',
                'overdue_debt_formatted' => '0 so\'m'
            ],
            'initial_payments' => [],
            'payment_history' => ['payments' => []],
            'amendments' => [],
            'available_years' => [],
            'quarter_options' => []
        ];

        return view('contracts.create', compact('subjects', 'objects', 'statuses', 'baseAmounts', 'districts', 'paymentData'));
    }

    /**
     * Store a newly created contract
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'contract_number' => 'required|string|min:3|max:50|unique:contracts,contract_number',
            'subject_id' => 'required|exists:subjects,id',
            'object_id' => 'required|exists:objects,id',
            'contract_date' => 'required|date|before_or_equal:today',
            'completion_date' => 'nullable|date|after:contract_date',
            'status_id' => 'required|exists:contract_statuses,id',
            'base_amount_id' => 'required|exists:base_calculation_amounts,id',
            'contract_volume' => 'required|numeric|min:0.01',
            'coefficient' => 'required|numeric|min:0.0001',
            'total_amount' => 'required|numeric|min:1',
            'payment_type' => 'required|in:installment,full',
            'initial_payment_percent' => 'nullable|numeric|min:0|max:100',
            'construction_period_years' => 'nullable|numeric|min:1|max:10',
            'quarters_count' => 'nullable|numeric|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Convert and validate inputs
            $totalAmount = (float) $request->total_amount;
            $initialPaymentPercent = (float) ($request->initial_payment_percent ??
                ($request->payment_type === 'full' ? 100 : 20));
            $quartersCount = (int) ($request->quarters_count ??
                ($request->payment_type === 'full' ? 0 : 8));

            // Validate calculations
            if ($totalAmount <= 0) {
                throw new \Exception('Jami summa 0 dan katta bo\'lishi kerak');
            }

            if ($initialPaymentPercent < 0 || $initialPaymentPercent > 100) {
                throw new \Exception('Boshlang\'ich to\'lov foizi 0-100% orasida bo\'lishi kerak');
            }

            $contract = Contract::create([
                'contract_number' => $request->contract_number,
                'subject_id' => $request->subject_id,
                'object_id' => $request->object_id,
                'contract_date' => $request->contract_date,
                'completion_date' => $request->completion_date,
                'status_id' => $request->status_id,
                'base_amount_id' => $request->base_amount_id,
                'contract_volume' => (float) $request->contract_volume,
                'coefficient' => (float) $request->coefficient,
                'total_amount' => $totalAmount,
                'payment_type' => $request->payment_type,
                'initial_payment_percent' => $initialPaymentPercent,
                'construction_period_years' => (int) ($request->construction_period_years ?? 2),
                'quarters_count' => $quartersCount,
                'formula' => $request->formula ?? 'V × K × BA',
                'is_active' => true,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return redirect()
                ->route('contracts.payment_update', $contract)
                ->with('success', 'Shartnoma muvaffaqiyatli yaratildi');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract creation failed:', [
                'error' => $e->getMessage(),
                'input_data' => $request->all(),
            ]);

            return back()->withInput()->with('error', 'Shartnoma yaratishda xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified contract
     */
    public function show(Contract $contract): View
    {
        $contract->load(['subject', 'object.district', 'status', 'baseAmount', 'amendments']);
        $paymentData = $this->paymentService->getContractPaymentData($contract);
        return view('contracts.show', compact('contract', 'paymentData'));
    }

    /**
     * Show the form for editing the contract
     */
    public function edit(Contract $contract): View
    {
        $subjects = Subject::where('is_active', true)->get();
        $objects = Objectt::where('is_active', true)->with('district')->get();
        $statuses = \App\Models\ContractStatus::where('is_active', true)->get();
        $baseAmounts = \App\Models\BaseCalculationAmount::where('is_active', true)->get();

        return view('contracts.edit', compact('contract', 'subjects', 'objects', 'statuses', 'baseAmounts'));
    }

    /**
     * Update the specified contract
     */
    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'contract_number' => 'required|string|min:3|max:50|unique:contracts,contract_number,' . $contract->id,
            'subject_id' => 'nullable|exists:subjects,id',
            'object_id' => 'nullable|exists:objects,id',
            'contract_date' => 'required|date|before_or_equal:today',
            'completion_date' => 'nullable|date|after:contract_date',
            'total_amount' => 'required|numeric|min:1',
            'payment_type' => 'required|in:installment,full',
            'initial_payment_percent' => 'nullable|numeric|min:0|max:100',
            'construction_period_years' => 'nullable|numeric|min:1|max:10',
            'quarters_count' => 'nullable|numeric|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $contract->update([
                'contract_number' => $request->contract_number,
                'subject_id' => $request->subject_id ?? $contract->subject_id,
                'object_id' => $request->object_id ?? $contract->object_id,
                'contract_date' => $request->contract_date,
                'completion_date' => $request->completion_date,
                'total_amount' => $request->total_amount,
                'payment_type' => $request->payment_type,
                'initial_payment_percent' => $request->initial_payment_percent ?? ($request->payment_type === 'full' ? 100 : 20),
                'construction_period_years' => $request->construction_period_years ?? 2,
                'quarters_count' => $request->quarters_count ?? ($request->payment_type === 'full' ? 0 : 8),
                'updated_by' => auth()->id()
            ]);

            // Update initial payment schedule if it exists
            $initialSchedule = PaymentSchedule::where('contract_id', $contract->id)
                ->where('is_initial_payment', true)
                ->where('is_active', true)
                ->first();

            if ($initialSchedule) {
                $newInitialAmount = $contract->fresh()->initial_payment_amount;
                $initialSchedule->update(['quarter_amount' => $newInitialAmount]);
            }

            DB::commit();

            // If we're in payment update context, redirect there
            if ($request->has('from_payment_update')) {
                return redirect()->route('contracts.payment_update', $contract)
                    ->with('success', 'Shartnoma muvaffaqiyatli yangilandi');
            }

            return back()->with('success', 'Shartnoma muvaffaqiyatli yangilandi');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Shartnoma yangilashda xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified contract
     */
    public function destroy(Contract $contract): RedirectResponse
    {
        try {
            $contract->update(['is_active' => false]);
            return back()->with('success', 'Shartnoma muvaffaqiyatli o\'chirildi');
        } catch (\Exception $e) {
            return back()->with('error', 'Shartnomani o\'chirishda xatolik: ' . $e->getMessage());
        }
    }

    // ========== PAYMENT MANAGEMENT METHODS ==========

    /**
     * Display contract payment management page
     */
    public function payment_update(Contract $contract): View
    {
        $contract->load(['subject', 'object.district', 'status', 'payments', 'schedules']);
        $paymentData = $this->paymentService->getContractPaymentData($contract);
        $statuses = \App\Models\ContractStatus::where('is_active', true)->orderBy('id')->get();

        return view('contracts.payment_update', compact('paymentData', 'statuses', 'contract'));
    }


    /**
     * Update contract status
     */
    public function updateStatus(Request $request, Contract $contract): RedirectResponse
    {
        // Validate input
        $request->validate([
            'status_id' => 'required|exists:contract_statuses,id',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $contract->status;
            $newStatus = \App\Models\ContractStatus::findOrFail($request->status_id);

            // Update contract status
            $contract->update([
                'status_id' => $request->status_id,
                'updated_by' => auth()->id()
            ]);
            // dd($contract->status_id);

            DB::commit();

            return redirect()->back()->with([
                'success' => 'Shartnoma holati muvaffaqiyatli o\'zgartirildi',
                'status_updated' => "{$oldStatus->name_uz} → {$newStatus->name_uz}"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Status update failed:', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Holat o\'zgartirishda xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Get status change history
     */
    public function getStatusHistory(Contract $contract)
    {
        $history = \App\Models\ContractStatusHistory::where('contract_id', $contract->id)
            ->with(['oldStatus', 'newStatus', 'changedBy'])
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json($history);
    }

    /**
     * Show payment schedule creation form
     */
    public function createSchedule(Contract $contract): View
    {
        $paymentData = $this->paymentService->getContractPaymentData($contract);
        return view('contracts.create-schedule', compact('contract', 'paymentData'));
    }

    /**
     * Store payment schedule
     */
    public function storeSchedule(Request $request, Contract $contract): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'schedule_type' => 'required|in:auto,custom',
            'quarters_count' => 'required|integer|min:1|max:20',
            'total_schedule_amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $result = $this->paymentService->createPaymentSchedule($contract, $request->all());

        if ($result['success']) {
            return redirect()->route('contracts.payment_update', $contract)->with('success', $result['message']);
        }

        return back()->withInput()->with('error', $result['message']);
    }

    /**
     * Show add payment form
     */
    public function addPayment(Contract $contract): View
    {
        $paymentData = $this->paymentService->getContractPaymentData($contract);
        return view('contracts.add-payment', compact('contract', 'paymentData'));
    }

    /**
     * Store payment
     */
    public function storePayment(Request $request, Contract $contract): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_date' => 'required|date|after_or_equal:' . $contract->contract_date->format('Y-m-d'),
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_number' => 'nullable|string|max:50',
            'payment_notes' => 'nullable|string|max:500',
            'target_year' => 'nullable|integer',
            'target_quarter' => 'nullable|integer|min:1|max:4'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Debug: Log request data
            \Log::info('Payment request data: ', $request->all());

            // Prepare data for the payment service with correct field names
            $paymentData = [
                'payment_date' => $request->input('payment_date'),
                'payment_amount' => $request->input('payment_amount'), // Keep original name
                'amount' => $request->input('payment_amount'), // Also add as 'amount' for service
                'payment_number' => $request->input('payment_number'),
                'payment_notes' => $request->input('payment_notes'),
                'notes' => $request->input('payment_notes'), // Also add as 'notes' for service
                'year' => $request->input('target_year'),
                'quarter' => $request->input('target_quarter'),
                'target_year' => $request->input('target_year'), // Keep original name
                'target_quarter' => $request->input('target_quarter'), // Keep original name
                'payment_category' => $request->input('target_quarter') ? 'quarterly' : 'initial',
                'created_by' => auth()->id()
            ];

            // Check if required fields exist
            if (!$request->has('payment_amount') || !$request->has('payment_date')) {
                return redirect()->back()
                    ->withErrors(['error' => 'Majburiy maydonlar to\'ldirilmagan'])
                    ->withInput();
            }

            $result = $this->paymentService->addPayment($contract, $paymentData);

            if ($result['success']) {
                return redirect()->route('contracts.payment_update', $contract)
                    ->with('success', 'To\'lov muvaffaqiyatli qo\'shildi');
            } else {
                return redirect()->back()
                    ->withErrors(['error' => $result['message']])
                    ->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('Payment creation error: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'To\'lov qo\'shishda xatolik yuz berdi'])
                ->withInput();
        }
    }
    /**
     * Show quarter payment form
     */
    public function addQuarterPayment(Contract $contract, int $year, int $quarter): View
    {
        $paymentData = $this->paymentService->getContractPaymentData($contract);
        $suggestedDate = Carbon::create($year, ($quarter - 1) * 3 + 2, 15)->format('Y-m-d');

        return view('contracts.add-payment', compact('contract', 'paymentData', 'year', 'quarter', 'suggestedDate'));
    }

    /**
     * Show quarter details
     */
    public function quarterDetails(Contract $contract, int $year, int $quarter): View
    {
        $paymentData = $this->paymentService->getContractPaymentData($contract);
        $quarterData = $paymentData['quarterly_breakdown'][$year]['quarters'][$quarter] ?? null;

        if (!$quarterData) {
            return redirect()->route('contracts.payment_update', $contract)
                ->with('error', "Ma'lumot topilmadi: {$quarter}-chorak {$year}");
        }

        return view('contracts.quarter-details', compact('contract', 'year', 'quarter', 'quarterData', 'paymentData'));
    }

    // ========== AMENDMENT METHODS ==========

    /**
     * Show create amendment form
     */
    public function createAmendment(Contract $contract): View
    {
        $paymentData = $this->paymentService->getContractPaymentData($contract);
        return view('contracts.create-amendment', compact('contract', 'paymentData'));
    }

public function createAmendmentSchedule($contract, $amendment): RedirectResponse
{
    $contract = Contract::findOrFail($contract);

    $amendment = ContractAmendment::where('contract_id', $contract->id)
        ->where('id', $amendment)
        ->firstOrFail();

    // Your schedule creation logic here

    return back()->with('success', 'Jadval yaratildi');
}

    /**
     * Store new amendment
     */
    public function storeAmendment(Request $request, Contract $contract): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'amendment_number' => 'required|string|max:50',
            'amendment_date' => 'required|date|after_or_equal:' . $contract->contract_date->format('Y-m-d'),
            'new_total_amount' => 'nullable|numeric|min:1',
            'new_completion_date' => 'nullable|date|after:' . $contract->contract_date->format('Y-m-d'),
            'new_initial_payment_percent' => 'nullable|numeric|min:0|max:100',
            'new_quarters_count' => 'nullable|numeric|min:1|max:20',
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $result = $this->paymentService->createAmendment($contract, $request->all());

        if ($result['success']) {
            return redirect()->route('contracts.payment_update', $contract)->with('success', $result['message']);
        }

        return back()->withInput()->with('error', $result['message']);
    }

    /**
     * Show amendment details
     */
public function showAmendment($contract, $amendment): View
{
    // Manually fetch the models
    $contract = Contract::findOrFail($contract);

    $amendment = ContractAmendment::where('contract_id', $contract->id)
        ->where('id', $amendment)
        ->firstOrFail();

    $paymentData = $this->paymentService->getContractPaymentData($contract);

    return view('contracts.amendment-details', compact('contract', 'amendment', 'paymentData'));
}

    /**
     * Approve amendment
     */
   public function approveAmendment($contract, $amendment): RedirectResponse
{
    $contract = Contract::findOrFail($contract);

    $amendment = ContractAmendment::where('contract_id', $contract->id)
        ->where('id', $amendment)
        ->firstOrFail();

    $result = $this->paymentService->approveAmendment($amendment);

    if ($result['success']) {
        return back()->with('success', $result['message']);
    }

    return back()->with('error', $result['message']);
}

public function editAmendment($contract, $amendment): View
{
    $contract = Contract::findOrFail($contract);
    $amendment = ContractAmendment::where('contract_id', $contract->id)
        ->where('id', $amendment)
        ->firstOrFail();

    // Only allow editing unapproved amendments
    if ($amendment->is_approved) {
        return redirect()->route('contracts.amendments.show', [$contract, $amendment])
            ->with('error', 'Tasdiqlangan kelishuvni tahrirlash mumkin emas');
    }

    $paymentData = $this->paymentService->getContractPaymentData($contract);

    return view('contracts.edit-amendment', compact('contract', 'amendment', 'paymentData'));
}

/**
 * Update amendment
 */
public function updateAmendment(Request $request, $contract, $amendment): RedirectResponse
{
    $contract = Contract::findOrFail($contract);
    $amendment = ContractAmendment::where('contract_id', $contract->id)
        ->where('id', $amendment)
        ->firstOrFail();

    // Only allow editing unapproved amendments
    if ($amendment->is_approved) {
        return back()->with('error', 'Tasdiqlangan kelishuvni tahrirlash mumkin emas');
    }

    $validator = Validator::make($request->all(), [
        'amendment_number' => 'required|string|max:50',
        'amendment_date' => 'required|date|after_or_equal:' . $contract->contract_date->format('Y-m-d'),
        'new_total_amount' => 'nullable|numeric|min:1',
        'new_completion_date' => 'nullable|date|after:' . $contract->contract_date->format('Y-m-d'),
        'new_initial_payment_percent' => 'nullable|numeric|min:0|max:100',
        'new_quarters_count' => 'nullable|numeric|min:1|max:20',
        'reason' => 'required|string|max:500',
        'description' => 'nullable|string|max:1000'
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    try {
        DB::beginTransaction();

        $amendment->update([
            'amendment_number' => $request->amendment_number,
            'amendment_date' => $request->amendment_date,
            'new_total_amount' => $request->new_total_amount,
            'new_completion_date' => $request->new_completion_date,
            'new_initial_payment_percent' => $request->new_initial_payment_percent,
            'new_quarters_count' => $request->new_quarters_count,
            'reason' => $request->reason,
            'description' => $request->description,
            'updated_by' => auth()->id()
        ]);

        DB::commit();

        return redirect()->route('contracts.amendments.show', [$contract, $amendment])
            ->with('success', 'Qo\'shimcha kelishuv muvaffaqiyatli yangilandi');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Yangilashda xatolik: ' . $e->getMessage());
    }
}
    /**
     * Delete amendment
     */
   public function deleteAmendment($contract, $amendment): RedirectResponse
{
    $contract = Contract::findOrFail($contract);

    $amendment = ContractAmendment::where('contract_id', $contract->id)
        ->where('id', $amendment)
        ->firstOrFail();

    try {
        $amendment->delete();
        return back()->with('success', 'Qo\'shimcha kelishuv o\'chirildi');
    } catch (\Exception $e) {
        return back()->with('error', 'O\'chirishda xatolik: ' . $e->getMessage());
    }
}
    // ========== PAYMENT CRUD OPERATIONS ==========

    /**
     * Update existing payment
     */
    public function updatePayment(Request $request, $paymentId): JsonResponse
    {
        try {
            $payment = ActualPayment::findOrFail($paymentId);

            // Check permission - only allow editing payments from last 30 days
            if ($payment->created_at->diffInDays(now()) > 30) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu to\'lov 30 kundan ortiq vaqt oldin yaratilgan, tahrirlash mumkin emas'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'payment_date' => 'required|date',
                'payment_amount' => 'required|numeric|min:0.01',
                'payment_number' => 'nullable|string|max:50',
                'payment_notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ma\'lumotlar noto\'g\'ri',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->paymentService->updatePayment($payment, $request->all());
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Payment update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'To\'lov yangilashda xatolik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete payment
     */
    public function deletePayment(Request $request, $paymentId): JsonResponse
    {
        try {
            $payment = ActualPayment::findOrFail($paymentId);

            // Check permission - only allow deleting payments from last 30 days
            if ($payment->created_at->diffInDays(now()) > 30) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu to\'lov 30 kundan ortiq vaqt oldin yaratilgan, o\'chirish mumkin emas'
                ], 403);
            }

            $result = $this->paymentService->deletePayment($payment);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Payment deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'To\'lov o\'chirishda xatolik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment details
     */
    public function getPaymentDetails($paymentId): JsonResponse
    {
        try {
            $payment = ActualPayment::with(['contract'])->findOrFail($paymentId);

            return response()->json([
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'amount_formatted' => $payment->formatted_amount,
                    'payment_date' => $payment->payment_date->format('d.m.Y'),
                    'payment_date_iso' => $payment->payment_date->format('Y-m-d'),
                    'payment_number' => $payment->payment_number,
                    'notes' => $payment->notes,
                    'year' => $payment->year,
                    'quarter' => $payment->quarter,
                    'is_initial_payment' => $payment->is_initial_payment,
                    'quarter_info' => $payment->quarter_info,
                    'created_at' => $payment->created_at->format('d.m.Y H:i'),
                    'updated_at' => $payment->updated_at->format('d.m.Y H:i'),
                    'can_edit' => $payment->can_edit,
                    'can_delete' => $payment->can_delete,
                    'contract' => [
                        'id' => $payment->contract->id,
                        'contract_number' => $payment->contract->contract_number,
                        'contract_date' => $payment->contract->contract_date->format('d.m.Y')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'To\'lov ma\'lumotlari topilmadi'
            ], 404);
        }
    }

    // ========== API ENDPOINTS ==========

    /**
     * Get quarterly breakdown (API)
     */
    public function getQuarterlyBreakdown(Contract $contract): JsonResponse
    {
        try {
            $data = $this->paymentService->getQuarterlyBreakdown($contract);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get payment history (API)
     */
    public function getPaymentHistory(Contract $contract): JsonResponse
    {
        try {
            $history = $this->paymentService->getPaymentHistory($contract);
            return response()->json(['success' => true, 'history' => $history]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get contract payment summary (API)
     */
    public function getContractPaymentSummary(Contract $contract): JsonResponse
    {
        try {
            $summary = $this->paymentService->getSummaryCards($contract);
            return response()->json(['success' => true, 'summary' => $summary]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Export contract report
     */
    public function exportReport(Contract $contract)
    {
        try {
            $reportData = $this->paymentService->exportReport($contract);
            $filename = 'shartnoma_' . $contract->contract_number . '_hisobot_' . date('Y-m-d') . '.json';

            return response()->json($reportData)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return back()->with('error', 'Hisobot yaratishda xatolik: ' . $e->getMessage());
        }
    }

    // ========== UTILITY METHODS ==========

    /**
     * Create subject (AJAX)
     */
    public function createSubject(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_legal_entity' => 'required|boolean',
                'company_name' => 'required_if:is_legal_entity,1|string|max:255',
                'inn' => 'required_if:is_legal_entity,1|string|max:20',
                'pinfl' => 'required_if:is_legal_entity,0|string|max:14',
                'document_number' => 'required_if:is_legal_entity,0|string|max:20',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $subjectData = $request->all();
            $subjectData['is_active'] = true;
            $subjectData['created_by'] = auth()->id();

            $subject = Subject::create($subjectData);

            return response()->json([
                'success' => true,
                'message' => 'Mulk egasi muvaffaqiyatli yaratildi',
                'subject' => [
                    'id' => $subject->id,
                    'text' => $subject->display_name . ' (' . $subject->identifier . ')'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create object (AJAX)
     */
    public function createObject(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject_id' => 'required|exists:subjects,id',
                'district_id' => 'required|exists:districts,id',
                'address' => 'required|string|max:500',
                'construction_volume' => 'required|numeric|min:0.01',
                'construction_type_id' => 'nullable|integer',
                'object_type_id' => 'nullable|integer',
                'territorial_zone_id' => 'nullable|integer|min:1|max:5',
                'location_type' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $objectData = $request->all();
            $objectData['is_active'] = true;
            $objectData['created_by'] = auth()->id();

            $object = Objectt::create($objectData);

            return response()->json([
                'success' => true,
                'message' => 'Obyekt muvaffaqiyatli yaratildi',
                'object' => [
                    'id' => $object->id,
                    'text' => $object->address . ' (' . ($object->district->name_uz ?? 'N/A') . ') - ' . number_format($object->construction_volume, 2) . ' m³',
                    'volume' => $object->construction_volume,
                    'abovepermit' => $object->above_permit_volume ?? 0,
                    'parking' => $object->parking_volume ?? 0,
                    'technical' => $object->technical_rooms_volume ?? 0,
                    'common' => $object->common_area_volume ?? 0,
                    'constructiontype' => $object->construction_type_id ?? 1,
                    'objecttype' => $object->object_type_id ?? 5,
                    'zone' => $object->territorial_zone_id ?? 3,
                    'location' => $object->location_type ?? 'other_locations'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate payment date (API)
     */
    public function validatePaymentDate(Request $request): JsonResponse
    {
        try {
            $paymentDate = $request->payment_date;
            $contractId = $request->contract_id;

            if (!$paymentDate || !$contractId) {
                return response()->json(['valid' => false, 'message' => 'Xatolik: ' . $e->getMessage()]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    /**
     * Get quarter from date (API)
     */
    public function getQuarterFromDate(string $date): JsonResponse
    {
        try {
            $carbonDate = Carbon::parse($date);
            $quarter = ceil($carbonDate->month / 3);

            return response()->json([
                'success' => true,
                'year' => $carbonDate->year,
                'quarter' => $quarter,
                'quarter_name' => "{$quarter}-chorak {$carbonDate->year}"
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Calculate payment breakdown preview
     */
    public function calculateBreakdown(Request $request): JsonResponse
    {
        try {
            $breakdown = $this->paymentService->calculatePaymentBreakdown($request->all());
            return response()->json(['success' => true, 'breakdown' => $breakdown]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get objects by subject
     */
    public function getObjectsBySubject(Subject $subject): JsonResponse
    {
        try {
            $objects = $subject->objects()->with('district')->get();
            return response()->json(['success' => true, 'objects' => $objects]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ========== DEBTORS AND REPORTS ==========

    /**
     * Show debtors list
     */
    public function debtors(Request $request): View
    {
        $query = Contract::with(['subject', 'object.district'])
            ->where('is_active', true)
            ->withDebt(); // Using scope from Contract model

        // Apply filters
        if ($request->contract_number) {
            $query->where('contract_number', 'like', '%' . $request->contract_number . '%');
        }

        if ($request->district_id) {
            $query->whereHas('object', function ($q) use ($request) {
                $q->where('district_id', $request->district_id);
            });
        }

        if ($request->debt_from) {
            $debtFrom = (float) $request->debt_from;
            $query->whereRaw('total_amount - (SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id) >= ?', [$debtFrom]);
        }

        // Calculate remaining debt for each contract
        $debtors = $query->get()->map(function ($contract) {
            $contract->remaining_debt = $contract->remaining_debt;
            $contract->total_paid = $contract->total_paid_amount;
            $contract->payment_percent = $contract->payment_percent;
            return $contract;
        })->where('remaining_debt', '>', 0);

        // Paginate manually
        $page = $request->get('page', 1);
        $perPage = 20;
        $total = $debtors->count();
        $debtors = $debtors->slice(($page - 1) * $perPage, $perPage);

        // Create paginator
        $debtors = new \Illuminate\Pagination\LengthAwarePaginator(
            $debtors,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('contracts.debtors', compact('debtors'));
    }

    // ========== BULK OPERATIONS ==========

    /**
     * Bulk operations for payments
     */
    public function bulkUpdatePayments(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_ids' => 'required|array|min:1',
                'payment_ids.*' => 'required|integer|exists:actual_payments,id',
                'action' => 'required|in:delete,update_quarter,update_amount',
                'new_quarter' => 'required_if:action,update_quarter|integer|min:1|max:4',
                'new_year' => 'required_if:action,update_quarter|integer|min:2020',
                'amount_multiplier' => 'required_if:action,update_amount|numeric|min:0.1|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ma\'lumotlar noto\'g\'ri',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $paymentIds = $request->payment_ids;
            $action = $request->action;
            $updatedCount = 0;

            foreach ($paymentIds as $paymentId) {
                $payment = ActualPayment::find($paymentId);

                if (!$payment || $payment->created_at->diffInDays(now()) > 30) {
                    continue; // Skip old or non-existent payments
                }

                switch ($action) {
                    case 'delete':
                        $payment->delete();
                        $updatedCount++;
                        break;

                    case 'update_quarter':
                        if (!$payment->is_initial_payment) {
                            $payment->update([
                                'year' => $request->new_year,
                                'quarter' => $request->new_quarter,
                                'updated_by' => auth()->id()
                            ]);
                            $updatedCount++;
                        }
                        break;

                    case 'update_amount':
                        $newAmount = $payment->amount * $request->amount_multiplier;
                        $payment->update([
                            'amount' => $newAmount,
                            'updated_by' => auth()->id()
                        ]);
                        $updatedCount++;
                        break;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} ta to'lov {$action} amaliyoti bajarildi",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ommaviy amaliyot bajarishda xatolik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment statistics for dashboard
     */
    public function getPaymentStatistics(Request $request): JsonResponse
    {
        try {
            $contractId = $request->contract_id;
            $year = $request->year ?? date('Y');

            $query = ActualPayment::query();

            if ($contractId) {
                $query->where('contract_id', $contractId);
            }

            if ($year) {
                $query->where('year', $year);
            }

            $statistics = [
                'total_payments' => $query->count(),
                'total_amount' => $query->sum('amount'),
                'average_payment' => $query->avg('amount'),
                'initial_payments' => [
                    'count' => $query->clone()->where('is_initial_payment', true)->count(),
                    'total' => $query->clone()->where('is_initial_payment', true)->sum('amount')
                ],
                'quarterly_payments' => [
                    'count' => $query->clone()->where('is_initial_payment', false)->count(),
                    'total' => $query->clone()->where('is_initial_payment', false)->sum('amount')
                ],
                'monthly_breakdown' => $query->selectRaw('MONTH(payment_date) as month, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'month' => $item->month,
                            'month_name' => date('F', mktime(0, 0, 0, $item->month, 1)),
                            'count' => $item->count,
                            'total' => $item->total,
                            'total_formatted' => number_format($item->total, 0, '.', ' ') . ' so\'m'
                        ];
                    }),
                'quarterly_breakdown' => $query->selectRaw('quarter, COUNT(*) as count, SUM(amount) as total')
                    ->where('is_initial_payment', false)
                    ->groupBy('quarter')
                    ->orderBy('quarter')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'quarter' => $item->quarter,
                            'quarter_name' => "{$item->quarter}-chorak",
                            'count' => $item->count,
                            'total' => $item->total,
                            'total_formatted' => number_format($item->total, 0, '.', ' ') . ' so\'m'
                        ];
                    })
            ];

            return response()->json([
                'success' => true,
                'statistics' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Statistika olishda xatolik: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== LEGACY SUPPORT METHODS ==========

    /**
     * Create quarterly schedule (legacy)
     */
    public function createQuarterlySchedule(Request $request, Contract $contract): JsonResponse
    {
        $result = $this->paymentService->createPaymentSchedule($contract, $request->all());
        return response()->json($result);
    }

    /**
     * Store fact payment (legacy)
     */
    public function storeFactPayment(Request $request, Contract $contract): JsonResponse
    {
        $result = $this->paymentService->addPayment($contract, $request->all());
        return response()->json($result);
    }

    /**
     * Edit payment (legacy)
     */
    public function editPayment(Request $request, ActualPayment $payment): JsonResponse
    {
        $result = $this->paymentService->updatePayment($payment, $request->all());
        return response()->json($result);
    }

    /**
     * Delete fact payment (legacy)
     */
    public function deleteFactPayment(ActualPayment $payment): JsonResponse
    {
        $result = $this->paymentService->deletePayment($payment);
        return response()->json($result);
    }
};
