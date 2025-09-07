<?php

namespace App\Http\Controllers;

use App\Models\ActualPayment;
use App\Models\Contract;
use App\Models\PaymentHistory;
use App\Models\Subject;
use App\Models\Objectt;
use App\Models\ContractStatus;
use App\Models\BaseCalculationAmount;
use App\Models\District;
use App\Models\ObjectType;
use App\Models\ConstructionType;
use App\Models\ContractAmendment;
use App\Models\TerritorialZone;
use App\Models\PermitType;
use App\Models\IssuingAuthority;
use App\Models\OrgForm;
use App\Models\PaymentSchedule;
use App\Services\CoefficientCalculatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with(['subject', 'object', 'status'])
            ->latest()
            ->paginate(20);

        return view('contracts.index', compact('contracts'));
    }

    public function create()
    {
        $data = [
            'subjects' => Subject::where('is_active', true)->get(),
            'objects' => Objectt::with(['district', 'subject'])->where('is_active', true)->get(),
            'statuses' => ContractStatus::where('is_active', true)->get(),
            'baseAmounts' => BaseCalculationAmount::where('is_active', true)->orderBy('effective_from', 'desc')->get(),
            'districts' => District::where('is_active', true)->get(),
            'objectTypes' => ObjectType::where('is_active', true)->get(),
            'constructionTypes' => ConstructionType::where('is_active', true)->get(),
            'territorialZones' => TerritorialZone::where('is_active', true)->get(),
            'permitTypes' => PermitType::where('is_active', true)->get(),
            'issuingAuthorities' => IssuingAuthority::where('is_active', true)->get(),
            'orgForms' => OrgForm::where('is_active', true)->get(),
        ];

        return view('contracts.create', $data);
    }

    public function store(Request $request)
    {
        // Comprehensive validation including all fields
        $validated = $request->validate([
            // Basic contract information
            'contract_number' => 'required|string|max:50|unique:contracts',
            'object_id' => 'required|exists:objects,id',
            'subject_id' => 'required|exists:subjects,id',
            'contract_date' => 'required|date',
            'completion_date' => 'nullable|date|after:contract_date',
            'status_id' => 'required|exists:contract_statuses,id',

            // Calculation fields
            'base_amount_id' => 'required|exists:base_calculation_amounts,id',
            'contract_volume' => 'required|numeric|min:0.01',
            'calculated_bh' => 'required|numeric|min:0',

            // Payment terms
            'payment_type' => 'required|in:full,installment',
            'initial_payment_percent' => 'required|integer|min:0|max:100',
            'construction_period_years' => 'required|integer|min:1|max:10',
        ]);

        DB::beginTransaction();
        try {
            // Get related models with all relationships
            $object = Objectt::with([
                'subject',
                'district',
                'constructionType',
                'objectType',
                'territorialZone',
                'permitType',
                'issuingAuthority'
            ])->findOrFail($validated['object_id']);

            $subject = Subject::with('orgForm')->findOrFail($validated['subject_id']);
            $baseAmount = BaseCalculationAmount::findOrFail($validated['base_amount_id']);
            $status = ContractStatus::findOrFail($validated['status_id']);

            // Use calculator service for precise calculations
            $calculatorService = new CoefficientCalculatorService();

            // Get coefficient breakdown for transparency
            $coefficients = $calculatorService->getCoefficientBreakdown($object);

            // Calculate total amount using proper formula
            $totalAmount = $calculatorService->calculateTotalAmount(
                $object,
                $baseAmount,
                $validated['contract_volume']
            );

            // Build formula string for documentation
            $formulaString = $calculatorService->buildFormulaString(
                $object,
                $baseAmount,
                $validated['contract_volume'],
                $coefficients
            );

            // Calculate quarters count
            $quartersCount = $validated['construction_period_years'] * 4;

            // Create the contract with all data
            $contract = Contract::create([
                // Basic information
                'contract_number' => $validated['contract_number'],
                'object_id' => $validated['object_id'],
                'subject_id' => $validated['subject_id'],
                'contract_date' => $validated['contract_date'],
                'completion_date' => $validated['completion_date'],
                'status_id' => $validated['status_id'],

                // Financial calculations
                'base_amount_id' => $validated['base_amount_id'],
                'contract_volume' => $validated['contract_volume'],
                'coefficient' => $coefficients['total_coefficient'],
                'total_amount' => $totalAmount,
                'formula' => $formulaString,

                // Payment terms
                'payment_type' => $validated['payment_type'],
                'initial_payment_percent' => $validated['initial_payment_percent'],
                'construction_period_years' => $validated['construction_period_years'],
                'quarters_count' => $quartersCount,

                // System fields
                'is_active' => true,
                'created_by' => auth()->id(), // if using authentication
            ]);

            // Generate payment schedule based on payment type
            $this->generatePaymentSchedule($contract, $calculatorService);

            // Log the creation for audit trail
            Log::info('Contract created successfully', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'total_amount' => $totalAmount,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            // Return appropriate response based on request type
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shartnoma muvaffaqiyatli yaratildi',
                    'contract' => [
                        'id' => $contract->id,
                        'contract_number' => $contract->contract_number,
                        'total_amount' => $totalAmount
                    ],
                    'redirect' => route('contracts.show', $contract)
                ]);
            }

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', 'Shartnoma muvaffaqiyatli yaratildi');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validatsiya xatosi',
                    'errors' => $e->errors()
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            DB::rollback();

            // Log the error for debugging
            Log::error('Contract creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token'])
            ]);

            $errorMessage = 'Shartnoma yaratishda xato yuz berdi';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage . ': ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Generate payment schedule for the contract
     */
    private function generatePaymentSchedule(Contract $contract, CoefficientCalculatorService $calculatorService)
    {
        // Delete any existing payment schedules (in case of regeneration)
        $contract->paymentSchedules()->delete();

        if ($contract->payment_type === 'full') {
            // For full payment, create single payment record
            $contract->paymentSchedules()->create([
                'year' => date('Y', strtotime($contract->contract_date)),
                'quarter' => ceil(date('n', strtotime($contract->contract_date)) / 3),
                'quarter_amount' => $contract->total_amount,
                'custom_percent' => 100,
                'is_active' => true
            ]);
        } else {
            // Calculate installment payment schedule
            $paymentData = $calculatorService->calculatePaymentSchedule(
                $contract->total_amount,
                $contract->initial_payment_percent,
                $contract->quarters_count
            );

            $startYear = date('Y', strtotime($contract->contract_date));
            $startQuarter = ceil(date('n', strtotime($contract->contract_date)) / 3);

            // Create initial payment record
            $contract->paymentSchedules()->create([
                'year' => $startYear,
                'quarter' => $startQuarter,
                'quarter_amount' => $paymentData['initial_payment'],
                'custom_percent' => $contract->initial_payment_percent,
                'is_active' => true
            ]);

            // Create quarterly payment records
            for ($i = 0; $i < $contract->quarters_count; $i++) {
                $currentQuarter = (($startQuarter - 1 + $i) % 4) + 1;
                $currentYear = $startYear + intval(($startQuarter - 1 + $i) / 4);

                $contract->paymentSchedules()->create([
                    'year' => $currentYear,
                    'quarter' => $currentQuarter,
                    'quarter_amount' => $paymentData['quarterly_payment'],
                    'is_active' => true
                ]);
            }
        }
    }

    /**
     * Create subject via AJAX
     */
    public function createSubject(Request $request)
    {
        // Dynamic validation based on entity type
        $baseRules = [
            'is_legal_entity' => 'required|boolean',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'physical_address' => 'nullable|string|max:1000'
        ];

        if ($request->input('is_legal_entity')) {
            // Legal entity validation
            $rules = array_merge($baseRules, [
                'company_name' => 'required|string|max:300',
                'inn' => 'required|string|size:9|unique:subjects,inn',
                'org_form_id' => 'nullable|exists:org_forms,id',
                'bank_name' => 'nullable|string|max:200',
                'bank_code' => 'nullable|string|max:10',
                'bank_account' => 'nullable|string|max:30',
                'legal_address' => 'nullable|string|max:1000',
                'oked' => 'nullable|string|max:10',
                'is_resident' => 'boolean',
                'country_code' => 'nullable|string|max:3'
            ]);
        } else {
            // Physical person validation
            $rules = array_merge($baseRules, [
                'document_type' => 'required|string|max:50',
                'document_series' => 'nullable|string|max:10',
                'document_number' => 'required|string|max:20',
                'issued_by' => 'nullable|string|max:200',
                'issued_date' => 'nullable|date|before:today',
                'pinfl' => 'required|string|size:14|unique:subjects,pinfl'
            ]);
        }

        try {
            $validated = $request->validate($rules);

            // Create subject with all validated data
            $subject = Subject::create(array_merge($validated, [
                'is_active' => true
            ]));

            // Prepare response data
            $displayName = $subject->is_legal_entity
                ? $subject->company_name
                : ($subject->document_series . $subject->document_number);

            $identifier = $subject->is_legal_entity
                ? $subject->inn
                : $subject->pinfl;

            return response()->json([
                'success' => true,
                'message' => 'Buyurtmachi muvaffaqiyatli yaratildi',
                'subject' => [
                    'id' => $subject->id,
                    'text' => $displayName . ' (' . $identifier . ')',
                    'display_name' => $displayName,
                    'identifier' => $identifier
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Subject creation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Buyurtmachi yaratishda xato: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create object via AJAX
     */
    public function createObject(Request $request)
    {
        $validated = $request->validate([
            // Required fields
            'subject_id' => 'required|exists:subjects,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'required|string|max:500',
            'construction_volume' => 'required|numeric|min:0.01',

            // Optional basic fields
            'cadastre_number' => 'nullable|string|max:50',
            'geolocation' => 'nullable|string|max:100',

            // Volume fields
            'above_permit_volume' => 'nullable|numeric|min:0',
            'parking_volume' => 'nullable|numeric|min:0',
            'technical_rooms_volume' => 'nullable|numeric|min:0',
            'common_area_volume' => 'nullable|numeric|min:0',

            // Coefficient-related fields
            'construction_type_id' => 'nullable|exists:construction_types,id',
            'object_type_id' => 'nullable|exists:object_types,id',
            'territorial_zone_id' => 'nullable|exists:territorial_zones,id',
            'location_type' => 'nullable|string|max:100',

            // Permit information
            'application_number' => 'nullable|string|max:50',
            'application_date' => 'nullable|date',
            'permit_document_name' => 'nullable|string|max:300',
            'permit_type_id' => 'nullable|exists:permit_types,id',
            'issuing_authority_id' => 'nullable|exists:issuing_authorities,id',
            'permit_date' => 'nullable|date',
            'permit_number' => 'nullable|string|max:100',
            'work_type' => 'nullable|string|max:200',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Set default values for nullable numeric fields
            $validated = array_merge([
                'above_permit_volume' => 0,
                'parking_volume' => 0,
                'technical_rooms_volume' => 0,
                'common_area_volume' => 0,
                'location_type' => 'other_locations'
            ], $validated);

            // Create the object
            $object = Objectt::create(array_merge($validated, [
                'is_active' => true
            ]));

            // Load relationships for response - with null checking
            $object->load(['district', 'subject']);

            DB::commit();

            // Safely prepare response data with null checking
            $districtName = 'N/A';
            if ($object->district) {
                $districtName = $object->district->name_uz ?? $object->district->name_ru ?? 'Unknown District';
            }

            $displayText = $object->address .
                ' (' . $districtName . ') - ' .
                number_format($object->construction_volume, 2) . ' m³';

            return response()->json([
                'success' => true,
                'message' => 'Obyekt muvaffaqiyatli yaratildi',
                'object' => [
                    'id' => $object->id,
                    'text' => $displayText,
                    'construction_volume' => $object->construction_volume,
                    'above_permit_volume' => $object->above_permit_volume ?? 0,
                    'parking_volume' => $object->parking_volume ?? 0,
                    'technical_rooms_volume' => $object->technical_rooms_volume ?? 0,
                    'common_area_volume' => $object->common_area_volume ?? 0,
                    'construction_type_id' => $object->construction_type_id,
                    'object_type_id' => $object->object_type_id,
                    'territorial_zone_id' => $object->territorial_zone_id,
                    'location_type' => $object->location_type ?? 'other_locations',
                    'subject_id' => $object->subject_id
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();

            // More detailed error logging
            Log::error('Object creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token']),
                'validated_data' => $validated ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Obyekt yaratishda xato: ' . $e->getMessage()
            ], 500);
        }
    }
    private function calculateTotalAmount($object, $baseAmount, $validated)
    {
        $calculatorService = new \App\Services\CoefficientCalculatorService();
        return $calculatorService->calculateTotalAmount($object, $baseAmount);
    }

    private function buildFormulaString($object, $baseAmount, $validated)
    {
        $calculatorService = new \App\Services\CoefficientCalculatorService();
        return $calculatorService->buildFormulaString($object, $baseAmount);
    }


    // Additional AJAX endpoints for enhanced functionality
    public function getObjectsBySubject(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $objects = Objectt::with('district')
            ->where('subject_id', $validated['subject_id'])
            ->where('is_active', true)
            ->get()
            ->map(function ($object) {
                return [
                    'id' => $object->id,
                    'text' => $object->address . ' (' . $object->district->name_ru . ') - ' . number_format($object->construction_volume, 2) . ' м³',
                    'construction_volume' => $object->construction_volume,
                    'above_permit_volume' => $object->above_permit_volume,
                    'parking_volume' => $object->parking_volume,
                    'technical_rooms_volume' => $object->technical_rooms_volume,
                    'common_area_volume' => $object->common_area_volume,
                    'calculated_volume' => $object->calculated_volume,
                ];
            });

        return response()->json([
            'success' => true,
            'objects' => $objects
        ]);
    }

    public function calculateCoefficients(Request $request)
    {
        $validated = $request->validate([
            'object_id' => 'required|exists:objects,id'
        ]);

        $object = Objectt::with(['constructionType', 'objectType', 'territorialZone'])->find($validated['object_id']);
        $calculatorService = new \App\Services\CoefficientCalculatorService();
        $coefficients = $calculatorService->getCoefficientBreakdown($object);

        return response()->json([
            'success' => true,
            'coefficients' => $coefficients
        ]);
    }

    public function validateObjectVolumes(Request $request)
    {
        $validated = $request->validate([
            'construction_volume' => 'required|numeric|min:0.01',
            'above_permit_volume' => 'nullable|numeric|min:0',
            'parking_volume' => 'nullable|numeric|min:0',
            'technical_rooms_volume' => 'nullable|numeric|min:0',
            'common_area_volume' => 'nullable|numeric|min:0',
        ]);

        $calculatorService = new \App\Services\CoefficientCalculatorService();
        $errors = $calculatorService->validateObjectVolumes($validated);

        if (empty($errors)) {
            $hb = floatval($validated['construction_volume']);
            $hyu = floatval($validated['above_permit_volume'] ?? 0);
            $ha = floatval($validated['parking_volume'] ?? 0);
            $ht = floatval($validated['technical_rooms_volume'] ?? 0);
            $hu = floatval($validated['common_area_volume'] ?? 0);

            $effectiveVolume = ($hb + $hyu) - ($ha + $ht + $hu);

            return response()->json([
                'success' => true,
                'effective_volume' => $effectiveVolume,
                'message' => 'Объемы корректны'
            ]);
        }

        return response()->json([
            'success' => false,
            'errors' => $errors
        ], 422);
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
        $data = [
            'contract' => $contract->load(['subject', 'object.district', 'status', 'baseAmount']),
            'subjects' => Subject::where('is_active', true)->get(),
            'objects' => Objectt::with(['district', 'subject'])->where('is_active', true)->get(),
            'statuses' => ContractStatus::where('is_active', true)->get(),
            'baseAmounts' => BaseCalculationAmount::where('is_active', true)->orderBy('effective_from', 'desc')->get(),
            'districts' => District::where('is_active', true)->get(),
            'objectTypes' => ObjectType::where('is_active', true)->get(),
            'constructionTypes' => ConstructionType::where('is_active', true)->get(),
            'territorialZones' => TerritorialZone::where('is_active', true)->get(),
            'permitTypes' => PermitType::where('is_active', true)->get(),
            'issuingAuthorities' => IssuingAuthority::where('is_active', true)->get(),
            'orgForms' => OrgForm::where('is_active', true)->get(),
        ];

        return view('contracts.edit', $data);
    }

public function update(Request $request, Contract $contract)
{
    $request->validate([
        'contract_number' => 'required|string|max:50|unique:contracts,contract_number,' . $contract->id,
        'contract_date' => 'required|date',
        'completion_date' => 'nullable|date',
        'total_amount' => 'required|numeric|min:0',
        'payment_type' => 'required|in:full,installment',
        'initial_payment_percent' => 'required|integer|min:0|max:100',
        'construction_period_years' => 'required|integer|min:1|max:10',
        'quarters_count' => 'required|integer|min:0|max:20',
    ]);

    try {
        // ✅ Capture old values before update
        $oldValues = $contract->only([
            'contract_number',
            'contract_date',
            'completion_date',
            'total_amount',
            'payment_type',
            'initial_payment_percent',
            'construction_period_years',
            'quarters_count',
        ]);

        // Perform update
        $contract->update([
            'contract_number' => $request->contract_number,
            'contract_date' => $request->contract_date,
            'completion_date' => $request->completion_date,
            'total_amount' => $request->total_amount,
            'payment_type' => $request->payment_type,
            'initial_payment_percent' => $request->initial_payment_percent,
            'construction_period_years' => $request->construction_period_years,
            'quarters_count' => $request->quarters_count,
        ]);

        // ✅ Capture new values after update
        $newValues = $contract->only([
            'contract_number',
            'contract_date',
            'completion_date',
            'total_amount',
            'payment_type',
            'initial_payment_percent',
            'construction_period_years',
            'quarters_count',
        ]);

        // ✅ Log history
        \App\Models\PaymentHistory::logAction(
            $contract->id,
            'updated',
            'contracts',
            $contract->id,
            $oldValues,
            $newValues,
            "Shartnoma ma'lumotlari yangilandi"
        );

        return response()->json([
            'success' => true,
            'message' => 'Shartnoma muvaffaqiyatli yangilandi',
            'contract' => $contract->fresh()
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server xatosi: ' . $e->getMessage()
        ], 500);
    }
}
    public function destroy(Contract $contract)
    {
        try {
            $contract->delete(); // Soft delete

            return redirect()->route('contracts.index')->with('success', 'Договор успешно удален');
        } catch (\Exception $e) {
            Log::error('Contract deletion error: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при удалении договора');
        }
    }

    // AJAX methods for creating subjects and objects


    public function getZoneByCoordinates(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);

        try {
            // Load and parse KML file
            $kmlPath = public_path('zona.kml');
            if (!file_exists($kmlPath)) {
                throw new \Exception('Zone KML file not found');
            }

            $kmlContent = file_get_contents($kmlPath);
            $xml = simplexml_load_string($kmlContent);
            $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');

            $point = [$validated['lng'], $validated['lat']];
            $zone = null;

            foreach ($xml->xpath('//kml:Placemark') as $placemark) {
                $extendedData = $placemark->ExtendedData;
                if ($extendedData) {
                    $schemaData = $extendedData->SchemaData;
                    $zoneName = null;

                    foreach ($schemaData->SimpleData as $simpleData) {
                        if ((string)$simpleData['name'] === 'SONI') {
                            $zoneName = (string)$simpleData;
                            break;
                        }
                    }

                    if ($zoneName) {
                        $coordinates = (string)$placemark->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates;
                        $polygon = $this->parseCoordinates($coordinates);

                        if ($this->pointInPolygon($point, $polygon)) {
                            $zone = [
                                'name' => $zoneName,
                                'coefficient' => $this->getZoneCoefficient($zoneName)
                            ];
                            break;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'zone' => $zone
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при определении зоны: ' . $e->getMessage()
            ], 422);
        }
    }

    private function parseCoordinates($coordinatesString)
    {
        $coordinates = [];
        $points = explode(' ', trim($coordinatesString));

        foreach ($points as $point) {
            $coords = explode(',', trim($point));
            if (count($coords) >= 2) {
                $coordinates[] = [floatval($coords[0]), floatval($coords[1])];
            }
        }

        return $coordinates;
    }

    private function pointInPolygon($point, $polygon)
    {
        $x = $point[0];
        $y = $point[1];
        $inside = false;

        for ($i = 0, $j = count($polygon) - 1; $i < count($polygon); $j = $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];

            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    private function getZoneCoefficient($zoneName)
    {
        $coefficients = [
            'ЗОНА-1' => 1.40,
            'ЗОНА-2' => 1.25,
            'ЗОНА-3' => 1.00,
            'ЗОНА-4' => 0.75,
            'ЗОНА-5' => 0.50
        ];

        return $coefficients[$zoneName] ?? 1.00;
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






//-----------------------------


// Add these methods to your ContractController
 public function payment_update(Contract $contract)
    {
        $contract->load([
            'subject',
            'object.district',
            'status',
            'paymentSchedules' => function($query) {
                $query->where('is_active', true)->orderBy('year')->orderBy('quarter');
            },
            'actualPayments' => function($query) {
                $query->orderBy('payment_date', 'desc');
            }
        ]);

        // Calculate payment breakdown
        $initialPaymentAmount = ($contract->total_amount * $contract->initial_payment_percent) / 100;
        $remainingAmount = $contract->total_amount - $initialPaymentAmount;

        // Get payment summary by quarters
        $paymentSummary = [];
        $hasPaymentData = false;

        if ($contract->paymentSchedules->count() > 0) {
            $years = $contract->paymentSchedules->pluck('year')->unique()->sort()->values();
            $hasPaymentData = true;
        } else {
            $years = collect([date('Y'), date('Y') + 1]);
        }

        foreach ($years as $year) {
            $yearData = [];
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $planPayment = $contract->paymentSchedules
                    ->where('year', $year)
                    ->where('quarter', $quarter)
                    ->first();

                $factPayments = $contract->actualPayments
                    ->where('year', $year)
                    ->where('quarter', $quarter);

                $factTotal = $factPayments->sum('amount');
                $planAmount = $planPayment ? $planPayment->quarter_amount : 0;

                $yearData[$quarter] = [
                    'plan' => $planPayment,
                    'plan_amount' => $planAmount,
                    'fact_total' => $factTotal,
                    'debt' => $planAmount - $factTotal,
                    'fact_payments' => $factPayments,
                    'payment_percent' => $planAmount > 0 ? ($factTotal / $planAmount) * 100 : 0,
                    'has_data' => ($planAmount > 0 || $factTotal > 0)
                ];
            }

            if ($hasPaymentData && !collect($yearData)->pluck('has_data')->contains(true)) {
                continue;
            }

            $paymentSummary[$year] = $yearData;
        }

        if (empty($paymentSummary)) {
            $currentYear = date('Y');
            $yearData = [];
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $yearData[$quarter] = [
                    'plan' => null,
                    'plan_amount' => 0,
                    'fact_total' => 0,
                    'debt' => 0,
                    'fact_payments' => collect([]),
                    'payment_percent' => 0,
                    'has_data' => false
                ];
            }
            $paymentSummary[$currentYear] = $yearData;
        }

        return view('contracts.payment_update', compact(
            'contract',
            'paymentSummary',
            'hasPaymentData',
            'initialPaymentAmount',
            'remainingAmount'
        ));
    }

    /**
     * Update contract basic information
     */
public function updateContractInfo(Request $request, Contract $contract)
{
    $request->validate([
        'contract_number' => 'required|string|max:50|unique:contracts,contract_number,' . $contract->id,
        'contract_date' => 'required|date',
        'total_amount' => 'required|numeric|min:0',
        'initial_payment_percent' => 'required|integer|min:0|max:100',
        'construction_period_years' => 'required|integer|min:1|max:10',
        'quarters_count' => 'required|integer|min:1|max:20',
        'payment_type' => 'required|in:installment,full'
    ]);

    try {
        DB::beginTransaction();

        // Store old values before update
        $oldValues = $contract->only([
            'contract_number', 'contract_date', 'total_amount',
            'initial_payment_percent', 'construction_period_years',
            'quarters_count', 'payment_type'
        ]);

        // Update contract
        $contract->update([
            'contract_number' => $request->contract_number,
            'contract_date' => $request->contract_date,
            'total_amount' => $request->total_amount,
            'initial_payment_percent' => $request->initial_payment_percent,
            'construction_period_years' => $request->construction_period_years,
            'quarters_count' => $request->quarters_count,
            'payment_type' => $request->payment_type
        ]);

        // Get new values after update
        $newValues = $contract->only([
            'contract_number', 'contract_date', 'total_amount',
            'initial_payment_percent', 'construction_period_years',
            'quarters_count', 'payment_type'
        ]);

        // Try-catch inside to isolate failure
        try {
         PaymentHistory::logAction(
            $contract->id,
            'updated',
            'contracts',
            $contract->id,
            $oldValues,
            $contract->only([
                'contract_number', 'contract_date', 'total_amount',
                'initial_payment_percent', 'construction_period_years',
                'quarters_count', 'payment_type'
            ]),
            'Shartnoma asosiy ma\'lumotlari yangilandi'
        );

        } catch (\Exception $logError) {
            \Log::error('Failed to log contract history', [
                'error' => $logError->getMessage(),
                'contract_id' => $contract->id
            ]);
            // Optional: you could throw here to cancel entire transaction
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Shartnoma ma\'lumotlari muvaffaqiyatli yangilandi',
            'contract' => $contract->fresh()
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Contract update failed', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Xatolik yuz berdi: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Auto-generate payment schedule: $plan_sum - n% = $r, then $r / quarter_num
     */
    public function generateAutoPaymentSchedule(Request $request, Contract $contract)
    {
        $request->validate([
            'total_quarters' => 'required|integer|min:1|max:20', // 16 means 4 years
            'start_year' => 'required|integer|min:2020|max:2050'
        ]);

        try {
            DB::beginTransaction();

            $totalQuarters = $request->total_quarters;
            $startYear = $request->start_year;

            // Calculate: $plan_sum - n% = $r
            $planSum = $contract->total_amount;
            $initialPercent = $contract->initial_payment_percent;
            $initialAmount = ($planSum * $initialPercent) / 100;
            $remainingAmount = $planSum - $initialAmount; // This is $r

            // Calculate: $r / quarter_num
            $quarterAmount = $remainingAmount / $totalQuarters;

            // Delete existing schedules
            PaymentSchedule::where('contract_id', $contract->id)
                ->where('amendment_id', null)
                ->delete();

            // Generate quarters
            $currentYear = $startYear;
            $currentQuarter = 1;

            for ($i = 0; $i < $totalQuarters; $i++) {
                PaymentSchedule::create([
                    'contract_id' => $contract->id,
                    'year' => $currentYear,
                    'quarter' => $currentQuarter,
                    'quarter_amount' => $quarterAmount,
                    'custom_percent' => ($quarterAmount / $remainingAmount) * 100,
                    'amendment_id' => null,
                    'is_active' => true
                ]);

                // Move to next quarter
                $currentQuarter++;
                if ($currentQuarter > 4) {
                    $currentQuarter = 1;
                    $currentYear++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$totalQuarters} чорак учун автоматик график тузилди",
                'calculation' => [
                    'plan_sum' => $planSum,
                    'initial_percent' => $initialPercent,
                    'initial_amount' => $initialAmount,
                    'remaining_amount' => $remainingAmount,
                    'total_quarters' => $totalQuarters,
                    'quarter_amount' => $quarterAmount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'График тузишда хатолик: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual quarter plan creation
     */
    public function storePlanPayment(Request $request, Contract $contract)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2050',
            'quarter' => 'required|integer|min:1|max:4',
            'amount' => 'required|numeric|min:0'
        ]);

        try {
            $planPayment = PaymentSchedule::updateOrCreate(
                [
                    'contract_id' => $contract->id,
                    'year' => $request->year,
                    'quarter' => $request->quarter,
                    'amendment_id' => null,
                    'is_active' => true
                ],
                [
                    'quarter_amount' => $request->amount
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'План тўлов муваффақиятли сақланди',
                'data' => $planPayment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Хатолик: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store actual payment with proper quarter calculation
     */

public function storeFactPayment(Request $request, Contract $contract)
{
    try {
        // Enhanced validation with contract date checking
        $request->validate([
            'payment_date' => [
                'required',
                'date',
                'before_or_equal:today',
                function ($attribute, $value, $fail) use ($contract) {
                    $paymentDate = Carbon::parse($value);
                    $contractDate = Carbon::parse($contract->contract_date);
                    
                    if ($paymentDate < $contractDate) {
                        $fail("To'lov sanasi shartnoma sanasidan ({$contractDate->format('d.m.Y')}) oldin bo'lishi mumkin emas");
                    }
                }
            ],
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_number' => 'nullable|string|max:50',
            'payment_notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        // Check if payment exceeds contract total
        $totalPaid = $contract->actualPayments()->sum('amount');
        $newTotal = $totalPaid + $request->payment_amount;
        
        if ($newTotal > $contract->total_amount) {
            return response()->json([
                'success' => false,
                'message' => 'To\'lov summasi shartnoma summasidan oshib ketmoqda. Qolgan summa: ' . 
                           number_format($contract->total_amount - $totalPaid, 0) . ' so\'m'
            ], 422);
        }

        // Create payment record
        $payment = ActualPayment::create([
            'contract_id' => $contract->id,
            'payment_date' => $request->payment_date,
            'amount' => $request->payment_amount,
            'payment_number' => $request->payment_number,
            'notes' => $request->payment_notes,
            'created_by' => auth()->id()
        ]);

        // Log payment history with quarter info
        $paymentDate = Carbon::parse($request->payment_date);
        $quarter = ceil($paymentDate->month / 3);
        
        PaymentHistory::logAction(
            $contract->id,
            'created',
            'actual_payments',
            $payment->id,
            null,
            $payment->toArray(),
            "Yangi to'lov qo'shildi: " . number_format($payment->amount, 0) . " so'm ({$quarter}-chorak {$paymentDate->year})"
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'To\'lov muvaffaqiyatli qo\'shildi',
            'payment' => $payment,
            'quarter_info' => [
                'year' => $paymentDate->year,
                'quarter' => $quarter
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error storing fact payment', [
            'contract_id' => $contract->id,
            'error' => $e->getMessage(),
            'contract_date' => $contract->contract_date
        ]);

        return response()->json([
            'success' => false,
            'message' => 'To\'lovni saqlashda xatolik: ' . $e->getMessage()
        ], 500);
    }
}

private function calculateQuarterFromDate($date)
{
    $month = Carbon::parse($date)->month;
    return ceil($month / 3);
}

    /**
     * Calculate payment plan preview
     */
    public function calculatePaymentPlan(Request $request, Contract $contract)
    {
        $request->validate([
            'total_quarters' => 'required|integer|min:1|max:20',
            'initial_percent' => 'nullable|integer|min:0|max:100'
        ]);

        $totalQuarters = $request->total_quarters;
        $initialPercent = $request->initial_percent ?? $contract->initial_payment_percent;
        $planSum = $contract->total_amount;

        // Formula: $plan_sum - n% = $r
        $initialAmount = ($planSum * $initialPercent) / 100;
        $remainingAmount = $planSum - $initialAmount;

        // Formula: $r / quarter_num
        $quarterAmount = $remainingAmount / $totalQuarters;

        $years = ceil($totalQuarters / 4);
        $quarterlyBreakdown = [];

        $currentYear = date('Y');
        $currentQuarter = 1;

        for ($i = 0; $i < $totalQuarters; $i++) {
            $quarterlyBreakdown[] = [
                'sequence' => $i + 1,
                'year' => $currentYear,
                'quarter' => $currentQuarter,
                'quarter_name' => "{$currentQuarter}-чорак {$currentYear}",
                'amount' => $quarterAmount,
                'formatted_amount' => number_format($quarterAmount / 1000000, 2) . 'М'
            ];

            $currentQuarter++;
            if ($currentQuarter > 4) {
                $currentQuarter = 1;
                $currentYear++;
            }
        }

        return response()->json([
            'success' => true,
            'calculation' => [
                'plan_sum' => $planSum,
                'initial_percent' => $initialPercent,
                'initial_amount' => $initialAmount,
                'remaining_amount' => $remainingAmount,
                'total_quarters' => $totalQuarters,
                'quarter_amount' => $quarterAmount,
                'years_span' => $years
            ],
            'quarterly_breakdown' => $quarterlyBreakdown,
            'formatted' => [
                'plan_sum' => number_format($planSum / 1000000, 2) . 'М',
                'initial_amount' => number_format($initialAmount / 1000000, 2) . 'М',
                'remaining_amount' => number_format($remainingAmount / 1000000, 2) . 'М',
                'quarter_amount' => number_format($quarterAmount / 1000000, 2) . 'М'
            ]
        ]);
    }

    /**
     * Delete payment schedule
     */
    public function deletePlanPayment($id)
    {
        try {
            $planPayment = PaymentSchedule::findOrFail($id);

            if ($planPayment->amendment_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Автоматик тузилган графикни ўчириб бўлмайди'
                ], 403);
            }

            $planPayment->delete();

            return response()->json([
                'success' => true,
                'message' => 'План тўлов ўчирилди'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Хатолик: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete actual payment
     */
public function deleteFactPayment(Contract $contract, $paymentId)
{
    try {
        $payment = ActualPayment::where('contract_id', $contract->id)
                                ->findOrFail($paymentId);

        $oldValues = $payment->toArray();

        DB::beginTransaction();

        // Log before deletion
        PaymentHistory::logAction(
            $contract->id,
            'deleted',
            'actual_payments',
            $payment->id,
            $oldValues,
            null,
            "To'lov o'chirildi: " . number_format($payment->amount, 0) . " so'm"
        );

        $payment->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'To\'lov muvaffaqiyatli o\'chirildi'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error deleting payment', [
            'contract_id' => $contract->id,
            'payment_id' => $paymentId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'To\'lovni o\'chirishda xatolik yuz berdi: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Get contract statistics
     */
    public function getContractStatistics(Contract $contract)
    {
        $initialAmount = ($contract->total_amount * $contract->initial_payment_percent) / 100;
        $remainingAmount = $contract->total_amount - $initialAmount;

        $plannedTotal = $contract->paymentSchedules()->where('is_active', true)->sum('quarter_amount');
        $paidTotal = $contract->actualPayments()->sum('amount');

        $totalDebt = $contract->total_amount - $paidTotal;
        $scheduleDebt = $plannedTotal - $paidTotal;

        return response()->json([
            'contract_info' => [
                'total_amount' => $contract->total_amount,
                'initial_percent' => $contract->initial_payment_percent,
                'initial_amount' => $initialAmount,
                'remaining_amount' => $remainingAmount
            ],
            'payment_status' => [
                'planned_total' => $plannedTotal,
                'paid_total' => $paidTotal,
                'total_debt' => $totalDebt,
                'schedule_debt' => $scheduleDebt,
                'payment_progress' => $contract->total_amount > 0 ? ($paidTotal / $contract->total_amount) * 100 : 0,
                'schedule_progress' => $plannedTotal > 0 ? ($paidTotal / $plannedTotal) * 100 : 0
            ],
            'formatted' => [
                'total_amount' => number_format($contract->total_amount / 1000000, 2) . 'М',
                'initial_amount' => number_format($initialAmount / 1000000, 2) . 'М',
                'remaining_amount' => number_format($remainingAmount / 1000000, 2) . 'М',
                'planned_total' => number_format($plannedTotal / 1000000, 2) . 'М',
                'paid_total' => number_format($paidTotal / 1000000, 2) . 'М',
                'total_debt' => number_format($totalDebt / 1000000, 2) . 'М'
            ]
        ]);
    }
//21111111111111111111111111


 /**
     * Handle quarterly distribution when only 1 or 2 quarters are selected
     */
public function getQuarterlyBreakdown(Contract $contract)
{
    try {
        // Get payment schedules ordered properly
        $schedules = PaymentSchedule::where('contract_id', $contract->id)
                                   ->where('is_active', true)
                                   ->orderBy('year')
                                   ->orderBy('quarter')
                                   ->get();

        // Get actual payments
        $payments = ActualPayment::where('contract_id', $contract->id)
                                ->orderBy('payment_date')
                                ->get();

        $breakdown = [];
        $currentDate = now();
        $contractDate = Carbon::parse($contract->contract_date);

        foreach ($schedules as $schedule) {
            $year = $schedule->year;
            $quarter = $schedule->quarter;

            // Initialize year if not exists
            if (!isset($breakdown[$year])) {
                $breakdown[$year] = [];
            }

            // Get quarter date range
            $quarterDates = $this->getQuarterDateRangeFromContract($year, $quarter, $contractDate);
            
            // Check if quarter is overdue (past end date and has unpaid balance)
            $isOverdue = $currentDate > $quarterDates['end'];

            // Calculate payments for this specific quarter
            $quarterPayments = $payments->filter(function ($payment) use ($year, $quarter) {
                return $payment->year == $year && $payment->quarter == $quarter;
            });

            $factTotal = $quarterPayments->sum('amount');
            $planAmount = $schedule->quarter_amount;
            $debt = $planAmount - $factTotal;
            $paymentPercent = $planAmount > 0 ? ($factTotal / $planAmount) * 100 : 0;

            $breakdown[$year][$quarter] = [
                'year' => $year,
                'quarter' => $quarter,
                'plan_amount' => $planAmount,
                'fact_total' => $factTotal,
                'debt' => $debt,
                'payment_percent' => min(100, $paymentPercent),
                'is_overdue' => $isOverdue && $debt > 0,
                'quarter_start' => $quarterDates['start_formatted'],
                'quarter_end' => $quarterDates['end_formatted'],
                'contract_quarter_info' => [
                    'is_contract_start_quarter' => ($year == $contractDate->year && $quarter == ceil($contractDate->month / 3)),
                    'contract_start_date' => $contractDate->format('d.m.Y')
                ],
                'payments' => $quarterPayments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_date' => $payment->payment_date->format('Y-m-d'),
                        'amount' => $payment->amount,
                        'payment_number' => $payment->payment_number,
                        'notes' => $payment->notes
                    ];
                })->values()->toArray()
            ];
        }

        return response()->json($breakdown);

    } catch (\Exception $e) {
        \Log::error('Error getting quarterly breakdown', [
            'contract_id' => $contract->id,
            'error' => $e->getMessage(),
            'contract_date' => $contract->contract_date
        ]);

        return response()->json([]);
    }
}

private function getQuarterDateRangeFromContract($year, $quarter, $contractDate)
{
    $startMonth = ($quarter - 1) * 3 + 1;
    $endMonth = $quarter * 3;

    $start = Carbon::create($year, $startMonth, 1)->startOfDay();
    $end = Carbon::create($year, $endMonth, 1)->endOfMonth()->endOfDay();

    // If this is the contract start quarter and year, adjust start date
    if ($year == $contractDate->year && $quarter == ceil($contractDate->month / 3)) {
        $start = $contractDate->copy()->startOfDay();
    }

    return [
        'start' => $start,
        'end' => $end,
        'start_formatted' => $start->format('d.m.Y'),
        'end_formatted' => $end->format('d.m.Y')
    ];
}

public function createQuarterlySchedule(Request $request, Contract $contract)
{
    try {
        $request->validate([
            'schedule_type' => 'required|in:auto,custom',
            'quarters_count' => 'required|integer|min:1|max:20',
            'total_schedule_amount' => 'required|numeric|min:0.01',
            'quarterly_schedule' => 'sometimes|json' // New: Pre-calculated schedule data
        ]);

        DB::beginTransaction();

        $scheduleType = $request->schedule_type;
        $quartersCount = $request->quarters_count;
        $totalAmount = $request->total_schedule_amount;

        // CRITICAL: Calculate quarters starting from contract date, not year start
        $contractDate = Carbon::parse($contract->contract_date);
        $contractYear = $contractDate->year;
        $contractMonth = $contractDate->month;
        $contractQuarter = ceil($contractMonth / 3); // 1-4

        // Delete existing schedules for this contract
        PaymentSchedule::where('contract_id', $contract->id)->delete();

        // Handle pre-calculated schedule from frontend
        if ($request->has('quarterly_schedule')) {
            $quarterlySchedule = json_decode($request->quarterly_schedule, true);
            
            foreach ($quarterlySchedule as $scheduleItem) {
                PaymentSchedule::create([
                    'contract_id' => $contract->id,
                    'year' => $scheduleItem['year'],
                    'quarter' => $scheduleItem['quarter'],
                    'quarter_amount' => round($scheduleItem['quarter_amount'], 2),
                    'is_active' => true,
                    'created_by' => auth()->id()
                ]);
            }
        } else {
            // Fallback: Generate schedule in backend
            $currentYear = $contractYear;
            $currentQuarter = $contractQuarter;

            for ($i = 0; $i < $quartersCount; $i++) {
                if ($scheduleType === 'auto') {
                    $quarterAmount = $totalAmount / $quartersCount;
                } else {
                    $percent = (float) $request->input("quarter_" . ($i + 1) . "_percent", 0);
                    $quarterAmount = $totalAmount * ($percent / 100);
                }

                PaymentSchedule::create([
                    'contract_id' => $contract->id,
                    'year' => $currentYear,
                    'quarter' => $currentQuarter,
                    'quarter_amount' => round($quarterAmount, 2),
                    'is_active' => true,
                    'created_by' => auth()->id()
                ]);

                // Move to next quarter
                $currentQuarter++;
                if ($currentQuarter > 4) {
                    $currentQuarter = 1;
                    $currentYear++;
                }
            }
        }

        // Log schedule creation with proper contract date info
        PaymentHistory::logAction(
            $contract->id,
            'created',
            'payment_schedules',
            null,
            null,
            [
                'quarters_count' => $quartersCount,
                'total_amount' => $totalAmount,
                'contract_start_date' => $contract->contract_date->format('Y-m-d'),
                'start_quarter' => $contractQuarter,
                'start_year' => $contractYear
            ],
            "Shartnoma sanasidan boshlanadigan {$quartersCount} ta choraklik to'lov jadvali yaratildi ({$contractQuarter}-chorak {$contractYear} dan)"
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "To'lov jadvali muvaffaqiyatli yaratildi ({$contractQuarter}-chorak {$contractYear} dan boshlanadi)",
            'start_info' => [
                'start_quarter' => $contractQuarter,
                'start_year' => $contractYear,
                'contract_date' => $contract->contract_date->format('d.m.Y')
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error creating quarterly schedule', [
            'contract_id' => $contract->id,
            'error' => $e->getMessage(),
            'contract_date' => $contract->contract_date
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Jadval yaratishda xatolik: ' . $e->getMessage()
        ], 500);
    }
}

private function isQuarterOverdue($year, $quarter)
{
    $currentYear = now()->year;
    $currentQuarter = ceil(now()->month / 3);

    if ($year < $currentYear) {
        return true;
    } elseif ($year == $currentYear && $quarter < $currentQuarter) {
        return true;
    }

    return false;
}

    /**
     * Get contract payment summary with initial payment calculations
     */
   public function getContractPaymentSummary(Contract $contract)
{
    try {
        $totalPaid = $contract->actualPayments()->sum('amount');
        $totalPlan = PaymentSchedule::where('contract_id', $contract->id)
                                   ->where('is_active', true)
                                   ->sum('quarter_amount');
        
        $overduePayments = $this->getOverduePaymentsForContract($contract);
        $upcomingPayments = $this->getUpcomingPaymentsForContract($contract);
        
        $completionPercentage = $contract->total_amount > 0 
            ? ($totalPaid / $contract->total_amount) * 100 
            : 0;

        return response()->json([
            'success' => true,
            'summary' => [
                'contract_total' => $contract->total_amount,
                'total_paid' => $totalPaid,
                'total_plan' => $totalPlan,
                'remaining_amount' => $contract->total_amount - $totalPaid,
                'completion_percentage' => round($completionPercentage, 2),
                'overdue_debt' => $overduePayments['total_debt'],
                'overdue_count' => $overduePayments['count'],
                'upcoming_payments' => $upcomingPayments,
                'last_payment_date' => $contract->actualPayments()
                                               ->orderBy('payment_date', 'desc')
                                               ->value('payment_date'),
                'average_payment' => $contract->actualPayments()->avg('amount') ?? 0,
                'payment_count' => $contract->actualPayments()->count()
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error getting payment summary', [
            'contract_id' => $contract->id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Hisobot olishda xatolik yuz berdi'
        ], 500);
    }
}

/**
 * Helper method to get upcoming payments for a contract
 */
private function getUpcomingPaymentsForContract(Contract $contract)
{
    $currentDate = now();
    $upcomingPayments = [];

    $schedules = PaymentSchedule::where('contract_id', $contract->id)
                               ->where('is_active', true)
                               ->orderBy('year')
                               ->orderBy('quarter')
                               ->get();

    foreach ($schedules as $schedule) {
        $quarterDates = ActualPayment::getQuarterDateRange($schedule->year, $schedule->quarter);
        
        if ($currentDate <= $quarterDates['end']) {
            $quarterPaid = ActualPayment::where('contract_id', $contract->id)
                                       ->where('year', $schedule->year)
                                       ->where('quarter', $schedule->quarter)
                                       ->sum('amount');
            
            $remaining = $schedule->quarter_amount - $quarterPaid;
            if ($remaining > 0) {
                $upcomingPayments[] = [
                    'year' => $schedule->year,
                    'quarter' => $schedule->quarter,
                    'due_date' => $quarterDates['end_formatted'],
                    'planned_amount' => $schedule->quarter_amount,
                    'paid_amount' => $quarterPaid,
                    'remaining_amount' => $remaining,
                    'days_remaining' => $currentDate->diffInDays($quarterDates['end'], false)
                ];
            }
        }
    }

    return collect($upcomingPayments)->take(5)->values();
}

public function validatePaymentDate(Request $request)
{
    $request->validate([
        'payment_date' => 'required|date',
        'contract_id' => 'required|exists:contracts,id'
    ]);

    try {
        $contract = Contract::findOrFail($request->contract_id);
        $paymentDate = Carbon::parse($request->payment_date);
        $contractDate = $contract->contract_date;

        $errors = [];

        // Check if payment date is before contract date
        if ($paymentDate < $contractDate) {
            $errors[] = 'To\'lov sanasi shartnoma sanasidan oldin bo\'lishi mumkin emas';
        }

        // Check if payment date is in the future
        if ($paymentDate > now()) {
            $errors[] = 'To\'lov sanasi kelajakda bo\'lishi mumkin emas';
        }

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
            'quarter_info' => [
                'year' => $paymentDate->year,
                'quarter' => ActualPayment::calculateQuarterFromDate($paymentDate),
                'quarter_name' => ActualPayment::calculateQuarterFromDate($paymentDate) . '-chorak ' . $paymentDate->year . ' yil'
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'valid' => false,
            'errors' => ['Sanani tekshirishda xatolik yuz berdi'],
            'quarter_info' => null
        ], 500);
    }
}


public function editPayment(Request $request, Contract $contract, $paymentId)
{
    try {
        $payment = ActualPayment::where('contract_id', $contract->id)
                                ->findOrFail($paymentId);

        $oldValues = $payment->toArray();

        // Enhanced validation
        $request->validate([
            'payment_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . $contract->contract_date->format('Y-m-d')
            ],
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_number' => 'nullable|string|max:50',
            'payment_notes' => 'nullable|string|max:500'
        ], [
            'payment_date.after_or_equal' => 'To\'lov sanasi shartnoma sanasidan oldin bo\'lishi mumkin emas',
            'payment_date.before_or_equal' => 'To\'lov sanasi bugundan kech bo\'lishi mumkin emas',
            'payment_amount.min' => 'To\'lov summasi 0 dan katta bo\'lishi kerak'
        ]);

        DB::beginTransaction();

        // Check if updated payment exceeds contract total
        $totalPaidExcludingThis = $contract->actualPayments()
                                          ->where('id', '!=', $payment->id)
                                          ->sum('amount');
        $newTotal = $totalPaidExcludingThis + $request->payment_amount;
        
        if ($newTotal > $contract->total_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Yangilangan to\'lov summasi shartnoma summasidan oshib ketmoqda'
            ], 422);
        }

        // Update payment
        $payment->update([
            'payment_date' => $request->payment_date,
            'amount' => $request->payment_amount,
            'payment_number' => $request->payment_number,
            'notes' => $request->payment_notes
        ]);

        // Log payment history
        PaymentHistory::logAction(
            $contract->id,
            'updated',
            'actual_payments',
            $payment->id,
            $oldValues,
            $payment->fresh()->toArray(),
            "To'lov yangilandi: " . number_format($payment->amount, 0) . " so'm"
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'To\'lov muvaffaqiyatli yangilandi',
            'payment' => $payment->fresh()
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error updating payment', [
            'contract_id' => $contract->id,
            'payment_id' => $paymentId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'To\'lovni yangilashda xatolik yuz berdi: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Helper method to get overdue payments for a contract
 */
private function getOverduePaymentsForContract(Contract $contract)
{
    $currentDate = now();
    $overdueDebt = 0;
    $overdueCount = 0;

    $schedules = PaymentSchedule::where('contract_id', $contract->id)
                               ->where('is_active', true)
                               ->get();

    foreach ($schedules as $schedule) {
        $quarterDates = ActualPayment::getQuarterDateRange($schedule->year, $schedule->quarter);
        
        if ($currentDate > $quarterDates['end']) {
            $quarterPaid = ActualPayment::where('contract_id', $contract->id)
                                       ->where('year', $schedule->year)
                                       ->where('quarter', $schedule->quarter)
                                       ->sum('amount');
            
            $debt = $schedule->quarter_amount - $quarterPaid;
            if ($debt > 0) {
                $overdueDebt += $debt;
                $overdueCount++;
            }
        }
    }

    return [
        'total_debt' => $overdueDebt,
        'count' => $overdueCount
    ];
}

    /**
     * Calculate remaining amount after initial payment
     */
    public function calculateRemainingAmount(Request $request, Contract $contract)
    {
        $initialPaymentPercent = $request->input('initial_payment_percent', $contract->initial_payment_percent);
        $contractTotal = $request->input('contract_total', $contract->total_amount);

        $initialPaymentAmount = ($contractTotal * $initialPaymentPercent) / 100;
        $remainingAmount = $contractTotal - $initialPaymentAmount;

        return response()->json([
            'contract_total' => $contractTotal,
            'initial_payment_percent' => $initialPaymentPercent,
            'initial_payment_amount' => $initialPaymentAmount,
            'remaining_amount' => $remainingAmount,
            'formatted' => [
                'contract_total' => number_format($contractTotal / 1000000, 1) . 'М',
                'initial_payment' => number_format($initialPaymentAmount / 1000000, 1) . 'М',
                'remaining' => number_format($remainingAmount / 1000000, 1) . 'М'
            ]
        ]);
    }

    /**
     * Validate payment distribution before saving
     */
    public function validatePaymentDistribution(Request $request, Contract $contract)
    {
        $year = $request->input('year');
        $totalAmount = $request->input('total_amount');
        $quartersCount = $request->input('quarters_count');
        $percentages = [
            1 => $request->input('q1_percent', 0),
            2 => $request->input('q2_percent', 0),
            3 => $request->input('q3_percent', 0),
            4 => $request->input('q4_percent', 0)
        ];

        $errors = [];
        $warnings = [];

        // Validate total amount
        $remainingAmount = $contract->remaining_amount;
        if ($totalAmount > $remainingAmount) {
            $errors[] = "Жами сумма қолган сумма ({$remainingAmount}) дан ошмаслиги керак";
        }

        // Validate percentages for custom distribution
        if ($request->input('distribution_type') === 'custom') {
            $totalPercent = array_sum($percentages);
            if (abs($totalPercent - 100) > 0.1) {
                $errors[] = "Фоизлар йиғиндиси 100% бўлиши керак (ҳозир {$totalPercent}%)";
            }
        }

        // Check for existing payments in the year
        $existingPayments = $contract->actualPayments()
            ->where('year', $year)
            ->sum('amount');

        if ($existingPayments > 0) {
            $warnings[] = "{$year} йилда {$existingPayments} сум тўлов бор. Янги график мавжуд тўловларга таъсир қилмайди.";
        }

        // Check quarters count logic
        if ($quartersCount < 4) {
            $warnings[] = "Сиз {$quartersCount} чорак танладингиз. Қолган чораклар бўш қолади.";
        }

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'summary' => [
                'total_amount' => $totalAmount,
                'quarters_count' => $quartersCount,
                'per_quarter_equal' => $totalAmount / $quartersCount,
                'existing_payments' => $existingPayments
            ]
        ]);
    }

    /**
     * Get upcoming payment reminders
     */
    public function getUpcomingPayments()
    {
        $currentDate = now();
        $currentYear = $currentDate->year;
        $currentQuarter = ceil($currentDate->month / 3);

        $upcomingPayments = PaymentSchedule::join('contracts', 'payment_schedules.contract_id', '=', 'contracts.id')
            ->where('payment_schedules.is_active', true)
            ->where(function ($query) use ($currentYear, $currentQuarter) {
                $query->where('payment_schedules.year', '>', $currentYear)
                      ->orWhere(function ($q) use ($currentYear, $currentQuarter) {
                          $q->where('payment_schedules.year', $currentYear)
                            ->where('payment_schedules.quarter', '>=', $currentQuarter);
                      });
            })
            ->select('payment_schedules.*', 'contracts.contract_number', 'contracts.total_amount')
            ->orderBy('payment_schedules.year')
            ->orderBy('payment_schedules.quarter')
            ->get();

        $upcomingPaymentsData = [];

        foreach ($upcomingPayments as $payment) {
            $actualPaid = ActualPayment::where('contract_id', $payment->contract_id)
                ->where('year', $payment->year)
                ->where('quarter', $payment->quarter)
                ->sum('amount');

            if ($actualPaid < $payment->quarter_amount) {
                $upcomingPaymentsData[] = [
                    'contract_id' => $payment->contract_id,
                    'contract_number' => $payment->contract_number,
                    'year' => $payment->year,
                    'quarter' => $payment->quarter,
                    'quarter_name' => $payment->quarter . '-чорак ' . $payment->year,
                    'due_amount' => $payment->quarter_amount - $actualPaid,
                    'plan_amount' => $payment->quarter_amount,
                    'paid_amount' => $actualPaid,
                    'payment_percent' => $payment->quarter_amount > 0 ? ($actualPaid / $payment->quarter_amount) * 100 : 0,
                    'is_overdue' => ($payment->year < $currentYear) ||
                                   ($payment->year == $currentYear && $payment->quarter < $currentQuarter),
                    'days_until_due' => $this->calculateDaysUntilQuarterEnd($payment->year, $payment->quarter)
                ];
            }
        }

        return response()->json([
            'success' => true,
            'upcoming_payments' => $upcomingPaymentsData,
            'total_due' => collect($upcomingPaymentsData)->sum('due_amount'),
            'overdue_count' => collect($upcomingPaymentsData)->where('is_overdue', true)->count()
        ]);
    }

    /**
     * Calculate days until quarter end
     */
    private function calculateDaysUntilQuarterEnd($year, $quarter)
    {
        $endMonth = $quarter * 3;
        $quarterEnd = Carbon::create($year, $endMonth, 1)->endOfMonth();
        $today = Carbon::now();

        return $quarterEnd->diffInDays($today, false); // negative if overdue
    }

    /**
     * Get payment analytics for reporting
     */
    public function getPaymentAnalytics()
    {
        $currentYear = date('Y');

        $analytics = [
            'total_contracts' => Contract::active()->count(),
            'contracts_with_schedule' => Contract::whereHas('paymentSchedules')->count(),
            'contracts_with_payments' => Contract::whereHas('actualPayments')->count(),

            'current_year_stats' => [
                'total_planned' => PaymentSchedule::where('year', $currentYear)
                    ->where('is_active', true)
                    ->sum('quarter_amount'),
                'total_paid' => ActualPayment::where('year', $currentYear)
                    ->sum('amount'),
                'quarters_completed' => $this->getCompletedQuartersCount($currentYear)
            ],

            'payment_distribution' => $this->getPaymentDistributionByQuarter($currentYear),
            'debt_analysis' => $this->getDebtAnalysis(),
            'top_debtors' => $this->getTopDebtors(10)
        ];

        return response()->json($analytics);
    }

    /**
     * Get payment distribution by quarter for current year
     */
    private function getPaymentDistributionByQuarter($year)
    {
        $distribution = [];

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $planned = PaymentSchedule::where('year', $year)
                ->where('quarter', $quarter)
                ->where('is_active', true)
                ->sum('quarter_amount');

            $actual = ActualPayment::where('year', $year)
                ->where('quarter', $quarter)
                ->sum('amount');

            $distribution[$quarter] = [
                'quarter' => $quarter,
                'quarter_name' => $quarter . '-чорак',
                'planned' => $planned,
                'actual' => $actual,
                'completion_percent' => $planned > 0 ? ($actual / $planned) * 100 : 0,
                'debt' => $planned - $actual
            ];
        }

        return $distribution;
    }

    /**
     * Get debt analysis across all contracts
     */
    private function getDebtAnalysis()
    {
        $contracts = Contract::active()
            ->with(['actualPayments', 'paymentSchedules'])
            ->get();

        $totalDebt = 0;
        $contractsWithDebt = 0;
        $debtByQuarter = [];

        foreach ($contracts as $contract) {
            $contractDebt = $contract->total_amount - $contract->actualPayments->sum('amount');

            if ($contractDebt > 0) {
                $totalDebt += $contractDebt;
                $contractsWithDebt++;
            }
        }

        return [
            'total_debt' => $totalDebt,
            'contracts_with_debt' => $contractsWithDebt,
            'average_debt_per_contract' => $contractsWithDebt > 0 ? $totalDebt / $contractsWithDebt : 0,
            'debt_percentage' => $contracts->sum('total_amount') > 0
                ? ($totalDebt / $contracts->sum('total_amount')) * 100
                : 0
        ];
    }

    /**
     * Get top debtors
     */
    private function getTopDebtors($limit = 10)
    {
        return Contract::active()
            ->with(['subject', 'actualPayments'])
            ->get()
            ->map(function ($contract) {
                $debt = $contract->total_amount - $contract->actualPayments->sum('amount');
                return [
                    'contract_id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'subject_name' => $contract->subject->is_legal_entity
                        ? $contract->subject->company_name
                        : 'Жисмоний шахс',
                    'total_amount' => $contract->total_amount,
                    'paid_amount' => $contract->actualPayments->sum('amount'),
                    'debt' => $debt,
                    'debt_percent' => $contract->total_amount > 0 ? ($debt / $contract->total_amount) * 100 : 0
                ];
            })
            ->where('debt', '>', 0)
            ->sortByDesc('debt')
            ->take($limit)
            ->values();
    }

    /**
     * Get completed quarters count for current year
     */
    private function getCompletedQuartersCount($year)
    {
        $currentQuarter = ceil(date('n') / 3);
        $currentYear = date('Y');

        if ($year < $currentYear) {
            return 4; // All quarters completed for past years
        } elseif ($year == $currentYear) {
            return $currentQuarter - 1; // Current quarter not complete yet
        } else {
            return 0; // Future year
        }
    }

    /**
     * Export payment schedule to Excel
     */
    public function exportPaymentSchedule(Contract $contract)
    {
        $schedules = $contract->paymentSchedules()
            ->where('is_active', true)
            ->orderBy('year')
            ->orderBy('quarter')
            ->get();

        $filename = "payment_schedule_{$contract->contract_number}_" . date('Y-m-d') . ".xlsx";

        return Excel::download(new PaymentScheduleExport($schedules), $filename);
    }

    /**
     * Export actual payments to Excel
     */
    public function exportActualPayments(Contract $contract)
    {
        $payments = $contract->actualPayments()
            ->orderBy('payment_date', 'desc')
            ->get();

        $filename = "actual_payments_{$contract->contract_number}_" . date('Y-m-d') . ".xlsx";

        return Excel::download(new ActualPaymentsExport($payments), $filename);
    }

    /**
     * Generate comprehensive payment report
     */
    public function generatePaymentReport(Contract $contract)
    {
        $report = [
            'contract_info' => [
                'number' => $contract->contract_number,
                'date' => $contract->contract_date->format('d.m.Y'),
                'total_amount' => $contract->total_amount,
                'subject_name' => $contract->subject->is_legal_entity
                    ? $contract->subject->company_name
                    : 'Жисмоний шахс',
                'status' => $contract->status->name_ru
            ],

            'payment_breakdown' => [
                'initial_payment_percent' => $contract->initial_payment_percent,
                'initial_payment_amount' => $contract->initial_payment_amount,
                'remaining_amount' => $contract->remaining_amount
            ],

            'schedule_summary' => $this->getScheduleSummary($contract),
            'payment_summary' => $this->getPaymentSummaryByYear($contract),
            'debt_analysis' => $this->getContractDebtAnalysis($contract),
            'payment_history' => $this->getPaymentHistory($contract),
        ];

        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }

    /**
     * Helper methods for reporting
     */
    private function getScheduleSummary(Contract $contract)
    {
        $schedules = $contract->paymentSchedules()
            ->where('is_active', true)
            ->orderBy('year')
            ->orderBy('quarter')
            ->get();

        return [
            'total_planned' => $schedules->sum('quarter_amount'),
            'quarters_planned' => $schedules->count(),
            'years_covered' => $schedules->pluck('year')->unique()->count(),
            'average_quarter_amount' => $schedules->count() > 0 ? $schedules->avg('quarter_amount') : 0
        ];
    }

    private function getPaymentSummaryByYear(Contract $contract)
    {
        $years = $contract->actualPayments()
            ->distinct('year')
            ->orderBy('year')
            ->pluck('year');

        $summary = [];

        foreach ($years as $year) {
            $yearPayments = $contract->actualPayments()->where('year', $year)->get();

            $summary[$year] = [
                'total_paid' => $yearPayments->sum('amount'),
                'payment_count' => $yearPayments->count(),
                'quarters_with_payments' => $yearPayments->pluck('quarter')->unique()->count(),
                'average_payment' => $yearPayments->count() > 0 ? $yearPayments->avg('amount') : 0
            ];
        }

        return $summary;
    }

    private function getContractDebtAnalysis(Contract $contract)
    {
        $totalPaid = $contract->actualPayments->sum('amount');
        $totalPlanned = $contract->paymentSchedules()->where('is_active', true)->sum('quarter_amount');

        return [
            'total_debt' => $contract->total_amount - $totalPaid,
            'planned_debt' => $totalPlanned - $totalPaid,
            'payment_completion' => $contract->total_amount > 0 ? ($totalPaid / $contract->total_amount) * 100 : 0,
            'schedule_completion' => $totalPlanned > 0 ? ($totalPaid / $totalPlanned) * 100 : 0
        ];
    }

public function getPaymentHistory(Contract $contract)
{
    try {
        $history = PaymentHistory::where('contract_id', $contract->id)
                                ->with('user:id,name')
                                ->orderBy('created_at', 'desc')
                                ->get();

        // Transform history data
        $transformedHistory = $history->map(function ($item) {
            return [
                'id' => $item->id,
                'action' => $item->action,
                'table_name' => $item->table_name,
                'record_id' => $item->record_id,
                'description' => $item->description,
                'formatted_description' => $item->formatted_description,
                'changes_summary' => $item->changes_summary,
                'user' => $item->user,
                'created_at' => $item->created_at->toISOString(),
                'created_at_formatted' => $item->created_at->format('d.m.Y H:i')
            ];
        });

        return response()->json([
            'success' => true,
            'history' => $transformedHistory
        ]);

    } catch (\Exception $e) {
        \Log::error('Error fetching payment history', [
            'contract_id' => $contract->id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Tarixni yuklashda xatolik yuz berdi',
            'history' => []
        ]);
    }
}
}
