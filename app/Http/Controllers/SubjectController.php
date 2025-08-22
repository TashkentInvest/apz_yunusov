<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\District;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->search . '%')
                  ->orWhere('inn', 'like', '%' . $request->search . '%')
                  ->orWhere('pinfl', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('is_legal_entity') && $request->is_legal_entity !== '') {
            $query->where('is_legal_entity', $request->is_legal_entity);
        }

        $subjects = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('subjects.create');
    }

    public function store(Request $request)
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

        $validated = $request->validate($rules);

        Subject::create([
            ...$validated,
            'is_active' => true
        ]);

        return redirect()->route('subjects.index')->with('success', 'Заказчик успешно создан');
    }

    public function show(Subject $subject)
    {
        $subject->load(['contracts.status', 'contracts.object.district']);

        return view('subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
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
                'inn' => 'required|string|size:9|unique:subjects,inn,' . $subject->id,
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
                'pinfl' => 'required|string|size:14|unique:subjects,pinfl,' . $subject->id
            ]);
        }

        $validated = $request->validate($rules);

        $subject->update($validated);

        return redirect()->route('subjects.show', $subject)->with('success', 'Заказчик успешно обновлен');
    }

    public function destroy(Subject $subject)
    {
        $subject->update(['is_active' => false]);
        return redirect()->route('subjects.index')->with('success', 'Заказчик успешно удален');
    }
}
