@extends('layouts.app')

@section('title', 'Мониторинг - АПЗ Тизими')
@section('page-title', 'Тошкент шаҳрида АРТ маълумотлари')

@section('content')
<div class="space-y-6">
    <!-- Header Info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">
                "Тошкент Инвест компанияси" АЖга киритилиши тўғрисида МАЪЛУМОТ
            </h3>
            <div class="text-sm font-medium text-gray-600">
                {{ now()->format('d.m.Y') }} йил
            </div>
        </div>
    </div>

    <!-- Monitoring Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-300 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-xs">
                <thead class="bg-gray-100">
                    <!-- First row -->
                    <tr>
                        <th rowspan="3" class="border border-gray-400 px-3 py-3 text-xs font-bold text-gray-800">Т/р</th>
                        <th rowspan="3" class="border border-gray-400 px-3 py-3 text-xs font-bold text-gray-800 min-w-[140px] sticky left-0 bg-gray-100 z-10">Ҳудуд номи</th>
                        <th colspan="2" rowspan="2" class="border border-gray-400 px-3 py-3 text-xs font-bold text-gray-800">АРТ бўйича жами тузилган шартномалар</th>
                        <th colspan="10" class="border border-gray-400 px-3 py-3 text-xs font-bold text-gray-800">шундан,</th>
                        <th colspan="5" rowspan="2" class="border border-gray-400 px-3 py-3 text-xs font-bold text-gray-800">Объект қурилиши бўйича ҳолати</th>
                        <th colspan="5" class="border border-gray-400 px-3 py-3 text-xs font-bold text-gray-800">2025 йилнинг 3-чорак якунига қадар тўланадиганлар</th>
                        <th colspan="5" class="border border-gray-400 px-3 py-3 text-xs font-bold text-gray-800">2025 йилнинг 4-чорак якунига қадар тўланадиганлар</th>
                        <th colspan="5" class="border border-gray-400 px-3 py-3 text-xs font-bold text-gray-800">2026 йилда тўланадиганлар</th>
                        <th colspan="5" class="border border-gray-400 px-3 py-3 text-xs font-bold text-gray-800">2027 йилда тўланадиганлар</th>
                    </tr>

                    <!-- Second row -->
                    <tr>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Бекор қилинганлар</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">тўлиқ тўланганлар</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">тўлов суммалари қайтарилganлар</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">назоратдагилар</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">графикдан ортда қолганлар</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Режа</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Факт</th>
                        <th colspan="1" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Фарқи</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Режа</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Факт</th>
                        <th colspan="1" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Фарқи</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Режа</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Факт</th>
                        <th colspan="1" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Фарқи</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Режа</th>
                        <th colspan="2" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Факт</th>
                        <th colspan="1" class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Фарқи</th>
                    </tr>

                    <!-- Third row -->
                    <tr class="bg-gray-200">
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Рухсатнома</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">АПЗ</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Кенгаш</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">Экспертиза</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">ГАСН</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">сони</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                        <th class="border border-gray-400 px-2 py-2 text-xs font-semibold text-gray-700">қиймати (млн сўм)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- City Total -->
                    <tr class="bg-gray-200 font-bold border-t-2 border-b-2 border-gray-500">
                        <td class="border border-gray-400 px-3 py-3 text-center"></td>
                        <td class="border border-gray-400 px-3 py-3 sticky left-0 bg-gray-200 z-10 font-bold">Тошкент шаҳри</td>
                        <td class="border border-gray-400 px-3 py-3 text-center">{{ $cityTotals['total_contracts'] }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['total_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.status', 'cancelled') }}" class="text-blue-700 hover:underline">{{ $cityTotals['cancelled_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['cancelled_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.status', 'completed') }}" class="text-blue-700 hover:underline">{{ $cityTotals['completed_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['completed_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center">{{ $cityTotals['returned_count'] }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['returned_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.status', 'active') }}" class="text-blue-700 hover:underline">{{ $cityTotals['active_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['active_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center">{{ $cityTotals['overdue_count'] }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['overdue_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.permit-type', 4) }}" class="text-blue-700 hover:underline">{{ $cityTotals['permit_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.permit-type', 1) }}" class="text-blue-700 hover:underline">{{ $cityTotals['apz_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.permit-type', 3) }}" class="text-blue-700 hover:underline">{{ $cityTotals['kengash_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.permit-type', 5) }}" class="text-blue-700 hover:underline">{{ $cityTotals['expertise_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.permit-type', 2) }}" class="text-blue-700 hover:underline">{{ $cityTotals['gasn_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.quarter', ['year' => 2025, 'quarter' => 3, 'type' => 'plan']) }}" class="text-blue-700 hover:underline">{{ $cityTotals['q3_2025_plan_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['q3_2025_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.quarter', ['year' => 2025, 'quarter' => 3, 'type' => 'fact']) }}" class="text-blue-700 hover:underline">{{ $cityTotals['q3_2025_fact_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['q3_2025_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format(($cityTotals['q3_2025_plan_amount'] - $cityTotals['q3_2025_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.quarter', ['year' => 2025, 'quarter' => 4, 'type' => 'plan']) }}" class="text-blue-700 hover:underline">{{ $cityTotals['q4_2025_plan_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['q4_2025_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.quarter', ['year' => 2025, 'quarter' => 4, 'type' => 'fact']) }}" class="text-blue-700 hover:underline">{{ $cityTotals['q4_2025_fact_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['q4_2025_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format(($cityTotals['q4_2025_plan_amount'] - $cityTotals['q4_2025_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.year', ['year' => 2026, 'type' => 'plan']) }}" class="text-blue-700 hover:underline">{{ $cityTotals['y2026_plan_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['y2026_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.year', ['year' => 2026, 'type' => 'fact']) }}" class="text-blue-700 hover:underline">{{ $cityTotals['y2026_fact_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['y2026_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format(($cityTotals['y2026_plan_amount'] - $cityTotals['y2026_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.year', ['year' => 2027, 'type' => 'plan']) }}" class="text-blue-700 hover:underline">{{ $cityTotals['y2027_plan_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['y2027_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center"><a href="{{ route('monitoring.year', ['year' => 2027, 'type' => 'fact']) }}" class="text-blue-700 hover:underline">{{ $cityTotals['y2027_fact_count'] }}</a></td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format($cityTotals['y2027_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-right">{{ number_format(($cityTotals['y2027_plan_amount'] - $cityTotals['y2027_fact_amount']) / 1000000, 1) }}</td>
                    </tr>

                    <!-- Districts -->
                    @foreach(array_reverse($monitoringData) as $index => $data)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="border border-gray-300 px-3 py-2 text-center text-gray-700">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-3 py-2 sticky left-0 bg-white z-10"><a href="{{ route('monitoring.district', $data['district']) }}" class="text-blue-700 hover:underline font-medium">{{ $data['district']->name_uz }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-center text-gray-700">{{ $data['total_contracts'] }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['total_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.status', 'cancelled') }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['cancelled_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['cancelled_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.status', 'completed') }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['completed_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['completed_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center text-gray-700">{{ $data['returned_count'] }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['returned_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.status', 'active') }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['active_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['active_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center text-gray-700">{{ $data['overdue_count'] }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['overdue_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.permit-type', 4) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['permit_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.permit-type', 1) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['apz_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.permit-type', 3) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['kengash_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.permit-type', 5) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['expertise_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.permit-type', 2) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['gasn_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.quarter', ['year' => 2025, 'quarter' => 3, 'type' => 'plan']) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['q3_2025_plan_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['q3_2025_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.quarter', ['year' => 2025, 'quarter' => 3, 'type' => 'fact']) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['q3_2025_fact_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['q3_2025_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format(($data['q3_2025_plan_amount'] - $data['q3_2025_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.quarter', ['year' => 2025, 'quarter' => 4, 'type' => 'plan']) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['q4_2025_plan_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['q4_2025_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.quarter', ['year' => 2025, 'quarter' => 4, 'type' => 'fact']) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['q4_2025_fact_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['q4_2025_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format(($data['q4_2025_plan_amount'] - $data['q4_2025_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.year', ['year' => 2026, 'type' => 'plan']) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['y2026_plan_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['y2026_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.year', ['year' => 2026, 'type' => 'fact']) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['y2026_fact_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['y2026_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format(($data['y2026_plan_amount'] - $data['y2026_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.year', ['year' => 2027, 'type' => 'plan']) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['y2027_plan_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['y2027_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center"><a href="{{ route('monitoring.year', ['year' => 2027, 'type' => 'fact']) }}?district={{ $data['district']->id }}" class="text-blue-700 hover:underline">{{ $data['y2027_fact_count'] }}</a></td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format($data['y2027_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">{{ number_format(($data['y2027_plan_amount'] - $data['y2027_fact_amount']) / 1000000, 1) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
