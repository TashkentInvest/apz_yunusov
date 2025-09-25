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
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600">Сони</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600">Қиймати</th>

                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 1) }}"
                               class="hover:text-blue-600 hover:underline">
                                АПЗ
                            </a>
                        </th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 2) }}"
                               class="hover:text-blue-600 hover:underline">
                                ГАСН
                            </a>
                        </th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 3) }}"
                               class="hover:text-blue-600 hover:underline">
                                Кенгаш
                            </a>
                        </th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 4) }}"
                               class="hover:text-blue-600 hover:underline">
                                Рухсатнома
                            </a>
                        </th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 5) }}"
                               class="hover:text-blue-600 hover:underline">
                                Экспертиза
                            </a>
                        </th>

                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Амалда</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Бекор</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Якун</th>
                        <th class="border border-gray-300 px-4 py-2 text-xs font-medium text-gray-600 bg-green-50">Қайтар</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- City Total Row -->
                    <tr class="bg-yellow-50 font-semibold hover:bg-yellow-100 transition-colors">
                        <td class="border border-gray-300 px-4 py-3 text-sm"></td>
                        <td class="border border-gray-300 px-4 py-3 text-sm">
                            <a href="#!"
                               class="flex items-center justify-between text-gray-900 hover:text-blue-600 transition-colors group">
                                <span>Тошкент шаҳри</span>
                                <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center">{{ $cityTotals['total_contracts'] }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($cityTotals['total_amount']) }}</td>

                        <!-- Clickable Permit Type Columns -->
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 1) }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $cityTotals['apz_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 2) }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $cityTotals['gasn_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 3) }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $cityTotals['kengash_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 4) }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $cityTotals['permit_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 5) }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $cityTotals['expertise_count'] }}
                            </a>
                        </td>

                        <!-- Clickable Status Columns -->
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">
                            <a href="{{ route('monitoring.status', 'active') }}"
                               class="text-gray-900 hover:text-green-600 hover:underline">
                                {{ $cityTotals['active_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">
                            <a href="{{ route('monitoring.status', 'cancelled') }}"
                               class="text-gray-900 hover:text-red-600 hover:underline">
                                {{ $cityTotals['cancelled_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">
                            <a href="{{ route('monitoring.status', 'completed') }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $cityTotals['completed_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right bg-green-50">{{ number_format($cityTotals['returned_amount']) }}</td>

                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($cityTotals['total_paid']) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($cityTotals['total_debt']) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($cityTotals['debt_2025']) }}</td>
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
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($data['total_amount']) }}</td>

                        <!-- Clickable Permit Type Columns -->
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 1) }}?district={{ $data['district']->id }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $data['apz_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 2) }}?district={{ $data['district']->id }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $data['gasn_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 3) }}?district={{ $data['district']->id }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $data['kengash_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 4) }}?district={{ $data['district']->id }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $data['permit_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-blue-50">
                            <a href="{{ route('monitoring.permit-type', 5) }}?district={{ $data['district']->id }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $data['expertise_count'] }}
                            </a>
                        </td>

                        <!-- Clickable Status Columns -->
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">
                            <a href="{{ route('monitoring.status', 'active') }}?district={{ $data['district']->id }}"
                               class="text-gray-900 hover:text-green-600 hover:underline">
                                {{ $data['active_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">
                            <a href="{{ route('monitoring.status', 'cancelled') }}?district={{ $data['district']->id }}"
                               class="text-gray-900 hover:text-red-600 hover:underline">
                                {{ $data['cancelled_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-center bg-green-50">
                            <a href="{{ route('monitoring.status', 'completed') }}?district={{ $data['district']->id }}"
                               class="text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $data['completed_count'] }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right bg-green-50">{{ number_format($data['returned_amount']) }}</td>

                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($data['total_paid']) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($data['total_debt']) }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-sm text-right">{{ number_format($data['debt_2025']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
