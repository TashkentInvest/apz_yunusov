<?php

namespace App\Http\Controllers;

use App\Models\ActualPayment;
use App\Models\Contract;
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
            $object = Objectt::with('territorialZone')->find($validated['object_id']);
            $baseAmount = BaseCalculationAmount::find($validated['base_amount_id']);

            $totalAmount = $this->calculateTotalAmount($object, $baseAmount, $validated);

            $contract->update([
                ...$validated,
                'total_amount' => $totalAmount,
                'formula' => $this->buildFormulaString($object, $baseAmount, $validated),
                'quarters_count' => $validated['construction_period_years'] * 4,
            ]);

            // Regenerate payment schedule if payment terms changed
            if ($contract->wasChanged(['total_amount', 'payment_type', 'initial_payment_percent', 'construction_period_years'])) {
                $contract->paymentSchedules()->delete();
                $this->generatePaymentSchedule($contract);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Договор успешно обновлен',
                    'redirect' => route('contracts.show', $contract)
                ]);
            }

            return redirect()->route('contracts.show', $contract)->with('success', 'Договор успешно обновлен');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Contract update error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при обновлении договора: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()->with('error', 'Ошибка: ' . $e->getMessage());
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
public function payment_update(Contract $contract)
{
    $contract->load([
        'subject',
        'object.district',
        'paymentSchedules' => function($query) {
            $query->where('is_active', true)->orderBy('year')->orderBy('quarter');
        },
        'actualPayments' => function($query) {
            $query->orderBy('payment_date', 'desc');
        }
    ]);

    // Calculate payment summary by quarters
    $paymentSummary = [];
    $years = $contract->paymentSchedules->pluck('year')->unique()->sort()->values();

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
                'payment_percent' => $planAmount > 0 ? ($factTotal / $planAmount) * 100 : 0
            ];
        }
        $paymentSummary[$year] = $yearData;
    }

    return view('contracts.payment_update', compact('contract', 'paymentSummary'));
}

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
                'amendment_id' => null, // For manual entries
                'is_active' => true
            ],
            [
                'quarter_amount' => $request->amount
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Плановый платеж успешно сохранен',
            'data' => $planPayment
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Ошибка при сохранении планового платежа: ' . $e->getMessage()
        ], 500);
    }
}

public function storeFactPayment(Request $request, Contract $contract)
{
    $request->validate([
        'payment_date' => 'required|date',
        'amount' => 'required|numeric|min:0',
        'payment_number' => 'nullable|string|max:50',
        'notes' => 'nullable|string'
    ]);

    try {
        $paymentDate = Carbon::parse($request->payment_date);
        $year = $paymentDate->year;
        $quarter = ActualPayment::calculateQuarterFromDate($request->payment_date);

        $factPayment = ActualPayment::create([
            'contract_id' => $contract->id,
            'payment_date' => $request->payment_date,
            'year' => $year,
            'quarter' => $quarter,
            'amount' => $request->amount,
            'payment_number' => $request->payment_number,
            'notes' => $request->notes,
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Фактический платеж успешно добавлен',
            'data' => $factPayment
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Ошибка при добавлении фактического платежа: ' . $e->getMessage()
        ], 500);
    }
}

public function deletePlanPayment($id)
{
    try {
        $planPayment = PaymentSchedule::findOrFail($id);

        // Check if this is a manual payment schedule entry (not from amendment)
        if ($planPayment->amendment_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить автоматически созданный график платежей'
            ], 403);
        }

        $planPayment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Плановый платеж успешно удален'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Ошибка при удалении планового платежа: ' . $e->getMessage()
        ], 500);
    }
}

public function deleteFactPayment($id)
{
    try {
        $factPayment = ActualPayment::findOrFail($id);
        $factPayment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Фактический платеж успешно удален'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Ошибка при удалении фактического платежа: ' . $e->getMessage()
        ], 500);
    }
}








}
