@extends('layouts.app')

@section('title', $district->name_uz . ' - Мониторинг')
@section('page-title', $district->name_uz . ' тумани шартномалари')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('monitoring') }}" class="text-blue-600 hover:text-blue-800">
            ← Орқага қайтиш
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">№</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Шартнома</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Буюртмачи</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Рухсатнома тури</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Сумма</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Тўланган</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Ҳолати</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($contracts as $contract)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('contracts.show', $contract) }}"
                               class="text-blue-600 hover:text-blue-800">
                                {{ $contract->contract_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $contract->subject->company_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $contract->object->permitType->name_uz ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">{{ number_format($contract->total_amount, 0, '.', ' ') }}</td>
                        <td class="px-6 py-4 text-sm">{{ number_format($contract->actualPayments->sum('amount'), 0, '.', ' ') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full"
                                  style="background-color: {{ $contract->status->color ?? '#gray' }}20; color: {{ $contract->status->color ?? '#gray' }}">
                                {{ $contract->status->name_uz ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($contracts->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $contracts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
