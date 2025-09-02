{{-- Enhanced Payment Update View with Quarterly Distribution --}}
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
    <button onclick="openQuarterlyPlanModal()"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="calendar-plus" class="w-4 h-4 mr-2"></i>
        Создать график платежей
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
.debt-card { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-left: 4px solid #dc2626; }
.paid-card { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-left: 4px solid #16a34a; }
.plan-card { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-left: 4px solid #2563eb; }
.initial-card { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b; }
.remaining-card { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-left: 4px solid #6366f1; }

.quarter-card { transition: all 0.3s ease; border: 2px solid transparent; }
.quarter-card:hover { border-color: #3b82f6; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.quarter-complete { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-color: #16a34a; }
.quarter-partial { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-color: #f59e0b; }
.quarter-overdue { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-color: #dc2626; }
.quarter-empty { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-color: #94a3b8; }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Contract Header with Key Information -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
        <!-- Contract Basic Info -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-8 border-l-4 border-blue-500">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i data-feather="file-text" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-blue-600 uppercase">Шарт. №</p>
                        <p class="text-lg font-bold text-gray-900">{{ $contract->contract_number }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i data-feather="calendar" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-green-600 uppercase">Санаси</p>
                        <p class="text-lg font-bold text-gray-900">{{ $contract->contract_date->format('d.m.Y') }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center"
                         style="background-color: {{ $contract->status->color }}20;">
                        <i data-feather="info" class="w-5 h-5" style="color: {{ $contract->status->color }}"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase" style="color: {{ $contract->status->color }}">Ҳолати</p>
                        <p class="text-lg font-bold text-gray-900">{{ $contract->status->name_ru }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <i data-feather="percent" class="w-5 h-5 text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-purple-600 uppercase">Бошланғич %</p>
                        <p class="text-lg font-bold text-gray-900">{{ $contract->initial_payment_percent }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Summary Cards -->
        @php
            $summary = $contract->paymentSummary;
            $initialPaymentAmount = $contract->initial_payment_amount;
            $remainingAmount = $contract->remaining_amount;
            $isEmpty = $summary['plan_total'] == 0 && $summary['fact_total'] == 0;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <!-- Contract Total -->
            <div class="plan-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-blue-800">ЖАМИ СУММА</h3>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i data-feather="dollar-sign" class="w-5 h-5 text-blue-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-blue-900">{{ number_format($contract->total_amount / 1000000, 1) }}М</p>
                <p class="text-xs text-blue-700 mt-1">{{ number_format($contract->total_amount, 0, '.', ' ') }} сум</p>
            </div>

            <!-- Initial Payment -->
            <div class="initial-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-yellow-800">БОШЛАНҒИЧ</h3>
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i data-feather="zap" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-yellow-900">{{ number_format($initialPaymentAmount / 1000000, 1) }}М</p>
                <p class="text-xs text-yellow-700 mt-1">{{ $contract->initial_payment_percent }}% ({{ number_format($initialPaymentAmount, 0, '.', ' ') }})</p>
            </div>

            <!-- Remaining Amount -->
            <div class="remaining-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-indigo-800">ҚОЛГАН</h3>
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i data-feather="layers" class="w-5 h-5 text-indigo-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-indigo-900">{{ number_format($remainingAmount / 1000000, 1) }}М</p>
                <p class="text-xs text-indigo-700 mt-1">{{ number_format($remainingAmount, 0, '.', ' ') }} сум</p>
            </div>

            <!-- Paid Total -->
            <div class="paid-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-green-800">ТЎЛАНГАН</h3>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-green-900">{{ number_format($summary['fact_total'] / 1000000, 1) }}М</p>
                <p class="text-xs text-green-700 mt-1">{{ number_format($summary['fact_total'], 0, '.', ' ') }} сум</p>
            </div>

            <!-- Debt -->
            <div class="debt-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-red-800">ҚАРЗ</h3>
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i data-feather="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-red-900">{{ number_format($summary['debt'] / 1000000, 1) }}М</p>
                <div class="mt-2">
                    <div class="flex justify-between text-xs text-red-700 mb-1">
                        <span>Тўланган</span>
                        <span>{{ number_format($summary['payment_percent'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-red-300 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full transition-all duration-500"
                             style="width: {{ min(100, $summary['payment_percent']) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Object Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 bg-gray-50 rounded-xl p-6">
            <div>
                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                    <i data-feather="user" class="w-4 h-4 mr-2 text-blue-600"></i>
                    Буюртмачи
                </h4>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">Номи:</span> {{ $contract->subject->is_legal_entity ? $contract->subject->company_name : 'Жисмоний шахс' }}</p>
                    <p><span class="font-medium">{{ $contract->subject->is_legal_entity ? 'ИНН:' : 'ПИНФЛ:' }}</span> {{ $contract->subject->is_legal_entity ? $contract->subject->inn : $contract->subject->pinfl }}</p>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                    <i data-feather="map-pin" class="w-4 h-4 mr-2 text-green-600"></i>
                    Объект
                </h4>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">Туман:</span> {{ $contract->object->district->name_ru ?? $contract->object->district->name_uz }}</p>
                    <p><span class="font-medium">Ҳажм:</span> {{ number_format($contract->contract_volume, 2) }} м³</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Schedule Management -->
    @if(!empty($paymentSummary) && isset($hasPaymentData) && $hasPaymentData)
        @foreach($paymentSummary as $year => $quarters)
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <i data-feather="calendar" class="w-5 h-5 mr-2 text-purple-600"></i>
                    {{ $year }} йил
                </h3>
                <div class="flex space-x-2">
                    <button onclick="editYearSchedule({{ $year }})"
                            class="px-4 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors">
                        <i data-feather="edit" class="w-4 h-4 mr-1"></i>
                        Тахрирлаш
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @for($quarter = 1; $quarter <= 4; $quarter++)
                    @php
                        $quarterData = $quarters[$quarter];
                        $completionPercent = $quarterData['payment_percent'];
                        $cardClass = 'quarter-empty';
                        if ($completionPercent >= 100) $cardClass = 'quarter-complete';
                        elseif ($completionPercent > 0) $cardClass = 'quarter-partial';
                        elseif ($quarterData['plan_amount'] > 0 && $completionPercent == 0) $cardClass = 'quarter-overdue';
                    @endphp

                    <div class="quarter-card {{ $cardClass }} rounded-xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-800">{{ $quarter }}-чорак</h4>
                            <div class="relative w-10 h-10">
                                <svg class="progress-ring w-10 h-10 transform -rotate-90" viewBox="0 0 36 36">
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
                                    {{ $quarterData['debt'] > 0 ? 'Қарз:' : 'Ортиқча:' }}
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
    @else
        <!-- No Payment Schedule - Show Creation Interface -->
        <div class="bg-white rounded-2xl shadow-lg border-2 border-dashed border-gray-300 p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-feather="calendar-plus" class="w-10 h-10 text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Тўлов графигини тузинг</h3>
                <p class="text-gray-600 mb-6">Қолган {{ number_format($remainingAmount / 1000000, 1) }}М сумни чораклар бўйича тақсимлаб график тузинг</p>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i data-feather="info" class="w-5 h-5 text-yellow-600 mr-2"></i>
                        <div class="text-left">
                            <p class="text-sm font-medium text-yellow-800">Маълумот:</p>
                            <p class="text-sm text-yellow-700">Бошланғич тўлов {{ $contract->initial_payment_percent }}% ({{ number_format($initialPaymentAmount / 1000000, 1) }}М) ҳисобга олинган</p>
                        </div>
                    </div>
                </div>

                <button onclick="openQuarterlyPlanModal()"
                        class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium text-lg">
                    <i data-feather="calendar-plus" class="w-6 h-6 mr-3"></i>
                    График тузиш
                </button>
            </div>
        </div>
    @endif
</div>

<!-- Enhanced Quarterly Payment Plan Modal -->
<div id="quarterlyPlanModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-3xl sm:w-full max-h-screen overflow-y-auto">
            <form id="quarterlyPlanForm">
                @csrf
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Чораклик тўлов графигини тузиш</h3>
                    <p class="text-sm text-gray-600 mt-2">Қолган {{ number_format($remainingAmount / 1000000, 1) }}М сумни тақсимланг</p>
                </div>

                <div class="px-8 py-6 space-y-6">
                    <!-- Year and Basic Settings -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Йил</label>
                            <select name="year" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                @for($y = date('Y'); $y <= date('Y') + 5; $y++)
                                    <option value="{{ $y }}">{{ $y }} йил</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Чораклар сони</label>
                            <select name="quarters_count" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="updateQuartersDisplay()">
                                <option value="1">1 чорак</option>
                                <option value="2">2 чорак</option>
                                <option value="3">3 чорак</option>
                                <option value="4" selected>4 чорак</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Жами сумма</label>
                            <input type="number" name="total_amount" step="0.01" required value="{{ $remainingAmount }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Distribution Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Тақсимлаш усули</label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="distribution_type" value="equal" id="equal" checked class="text-blue-600">
                                <label for="equal" class="text-sm text-gray-700">Тенг тақсимлаш</label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="distribution_type" value="custom" id="custom" class="text-blue-600">
                                <label for="custom" class="text-sm text-gray-700">Фоизларни кўрсатиш</label>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Distribution -->
                    <div id="customDistribution" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Чораклар бўйича фоизлар</label>
                        <div class="grid grid-cols-2 gap-4" id="quartersGrid">
                            <div class="quarter-input" data-quarter="1">
                                <label class="block text-xs text-gray-600">1-чорак (%)</label>
                                <input type="number" name="q1_percent" min="0" max="100" value="25"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                       onchange="updatePercentageTotal()">
                            </div>
                            <div class="quarter-input" data-quarter="2">
                                <label class="block text-xs text-gray-600">2-чорак (%)</label>
                                <input type="number" name="q2_percent" min="0" max="100" value="25"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                       onchange="updatePercentageTotal()">
                            </div>
                            <div class="quarter-input" data-quarter="3">
                                <label class="block text-xs text-gray-600">3-чорак (%)</label>
                                <input type="number" name="q3_percent" min="0" max="100" value="25"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                       onchange="updatePercentageTotal()">
                            </div>
                            <div class="quarter-input" data-quarter="4">
                                <label class="block text-xs text-gray-600">4-чорак (%)</label>
                                <input type="number" name="q4_percent" min="0" max="100" value="25"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                       onchange="updatePercentageTotal()">
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-gray-100 rounded-lg">
                            <div class="flex justify-between text-sm">
                                <span>Жами:</span>
                                <span id="totalPercentage" class="font-semibold">100%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Distribution -->
                    <div id="distributionPreview" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-800 mb-3">Тақсимлаш кўриниши:</h4>
                        <div class="grid grid-cols-2 gap-3" id="previewGrid">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closeQuarterlyPlanModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Бекор қилиш
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        График тузиш
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
                    <h3 class="text-xl font-semibold text-gray-900">Чорак учун план</h3>
                    <p class="text-sm text-gray-600 mt-1" id="quarterPlanTitle"></p>
                </div>

                <div class="px-8 py-6 space-y-4">
                    <input type="hidden" name="year" id="quarterPlanYear">
                    <input type="hidden" name="quarter" id="quarterPlanQuarter">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Тўлов суммаси</label>
                        <input type="number" name="amount" step="0.01" min="0" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg"
                               placeholder="0.00">
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closeQuarterPlanModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Бекор қилиш
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Сақлаш
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
                        Янги тўлов
                    </h3>
                </div>

                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Тўлов санаси</label>
                        <input type="date" name="payment_date" required
                               value="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Тўлов суммаси</label>
                        <input type="number" name="amount" step="0.01" min="0" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg"
                               placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ҳужжат рақами</label>
                        <input type="text" name="payment_number" maxlength="50"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Чек, справка рақами">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Изоҳ</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Қўшимча маълумот"></textarea>
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closeFactPaymentModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Бекор қилиш
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Тўловни қўшиш
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
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-4xl sm:w-full max-h-screen overflow-y-auto">
            <div class="px-8 py-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Чорак тўловлари</h3>
                <p class="text-sm text-gray-600 mt-1" id="quarterPaymentsTitle"></p>
            </div>
            <div class="px-8 py-6 max-h-96 overflow-y-auto">
                <div id="quarterPaymentsList"></div>
            </div>
            <div class="px-8 py-6 border-t border-gray-200 flex justify-between">
                <div id="quarterPaymentsTotal" class="text-lg font-semibold text-green-600"></div>
                <button type="button" onclick="closeQuarterPaymentsModal()"
                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Ёпиш
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
const remainingAmount = {{ $remainingAmount }};
const contractTotal = {{ $contract->total_amount }};

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();

    // Setup event listeners
    setupEventListeners();
    updateDistributionPreview();
});

function setupEventListeners() {
    // Distribution type toggle
    document.querySelectorAll('input[name="distribution_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            toggleCustomDistribution();
            updateDistributionPreview();
        });
    });

    // Form change listeners
    document.querySelector('select[name="quarters_count"]').addEventListener('change', function() {
        updateQuartersDisplay();
        updateDistributionPreview();
    });

    document.querySelector('input[name="total_amount"]').addEventListener('input', updateDistributionPreview);
}

// Modal management functions
function openQuarterlyPlanModal() {
    document.getElementById('quarterlyPlanModal').classList.remove('hidden');
    updateDistributionPreview();
}

function closeQuarterlyPlanModal() {
    document.getElementById('quarterlyPlanModal').classList.add('hidden');
    document.getElementById('quarterlyPlanForm').reset();
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
    document.getElementById('quarterPlanTitle').textContent = `${quarter}-чорак ${year} йил`;
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

// Distribution management functions
function updateQuartersDisplay() {
    const quartersCount = parseInt(document.querySelector('select[name="quarters_count"]').value);
    const quarterInputs = document.querySelectorAll('.quarter-input');

    quarterInputs.forEach((input, index) => {
        if (index < quartersCount) {
            input.style.display = 'block';
            input.querySelector('input').value = 100 / quartersCount;
        } else {
            input.style.display = 'none';
            input.querySelector('input').value = 0;
        }
    });

    updatePercentageTotal();
}

function toggleCustomDistribution() {
    const customDiv = document.getElementById('customDistribution');
    const isCustom = document.getElementById('custom').checked;

    if (isCustom) {
        customDiv.classList.remove('hidden');
    } else {
        customDiv.classList.add('hidden');
        updateQuartersDisplay();
    }
}

function updatePercentageTotal() {
    const percentInputs = document.querySelectorAll('input[name^="q"][name$="_percent"]');
    let total = 0;

    percentInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });

    const totalSpan = document.getElementById('totalPercentage');
    totalSpan.textContent = total.toFixed(1) + '%';

    // Color code based on total
    if (Math.abs(total - 100) < 0.1) {
        totalSpan.className = 'font-semibold text-green-600';
    } else {
        totalSpan.className = 'font-semibold text-red-600';
    }

    updateDistributionPreview();
}

function updateDistributionPreview() {
    const totalAmount = parseFloat(document.querySelector('input[name="total_amount"]').value) || remainingAmount;
    const quartersCount = parseInt(document.querySelector('select[name="quarters_count"]').value);
    const isCustom = document.getElementById('custom').checked;
    const previewGrid = document.getElementById('previewGrid');

    let html = '';

    for (let quarter = 1; quarter <= quartersCount; quarter++) {
        let amount = 0;

        if (isCustom) {
            const percent = parseFloat(document.querySelector(`input[name="q${quarter}_percent"]`).value) || 0;
            amount = (totalAmount * percent) / 100;
        } else {
            amount = totalAmount / quartersCount;
        }

        html += `
            <div class="bg-white border border-blue-200 rounded-lg p-3">
                <div class="text-center">
                    <div class="text-xs font-medium text-blue-600">${quarter}-чорак</div>
                    <div class="text-lg font-bold text-blue-900">${(amount / 1000000).toFixed(1)}М</div>
                    <div class="text-xs text-gray-500">${new Intl.NumberFormat('ru-RU').format(amount)} сум</div>
                </div>
            </div>
        `;
    }

    previewGrid.innerHTML = html;
}

// Payment details modal
function showQuarterPayments(year, quarter) {
    const actualPayments = @json($contract->actualPayments->groupBy(function($payment) {
        return $payment->year . '-' . $payment->quarter;
    }));

    const key = year + '-' + quarter;
    const payments = actualPayments[key] || [];

    document.getElementById('quarterPaymentsTitle').textContent = `${quarter}-чорак ${year} йил`;

    if (payments.length === 0) {
        document.getElementById('quarterPaymentsList').innerHTML = `
            <div class="text-center py-12 text-gray-500">
                <i data-feather="inbox" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <p class="text-lg">Тўловлар топилмади</p>
                <p class="text-sm">Бу чоракда ҳали тўловлар бўлмаган</p>
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
                                    <h4 class="font-semibold text-gray-900">${payment.payment_number || 'Тўлов №' + payment.id}</h4>
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
                                        class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors">
                                    <i data-feather="edit-2" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteFactPayment(${payment.id})"
                                        class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors">
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
            `Жами: ${(totalAmount / 1000000).toFixed(1)}М сум`;
    }

    feather.replace();
    document.getElementById('quarterPaymentsModal').classList.remove('hidden');
}

function closeQuarterPaymentsModal() {
    document.getElementById('quarterPaymentsModal').classList.add('hidden');
}

// Form submissions
document.getElementById('quarterlyPlanForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');

    // Validate percentages if custom distribution
    const isCustom = document.getElementById('custom').checked;
    if (isCustom) {
        const totalPercent = ['q1_percent', 'q2_percent', 'q3_percent', 'q4_percent']
            .reduce((sum, name) => sum + (parseFloat(formData.get(name)) || 0), 0);

        if (Math.abs(totalPercent - 100) > 0.1) {
            showErrorMessage('Фоизлар йиғиндиси 100% бўлиши керак');
            return;
        }
    }

    toggleLoading(submitButton, true);

    try {
        const response = await fetch(`{{ route('contracts.create_quarterly_schedule', $contract) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeQuarterlyPlanModal();
            showSuccessMessage(result.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'График тузишда хатолик юз берди');
        }
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
            throw new Error(result.message || 'План тўловни сақлашда хатолик');
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
            throw new Error(result.message || 'Тўловни қўшишда хатолик');
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
    if (!confirm('Бу тўловни ўчиришга ишончингиз комилми?')) {
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
            throw new Error(result.message || 'Тўловни ўчиришда хатолик');
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
        button.innerHTML = '<i data-feather="loader" class="w-4 h-4 mr-2 inline animate-spin"></i>Юкланмоқда...';
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
    showNotification(message, 'success');
}

function showErrorMessage(message) {
    showNotification(message, 'error');
}

function showNotification(message, type) {
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const icon = type === 'success' ? 'check-circle' : 'alert-circle';

    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i data-feather="${icon}" class="w-5 h-5 mr-2"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);
    feather.replace();

    setTimeout(() => notification.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => document.body.removeChild(notification), 300);
    }, type === 'success' ? 3000 : 5000);
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

function editYearSchedule(year) {
    openQuarterlyPlanModal();
    document.querySelector('select[name="year"]').value = year;
}
</script>
@endpush
