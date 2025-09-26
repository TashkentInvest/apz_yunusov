@extends('layouts.app')

{{-- @section('title', $permitType->name_uz . ' - Шартномалар')
@section('page-title', $permitType->name_uz . ' бўйича шартномалар') --}}

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('monitoring') }}"
           class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Орқага қайтиш
        </a>
        <div class="text-sm text-gray-600">
            {{ now()->format('d.m.Y') }} йил
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-xs text-gray-600 mb-1">Жами шартномалар</div>
            <div class="text-2xl font-bold text-gray-900">{{ $totals['total_contracts'] }}</div>
        </div>
        <div class="bg-blue-50 rounded-lg shadow-sm border border-blue-200 p-4">
            <div class="text-xs text-blue-600 mb-1">Жами қиймат</div>
            <div class="text-2xl font-bold text-blue-700">{{ number_format($totals['total_amount'] / 1000000, 1) }} млн</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow-sm border border-green-200 p-4">
            <div class="text-xs text-green-600 mb-1">Тўланган</div>
            <div class="text-2xl font-bold text-green-700">{{ number_format($totals['total_paid'] / 1000000, 1) }} млн</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow-sm border border-red-200 p-4">
            <div class="text-xs text-red-600 mb-1">Қарз</div>
            <div class="text-2xl font-bold text-red-700">{{ number_format($totals['total_debt'] / 1000000, 1) }} млн</div>
        </div>
        <div class="bg-purple-50 rounded-lg shadow-sm border border-purple-200 p-4">
            <div class="text-xs text-purple-600 mb-1">График бўйича</div>
            <div class="text-2xl font-bold text-purple-700">{{ number_format($totals['total_plan'] / 1000000, 1) }} млн</div>
        </div>
        <div class="bg-amber-50 rounded-lg shadow-sm border border-amber-200 p-4">
            <div class="text-xs text-amber-600 mb-1">Фарқи</div>
            <div class="text-2xl font-bold text-amber-700">{{ number_format(($totals['total_plan'] - $totals['total_fact']) / 1000000, 1) }} млн</div>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">
                Шартномалар рўйхати ({{ $contracts->total() }} та)
                @if($district)
                    - {{ $district->name_uz }}
                @endif
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">№</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Шартнома</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Буюртмачи</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Туман</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Жами сумма</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase bg-purple-50">График бўйича</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase bg-green-50">Тўланган</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase bg-amber-50">Фарқи</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase bg-red-50">Қарз</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ҳолати</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($contracts as $contract)
                    @php
                        $paid = $contract->actualPayments->sum('amount');
                        $plan = $contract->paymentSchedules->sum('quarter_amount');
                        $debt = $contract->total_amount - $paid;
                        $difference = $plan - $paid;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ $loop->iteration + ($contracts->currentPage() - 1) * $contracts->perPage() }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('contracts.payment_update', $contract) }}"
                               class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                {{ $contract->contract_number }}
                            </a>
                            <div class="text-xs text-gray-500">{{ $contract->contract_date->format('d.m.Y') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium">{{ $contract->subject->company_name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $contract->subject->is_legal_entity ? 'СТИР: ' . $contract->subject->inn : 'ЖШШИР: ' . $contract->subject->pinfl }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $contract->object->district->name_uz ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium">{{ number_format($contract->total_amount, 0, '.', ' ') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium bg-purple-50">
                            {{ number_format($plan, 0, '.', ' ') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-medium bg-green-50 text-green-700">
                            {{ number_format($paid, 0, '.', ' ') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-medium bg-amber-50">
                            <span class="{{ $difference > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($difference, 0, '.', ' ') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-medium bg-red-50">
                            <span class="{{ $debt > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($debt, 0, '.', ' ') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full"
                                  style="background-color: {{ $contract->status->color ?? '#gray' }}20; color: {{ $contract->status->color ?? '#gray' }}">
                                {{ $contract->status->name_uz ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                            Шартномалар топилмади
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 font-semibold">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right text-sm">ЖАМИ:</td>
                        <td class="px-4 py-3 text-right text-sm">{{ number_format($totals['total_amount'], 0, '.', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-sm bg-purple-50">{{ number_format($totals['total_plan'], 0, '.', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-sm bg-green-50 text-green-700">{{ number_format($totals['total_paid'], 0, '.', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-sm bg-amber-50">
                            <span class="{{ ($totals['total_plan'] - $totals['total_fact']) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($totals['total_plan'] - $totals['total_fact'], 0, '.', ' ') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm bg-red-50 text-red-600">{{ number_format($totals['total_debt'], 0, '.', ' ') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        @if($contracts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $contracts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
