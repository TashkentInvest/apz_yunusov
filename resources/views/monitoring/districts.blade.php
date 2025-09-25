@extends('layouts.app')

@section('title', 'Туманлар бўйича маълумот - Мониторинг')
@section('page-title', 'Тошкент шаҳри туманлари')

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
            {{ now()->format('d.m.Y') }} йил ҳолатига
        </div>
    </div>

    <!-- City Summary Card -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
        <h2 class="text-2xl font-bold mb-4">Тошкент шаҳри умумий кўрсаткичлари</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <div class="text-sm opacity-90">Жами шартномалар</div>
                <div class="text-3xl font-bold">{{ $cityTotals['total_contracts'] }}</div>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <div class="text-sm opacity-90">Жами қиймат</div>
                <div class="text-2xl font-bold">{{ number_format($cityTotals['total_amount'] / 1000000, 1) }} млн</div>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <div class="text-sm opacity-90">Тўланган</div>
                <div class="text-2xl font-bold">{{ number_format($cityTotals['total_paid'] / 1000000, 1) }} млн</div>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <div class="text-sm opacity-90">Қарз</div>
                <div class="text-2xl font-bold">{{ number_format($cityTotals['total_debt'] / 1000000, 1) }} млн</div>
            </div>
        </div>
    </div>

    <!-- Districts Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">
                Туманлар бўйича батафсил маълумот ({{ count($monitoringData) }} та)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">№</th>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">Туман номи</th>
                        <th colspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">Жами шартнома</th>
                        <th colspan="5" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700 bg-blue-50">Қурилиш жараёни</th>
                        <th colspan="4" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700 bg-green-50">Шартнома ҳолати</th>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">Тўланган (млн)</th>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">Қарз (млн)</th>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">2025 қарз (млн)</th>
                    </tr>
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600">Сони</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600">Қиймати (млн)</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">АПЗ</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">ГАСН</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">Кенгаш</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">Рухсат</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">Эксперт</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Амалда</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Бекор</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Якун</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Қайтар</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monitoringData as $index => $data)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm">
                            <a href="{{ route('monitoring.district', $data['district']) }}"
                               class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                {{ $data['district']->name_uz }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center font-medium">{{ $data['total_contracts'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right font-medium">{{ number_format($data['total_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['apz_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['gasn_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['kengash_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['permit_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['expertise_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">{{ $data['active_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">{{ $data['cancelled_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">{{ $data['completed_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right bg-green-50">{{ number_format($data['returned_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right font-medium">{{ number_format($data['total_paid'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right font-medium
                            {{ $data['total_debt'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($data['total_debt'] / 1000000, 1) }}
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right font-medium
                            {{ $data['debt_2025'] > 0 ? 'text-orange-600' : 'text-green-600' }}">
                            {{ number_format($data['debt_2025'] / 1000000, 1) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
