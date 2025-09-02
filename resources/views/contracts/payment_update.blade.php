@extends('layouts.app')

@section('title', 'To\'lov boshqaruvi - ' . $contract->contract_number)
@section('page-title', 'SHARTNOMA №' . $contract->contract_number)
@section('page-subtitle', $contract->contract_date->format('d.m.Y') . ' • ' . ($contract->subject->is_legal_entity ? $contract->subject->company_name : 'Jismoniy shaxs') . ' • ' . $contract->status->name_ru)

@section('header-actions')
    <button onclick="openContractEditModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
        <i class="fas fa-edit mr-2"></i>Tahrir
    </button>
    <button onclick="generateReport()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
        <i class="fas fa-file-excel mr-2"></i>Hisobot
    </button>
@endsection

@section('content')
@php
    $initialAmount = ($contract->total_amount * $contract->initial_payment_percent) / 100;
    $remainingAmount = $contract->total_amount - $initialAmount;
    $plannedTotal = $contract->paymentSchedules()->where('is_active', true)->sum('quarter_amount');
    $paidTotal = $contract->actualPayments()->sum('amount');
    $totalDebt = $contract->total_amount - $paidTotal;
    $paymentPercent = $contract->total_amount > 0 ? ($paidTotal / $contract->total_amount) * 100 : 0;
@endphp

<!-- Contract Summary Card -->
<div class="bg-blue-600 text-white p-6 rounded-lg mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold mb-2">{{ number_format($contract->total_amount / 1000000, 1) }}M SO'M</h2>
            <p class="text-blue-100 mb-4">Jami shartnoma summasi</p>
            <div class="text-blue-100 text-sm space-y-1">
                <p>Boshlang'ich to'lov: {{ $contract->initial_payment_percent }}% ({{ number_format($initialAmount / 1000000, 1) }}M so'm)</p>
                <p>Qolgan summa: {{ number_format($remainingAmount / 1000000, 1) }}M so'm</p>
            </div>
        </div>
        <div class="text-right">
            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                <div class="text-sm text-blue-100">To'lov holati</div>
                <div class="text-3xl font-bold">{{ number_format($paymentPercent, 1) }}%</div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-600 uppercase">BOSHLANG'ICH TO'LOV</h3>
            <i class="fas fa-hand-holding-usd text-blue-500 text-xl"></i>
        </div>
        <div class="text-2xl font-bold text-blue-600">{{ number_format($initialAmount / 1000000, 1) }}M</div>
        <div class="text-sm text-gray-500">{{ $contract->initial_payment_percent }}% dan</div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-600 uppercase">TO'LANGAN</h3>
            <i class="fas fa-check-circle text-green-500 text-xl"></i>
        </div>
        <div class="text-2xl font-bold text-green-600">{{ number_format($paidTotal / 1000000, 1) }}M</div>
        <div class="text-sm text-gray-500">{{ number_format($paymentPercent, 1) }}%</div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-600 uppercase">QOLGAN SUMMA</h3>
            <i class="fas fa-coins text-yellow-500 text-xl"></i>
        </div>
        <div class="text-2xl font-bold text-yellow-600">{{ number_format($remainingAmount / 1000000, 1) }}M</div>
        <div class="text-sm text-gray-500">Taqsimlash uchun</div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-red-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-600 uppercase">QARZ</h3>
            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
        </div>
        <div class="text-2xl font-bold text-red-600">{{ number_format($totalDebt / 1000000, 1) }}M</div>
        <div class="text-sm text-gray-500">{{ number_format(100 - $paymentPercent, 1) }}% qolgan</div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Payment Schedule -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">TO'LOV GRAFIGI</h3>
                    <div class="flex space-x-2">
                        <button onclick="openAutoScheduleModal()" class="bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700 transition-colors">
                            <i class="fas fa-magic mr-1"></i>Avto grafik
                        </button>
                        <button onclick="openManualScheduleModal()" class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700 transition-colors">
                            <i class="fas fa-plus mr-1"></i>Qo'lda qo'shish
                        </button>
                    </div>
                </div>
            </div>

            @if(!empty($paymentSummary) && isset($hasPaymentData) && $hasPaymentData)
                @foreach($paymentSummary as $year => $quarters)
                <div class="p-6 border-b">
                    <h4 class="font-bold mb-4 text-gray-700 uppercase">{{ $year }} YIL</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @for($quarter = 1; $quarter <= 4; $quarter++)
                            @php
                                $quarterData = $quarters[$quarter];
                                $completionPercent = $quarterData['payment_percent'];
                                $cardClass = 'border-gray-200 bg-gray-50';
                                $badgeClass = 'bg-gray-100 text-gray-800';
                                $progressClass = 'bg-gray-400';

                                if ($completionPercent >= 100) {
                                    $cardClass = 'border-green-200 bg-green-50';
                                    $badgeClass = 'bg-green-100 text-green-800';
                                    $progressClass = 'bg-green-500';
                                } elseif ($completionPercent > 0) {
                                    $cardClass = 'border-yellow-200 bg-yellow-50';
                                    $badgeClass = 'bg-yellow-100 text-yellow-800';
                                    $progressClass = 'bg-yellow-500';
                                } elseif ($quarterData['plan_amount'] > 0 && $completionPercent == 0) {
                                    $cardClass = 'border-red-200 bg-red-50';
                                    $badgeClass = 'bg-red-100 text-red-800';
                                    $progressClass = 'bg-red-400';
                                }
                            @endphp

                            <div class="border-2 {{ $cardClass }} p-4 rounded-lg transition-all hover:shadow-md">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="font-bold">{{ $quarter }}-CHORAK</span>
                                    <span class="px-2 py-1 rounded text-xs {{ $badgeClass }}">
                                        {{ number_format($completionPercent, 0) }}%
                                    </span>
                                </div>

                                <div class="space-y-2 mb-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">PLAN:</span>
                                        <span class="font-bold">{{ number_format($quarterData['plan_amount'] / 1000000, 1) }}M</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">FAKT:</span>
                                        <span class="font-bold text-green-600">{{ number_format($quarterData['fact_total'] / 1000000, 1) }}M</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ $quarterData['debt'] > 0 ? 'QARZ:' : 'ORTIQCHA:' }}</span>
                                        <span class="font-bold {{ $quarterData['debt'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format(abs($quarterData['debt']) / 1000000, 1) }}M
                                        </span>
                                    </div>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                                    <div class="{{ $progressClass }} h-2 rounded-full transition-all duration-300"
                                         style="width: {{ min(100, $completionPercent) }}%"></div>
                                </div>

                                <div class="flex justify-center space-x-1">
                                    @if($quarterData['plan'])
                                        <button onclick="editQuarterPlan({{ $year }}, {{ $quarter }}, {{ $quarterData['plan_amount'] }})"
                                                class="p-2 text-blue-600 hover:bg-blue-100 rounded transition-colors" title="Tahrirlash">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <button onclick="addQuarterPlan({{ $year }}, {{ $quarter }})"
                                                class="p-2 text-blue-600 hover:bg-blue-100 rounded transition-colors" title="Plan qo'shish">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endif

                                    @if($quarterData['fact_payments']->count() > 0)
                                        <button onclick="showQuarterPayments({{ $year }}, {{ $quarter }})"
                                                class="p-2 text-green-600 hover:bg-green-100 rounded transition-colors" title="To'lovlarni ko'rish">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif

                                    <button onclick="addQuarterPayment({{ $year }}, {{ $quarter }})"
                                            class="p-2 text-green-600 hover:bg-green-100 rounded transition-colors" title="To'lov qo'shish">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
                @endforeach
            @else
                <div class="p-8 text-center">
                    <div class="mb-6">
                        <i class="fas fa-calendar-plus text-6xl text-gray-400"></i>
                    </div>
                    <h4 class="text-lg font-bold mb-3">TO'LOV GRAFIGI TUZILMAGAN</h4>
                    <p class="text-gray-600 mb-6">
                        Qolgan {{ number_format($remainingAmount / 1000000, 1) }}M so'mni choraklar bo'yicha taqsimlab grafik tuzing
                    </p>
                    <div class="bg-blue-50 border border-blue-200 p-4 rounded mb-6">
                        <strong>Formula:</strong> {{ number_format($contract->total_amount / 1000000, 1) }}M - {{ $contract->initial_payment_percent }}% = {{ number_format($remainingAmount / 1000000, 1) }}M,
                        keyin choraklar soniga bo'linadi
                    </div>
                    <button onclick="openAutoScheduleModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-magic mr-2"></i>GRAFIK TUZISH
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold mb-4 text-gray-900">TEZKOR AMALLAR</h3>
            <div class="space-y-3">
                <button onclick="openContractEditModal()"
                        class="w-full flex items-center p-3 text-left border border-gray-200 rounded hover:bg-gray-50 transition-colors">
                    <i class="fas fa-edit mr-3 text-blue-600"></i>
                    <span>Shartnoma tahriri</span>
                </button>
                <button onclick="openPaymentModal()"
                        class="w-full flex items-center p-3 text-left border border-gray-200 rounded hover:bg-gray-50 transition-colors">
                    <i class="fas fa-credit-card mr-3 text-green-600"></i>
                    <span>To'lov qo'shish</span>
                </button>
                <button onclick="calculatePlan()"
                        class="w-full flex items-center p-3 text-left border border-gray-200 rounded hover:bg-gray-50 transition-colors">
                    <i class="fas fa-calculator mr-3 text-yellow-600"></i>
                    <span>Hisoblash</span>
                </button>
                <button onclick="generateReport()"
                        class="w-full flex items-center p-3 text-left border border-gray-200 rounded hover:bg-gray-50 transition-colors">
                    <i class="fas fa-file-download mr-3 text-indigo-600"></i>
                    <span>Hisobot olish</span>
                </button>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold mb-4 text-gray-900">OXIRGI TO'LOVLAR</h3>
            @if($contract->actualPayments->count() > 0)
                <div class="space-y-3">
                    @foreach($contract->actualPayments->take(5) as $payment)
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100 last:border-b-0 last:pb-0">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">{{ number_format($payment->amount / 1000000, 1) }}M</div>
                            <div class="text-sm text-gray-600">{{ $payment->payment_date->format('d.m.Y') }} • {{ $payment->quarter }}-chorak</div>
                            @if($payment->payment_number)
                                <div class="text-xs text-gray-500">{{ $payment->payment_number }}</div>
                            @endif
                        </div>
                        <div class="relative">
                            <button class="text-gray-400 hover:text-gray-600 p-1" onclick="togglePaymentMenu({{ $payment->id }})">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="paymentMenu{{ $payment->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-10">
                                <button onclick="editPayment({{ $payment->id }})" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50">
                                    <i class="fas fa-edit mr-2 text-blue-600"></i>Tahrirlaish
                                </button>
                                <button onclick="deletePaymentConfirm({{ $payment->id }})" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 text-red-600">
                                    <i class="fas fa-trash mr-2"></i>O'chirish
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Hali to'lovlar yo'q</p>
            @endif
        </div>
    </div>
</div>

<!-- Contract Edit Modal -->
<div id="contractEditModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center p-4 z-50" style="display: none;">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-900">SHARTNOMA MA'LUMOTLARINI TAHRIRLASH</h3>
        </div>

        <form id="contractEditForm" class="p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">TO'LOV SANASI</label>
                    <input type="date"
                           name="payment_date"
                           value="{{ date('Y-m-d') }}"
                           class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">TO'LOV SUMMASI</label>
                    <input type="number"
                           name="amount"
                           step="0.01"
                           min="0"
                           placeholder="0.00"
                           class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">HUJJAT RAQAMI</label>
                <input type="text"
                       name="payment_number"
                       maxlength="50"
                       placeholder="Chek, spravka raqami"
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">IZOH</label>
                <textarea name="notes"
                          rows="3"
                          placeholder="Qo'shimcha ma'lumot"
                          class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button"
                        onclick="closeModal('paymentModal')"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                    BEKOR QILISH
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                    TO'LOVNI QO'SHISH
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quarter Plan Modal -->
<div id="quarterPlanModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center p-4 z-50" style="display: none;">
    <div class="bg-white rounded-lg max-w-lg w-full">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-900">CHORAK UCHUN PLAN</h3>
            <p class="text-gray-600" id="quarterModalTitle">3-chorak 2024 yil</p>
        </div>

        <form id="quarterPlanForm" class="p-6">
            @csrf
            <input type="hidden" name="year" id="quarterYear">
            <input type="hidden" name="quarter" id="quarterNumber">

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">TO'LOV SUMMASI</label>
                <input type="number"
                       name="amount"
                       step="0.01"
                       min="0"
                       placeholder="0.00"
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button"
                        onclick="closeModal('quarterPlanModal')"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                    BEKOR QILISH
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    SAQLASH
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quarter Details Modal -->
<div id="quarterDetailsModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center p-4 z-50" style="display: none;">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-900">CHORAK TO'LOVLARI TAFSILOTI</h3>
            <p class="text-gray-600" id="quarterDetailsTitle">2-chorak 2024 yil</p>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">SANA</th>
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">SUMMA</th>
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">HUJJAT</th>
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">IZOH</th>
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">AMALLAR</th>
                        </tr>
                    </thead>
                    <tbody id="quarterDetailsList">
                        <!-- Dynamic content will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-6 border-t flex justify-between items-center">
            <div class="text-lg font-bold text-green-600" id="quarterDetailsTotal">
                <!-- Total will be displayed here -->
            </div>
            <button onclick="closeModal('quarterDetailsModal')"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                YOPISH
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Essential utility functions with null checks
    function showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
        const alertDiv = document.createElement('div');

        const bgColor = type === 'success' ? 'bg-green-500' :
                       type === 'error' ? 'bg-red-500' :
                       type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';

        const icon = type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-triangle' :
                    type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

        alertDiv.className = `${bgColor} text-white p-4 rounded-lg shadow-lg mb-4 flex items-center max-w-sm`;
        alertDiv.innerHTML = `
            <i class="fas ${icon} mr-3 flex-shrink-0"></i>
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-3 text-white hover:text-gray-200 flex-shrink-0">
                <i class="fas fa-times"></i>
            </button>
        `;

        alertContainer.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv && alertDiv.parentElement) {
                alertDiv.remove();
            }
        }, 5000);
    }

    function createAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alertContainer';
        container.className = 'fixed top-4 right-4 z-50';
        document.body.appendChild(container);
        return container;
    }

    function showLoading(message = 'Ishlov berilmoqda...') {
        let overlay = document.getElementById('loadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            overlay.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                        <span id="loadingMessage">${message}</span>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        const loadingMessage = document.getElementById('loadingMessage');
        if (loadingMessage) {
            loadingMessage.textContent = message;
        }
        overlay.style.display = 'flex';
    }

    function hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                const firstInput = modal.querySelector('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 150);
        } else {
            console.error('Modal not found:', modalId);
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function toggleLoading(button, isLoading) {
        if (!button) return;

        if (isLoading) {
            button.disabled = true;
            const originalText = button.innerHTML;
            button.setAttribute('data-original-text', originalText);
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Ishlatilmoqda...';
        } else {
            button.disabled = false;
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.innerHTML = originalText;
            }
        }
    }

    function validateAmount(amount, max = Infinity) {
        if (isNaN(amount) || amount <= 0) {
            showAlert('Summa to\'g\'ri kiritilmagan', 'error');
            return false;
        }
        if (amount > max) {
            showAlert(`Summa ${formatNumber(max)} dan oshmasligi kerak`, 'error');
            return false;
        }
        return true;
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('uz-UZ').format(number);
    }

    function formatCurrency(amount) {
        return (amount / 1000000).toFixed(1) + 'M so\'m';
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('uz-UZ');
    }

    // Contract specific variables
    const contractId = {{ $contract->id }};
    const remainingAmount = {{ $remainingAmount }};
    const contractData = {
        id: {{ $contract->id }},
        contract_number: '{{ $contract->contract_number }}',
        total_amount: {{ $contract->total_amount }},
        initial_payment_percent: {{ $contract->initial_payment_percent }},
        remaining_amount: {{ $remainingAmount }}
    };

    // Payment summary data for quarter details
    const paymentSummary = {!! json_encode($paymentSummary ?? []) !!};

    // Modal control functions with null checks
    function openContractEditModal() {
        openModal('contractEditModal');
    }

    function openAutoScheduleModal() {
        openModal('autoScheduleModal');
        // Delay the calculation to ensure modal is rendered
        setTimeout(() => {
            updateCalculation();
        }, 200);
    }

    function openManualScheduleModal() {
        showAlert('Qo\'lda grafik tuzish funksiyasi ishlab chiqilmoqda', 'info');
    }

    function openPaymentModal() {
        openModal('paymentModal');

        // Wait for modal to render before accessing form
        setTimeout(() => {
            const form = document.getElementById('paymentForm');
            const dateInput = document.querySelector('#paymentModal input[name="payment_date"]');

            if (form) {
                form.reset();
            }
            if (dateInput) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }
        }, 100);
    }

    // Quarter management functions
    function addQuarterPlan(year, quarter) {
        const yearInput = document.getElementById('quarterYear');
        const quarterInput = document.getElementById('quarterNumber');
        const titleElement = document.getElementById('quarterModalTitle');
        const amountInput = document.querySelector('#quarterPlanForm input[name="amount"]');

        if (yearInput) yearInput.value = year;
        if (quarterInput) quarterInput.value = quarter;
        if (titleElement) titleElement.textContent = `${quarter}-chorak ${year} yil`;
        if (amountInput) amountInput.value = '';

        openModal('quarterPlanModal');
    }

    function editQuarterPlan(year, quarter, amount) {
        addQuarterPlan(year, quarter);

        setTimeout(() => {
            const amountInput = document.querySelector('#quarterPlanForm input[name="amount"]');
            if (amountInput) {
                amountInput.value = amount;
            }
        }, 100);
    }

    function addQuarterPayment(year, quarter) {
        openPaymentModal();

        setTimeout(() => {
            const firstMonth = (quarter - 1) * 3 + 2;
            const date = new Date(year, firstMonth - 1, 15);
            const dateInput = document.querySelector('#paymentModal input[name="payment_date"]');

            if (dateInput) {
                dateInput.value = date.toISOString().split('T')[0];
            }
        }, 100);
    }

    function showQuarterPayments(year, quarter) {
        const titleElement = document.getElementById('quarterDetailsTitle');
        if (titleElement) {
            titleElement.textContent = `${quarter}-chorak ${year} yil`;
        }

        const quarterData = paymentSummary[year] && paymentSummary[year][quarter];

        if (quarterData && quarterData.fact_payments && quarterData.fact_payments.length > 0) {
            populateQuarterDetails(quarterData.fact_payments, quarterData.fact_total);
        } else {
            const listElement = document.getElementById('quarterDetailsList');
            const totalElement = document.getElementById('quarterDetailsTotal');

            if (listElement) {
                listElement.innerHTML = `
                    <tr>
                        <td colspan="5" class="border p-4 text-center text-gray-500">
                            Bu chorakda to'lovlar yo'q
                        </td>
                    </tr>
                `;
            }
            if (totalElement) {
                totalElement.textContent = '';
            }
        }

        openModal('quarterDetailsModal');
    }

    function populateQuarterDetails(payments, total) {
        const listElement = document.getElementById('quarterDetailsList');
        const totalElement = document.getElementById('quarterDetailsTotal');

        if (!listElement || !totalElement) return;

        let html = '';

        payments.forEach(payment => {
            html += `
                <tr>
                    <td class="border border-gray-200 p-3">${formatDate(payment.payment_date)}</td>
                    <td class="border border-gray-200 p-3 font-bold text-green-600">${formatCurrency(payment.amount)}</td>
                    <td class="border border-gray-200 p-3">${payment.payment_number || '-'}</td>
                    <td class="border border-gray-200 p-3 max-w-xs truncate" title="${payment.notes || ''}">${payment.notes || '-'}</td>
                    <td class="border border-gray-200 p-3">
                        <button onclick="editPayment(${payment.id})" class="text-blue-600 hover:text-blue-800 mr-2" title="Tahrirlaish">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deletePaymentConfirm(${payment.id})" class="text-red-600 hover:text-red-800" title="O'chirish">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        listElement.innerHTML = html;
        totalElement.textContent = `Jami: ${formatCurrency(total)} (${payments.length} ta to'lov)`;
    }

    // Auto-schedule calculation with null checks
    function updateCalculation() {
        const quarterSelect = document.querySelector('#autoScheduleModal select[name="total_quarters"]');
        const quarterAmountElement = document.getElementById('quarterAmount');

        if (!quarterSelect || !quarterAmountElement) {
            console.warn('Calculation elements not found');
            return;
        }

        const totalQuarters = parseInt(quarterSelect.value);
        const quarterAmount = remainingAmount / totalQuarters;

        quarterAmountElement.textContent = formatCurrency(quarterAmount);
    }

    // Payment menu toggle with null checks
    function togglePaymentMenu(paymentId) {
        const menu = document.getElementById(`paymentMenu${paymentId}`);
        if (!menu) return;

        // Close all other menus
        document.querySelectorAll('[id^="paymentMenu"]').forEach(m => {
            if (m.id !== `paymentMenu${paymentId}`) {
                m.classList.add('hidden');
            }
        });

        menu.classList.toggle('hidden');
    }

    // Payment actions
    function editPayment(id) {
        showAlert('To\'lovni tahrirlaish funksiyasi ishlab chiqilmoqda', 'info');
        const menu = document.getElementById(`paymentMenu${id}`);
        if (menu) {
            menu.classList.add('hidden');
        }
    }

    function deletePaymentConfirm(id) {
        if (confirm('Bu to\'lovni o\'chirishga ishonchingiz komilmi?')) {
            deletePayment(id);
        }
        const menu = document.getElementById(`paymentMenu${id}`);
        if (menu) {
            menu.classList.add('hidden');
        }
    }

    async function deletePayment(id) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            showAlert('CSRF token topilmadi', 'error');
            return;
        }

        showLoading('To\'lov o\'chirilmoqda...');

        try {
            const response = await fetch(`/contracts/fact-payment/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(result.message || 'Xatolik yuz berdi', 'error');
            }
        } catch (error) {
            showAlert('Serverga ulanishda xatolik: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    // Quick action functions
    function calculatePlan() {
        openAutoScheduleModal();
    }

    async function generateReport() {
        showLoading('Hisobot tayyorlanmoqda...');

        try {
            const response = await fetch(`/contracts/${contractId}/payment-report`);

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `payment_report_${contractId}_${new Date().toISOString().split('T')[0]}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                showAlert('Hisobot muvaffaqiyatli yuklandi', 'success');
            } else {
                showAlert('Hisobot olishda xatolik: ' + response.status, 'error');
            }
        } catch (error) {
            showAlert('Hisobot olishda xatolik: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    // Initialize forms and event listeners when DOM is ready
    function initializeForms() {
        // Contract Edit Form
        const contractEditForm = document.getElementById('contractEditForm');
        if (contractEditForm) {
            contractEditForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitButton = this.querySelector('button[type="submit"]');
                const csrfToken = document.querySelector('meta[name="csrf-token"]');

                if (!csrfToken) {
                    showAlert('CSRF token topilmadi', 'error');
                    return;
                }

                toggleLoading(submitButton, true);

                try {
                    const formData = new FormData(this);
                    const response = await fetch(`/contracts/${contractId}/update-info`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken.content
                        },
                        body: formData
                    });

                    if (response.ok) {
                        const result = await response.json();
                        showAlert(result.message || 'Muvaffaqiyatli yangilandi', 'success');
                        closeModal('contractEditModal');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('Server xatolik: ' + response.status, 'error');
                    }
                } catch (error) {
                    showAlert('Xatolik yuz berdi: ' + error.message, 'error');
                } finally {
                    toggleLoading(submitButton, false);
                }
            });
        }

        // Auto Schedule Form
        const autoScheduleForm = document.getElementById('autoScheduleForm');
        if (autoScheduleForm) {
            autoScheduleForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitButton = this.querySelector('button[type="submit"]');
                const csrfToken = document.querySelector('meta[name="csrf-token"]');

                if (!csrfToken) {
                    showAlert('CSRF token topilmadi', 'error');
                    return;
                }

                toggleLoading(submitButton, true);

                try {
                    const formData = new FormData(this);
                    const response = await fetch(`/contracts/${contractId}/generate-auto-schedule`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken.content
                        },
                        body: formData
                    });

                    if (response.ok) {
                        const result = await response.json();
                        showAlert(result.message || 'Grafik muvaffaqiyatli tuzildi', 'success');
                        closeModal('autoScheduleModal');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('Server xatolik: ' + response.status, 'error');
                    }
                } catch (error) {
                    showAlert('Xatolik yuz berdi: ' + error.message, 'error');
                } finally {
                    toggleLoading(submitButton, false);
                }
            });
        }

        // Payment Form
        const paymentForm = document.getElementById('paymentForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitButton = this.querySelector('button[type="submit"]');
                const csrfToken = document.querySelector('meta[name="csrf-token"]');

                if (!csrfToken) {
                    showAlert('CSRF token topilmadi', 'error');
                    return;
                }

                const amount = parseFloat(this.amount.value);
                if (!validateAmount(amount, contractData.total_amount)) {
                    return;
                }

                toggleLoading(submitButton, true);

                try {
                    const formData = new FormData(this);
                    const response = await fetch(`/contracts/${contractId}/store-fact-payment`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken.content
                        },
                        body: formData
                    });

                    if (response.ok) {
                        const result = await response.json();
                        showAlert(result.message || 'To\'lov muvaffaqiyatli qo\'shildi', 'success');
                        closeModal('paymentModal');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('Server xatolik: ' + response.status, 'error');
                    }
                } catch (error) {
                    showAlert('Xatolik yuz berdi: ' + error.message, 'error');
                } finally {
                    toggleLoading(submitButton, false);
                }
            });
        }

        // Quarter Plan Form
        const quarterPlanForm = document.getElementById('quarterPlanForm');
        if (quarterPlanForm) {
            quarterPlanForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitButton = this.querySelector('button[type="submit"]');
                const csrfToken = document.querySelector('meta[name="csrf-token"]');

                if (!csrfToken) {
                    showAlert('CSRF token topilmadi', 'error');
                    return;
                }

                const amount = parseFloat(this.amount.value);
                if (!validateAmount(amount)) {
                    return;
                }

                toggleLoading(submitButton, true);

                try {
                    const formData = new FormData(this);
                    const response = await fetch(`/contracts/${contractId}/store-plan-payment`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken.content
                        },
                        body: formData
                    });

                    if (response.ok) {
                        const result = await response.json();
                        showAlert(result.message || 'Plan muvaffaqiyatli saqlandi', 'success');
                        closeModal('quarterPlanModal');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert('Server xatolik: ' + response.status, 'error');
                    }
                } catch (error) {
                    showAlert('Xatolik yuz berdi: ' + error.message, 'error');
                } finally {
                    toggleLoading(submitButton, false);
                }
            });
        }
    }

    // Initialize everything when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Payment update page loaded');

        // Initialize forms
        initializeForms();

        // Set default date
        setTimeout(() => {
            const today = new Date().toISOString().split('T')[0];
            const paymentDateInputs = document.querySelectorAll('input[name="payment_date"]');
            paymentDateInputs.forEach(input => {
                if (input) input.value = today;
            });
        }, 100);

        // Initialize quarter select change handler
        setTimeout(() => {
            const quarterSelect = document.querySelector('#autoScheduleModal select[name="total_quarters"]');
            if (quarterSelect) {
                quarterSelect.addEventListener('change', updateCalculation);
            }
        }, 200);

        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal[style*="flex"]').forEach(modal => {
                    modal.style.display = 'none';
                });
                document.body.style.overflow = 'auto';
            }
        });

        // Close modals on background click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Close payment menus when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[id^="paymentMenu"]') && !e.target.closest('button[onclick*="togglePaymentMenu"]')) {
                document.querySelectorAll('[id^="paymentMenu"]').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }
        });

        // Show welcome message
        setTimeout(() => {
            showAlert('To\'lov boshqaruvi sistemasi tayyor', 'success');
        }, 1000);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key.toLowerCase()) {
                case 'n':
                    e.preventDefault();
                    openPaymentModal();
                    break;
                case 's':
                    e.preventDefault();
                    openAutoScheduleModal();
                    break;
                case 'e':
                    e.preventDefault();
                    openContractEditModal();
                    break;
                case 'r':
                    e.preventDefault();
                    location.reload();
                    break;
            }
        }
    });
</script>
@endpush
