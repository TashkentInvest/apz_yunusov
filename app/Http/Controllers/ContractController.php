<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ActualPayment;
use App\Models\ContractAmendment;
use App\Models\PaymentSchedule;
use App\Models\Subject;
use App\Models\Objectt;
use App\Services\ContractPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContractController extends Controller
{
    protected ContractPaymentService $paymentService;

    public function __construct(ContractPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of contracts
     */
    public function index(Request $request): View
    {
        $query = Contract::with(['subject', 'object.district', 'status'])
            ->where('is_active', true);

        // Search filters
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('subject', function($sq) use ($search) {
                      $sq->where('company_name', 'like', "%{$search}%")
                        ->orWhere('inn', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->district_id) {
            $query->whereHas('object', function($q) use ($request) {
                $q->where('district_id', $request->district_id);
            });
        }

        $contracts = $query->paginate(20)->appends($request->query());

        // Get filter options
        $statuses = \App\Models\ContractStatus::where('is_active', true)->get();
        $districts = \App\Models\District::where('is_active', true)->get();

        return view('contracts.index', compact('contracts', 'statuses', 'districts'));
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
            'payment_history' => ['payments' => []],
            'amendments' => [],
            'available_years' => [],
            'quarter_options' => []
        ];

        return view('contracts.create', compact('subjects', 'objects', 'statuses', 'baseAmounts', 'paymentData'));
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
        // Convert and validate inputs
        $totalAmount = (float) $request->total_amount;
        $initialPaymentPercent = (float) ($request->initial_payment_percent ??
            ($request->payment_type === 'full' ? 100 : 20));
        $quartersCount = (int) ($request->quarters_count ??
            ($request->payment_type === 'full' ? 0 : 8));

        // Log the values before saving
        \Log::info('Contract Store Debug:', [
            'input_total_amount' => $request->total_amount,
            'converted_total_amount' => $totalAmount,
            'input_initial_percent' => $request->initial_payment_percent,
            'converted_initial_percent' => $initialPaymentPercent,
            'payment_type' => $request->payment_type,
            'quarters_count' => $quartersCount,
        ]);

        // Validate calculations
        if ($totalAmount <= 0) {
            return back()->withInput()->with('error', 'Jami summa 0 dan katta bo\'lishi kerak');
        }

        if ($initialPaymentPercent < 0 || $initialPaymentPercent > 100) {
            return back()->withInput()->with('error', 'Boshlang\'ich to\'lov foizi 0-100% orasida bo\'lishi kerak');
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
            'total_amount' => $totalAmount, // Make sure this is float
            'payment_type' => $request->payment_type,
            'initial_payment_percent' => $initialPaymentPercent, // Make sure this is float
            'construction_period_years' => (int) ($request->construction_period_years ?? 2),
            'quarters_count' => $quartersCount,
            'formula' => $request->formula ?? 'V × K × BA',
            'is_active' => true,
            'created_by' => auth()->id()
        ]);

        // Log what was actually saved
        \Log::info('Contract Saved:', [
            'id' => $contract->id,
            'total_amount' => $contract->total_amount,
            'total_amount_type' => gettype($contract->total_amount),
            'initial_payment_percent' => $contract->initial_payment_percent,
            'initial_payment_percent_type' => gettype($contract->initial_payment_percent),
        ]);

        return redirect()
            ->route('contracts.payment_update', $contract)
            ->with('success', 'Shartnoma muvaffaqiyatli yaratildi');

    } catch (\Exception $e) {
        \Log::error('Contract creation failed:', [
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

        // Get payment summary using service
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

            // If we're in payment update context, redirect there
            if ($request->has('from_payment_update')) {
                return redirect()->route('contracts.payment_update', $contract)
                    ->with('success', 'Shartnoma muvaffaqiyatli yangilandi');
            }

            return back()->with('success', 'Shartnoma muvaffaqiyatli yangilandi');

        } catch (\Exception $e) {
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
        $paymentData = $this->paymentService->getContractPaymentData($contract);
        return view('contracts.payment_update', compact('paymentData'));
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
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_number' => 'nullable|string|max:50',
            'payment_notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $result = $this->paymentService->addPayment($contract, $request->all());

        if ($result['success']) {
            return redirect()->route('contracts.payment_update', $contract)->with('success', $result['message']);
        }

        return back()->withInput()->with('error', $result['message']);
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
    public function showAmendment(Contract $contract, ContractAmendment $amendment): View
    {
        if ($amendment->contract_id !== $contract->id) {
            abort(404);
        }

        $paymentData = $this->paymentService->getContractPaymentData($contract);
        return view('contracts.amendment-details', compact('contract', 'amendment', 'paymentData'));
    }

    /**
     * Approve amendment
     */
    public function approveAmendment(Contract $contract, ContractAmendment $amendment): RedirectResponse
    {
        if ($amendment->contract_id !== $contract->id) {
            abort(404);
        }

        $result = $this->paymentService->approveAmendment($amendment);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Delete amendment
     */
    public function deleteAmendment(Contract $contract, ContractAmendment $amendment): RedirectResponse
    {
        if ($amendment->contract_id !== $contract->id) {
            abort(404);
        }

        try {
            $amendment->delete();
            return back()->with('success', 'Qo\'shimcha kelishuv o\'chirildi');
        } catch (\Exception $e) {
            return back()->with('error', 'O\'chirishda xatolik: ' . $e->getMessage());
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
     * Get amendments (API)
     */
    public function getAmendments(Contract $contract): JsonResponse
    {
        try {
            $amendments = $this->paymentService->getAmendments($contract);
            return response()->json(['success' => true, 'amendments' => $amendments]);
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

    /**
     * Calculate payment breakdown (API)
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
     * Validate payment date (API)
     */
    public function validatePaymentDate(Request $request): JsonResponse
    {
        try {
            $paymentDate = $request->payment_date;
            $contractId = $request->contract_id;

            if (!$paymentDate || !$contractId) {
                return response()->json(['valid' => false, 'message' => 'Ma\'lumotlar yetarli emas']);
            }

            $contract = Contract::findOrFail($contractId);
            $contractDate = $contract->contract_date;

            if ($paymentDate < $contractDate->format('Y-m-d')) {
                return response()->json([
                    'valid' => false,
                    'message' => 'To\'lov sanasi shartnoma sanasidan oldin bo\'lishi mumkin emas'
                ]);
            }

            return response()->json(['valid' => true, 'message' => 'To\'lov sanasi to\'g\'ri']);

        } catch (\Exception $e) {
            return response()->json(['valid' => false, 'message' => 'Xatolik: ' . $e->getMessage()]);
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

    // ========== UTILITY METHODS ==========

    /**
     * Create subject (AJAX)
     */
    public function createSubject(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'inn' => 'nullable|string|max:20',
                'subject_type' => 'required|in:individual,legal'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $subject = Subject::create($request->all());

            return response()->json([
                'success' => true,
                'subject' => [
                    'id' => $subject->id,
                    'name' => $subject->company_name,
                    'inn' => $subject->inn
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
                'address' => 'required|string|max:500',
                'district_id' => 'required|exists:districts,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $object = Objectt::create($request->all());

            return response()->json([
                'success' => true,
                'object' => [
                    'id' => $object->id,
                    'address' => $object->address,
                    'district' => $object->district->name_uz ?? ''
                ]
            ]);
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

    /**
     * Calculate coefficients
     */
    public function calculateCoefficients(Request $request): JsonResponse
    {
        try {
            $volume = (float) $request->volume;
            $baseAmount = (float) $request->base_amount;
            $coefficient = (float) $request->coefficient;

            $totalAmount = $volume * $baseAmount * $coefficient;

            return response()->json([
                'success' => true,
                'calculation' => [
                    'volume' => $volume,
                    'base_amount' => $baseAmount,
                    'coefficient' => $coefficient,
                    'total_amount' => $totalAmount,
                    'formatted_amount' => number_format($totalAmount, 2, '.', ' ') . ' so\'m'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate object volumes
     */
    public function validateObjectVolumes(Request $request): JsonResponse
    {
        try {
            $volume = (float) $request->volume;

            $validation = [
                'valid' => $volume > 0,
                'volume' => $volume,
                'message' => $volume > 0 ? 'Hajm to\'g\'ri' : 'Hajm 0 dan katta bo\'lishi kerak'
            ];

            return response()->json(['success' => true, 'validation' => $validation]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate payment report
     */
    public function generatePaymentReport(Contract $contract): JsonResponse
    {
        try {
            $report = $this->paymentService->exportReport($contract);
            return response()->json(['success' => true, 'report' => $report]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


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

        DB::beginTransaction();

        $oldAmount = $payment->amount;
        $newAmount = (float) $request->payment_amount;
        $paymentDate = Carbon::parse($request->payment_date);

        // Validate payment date
        if ($paymentDate->lt($payment->contract->contract_date)) {
            return response()->json([
                'success' => false,
                'message' => 'To\'lov sanasi shartnoma sanasidan oldin bo\'lishi mumkin emas'
            ], 400);
        }

        // Determine new quarter if date changed
        $targetYear = $paymentDate->year;
        $targetQuarter = ceil($paymentDate->month / 3);

        // Check if target quarter exists in payment schedule
        $schedule = PaymentSchedule::where('contract_id', $payment->contract_id)
            ->where('year', $targetYear)
            ->where('quarter', $targetQuarter)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => "Yangi sana uchun to'lov jadvali topilmadi: {$targetQuarter}-chorak {$targetYear}"
            ], 400);
        }

        // Check for duplicate payment number (excluding current payment)
        if ($request->payment_number) {
            $existingPayment = ActualPayment::where('contract_id', $payment->contract_id)
                ->where('payment_number', $request->payment_number)
                ->where('id', '!=', $payment->id)
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu hujjat raqami bilan to\'lov allaqachon mavjud'
                ], 400);
            }
        }

        // Update payment
        $payment->update([
            'payment_date' => $paymentDate,
            'amount' => $newAmount,
            'year' => $targetYear,
            'quarter' => $targetQuarter,
            'payment_number' => $request->payment_number,
            'notes' => $request->payment_notes,
            'updated_by' => auth()->id()
        ]);

        DB::commit();

        // Log the change
        \Log::info('Payment updated:', [
            'payment_id' => $payment->id,
            'contract_id' => $payment->contract_id,
            'old_amount' => $oldAmount,
            'new_amount' => $newAmount,
            'difference' => $newAmount - $oldAmount,
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'To\'lov muvaffaqiyatli yangilandi',
            'payment' => [
                'id' => $payment->id,
                'amount' => $newAmount,
                'amount_formatted' => number_format($newAmount, 0, '.', ' ') . ' so\'m',
                'old_amount' => $oldAmount,
                'difference' => $newAmount - $oldAmount,
                'date' => $paymentDate->format('d.m.Y'),
                'quarter' => "{$targetQuarter}-chorak {$targetYear}"
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Payment update failed: ' . $e->getMessage());

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

        DB::beginTransaction();

        $paymentInfo = [
            'id' => $payment->id,
            'amount' => $payment->amount,
            'amount_formatted' => number_format($payment->amount, 0, '.', ' ') . ' so\'m',
            'date' => $payment->payment_date->format('d.m.Y'),
            'quarter' => "{$payment->quarter}-chorak {$payment->year}",
            'contract_id' => $payment->contract_id
        ];

        // Log before deletion
        \Log::info('Payment deleted:', [
            'payment_info' => $paymentInfo,
            'deleted_by' => auth()->id()
        ]);

        $payment->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "To'lov o'chirildi: {$paymentInfo['amount_formatted']} ({$paymentInfo['date']})",
            'deleted_payment' => $paymentInfo
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Payment deletion failed: ' . $e->getMessage());

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
                'amount_formatted' => number_format($payment->amount, 0, '.', ' ') . ' so\'m',
                'payment_date' => $payment->payment_date->format('d.m.Y'),
                'payment_date_iso' => $payment->payment_date->format('Y-m-d'),
                'payment_number' => $payment->payment_number,
                'notes' => $payment->notes,
                'year' => $payment->year,
                'quarter' => $payment->quarter,
                'quarter_info' => "{$payment->quarter}-chorak {$payment->year}",
                'created_at' => $payment->created_at->format('d.m.Y H:i'),
                'updated_at' => $payment->updated_at->format('d.m.Y H:i'),
                'can_edit' => $payment->created_at->diffInDays(now()) <= 30,
                'can_delete' => $payment->created_at->diffInDays(now()) <= 30,
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
                    $payment->update([
                        'year' => $request->new_year,
                        'quarter' => $request->new_quarter,
                        'updated_by' => auth()->id()
                    ]);
                    $updatedCount++;
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
            'monthly_breakdown' => $query->selectRaw('MONTH(payment_date) as month, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(function($item) {
                    return [
                        'month' => $item->month,
                        'month_name' => date('F', mktime(0, 0, 0, $item->month, 1)),
                        'count' => $item->count,
                        'total' => $item->total,
                        'total_formatted' => number_format($item->total, 0, '.', ' ') . ' so\'m'
                    ];
                }),
            'quarterly_breakdown' => $query->selectRaw('quarter, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('quarter')
                ->orderBy('quarter')
                ->get()
                ->map(function($item) {
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
}
