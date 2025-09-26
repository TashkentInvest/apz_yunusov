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
            <table class="w-full border-collapse text-xs">
                <thead class="bg-gray-50">
                    <!-- First row -->
                    <tr>
                        <th rowspan="3" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700">Т/р</th>
                        <th rowspan="3" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700 min-w-[120px] sticky left-0 bg-gray-50 z-10">Ҳудуд номи</th>
                        <th colspan="2" rowspan="2" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700">Жами шартнома</th>
                        <th colspan="8" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700 bg-yellow-50">шундан,</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700 bg-orange-50">шундан,</th>
                        <th colspan="5" rowspan="2" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700 bg-blue-50">Объект қурилиши бўйича ҳолати</th>
                        <th colspan="4" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700 bg-green-50">2025 йилнинг 3-чорак якунига қадар тўланадиганлар</th>
                        <th colspan="4" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700 bg-teal-50">2025 йилнинг 4-чорак якунига қадар тўланадиганлар</th>
                        <th colspan="4" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700 bg-purple-50">2026 йилда тўланадиганлар</th>
                        <th colspan="4" class="border border-gray-300 px-2 py-2 text-xs font-semibold text-gray-700 bg-indigo-50">2027 йилда тўланадиганлар</th>
                    </tr>

                    <!-- Second row -->
                    <tr>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-yellow-50">Бекор қилинганлар (soni)</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-yellow-50">тўлиқ тўланганлар (yakunlangan)</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-yellow-50">тўлов суммалари қайтарилganлар (toxtaligan)</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-yellow-50">назоратдагилар (amaldagi)</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-orange-50">графикдан ортда қолганлар (muddati otgan)</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-green-50">reja</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-green-50">fakt</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-teal-50">reja</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-teal-50">fakt</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-purple-50">reja</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-purple-50">fakt</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-indigo-50">reja</th>
                        <th colspan="2" class="border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 bg-indigo-50">fakt</th>
                    </tr>

                    <!-- Third row -->
                    <tr>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-yellow-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-yellow-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-yellow-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-yellow-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-yellow-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-yellow-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-yellow-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-yellow-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-orange-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-orange-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-blue-50">АПТ</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-blue-50">ГАСН</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-blue-50">Кенгаш</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-blue-50">Рухсатнома</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-blue-50">Экспертиза</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-green-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-green-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-green-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-green-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-teal-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-teal-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-teal-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-teal-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-purple-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-purple-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-purple-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-purple-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-indigo-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-indigo-50">қиймати (млн сўм)</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-indigo-50">сони</th>
                        <th class="border border-gray-300 px-1 py-1 text-xs font-medium text-gray-600 bg-indigo-50">қиймати (млн сўм)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- City Total -->
                    <tr class="bg-yellow-100 font-semibold">
                        <td class="border border-gray-300 px-2 py-2 text-center"></td>
                        <td class="border border-gray-300 px-2 py-2 sticky left-0 bg-yellow-100 z-10">Тошкент шаҳри</td>
                        <td class="border border-gray-300 px-2 py-2 text-center">{{ $cityTotals['total_contracts'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right">{{ number_format($cityTotals['total_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-yellow-50"><a href="{{ route('monitoring.status', 'cancelled') }}" class="hover:underline">{{ $cityTotals['cancelled_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-50">{{ number_format(($cityTotals['cancelled_amount'] ?? 0) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-yellow-50"><a href="{{ route('monitoring.status', 'completed') }}" class="hover:underline">{{ $cityTotals['completed_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-50">{{ number_format(($cityTotals['completed_amount'] ?? 0) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-yellow-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-50">0.0</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-yellow-50"><a href="{{ route('monitoring.status', 'active') }}" class="hover:underline">{{ $cityTotals['active_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-50">{{ number_format($cityTotals['total_paid'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-orange-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-orange-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 1) }}" class="hover:underline">0</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 2) }}" class="hover:underline">0</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 3) }}" class="hover:underline">1</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 4) }}" class="hover:underline">0</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 5) }}" class="hover:underline">0</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-green-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-green-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-green-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-green-50">{{ number_format(65890.6, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-teal-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-teal-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-teal-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-teal-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-purple-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-purple-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-purple-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-purple-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-indigo-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-indigo-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-indigo-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-indigo-50">-</td>
                    </tr>

                    <!-- Districts -->
                    @foreach($monitoringData as $index => $data)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-2 py-2 text-center">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-2 py-2 sticky left-0 bg-white z-10"><a href="{{ route('monitoring.district', $data['district']) }}" class="text-blue-600 hover:underline">{{ $data['district']->name_uz }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center">{{ $data['total_contracts'] }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-right">{{ number_format($data['total_amount'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-yellow-50"><a href="{{ route('monitoring.status', 'cancelled') }}?district={{ $data['district']->id }}" class="hover:underline">{{ $data['cancelled_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-50">{{ number_format(($data['cancelled_amount'] ?? 0) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-yellow-50"><a href="{{ route('monitoring.status', 'completed') }}?district={{ $data['district']->id }}" class="hover:underline">{{ $data['completed_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-50">{{ number_format(($data['completed_amount'] ?? 0) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-yellow-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-50">0.0</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-yellow-50"><a href="{{ route('monitoring.status', 'active') }}?district={{ $data['district']->id }}" class="hover:underline">{{ $data['active_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-50">{{ number_format($data['total_paid'] / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-orange-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-orange-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 1) }}?district={{ $data['district']->id }}" class="hover:underline">{{ $data['apz_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 2) }}?district={{ $data['district']->id }}" class="hover:underline">{{ $data['gasn_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 3) }}?district={{ $data['district']->id }}" class="hover:underline">{{ $data['kengash_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 4) }}?district={{ $data['district']->id }}" class="hover:underline">{{ $data['permit_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50"><a href="{{ route('monitoring.permit-type', 5) }}?district={{ $data['district']->id }}" class="hover:underline">{{ $data['expertise_count'] }}</a></td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-green-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-green-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-green-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-green-50">{{ number_format(($data['debt_q3_2025'] ?? 0) / 1000000, 1) }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-teal-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-teal-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-teal-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-teal-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-purple-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-purple-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-purple-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-purple-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-indigo-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-indigo-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-center bg-indigo-50">-</td>
                        <td class="border border-gray-300 px-2 py-2 text-right bg-indigo-50">-</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
