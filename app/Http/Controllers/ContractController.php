<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Subject;
use App\Models\Objectt;
use App\Models\ContractStatus;
use App\Models\BaseCalculationAmount;
use App\Models\District;
use App\Models\ObjectType;
use App\Models\ConstructionType;
use App\Models\TerritorialZone;
use App\Models\PermitType;
use App\Models\IssuingAuthority;
use App\Models\OrgForm;
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
            // Get object and calculate using the formula
            $object = Objectt::with('territorialZone')->find($validated['object_id']);
            $baseAmount = BaseCalculationAmount::find($validated['base_amount_id']);

            // Apply the formula: Ti=Bh*((Hb+Hyu)-(Ha+Ht+Hu))*Kt*Ko*Kz*Kj
            $totalAmount = $this->calculateTotalAmount($object, $baseAmount, $validated);

            $contract = Contract::create([
                ...$validated,
                'total_amount' => $totalAmount,
                'formula' => $this->buildFormulaString($object, $baseAmount, $validated),
                'quarters_count' => $validated['construction_period_years'] * 4,
                'is_active' => true
            ]);

            $this->generatePaymentSchedule($contract);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Договор успешно создан',
                    'redirect' => route('contracts.show', $contract)
                ]);
            }

            return redirect()->route('contracts.show', $contract)->with('success', 'Договор успешно создан');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Contract creation error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при создании договора: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()->with('error', 'Ошибка: ' . $e->getMessage());
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

    private function getConstructionTypeCoefficient($constructionTypeId)
    {
        $calculatorService = new \App\Services\CoefficientCalculatorService();
        return $calculatorService->getConstructionTypeCoefficient($constructionTypeId);
    }

    private function getObjectTypeCoefficient($objectTypeId)
    {
        $calculatorService = new \App\Services\CoefficientCalculatorService();
        return $calculatorService->getObjectTypeCoefficient($objectTypeId);
    }

    private function getLocationCoefficient($locationType)
    {
        $calculatorService = new \App\Services\CoefficientCalculatorService();
        return $calculatorService->getLocationCoefficient($locationType);
    }

    private function generatePaymentSchedule($contract)
    {
        if ($contract->payment_type === 'full') {
            // For full payment, create single payment schedule
            $contract->paymentSchedules()->create([
                'year' => date('Y', strtotime($contract->contract_date)),
                'quarter' => ceil(date('n', strtotime($contract->contract_date)) / 3),
                'quarter_amount' => $contract->total_amount,
                'is_active' => true
            ]);
        } else {
            // Calculate payment schedule using service
            $calculatorService = new \App\Services\CoefficientCalculatorService();
            $paymentData = $calculatorService->calculatePaymentSchedule(
                $contract->total_amount,
                $contract->initial_payment_percent,
                $contract->quarters_count
            );

            // Create initial payment
            $contract->paymentSchedules()->create([
                'year' => date('Y', strtotime($contract->contract_date)),
                'quarter' => ceil(date('n', strtotime($contract->contract_date)) / 3),
                'quarter_amount' => $paymentData['initial_payment'],
                'custom_percent' => $contract->initial_payment_percent,
                'is_active' => true
            ]);

            // Create quarterly payments
            $currentYear = date('Y', strtotime($contract->contract_date));
            $currentQuarter = ceil(date('n', strtotime($contract->contract_date)) / 3);

            for ($i = 1; $i <= $contract->quarters_count; $i++) {
                $quarter = ($currentQuarter + $i - 1) % 4 + 1;
                $year = $currentYear + intval(($currentQuarter + $i - 1) / 4);

                $contract->paymentSchedules()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'quarter_amount' => $paymentData['quarterly_payment'],
                    'is_active' => true
                ]);
            }
        }
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
        $contract->load([
            'subject.orgForm',
            'object.district',
            'object.constructionType',
            'object.objectType',
            'object.territorialZone',
            'status',
            'baseAmount',
            'paymentSchedules' => function ($query) {
                $query->orderBy('year')->orderBy('quarter');
            },
            'actualPayments' => function ($query) {
                $query->orderBy('payment_date', 'desc');
            }
        ]);

        $calculatorService = new \App\Services\CoefficientCalculatorService();
        $coefficients = $calculatorService->getCoefficientBreakdown($contract->object);

        return view('contracts.show', compact('contract', 'coefficients'));
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
    public function createSubject(Request $request)
    {
        $rules = [
            'is_legal_entity' => 'required|boolean',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'physical_address' => 'nullable|string'
        ];

        if ($request->is_legal_entity) {
            $rules = array_merge($rules, [
                'company_name' => 'required|string|max:300',
                'inn' => 'required|string|size:9|unique:subjects',
                'org_form_id' => 'nullable|exists:org_forms,id',
                'bank_name' => 'nullable|string',
                'bank_code' => 'nullable|string',
                'bank_account' => 'nullable|string',
                'legal_address' => 'nullable|string'
            ]);
        } else {
            $rules = array_merge($rules, [
                'document_type' => 'required|string',
                'document_series' => 'nullable|string',
                'document_number' => 'required|string',
                'issued_by' => 'nullable|string',
                'issued_date' => 'nullable|date',
                'pinfl' => 'required|string|size:14|unique:subjects'
            ]);
        }

        try {
            $validated = $request->validate($rules);

            $subject = Subject::create([
                ...$validated,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Заказчик успешно создан',
                'subject' => [
                    'id' => $subject->id,
                    'text' => $subject->display_name . ' (' . $subject->identifier . ')'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании заказчика: ' . $e->getMessage()
            ], 422);
        }
    }

    public function createObject(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'required|string|max:500',
            'cadastre_number' => 'nullable|string|max:50',
            'construction_volume' => 'required|numeric|min:0.01',
            'above_permit_volume' => 'nullable|numeric|min:0',
            'parking_volume' => 'nullable|numeric|min:0',
            'technical_rooms_volume' => 'nullable|numeric|min:0',
            'common_area_volume' => 'nullable|numeric|min:0',
            'construction_type_id' => 'nullable|exists:construction_types,id',
            'object_type_id' => 'nullable|exists:object_types,id',
            'territorial_zone_id' => 'nullable|exists:territorial_zones,id',
            'location_type' => 'nullable|string|max:100',
            'geolocation' => 'nullable|string|max:100',
            'additional_info' => 'nullable|string',

            // Permit information
            'application_number' => 'nullable|string|max:50',
            'application_date' => 'nullable|date',
            'permit_document_name' => 'nullable|string|max:300',
            'permit_type_id' => 'nullable|exists:permit_types,id',
            'issuing_authority_id' => 'nullable|exists:issuing_authorities,id',
            'permit_date' => 'nullable|date',
            'permit_number' => 'nullable|string|max:100',
            'work_type' => 'nullable|string|max:200',
        ]);

        try {
            $object = Objectt::create([
                ...$validated,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Объект успешно создан',
                'object' => [
                    'id' => $object->id,
                    'text' => $object->address . ' (' . $object->district->name_ru . ') - ' . number_format($object->construction_volume, 2) . ' м³',
                    'construction_volume' => $object->construction_volume,
                    'subject_id' => $object->subject_id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании объекта: ' . $e->getMessage()
            ], 422);
        }
    }

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
}
