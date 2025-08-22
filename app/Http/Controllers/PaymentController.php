<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActualPayment;
use App\Models\Contract;
use App\Models\PaymentSchedule;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = ActualPayment::with(['contract.subject', 'contract.object.district']);

        if ($request->contract_number) {
            $query->whereHas('contract', function($q) use ($request) {
                $q->where('contract_number', 'like', '%' . $request->contract_number . '%');
            });
        }

        if ($request->year) {
            $query->where('year', $request->year);
        }

        if ($request->quarter) {
            $query->where('quarter', $request->quarter);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'payment_number' => 'required|string',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'year' => 'required|integer|min:2020|max:2050',
            'quarter' => 'required|integer|min:1|max:4',
            'notes' => 'nullable|string'
        ]);

        ActualPayment::create($validated);

        return response()->json(['success' => true, 'message' => 'Платеж успешно добавлен']);
    }

    public function updateSchedule(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.id' => 'required|exists:payment_schedules,id',
            'schedules.*.quarter_amount' => 'required|numeric|min:0',
            'schedules.*.custom_percent' => 'nullable|numeric|min:0|max:100'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['schedules'] as $scheduleData) {
                PaymentSchedule::where('id', $scheduleData['id'])
                    ->update([
                        'quarter_amount' => $scheduleData['quarter_amount'],
                        'custom_percent' => $scheduleData['custom_percent']
                    ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'График платежей обновлен']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
        }
    }
}
