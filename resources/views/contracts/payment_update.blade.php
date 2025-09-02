{{-- resources/views/contracts/payment_update.blade.php --}}
@extends('layouts.app')

@section('title', 'Управление платежами - ' . $contract->contract_number)
@section('page-title', 'Управление платежами')

@section('header-actions')
<div class="flex flex-wrap gap-3">
    <a href="{{ route('contracts.show', $contract) }}"
       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
        Назад к договору
    </a>
    <button onclick="openQuickPlanModal()"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="calendar-plus" class="w-4 h-4 mr-2"></i>
        Быстрый план
    </button>
    <button onclick="openFactPaymentModal()"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
        Новый платеж
    </button>
</div>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.debt-card {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-left: 4px solid #dc2626;
}
.paid-card {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-left: 4px solid #16a34a;
}
.plan-card {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-left: 4px solid #2563eb;
}
.quarter-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}
.quarter-card:hover {
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.quarter-complete {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-color: #16a34a;
}
.quarter-partial {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #f59e0b;
}
.quarter-overdue {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-color: #dc2626;
}
.quarter-empty {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-color: #94a3b8;
}
.progress-ring {
    transform: rotate(-90deg);
}
.action-btn {
    padding: 8px;
    border-radius: 8px;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.action-btn:hover {
    transform: scale(1.1);
}
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Contract Summary Header -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
        <!-- Contract Header Info with the 3 required fields -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-8 border-l-4 border-blue-500">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i data-feather="file-text" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Шарт. №</p>
                        <p class="text-lg font-bold text-gray-900">{{ $contract->contract_number }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i data-feather="calendar" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Шартнома санаси</p>
                        <p class="text-lg font-bold text-gray-900">{{ $contract->contract_date->format('d.m.Y') }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         style="background-color: {{ $contract->status->color }}20;">
                        <i data-feather="info" class="w-6 h-6" style="color: {{ $contract->status->color }}"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide" style="color: {{ $contract->status->color }}">Контракт ҳолати</p>
                        <p class="text-lg font-bold text-gray-900">{{ $contract->status->name_ru }}</p>
                        @if($contract->completion_date)
                            <p class="text-xs text-gray-500">
                                Завершение: {{ $contract->completion_date->format('d.m.Y') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Stats Cards -->
        @php
            $summary = $contract->paymentSummary;
            $isEmpty = $summary['plan_total'] == 0 && $summary['fact_total'] == 0;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="plan-card rounded-xl p-6 {{ $isEmpty ? 'opacity-60' : '' }}">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-blue-800">ПЛАН ВСЕГО</h3>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i data-feather="target" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
                @if($isEmpty)
                    <p class="text-3xl font-bold text-blue-900">0М</p>
                    <p class="text-sm text-blue-700 mt-1">План не создан</p>
                    <button onclick="openQuickPlanModal()" class="mt-3 text-xs bg-blue-200 text-blue-800 px-3 py-1 rounded-full hover:bg-blue-300 transition-colors">
                        + Создать план
                    </button>
                @else
                    <p class="text-3xl font-bold text-blue-900">{{ number_format($summary['plan_total'] / 1000000, 1) }}М</p>
                    <p class="text-sm text-blue-700 mt-1">{{ number_format($summary['plan_total'], 0, '.', ' ') }} сум</p>
                @endif
            </div>

            <div class="paid-card rounded-xl p-6 {{ $isEmpty ? 'opacity-60' : '' }}">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-green-800">ОПЛАЧЕНО</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
                @if($isEmpty)
                    <p class="text-3xl font-bold text-green-900">0М</p>
                    <p class="text-sm text-green-700 mt-1">Платежей нет</p>
                    <button onclick="openFactPaymentModal()" class="mt-3 text-xs bg-green-200 text-green-800 px-3 py-1 rounded-full hover:bg-green-300 transition-colors">
                        + Добавить платеж
                    </button>
                @else
                    <p class="text-3xl font-bold text-green-900">{{ number_format($summary['fact_total'] / 1000000, 1) }}М</p>
                    <p class="text-sm text-green-700 mt-1">{{ number_format($summary['fact_total'], 0, '.', ' ') }} сум</p>
                @endif
            </div>

            <div class="debt-card rounded-xl p-6 {{ $isEmpty ? 'opacity-60' : '' }}">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-red-800">ДОЛГ</h3>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
                    </div>
                </div>
                @if($isEmpty)
                    <p class="text-3xl font-bold text-red-900">0М</p>
                    <p class="text-sm text-red-700 mt-1">Нет данных для расчета</p>
                @else
                    <p class="text-3xl font-bold text-red-900">{{ number_format($summary['debt'] / 1000000, 1) }}М</p>
                    <p class="text-sm text-red-700 mt-1">{{ number_format($summary['debt'], 0, '.', ' ') }} сум</p>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-red-700 mb-1">
                            <span>Оплачено</span>
                            <span>{{ number_format($summary['payment_percent'], 1) }}%</span>
                        </div>
                        <div class="w-full bg-red-300 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all duration-500"
                                 style="width: {{ min(100, $summary['payment_percent']) }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Customer & Object Info - Compact View -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 bg-gray-50 rounded-xl p-6">
            <div>
                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                    <i data-feather="user" class="w-4 h-4 mr-2 text-blue-600"></i>
                    Заказчик
                </h4>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">Название:</span>
                        {{ $contract->subject->is_legal_entity ? $contract->subject->company_name : 'Физ. лицо' }}
                    </p>
                    <p><span class="font-medium">{{ $contract->subject->is_legal_entity ? 'ИНН:' : 'ПИНФЛ:' }}</span>
                        {{ $contract->subject->is_legal_entity ? $contract->subject->inn : $contract->subject->pinfl }}
                    </p>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                    <i data-feather="map-pin" class="w-4 h-4 mr-2 text-green-600"></i>
                    Объект
                </h4>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">Район:</span> {{ $contract->object->district->name_ru ?? $contract->object->district->name_uz }}</p>
                    <p><span class="font-medium">Объем:</span> {{ number_format($contract->contract_volume, 2) }} м³</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Payment Progress Chart -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i data-feather="pie-chart" class="w-5 h-5 mr-2 text-blue-600"></i>
                Прогресс платежей
            </h3>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="paymentProgressChart"></canvas>
            </div>
        </div>

        <!-- Quarterly Trends Chart -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i data-feather="trending-up" class="w-5 h-5 mr-2 text-green-600"></i>
                Динамика по кварталам
            </h3>
            <div class="h-64">
                <canvas id="quarterlyTrendsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quarterly Payment Grid -->
    @if(!empty($paymentSummary) && isset($hasPaymentData) && $hasPaymentData)
        @foreach($paymentSummary as $year => $quarters)
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <i data-feather="calendar" class="w-5 h-5 mr-2 text-purple-600"></i>
                    {{ $year }} год
                </h3>
                <button onclick="addYearPlan({{ $year }})"
                        class="action-btn bg-blue-100 text-blue-600 hover:bg-blue-200">
                    <i data-feather="plus" class="w-4 h-4 mr-1"></i>
                    План на год
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @for($quarter = 1; $quarter <= 4; $quarter++)
                    @php
                        $quarterData = $quarters[$quarter];
                        $completionPercent = $quarterData['payment_percent'];
                        $cardClass = 'quarter-empty';
                        if ($completionPercent >= 100) {
                            $cardClass = 'quarter-complete';
                        } elseif ($completionPercent > 0) {
                            $cardClass = 'quarter-partial';
                        } elseif ($quarterData['plan_amount'] > 0 && $completionPercent == 0) {
                            $cardClass = 'quarter-overdue';
                        }
                    @endphp

                    <div class="quarter-card {{ $cardClass }} rounded-xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-800">{{ $quarter }} квартал</h4>
                            <div class="relative w-10 h-10">
                                <svg class="progress-ring w-10 h-10" viewBox="0 0 36 36">
                                    <path class="text-gray-300" stroke="currentColor" stroke-width="3" fill="none"
                                          d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                    <path class="{{ $completionPercent >= 100 ? 'text-green-500' : ($completionPercent > 0 ? 'text-yellow-500' : 'text-gray-300') }}"
                                          stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"
                                          stroke-dasharray="{{ $completionPercent }}, 100"
                                          d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-xs font-bold">{{ number_format($completionPercent, 0) }}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">План:</span>
                                <div class="text-right">
                                    <span class="font-bold text-blue-600">{{ number_format($quarterData['plan_amount'] / 1000000, 1) }}М</span>
                                    @if($quarterData['plan'])
                                        <button onclick="editQuarterPlan({{ $year }}, {{ $quarter }}, {{ $quarterData['plan_amount'] }})"
                                                class="ml-2 text-blue-500 hover:text-blue-700">
                                            <i data-feather="edit-2" class="w-3 h-3"></i>
                                        </button>
                                    @else
                                        <button onclick="addQuarterPlan({{ $year }}, {{ $quarter }})"
                                                class="ml-2 text-blue-500 hover:text-blue-700">
                                            <i data-feather="plus-circle" class="w-3 h-3"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Факт:</span>
                                <div class="text-right">
                                    <span class="font-bold text-green-600">{{ number_format($quarterData['fact_total'] / 1000000, 1) }}М</span>
                                    @if($quarterData['fact_payments']->count() > 0)
                                        <button onclick="showQuarterPayments({{ $year }}, {{ $quarter }})"
                                                class="ml-2 text-green-500 hover:text-green-700">
                                            <i data-feather="eye" class="w-3 h-3"></i>
                                        </button>
                                    @endif
                                    <button onclick="addQuarterPayment({{ $year }}, {{ $quarter }})"
                                            class="ml-1 text-green-500 hover:text-green-700">
                                        <i data-feather="plus-circle" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                <span class="text-sm font-medium {{ $quarterData['debt'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $quarterData['debt'] > 0 ? 'Долг:' : 'Переплата:' }}
                                </span>
                                <span class="font-bold {{ $quarterData['debt'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format(abs($quarterData['debt']) / 1000000, 1) }}М
                                </span>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
        @endforeach
    @elseif(!empty($paymentSummary))
        @php $currentYear = array_keys($paymentSummary)[0]; @endphp
        <div class="bg-white rounded-2xl shadow-lg border-2 border-dashed border-gray-300 p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-feather="calendar-plus" class="w-10 h-10 text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Настройте план платежей на {{ $currentYear }} год</h3>
                <p class="text-gray-600 mb-6">Создайте график платежей по кварталам для эффективного управления</p>

                <div class="flex flex-wrap justify-center gap-3 mb-8">
                    <button onclick="openQuickPlanModal()"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium">
                        <i data-feather="zap" class="w-5 h-5 mr-2"></i>
                        Быстрый план на год
                    </button>
                    <button onclick="addQuarterPlan({{ $currentYear }}, 1)"
                            class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-medium">
                        <i data-feather="plus-circle" class="w-5 h-5 mr-2"></i>
                        План по кварталам
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @for($quarter = 1; $quarter <= 4; $quarter++)
                    <div class="quarter-card quarter-empty rounded-xl p-5 border-2 border-dashed border-gray-300">
                        <div class="text-center">
                            <h4 class="font-semibold text-gray-600 mb-3">{{ $quarter }} квартал</h4>
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-feather="plus" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <p class="text-sm text-gray-500 mb-3">План не задан</p>
                            <div class="space-y-2">
                                <button onclick="addQuarterPlan({{ $currentYear }}, {{ $quarter }})"
                                        class="w-full px-3 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors text-sm">
                                    Добавить план
                                </button>
                                <button onclick="addQuarterPayment({{ $currentYear }}, {{ $quarter }})"
                                        class="w-full px-3 py-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors text-sm">
                                    Добавить платеж
                                </button>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-lg border-2 border-dashed border-gray-300 p-12 text-center">
            <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-feather="calendar-plus" class="w-12 h-12 text-blue-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Создайте первый план платежей</h3>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                Этот договор пока не имеет плана платежей. Начните с создания графика платежей для отслеживания прогресса и задолженностей
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <button onclick="openQuickPlanModal()"
                        class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium text-lg">
                    <i data-feather="calendar-plus" class="w-6 h-6 mr-3"></i>
                    Создать план на год
                </button>
                <button onclick="openFactPaymentModal()"
                        class="inline-flex items-center px-8 py-4 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium text-lg">
                    <i data-feather="credit-card" class="w-6 h-6 mr-3"></i>
                    Добавить платеж
                </button>
            </div>
        </div>
    @endif

    <!-- Recent Activity -->
    @if($contract->actualPayments->count() > 0)
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i data-feather="clock" class="w-5 h-5 mr-2 text-indigo-600"></i>
            Последние платежи
        </h3>
        <div class="space-y-4">
            @foreach($contract->actualPayments->take(5) as $payment)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i data-feather="credit-card" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $payment->payment_number ?: 'Платеж №' . $payment->id }}</p>
                            <p class="text-sm text-gray-600">{{ $payment->quarter }} кв. {{ $payment->year }} • {{ $payment->payment_date->format('d.m.Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-green-600">{{ number_format($payment->amount / 1000000, 1) }}М сум</p>
                        <div class="flex items-center space-x-2 mt-1">
                            <button onclick="editPayment({{ $payment->id }})"
                                    class="text-blue-500 hover:text-blue-700">
                                <i data-feather="edit-2" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteFactPayment({{ $payment->id }})"
                                    class="text-red-500 hover:text-red-700">
                                <i data-feather="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Quick Plan Modal -->
<div id="quickPlanModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-2xl sm:w-full">
            <form id="quickPlanForm">
                @csrf
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i data-feather="zap" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Быстрое создание плана
                    </h3>
                    <p class="text-sm text-gray-600 mt-2">Создайте план платежей для целого года</p>
                </div>

                <div class="px-8 py-6 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Год</label>
                            <select name="year" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @for($y = date('Y'); $y <= date('Y') + 5; $y++)
                                    <option value="{{ $y }}">{{ $y }} год</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Общая сумма</label>
                            <input type="number" name="total_amount" step="0.01" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Распределение по кварталам</label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="distribution_type" value="equal" id="equal" checked class="text-blue-600">
                                <label for="equal" class="text-sm text-gray-700">Равномерно по кварталам</label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="distribution_type" value="custom" id="custom" class="text-blue-600">
                                <label for="custom" class="text-sm text-gray-700">Настроить вручную</label>
                            </div>
                        </div>

                        <div id="customDistribution" class="hidden mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600">1 квартал (%)</label>
                                <input type="number" name="q1_percent" min="0" max="100" value="25"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">2 квартал (%)</label>
                                <input type="number" name="q2_percent" min="0" max="100" value="25"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">3 квартал (%)</label>
                                <input type="number" name="q3_percent" min="0" max="100" value="25"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">4 квартал (%)</label>
                                <input type="number" name="q4_percent" min="0" max="100" value="25"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closeQuickPlanModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Создать план
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Single Quarter Plan Modal -->
<div id="quarterPlanModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form id="quarterPlanForm">
                @csrf
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Плановый платеж</h3>
                    <p class="text-sm text-gray-600 mt-1" id="quarterPlanTitle"></p>
                </div>

                <div class="px-8 py-6 space-y-4">
                    <input type="hidden" name="year" id="quarterPlanYear">
                    <input type="hidden" name="quarter" id="quarterPlanQuarter">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Сумма платежа</label>
                        <input type="number" name="amount" step="0.01" min="0" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                               placeholder="0.00">
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closeQuarterPlanModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fact Payment Modal -->
<div id="factPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form id="factPaymentForm">
                @csrf
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i data-feather="credit-card" class="w-5 h-5 mr-2 text-green-600"></i>
                        Новый платеж
                    </h3>
                </div>

                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Дата платежа</label>
                        <input type="date" name="payment_date" required
                               value="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Сумма платежа</label>
                        <input type="number" name="amount" step="0.01" min="0" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg"
                               placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Номер документа</label>
                        <input type="text" name="payment_number" maxlength="50"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Номер чека, справки и т.д.">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Примечание</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Дополнительная информация"></textarea>
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closeFactPaymentModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Добавить платеж
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quarter Payments Details Modal -->
<div id="quarterPaymentsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-3xl sm:w-full">
            <div class="px-8 py-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Платежи за квартал</h3>
                <p class="text-sm text-gray-600 mt-1" id="quarterPaymentsTitle"></p>
            </div>
            <div class="px-8 py-6 max-h-96 overflow-y-auto">
                <div id="quarterPaymentsList"></div>
            </div>
            <div class="px-8 py-6 border-t border-gray-200 flex justify-between">
                <div id="quarterPaymentsTotal" class="text-lg font-semibold text-green-600"></div>
                <button type="button" onclick="closeQuarterPaymentsModal()"
                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/feather-icons"></script>
<script>
// Global variables
const contractId = {{ $contract->id }};
const paymentData = @json($paymentSummary);

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    feather.replace();

    // Distribution type toggle
    document.querySelectorAll('input[name="distribution_type"]').forEach(radio => {
        radio.addEventListener('change', toggleCustomDistribution);
    });
});

// Chart initialization functions
function initializeCharts() {
    initializePaymentProgressChart();
    initializeQuarterlyTrendsChart();
}

function initializePaymentProgressChart() {
    const ctx = document.getElementById('paymentProgressChart');
    if (!ctx) return;

    const summary = @json($contract->paymentSummary);

    if (summary.plan_total == 0 && summary.fact_total == 0) {
        const context = ctx.getContext('2d');
        ctx.width = ctx.offsetWidth;
        ctx.height = ctx.offsetHeight;

        context.fillStyle = '#f3f4f6';
        context.fillRect(0, 0, ctx.width, ctx.height);
        context.fillStyle = '#6b7280';
        context.font = '14px sans-serif';
        context.textAlign = 'center';
        context.fillText('Нет данных для отображения', ctx.width/2, ctx.height/2);
        context.fillText('Создайте план или добавьте платежи', ctx.width/2, ctx.height/2 + 20);
        return;
    }

    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Оплачено', 'Долг'],
            datasets: [{
                data: [summary.fact_total, Math.max(0, summary.debt)],
                backgroundColor: ['#10b981', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 14
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
}

function initializeQuarterlyTrendsChart() {
    const ctx = document.getElementById('quarterlyTrendsChart');
    if (!ctx) return;

    const labels = [];
    const planData = [];
    const factData = [];
    const hasData = Object.keys(paymentData).length > 0;

    if (!hasData) {
        const context = ctx.getContext('2d');
        ctx.width = ctx.offsetWidth;
        ctx.height = ctx.offsetHeight;

        context.fillStyle = '#f3f4f6';
        context.fillRect(0, 0, ctx.width, ctx.height);
        context.fillStyle = '#6b7280';
        context.font = '14px sans-serif';
        context.textAlign = 'center';
        context.fillText('Нет данных для отображения', ctx.width/2, ctx.height/2);
        context.fillText('Добавьте плановые платежи', ctx.width/2, ctx.height/2 + 20);
        return;
    }

    Object.keys(paymentData).forEach(year => {
        for (let quarter = 1; quarter <= 4; quarter++) {
            const quarterData = paymentData[year][quarter];
            if (quarterData.plan_amount > 0 || quarterData.fact_total > 0) {
                labels.push(`${quarter}Q${year}`);
                planData.push(quarterData.plan_amount / 1000000);
                factData.push(quarterData.fact_total / 1000000);
            }
        }
    });

    if (labels.length === 0) {
        const context = ctx.getContext('2d');
        context.fillStyle = '#f3f4f6';
        context.fillRect(0, 0, ctx.width, ctx.height);
        context.fillStyle = '#6b7280';
        context.font = '14px sans-serif';
        context.textAlign = 'center';
        context.fillText('Нет данных для графика', ctx.width/2, ctx.height/2);
        return;
    }

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'План (млн)',
                    data: planData,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
                },
                {
                    label: 'Факт (млн)',
                    data: factData,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Сумма (млн сум)'
                    }
                }
            }
        }
    });
}

// Modal functions
function openQuickPlanModal() {
    document.getElementById('quickPlanModal').classList.remove('hidden');
}

function closeQuickPlanModal() {
    document.getElementById('quickPlanModal').classList.add('hidden');
    document.getElementById('quickPlanForm').reset();
}

function openFactPaymentModal() {
    document.getElementById('factPaymentModal').classList.remove('hidden');
    document.querySelector('input[name="payment_date"]').value = new Date().toISOString().split('T')[0];
}

function closeFactPaymentModal() {
    document.getElementById('factPaymentModal').classList.add('hidden');
    document.getElementById('factPaymentForm').reset();
}

function addQuarterPlan(year, quarter) {
    document.getElementById('quarterPlanModal').classList.remove('hidden');
    document.getElementById('quarterPlanYear').value = year;
    document.getElementById('quarterPlanQuarter').value = quarter;
    document.getElementById('quarterPlanTitle').textContent = `${quarter} квартал ${year} года`;
    document.querySelector('#quarterPlanForm input[name="amount"]').value = '';
}

function editQuarterPlan(year, quarter, currentAmount) {
    addQuarterPlan(year, quarter);
    document.querySelector('#quarterPlanForm input[name="amount"]').value = currentAmount;
}

function closeQuarterPlanModal() {
    document.getElementById('quarterPlanModal').classList.add('hidden');
    document.getElementById('quarterPlanForm').reset();
}

function addQuarterPayment(year, quarter) {
    openFactPaymentModal();
    const firstMonth = (quarter - 1) * 3 + 2;
    const date = new Date(year, firstMonth - 1, 15);
    document.querySelector('input[name="payment_date"]').value = date.toISOString().split('T')[0];
}

function showQuarterPayments(year, quarter) {
    const actualPayments = @json($contract->actualPayments->groupBy(function($payment) {
        return $payment->year . '-' . $payment->quarter;
    }));

    const key = year + '-' + quarter;
    const payments = actualPayments[key] || [];

    document.getElementById('quarterPaymentsTitle').textContent = `${quarter} квартал ${year} года`;

    if (payments.length === 0) {
        document.getElementById('quarterPaymentsList').innerHTML = `
            <div class="text-center py-12 text-gray-500">
                <i data-feather="inbox" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <p class="text-lg">Платежей не найдено</p>
                <p class="text-sm">В этом квартале еще не было платежей</p>
            </div>
        `;
        document.getElementById('quarterPaymentsTotal').textContent = '';
    } else {
        let totalAmount = 0;
        let html = '<div class="space-y-4">';

        payments.forEach(payment => {
            totalAmount += parseFloat(payment.amount);
            const paymentDate = new Date(payment.payment_date).toLocaleDateString('ru-RU');
            html += `
                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center">
                                    <i data-feather="credit-card" class="w-5 h-5 text-green-700"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">${payment.payment_number || 'Платеж №' + payment.id}</h4>
                                    <p class="text-sm text-gray-600">${paymentDate}</p>
                                </div>
                            </div>
                            ${payment.notes ? `<p class="text-sm text-gray-700 bg-white bg-opacity-50 rounded-lg p-3">${payment.notes}</p>` : ''}
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-2xl font-bold text-green-700">${(payment.amount / 1000000).toFixed(1)}М</p>
                            <p class="text-sm text-gray-600">${new Intl.NumberFormat('ru-RU').format(payment.amount)} сум</p>
                            <div class="flex items-center space-x-2 mt-3">
                                <button onclick="editPayment(${payment.id})"
                                        class="action-btn bg-blue-100 text-blue-600 hover:bg-blue-200" title="Редактировать">
                                    <i data-feather="edit-2" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteFactPayment(${payment.id})"
                                        class="action-btn bg-red-100 text-red-600 hover:bg-red-200" title="Удалить">
                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        document.getElementById('quarterPaymentsList').innerHTML = html;
        document.getElementById('quarterPaymentsTotal').textContent =
            `Итого: ${(totalAmount / 1000000).toFixed(1)}М сум`;
    }

    feather.replace();
    document.getElementById('quarterPaymentsModal').classList.remove('hidden');
}

function closeQuarterPaymentsModal() {
    document.getElementById('quarterPaymentsModal').classList.add('hidden');
}

function toggleCustomDistribution() {
    const customDiv = document.getElementById('customDistribution');
    const isCustom = document.getElementById('custom').checked;

    if (isCustom) {
        customDiv.classList.remove('hidden');
    } else {
        customDiv.classList.add('hidden');
    }
}

// Form submissions
document.getElementById('quickPlanForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');

    const totalAmount = parseFloat(formData.get('total_amount'));
    const year = formData.get('year');
    const distributionType = formData.get('distribution_type');

    let quarters = [];

    if (distributionType === 'equal') {
        const quarterAmount = totalAmount / 4;
        for (let q = 1; q <= 4; q++) {
            quarters.push({ quarter: q, amount: quarterAmount });
        }
    } else {
        const percentages = [
            parseFloat(formData.get('q1_percent')) || 0,
            parseFloat(formData.get('q2_percent')) || 0,
            parseFloat(formData.get('q3_percent')) || 0,
            parseFloat(formData.get('q4_percent')) || 0
        ];

        const totalPercent = percentages.reduce((sum, p) => sum + p, 0);
        if (Math.abs(totalPercent - 100) > 0.1) {
            showErrorMessage('Сумма процентов должна быть равна 100%');
            return;
        }

        for (let q = 1; q <= 4; q++) {
            const amount = (totalAmount * percentages[q-1]) / 100;
            quarters.push({ quarter: q, amount: amount });
        }
    }

    toggleLoading(submitButton, true);

    try {
        for (const quarterData of quarters) {
            const quarterFormData = new FormData();
            quarterFormData.append('year', year);
            quarterFormData.append('quarter', quarterData.quarter);
            quarterFormData.append('amount', quarterData.amount);

            const response = await fetch(`{{ route('contracts.store_plan_payment', $contract) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: quarterFormData
            });

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || `Ошибка при создании плана для ${quarterData.quarter} квартала`);
            }
        }

        closeQuickPlanModal();
        showSuccessMessage('План успешно создан для всех кварталов');
        setTimeout(() => location.reload(), 1500);

    } catch (error) {
        console.error('Error:', error);
        showErrorMessage(error.message);
    } finally {
        toggleLoading(submitButton, false);
    }
});

document.getElementById('quarterPlanForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');

    toggleLoading(submitButton, true);

    try {
        const response = await fetch(`{{ route('contracts.store_plan_payment', $contract) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeQuarterPlanModal();
            showSuccessMessage(result.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Ошибка при сохранении планового платежа');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage(error.message);
    } finally {
        toggleLoading(submitButton, false);
    }
});

document.getElementById('factPaymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');

    toggleLoading(submitButton, true);

    try {
        const response = await fetch(`{{ route('contracts.store_fact_payment', $contract) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeFactPaymentModal();
            showSuccessMessage(result.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'Ошибка при добавлении фактического платежа');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage(error.message);
    } finally {
        toggleLoading(submitButton, false);
    }
});

// Delete functions
async function deleteFactPayment(id) {
    if (!confirm('Вы уверены, что хотите удалить этот платеж?')) {
        return;
    }

    try {
        const response = await fetch(`{{ url('contracts/fact-payment') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();

        if (result.success) {
            showSuccessMessage(result.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Ошибка при удалении платежа');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage(error.message);
    }
}

// Utility functions
function toggleLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        const originalText = button.innerHTML;
        button.setAttribute('data-original-text', originalText);
        button.innerHTML = '<i data-feather="loader" class="w-4 h-4 mr-2 inline animate-spin"></i>Загрузка...';
    } else {
        button.disabled = false;
        const originalText = button.getAttribute('data-original-text');
        if (originalText) {
            button.innerHTML = originalText;
        }
    }
    feather.replace();
}

function showSuccessMessage(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
    notification.innerHTML = `
        <div class="flex items-center">
            <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);
    feather.replace();

    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function showErrorMessage(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
    notification.innerHTML = `
        <div class="flex items-center">
            <i data-feather="alert-circle" class="w-5 h-5 mr-2"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);
    feather.replace();

    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}

// Close modals on background click
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});

// Placeholder functions for future implementation
function editPayment(id) {
    console.log('Edit payment:', id);
    // TODO: Implement edit payment functionality
}

function addYearPlan(year) {
    openQuickPlanModal();
    document.querySelector('select[name="year"]').value = year;
}
</script>
@endpush
