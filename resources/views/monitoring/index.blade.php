@extends('layouts.app')

@section('title', 'Мониторинг - АПЗ Тизими')
@section('page-title', 'Тошкент шаҳрида АРТ маълумотлари')

@section('content')
<div class="space-y-6">
    <!-- Header Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                "Тошкент Инвест компанияси" АЖга киритилиши тўғрисида МАЪЛУМОТ
            </h3>
            <div class="text-sm text-gray-600">
                {{ now()->format('d.m.Y') }} йил
            </div>
        </div>
    </div>

    <!-- Monitoring Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">Т/р</th>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">Ҳудуд номи</th>
                        <th colspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">Жами шартнома</th>
                        <th colspan="5" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700 bg-blue-50">Қурилиш жараёни ҳолати</th>
                        <th colspan="4" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700 bg-green-50">Шартнома ҳолати</th>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">Жами тўлов</th>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">25.09.2025 й холатига кўра қарздорлик</th>
                        <th rowspan="2" class="border border-gray-300 px-4 py-3 text-xs font-semibold text-gray-700">2025 йил оҳирига қадар тўланадиган қарздорлик</th>
                    </tr>
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600">сони</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">АПЗ</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">ГАСН</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">Кенгаш</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">Рухсатнома</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">Экспертиза</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Амалда</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Бекор қилинган</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Якунланган</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Қайтарилган маблағ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- City Total Row -->
                    <tr class="bg-yellow-50 font-semibold">
                        <td class="border border-gray-300 px-4 py-3 text-sm"></td>
                        <td class="border border-gray-300 px-4 py-3 text-sm">Тошкент шаҳри</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center">{{ $cityTotals['total_contracts'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($cityTotals['total_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $cityTotals['apz_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $cityTotals['gasn_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $cityTotals['kengash_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $cityTotals['permit_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $cityTotals['expertise_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">{{ $cityTotals['active_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">{{ $cityTotals['cancelled_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">{{ $cityTotals['completed_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right bg-green-50">{{ number_format($cityTotals['returned_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($cityTotals['total_paid'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($cityTotals['total_debt'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($cityTotals['debt_2025'] / 1000000, 1) }}</td>
                    </tr>

                    <!-- District Rows -->
                    @foreach($monitoringData as $index => $data)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm">
                            <a href="{{ route('monitoring.district', $data['district']) }}"
                               class="text-blue-600 hover:text-blue-800 hover:underline">
                                {{ $data['district']->name_uz }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center">{{ $data['total_contracts'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($data['total_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['apz_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['gasn_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['kengash_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['permit_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">{{ $data['expertise_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">{{ $data['active_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">{{ $data['cancelled_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">{{ $data['completed_count'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right bg-green-50">{{ number_format($data['returned_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($data['total_paid'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($data['total_debt'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($data['debt_2025'] / 1000000, 1) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
