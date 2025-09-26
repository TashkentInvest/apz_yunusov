@extends('layouts.app')

@section('title', $title . ' - Мониторинг')
@section('page-title', $title)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @if($district)
                    <p class="text-sm text-gray-600 mt-1">Туман: {{ $district->name_uz }}</p>
                @endif
            </div>
            <a href="{{ route('monitoring') }}" class="text-blue-600 hover:underline text-sm">
                ← Мониторинг саҳифасига қайтиш
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Жами шартномалар</p>
                <p class="text-2xl font-bold text-gray-900">{{ $contracts->total() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Жами сумма</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalAmount / 1000000, 1) }} млн сўм</p>
            </div>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border border-gray-300 px-3 py-2 text-left">№</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Шартнома рақами</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Буюртмачи</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Туман</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Жами сумма</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Ҳолати</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-3 py-2">{{ $loop->iteration + ($contracts->currentPage() - 1) * $contracts->perPage() }}</td>
                        <td class="border border-gray-300 px-3 py-2">
                            <a href="{{ route('contracts.payment_update', $contract) }}" class="text-blue-600 hover:underline">
                                {{ $contract->contract_number }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-3 py-2">{{ $contract->subject->company_name ?? $contract->subject->full_name }}</td>
                        <td class="border border-gray-300 px-3 py-2">{{ $contract->object->district->name_uz ?? '-' }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format($contract->total_amount, 0, '.', ' ') }}</td>
                        <td class="border border-gray-300 px-3 py-2">
                            <span class="px-2 py-1 rounded text-xs {{ $contract->status->code === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $contract->status->name_uz }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-3 py-4 text-center text-gray-500">
                            Маълумотлар топилмади
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $contracts->links() }}
        </div>
    </div>
</div>
@endsection
