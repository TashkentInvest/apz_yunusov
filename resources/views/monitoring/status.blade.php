@extends('layouts.app')

@section('title', $statusName . ' - Шартномалар')
@section('page-title', $statusName . ' шартномалари')

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

    <!-- Summary Card -->
    @php
        $bgColor = $statusType === 'active' ? 'from-green-500 to-green-600' :
                   ($statusType === 'cancelled' ? 'from-red-500 to-red-600' : 'from-blue-500 to-blue-600');
    @endphp
    <div class="bg-gradient-to-r {{ $bgColor }} rounded-xl shadow-lg p-6 text-white">
        <h2 class="text-2xl font-bold mb-4">{{ $statusName }} шартномалари</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <div class="text-sm opacity-90">Жами шартномалар</div>
                <div class="text-3xl font-bold">{{ $contracts->total() }}</div>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <div class="text-sm opacity-90">Жами қиймат</div>
                <div class="text-2xl font-bold">{{ number_format($totalAmount / 1000000, 1) }} млн</div>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <div class="text-sm opacity-90">Тўланган</div>
                <div class="text-2xl font-bold">{{ number_format($totalPaid / 1000000, 1) }} млн</div>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <div class="text-sm opacity-90">Қарз</div>
                <div class="text-2xl font-bold">{{ number_format($totalDebt / 1000000, 1) }} млн</div>
            </div>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">
                Шартномалар рўйхати ({{ $contracts->total() }} та)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">№</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Шартнома</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Буюртмачи</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Туман</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Рухсат тури</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Сумма</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Тўланган</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Қарз</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Ҳолати</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($contracts as $contract)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $loop->iteration + ($contracts->currentPage() - 1) * $contracts->perPage() }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('contracts.show', $contract) }}"
                               class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                {{ $contract->contract_number }}
                            </a>
                            <div class="text-xs text-gray-500">{{ $contract->contract_date->format('d.m.Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium">{{ $contract->subject->company_name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $contract->subject->is_legal_entity ? 'СТИР: ' . $contract->subject->inn : 'ЖШШИР: ' . $contract->subject->pinfl }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $contract->object->district->name_uz ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $contract->object->permitType->name_uz ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-medium">{{ number_format($contract->total_amount, 0, '.', ' ') }}</td>
                        <td class="px-6 py-4 text-sm text-green-600 font-medium">
                            {{ number_format($contract->actualPayments->sum('amount'), 0, '.', ' ') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            @php
                                $debt = $contract->total_amount - $contract->actualPayments->sum('amount');
                            @endphp
                            <span class="{{ $debt > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($debt, 0, '.', ' ') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full"
                                  style="background-color: {{ $contract->status->color ?? '#gray' }}20; color: {{ $contract->status->color ?? '#gray' }}">
                                {{ $contract->status->name_uz ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            Шартномалар топилмади
                        </td>
                    </tr>
                    @endforelse
                </tbody>
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
