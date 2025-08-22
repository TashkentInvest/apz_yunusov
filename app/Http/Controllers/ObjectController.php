<?php

// app/Http/Controllers/ObjectController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Objectt;
use App\Models\Subject;
use App\Models\District;
use App\Models\PermitType;
use App\Models\IssuingAuthority;
use App\Models\ConstructionType;
use App\Models\ObjectType;
use App\Models\TerritorialZone;

class ObjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Objectt::with(['subject', 'district']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('address', 'like', '%' . $request->search . '%')
                  ->orWhere('cadastre_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('subject', function($subQ) use ($request) {
                      $subQ->where('company_name', 'like', '%' . $request->search . '%')
                           ->orWhere('inn', 'like', '%' . $request->search . '%')
                           ->orWhere('pinfl', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        $objects = $query->orderBy('created_at', 'desc')->paginate(20);
        $districts = District::where('is_active', true)->get();
        $subjects = Subject::where('is_active', true)->get();

        return view('objects.index', compact('objects', 'districts', 'subjects'));
    }

    public function create(Request $request)
    {
        $subjects = Subject::where('is_active', true)->get();
        $districts = District::where('is_active', true)->get();
        $permitTypes = PermitType::where('is_active', true)->get();
        $issuingAuthorities = IssuingAuthority::where('is_active', true)->get();
        $constructionTypes = ConstructionType::where('is_active', true)->get();
        $objectTypes = ObjectType::where('is_active', true)->get();
        $territorialZones = TerritorialZone::where('is_active', true)->get();

        // Pre-select subject if provided
        $selectedSubjectId = $request->subject_id;

        return view('objects.create', compact(
            'subjects', 'districts', 'permitTypes', 'issuingAuthorities',
            'constructionTypes', 'objectTypes', 'territorialZones', 'selectedSubjectId'
        ));
    }

    public function store(Request $request)
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

        $object = Objectt::create([
            ...$validated,
            'is_active' => true
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Объект успешно создан',
                'object' => [
                    'id' => $object->id,
                    'text' => $object->address . ' (' . $object->district->name_ru . ') - ' . number_format($object->construction_volume, 2) . ' м³'
                ]
            ]);
        }

        return redirect()->route('objects.show', $object)->with('success', 'Объект успешно создан');
    }

    public function show(Object $object)
    {
        $object->load(['subject', 'district', 'permitType', 'issuingAuthority',
                      'constructionType', 'objectType', 'territorialZone', 'contracts.status']);

        return view('objects.show', compact('object'));
    }

    public function edit(Object $object)
    {
        $subjects = Subject::where('is_active', true)->get();
        $districts = District::where('is_active', true)->get();
        $permitTypes = PermitType::where('is_active', true)->get();
        $issuingAuthorities = IssuingAuthority::where('is_active', true)->get();
        $constructionTypes = ConstructionType::where('is_active', true)->get();
        $objectTypes = ObjectType::where('is_active', true)->get();
        $territorialZones = TerritorialZone::where('is_active', true)->get();

        return view('objects.edit', compact(
            'object', 'subjects', 'districts', 'permitTypes', 'issuingAuthorities',
            'constructionTypes', 'objectTypes', 'territorialZones'
        ));
    }

    public function update(Request $request, Object $object)
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

        $object->update($validated);

        return redirect()->route('objects.show', $object)->with('success', 'Объект успешно обновлен');
    }

    public function destroy(Object $object)
    {
        // Check if object has active contracts
        if ($object->contracts()->where('is_active', true)->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить объект с активными договорами'
            ], 400);
        }

        $object->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Объект успешно удален'
        ]);
    }

    // API endpoint for AJAX object selection
    public function search(Request $request)
    {
        $query = $request->get('q');
        $subjectId = $request->get('subject_id');

        $objectsQuery = Objectt::with(['district'])
            ->where('is_active', true);

        if ($subjectId) {
            $objectsQuery->where('subject_id', $subjectId);
        }

        if ($query) {
            $objectsQuery->where(function($q) use ($query) {
                $q->where('address', 'like', "%{$query}%")
                  ->orWhere('cadastre_number', 'like', "%{$query}%");
            });
        }

        $objects = $objectsQuery->limit(10)->get()->map(function($object) {
            return [
                'id' => $object->id,
                'text' => $object->address . ' (' . ($object->district->name_ru ?? 'Не указан') . ') - ' . number_format($object->construction_volume, 2) . ' м³',
                'volume' => $object->construction_volume,
                'district' => $object->district->name_ru ?? 'Не указан',
                'cadastre' => $object->cadastre_number
            ];
        });

        return response()->json($objects);
    }
}
