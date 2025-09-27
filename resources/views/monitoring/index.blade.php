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
                        <th rowspan="3" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800">Т/р</th>
                        <th rowspan="3" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800 min-w-[120px] sticky left-0 bg-gray-100 z-10">Ҳудуд номи</th>
                        <th colspan="2" rowspan="2" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800">АРТ бўйича жами</th>
                        <th colspan="6" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800">шундан,</th>
                        <th colspan="9" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800">Назоратдагилар</th>
                        <th colspan="2" rowspan="2" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800">Муддати ўтганлар</th>
                        <th colspan="5" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800">2025 III чорак</th>
                        <th colspan="5" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800">2025 IV чорак</th>
                        <th colspan="5" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800">2026 йил</th>
                        <th colspan="5" class="border border-gray-400 px-2 py-2 text-xs font-bold text-gray-800">2027 йил</th>
                    </tr>

                    <!-- Second row -->
                    <tr>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Бекор/Қайтар.</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Тўланган</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Жами</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Режа</th>
                        <th colspan="5" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Ҳолати</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Режа</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Факт</th>
                        <th rowspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Фарқ</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Режа</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Факт</th>
                        <th rowspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Фарқ</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Режа</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Факт</th>
                        <th rowspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Фарқ</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Режа</th>
                        <th colspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Факт</th>
                        <th rowspan="2" class="border border-gray-400 px-1 py-1 text-xs font-semibold text-gray-700">Фарқ</th>
                    </tr>

                    <!-- Third row -->
                    <tr class="bg-gray-200">
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">Рухс.</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">АПЗ</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">Кенг.</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">Экс.</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">ГАСН</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">дона</th>
                        <th class="border border-gray-400 px-1 py-1 text-[10px] font-semibold text-gray-700">млн</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- City Total -->
                    <tr class="bg-blue-50 font-bold border-t-2 border-b-2 border-blue-500">
                        <td class="border border-gray-400 px-2 py-2 text-center"></td>
                        <td class="border border-gray-400 px-2 py-2 sticky left-0 bg-blue-50 z-10 font-bold">Тошкент шаҳри</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['total_contracts'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['total_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['cancelled_returned_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['cancelled_returned_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['completed_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['completed_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['active_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['active_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['active_plan_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['active_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['active_permit_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['active_apz_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['active_kengash_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['active_expertise_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['active_gasn_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['overdue_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['overdue_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['q3_2025_plan_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['q3_2025_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['q3_2025_fact_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['q3_2025_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format(($cityTotals['q3_2025_plan_amount'] - $cityTotals['q3_2025_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['q4_2025_plan_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['q4_2025_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['q4_2025_fact_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['q4_2025_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format(($cityTotals['q4_2025_plan_amount'] - $cityTotals['q4_2025_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['y2026_plan_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['y2026_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['y2026_fact_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['y2026_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format(($cityTotals['y2026_plan_amount'] - $cityTotals['y2026_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['y2027_plan_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['y2027_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-center">{{ $cityTotals['y2027_fact_count'] }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($cityTotals['y2027_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format(($cityTotals['y2027_plan_amount'] - $cityTotals['y2027_fact_amount']) / 1000000, 1) }}</td>
                    </tr>

                    <!-- Districts -->
                    @foreach(array_reverse($monitoringData) as $index => $data)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-2 py-2 sticky left-0 bg-white z-10"><a href="{{ route('monitoring.district', $data['district']) }}" class="text-blue-700 hover:underline font-medium">{{ $data['district']->name_uz }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['total_contracts'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['total_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['cancelled_returned_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['cancelled_returned_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['completed_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['completed_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['active_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['active_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['active_plan_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['active_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['active_permit_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['active_apz_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['active_kengash_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['active_expertise_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['active_gasn_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['overdue_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['overdue_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['q3_2025_plan_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['q3_2025_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['q3_2025_fact_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['q3_2025_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format(($data['q3_2025_plan_amount'] - $data['q3_2025_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['q4_2025_plan_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['q4_2025_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['q4_2025_fact_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['q4_2025_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format(($data['q4_2025_plan_amount'] - $data['q4_2025_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['y2026_plan_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['y2026_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['y2026_fact_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['y2026_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format(($data['y2026_plan_amount'] - $data['y2026_fact_amount']) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['y2027_plan_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['y2027_plan_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-700">{{ $data['y2027_fact_count'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format($data['y2027_fact_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right text-gray-700">{{ number_format(($data['y2027_plan_amount'] - $data['y2027_fact_amount']) / 1000000, 1) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
