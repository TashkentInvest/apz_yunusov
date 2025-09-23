@extends('layouts.app')

@section('title', 'Shartnoma to\'lov boshqaruvi - ' . ($paymentData['contract']['contract_number'] ?? 'Yangi shartnoma'))

@section('header-actions')
<div class="flex flex-wrap gap-3">
    @if(isset($paymentData['contract']))
        <a href="{{ route('contracts.show', $paymentData['contract']['id']) }}" class="btn btn-secondary">
            <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
            Ortga qaytish
        </a>
        <a href="{{ route('contracts.export-report', $paymentData['contract']['id']) }}" class="btn btn-success">
            <i data-feather="download" class="w-4 h-4 mr-2"></i>
            Hisobot yuklab olish
        </a>
    @endif
</div>
@endsection

@section('content')
<style>
.govt-header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); }
.govt-card { border-left: 5px solid #1e40af; }
.success-gradient { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
.warning-gradient { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
.danger-gradient { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); }
.info-gradient { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
.purple-gradient { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }
.initial-gradient { background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); }
.btn { @apply inline-flex items-center px-4 py-2 rounded-lg font-medium transition-colors; }
.btn-primary { @apply bg-blue-600 text-white hover:bg-blue-700; }
.btn-secondary { @apply bg-gray-600 text-white hover:bg-gray-700; }
.btn-success { @apply bg-green-600 text-white hover:bg-green-700; }
.btn-warning { @apply bg-yellow-600 text-white hover:bg-yellow-700; }
.btn-danger { @apply bg-red-600 text-white hover:bg-red-700; }
.btn-purple { @apply bg-purple-600 text-white hover:bg-purple-700; }
.quarter-item { @apply bg-white border-2 border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer; }
.quarter-item.completed { @apply border-green-400 bg-green-50; }
.quarter-item.partial { @apply border-yellow-400 bg-yellow-50; }
.quarter-item.overdue { @apply border-red-400 bg-red-50; }
.initial-payment-card { @apply bg-white border-2 border-purple-300 rounded-xl p-6 hover:shadow-lg transition-all; }
.initial-payment-card.completed { @apply border-purple-500 bg-purple-50; }
.initial-payment-card.partial { @apply border-purple-400 bg-purple-25; }
.initial-payment-card.pending { @apply border-purple-300 bg-white; }
.status-badge { @apply px-3 py-1 rounded-full text-xs font-semibold uppercase; }
.status-completed { @apply bg-green-100 text-green-800; }
.status-partial { @apply bg-yellow-100 text-yellow-800; }
.status-overdue { @apply bg-red-100 text-red-800; }
.status-pending { @apply bg-gray-100 text-gray-800; }
.payment-card { @apply bg-white border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer; }
.payment-card:hover { transform: translateY(-2px); }
</style>

<div class="space-y-8">
    <!-- Government Header -->
    <div class="govt-header rounded-2xl shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">TIC</h1>
                <p class="text-xl opacity-90">Shartnoma to'lov boshqaruv tizimi</p>
            </div>
<!-- Complete Working Status Selector -->
@if(isset($paymentData['contract']) && isset($statuses))
<div class="flex-1 px-8">
    <form id="status-update-form"
          method="POST"
          action="{{ route('contracts.update-status', ['contract' => $contract->id]) }}"
          class="space-y-2">
        @csrf
        @method('PATCH')

        <label for="status_id" class="block text-sm font-medium opacity-90">
            Shartnoma holati
        </label>
        <div class="flex items-center space-x-3">
            <select id="status_id"
                    name="status_id"
                    class="flex-1 rounded-lg border-white/20 bg-white/10 text-white focus:ring-2 focus:ring-white/50 px-4 py-2"
                    required>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}"
                            data-color="{{ $status->color }}"
                            {{ $contract->status_id == $status->id ? 'selected' : '' }}
                            style="color: #000; background: #fff;">
                        {{ $status->name_uz }}
                    </option>
                @endforeach
            </select>

            <button type="submit"
                    id="save-status-btn"
                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Saqlash
            </button>
        </div>
    </form>
</div>


<script>
(function() {
    'use strict';

    const form = document.getElementById('status-update-form');
    const select = document.getElementById('status_id');
    const saveBtn = document.getElementById('save-status-btn');

    if (!form || !select || !saveBtn) {
        console.error('Status form elements not found');
        return;
    }

    // Store original value
    const originalValue = '{{ $contract->status_id }}';
    console.log('Original status ID:', originalValue);

    // Initially hide save button if no change
    if (select.value === originalValue) {
        saveBtn.classList.add('hidden');
    }

    // Handle select change
    select.addEventListener('change', function() {
        console.log('Status changed from', originalValue, 'to', this.value);

        if (this.value !== originalValue) {
            saveBtn.classList.remove('hidden');
            saveBtn.classList.add('animate-pulse');
        } else {
            saveBtn.classList.add('hidden');
            saveBtn.classList.remove('animate-pulse');
        }
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const selectedOption = select.options[select.selectedIndex];
        const statusName = selectedOption.text;

        console.log('Submitting status change to:', statusName);

        if (confirm('Shartnoma holatini "' + statusName + '" ga o\'zgartirishni tasdiqlaysizmi?')) {
            // Disable button and show loading
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saqlanmoqda...';

            // Submit form
            console.log('Form action:', this.action);
            this.submit();
        }
    });
})();
</script>
@endif
            <div class="text-right">
                <p class="text-lg font-semibold">{{ date('d.m.Y') }}</p>
                <p class="opacity-90">{{ date('H:i') }}</p>
            </div>
        </div>
    </div>

    @include('partials.flash-messages')

    <!-- Contract Form -->
    <div class="bg-white rounded-2xl shadow-lg border govt-card">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="file-text" class="w-6 h-6 mr-3 text-blue-600"></i>
                Shartnoma ma'lumotlari
                @if(isset($paymentData['contract']) && $paymentData['contract']['has_amendments'])
                    <span class="ml-4 bg-purple-100 text-purple-800 text-sm px-3 py-1 rounded-full">
                        {{ $paymentData['contract']['amendment_count'] }} ta qo'shimcha kelishuv
                    </span>
                @endif
            </h2>
        </div>

        <form method="POST" action="{{ isset($paymentData['contract']) ? route('contracts.update', $paymentData['contract']['id']) : route('contracts.store') }}" class="p-8 space-y-8">
            @csrf
            @if(isset($paymentData['contract']))
                @method('PUT')
                <input type="hidden" name="from_payment_update" value="1">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Shartnoma raqami *</label>
                    <input type="text" name="contract_number" required
                           value="{{ old('contract_number', $paymentData['contract']['contract_number'] ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('contract_number') border-red-300 @enderror">
                    @error('contract_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Shartnoma sanasi *</label>
                    <input type="date" name="contract_date" required
                           value="{{ old('contract_date', $paymentData['contract']['contract_date'] ?? date('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('contract_date') border-red-300 @enderror">
                    @error('contract_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Yakunlash sanasi</label>
                    <input type="date" name="completion_date"
                           value="{{ old('completion_date', $paymentData['contract']['completion_date'] ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('completion_date') border-red-300 @enderror">
                    @error('completion_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
                <h3 class="text-xl font-bold text-blue-900 mb-6">Moliyaviy ma'lumotlar</h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Jami shartnoma summasi (so'm) *</label>
                        <input type="number" name="total_amount" required step="0.01" min="1"
                               value="{{ old('total_amount', $paymentData['contract']['total_amount'] ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-bold @error('total_amount') border-red-300 @enderror">
                        @error('total_amount')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">To'lov turi *</label>
                        <select name="payment_type" required onchange="togglePaymentType(this)"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('payment_type') border-red-300 @enderror">
                            <option value="">To'lov turini tanlang</option>
                            <option value="installment" {{ old('payment_type', $paymentData['contract']['payment_type'] ?? '') === 'installment' ? 'selected' : '' }}>Bo'lib to'lash</option>
                            <option value="full" {{ old('payment_type', $paymentData['contract']['payment_type'] ?? '') === 'full' ? 'selected' : '' }}>To'liq to'lash</option>
                        </select>
                        @error('payment_type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div id="installmentSettings" class="space-y-6 mt-6" style="{{ old('payment_type', $paymentData['contract']['payment_type'] ?? '') === 'full' ? 'display: none;' : '' }}">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Boshlang'ich to'lov (%)</label>
                            <input type="number" name="initial_payment_percent" min="0" max="100" step="1"
                                   value="{{ old('initial_payment_percent', $paymentData['contract']['initial_payment_percent'] ?? 20) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Qurulish muddati (yil)</label>
                            <input type="number" name="construction_period_years" min="1" max="10" step="1"
                                   value="{{ old('construction_period_years', $paymentData['contract']['construction_period_years'] ?? 2) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Choraklar soni</label>
                            <input type="number" name="quarters_count" min="1" max="20" step="1"
                                   value="{{ old('quarters_count', $paymentData['contract']['quarters_count'] ?? 8) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    @if(isset($paymentData['contract']))
                    <div class="bg-white rounded-xl p-6 border-2 border-blue-200">
                        <h4 class="text-lg font-bold text-blue-900 mb-4">To'lov hisob-kitobi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div class="success-gradient rounded-lg p-4">
                                <p class="text-sm font-medium text-green-800">Boshlang'ich to'lov</p>
                                <p class="text-2xl font-bold text-green-900">{{ $paymentData['contract']['initial_payment_formatted'] }}</p>
                            </div>
                            <div class="info-gradient rounded-lg p-4">
                                <p class="text-sm font-medium text-blue-800">Qolgan summa</p>
                                <p class="text-2xl font-bold text-blue-900">{{ $paymentData['contract']['remaining_amount_formatted'] }}</p>
                            </div>
                            <div class="warning-gradient rounded-lg p-4">
                                <p class="text-sm font-medium text-indigo-800">Chorak to'lovi</p>
                                <p class="text-2xl font-bold text-indigo-900">{{ $paymentData['contract']['quarterly_amount_formatted'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

         <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
        <button type="button" onclick="clearFinancialFields()" class="btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Tozalash1
            </button>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ isset($paymentData['contract']) ? 'Yangilash' : 'Saqlash' }}
            </button>
        </div>
        </form>
    </div>

<script>
function clearFinancialFields() {
    // Check if we're editing an existing contract
    const isEditMode = document.querySelector('input[name="_method"][value="PUT"]') !== null;

    if (isEditMode) {
        alert('Tahrirlash rejimida moliyaviy ma\'lumotlarni tozalab bo\'lmaydi. O\'zgartirishlar kiriting va saqlang.');
        return; // Don't clear anything
    }

    // Only clear if creating new contract
    document.querySelector('input[name="total_amount"]').value = '';
    document.querySelector('select[name="payment_type"]').selectedIndex = 0;
    document.querySelector('input[name="initial_payment_percent"]').value = '';
    document.querySelector('input[name="construction_period_years"]').value = '';
    document.querySelector('input[name="quarters_count"]').value = '';
    document.getElementById('installmentSettings').style.display = 'none';
}

function showInfoMessage(message) {
    const existingAlert = document.getElementById('clear-info-alert');
    if (existingAlert) existingAlert.remove();

    const alert = document.createElement('div');
    alert.id = 'clear-info-alert';
    alert.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
    alert.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(alert);

    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 4000);
}

function togglePaymentType(select) {
    const installmentDiv = document.getElementById('installmentSettings');
    if (select.value === 'full') {
        installmentDiv.style.display = 'none';
    } else {
        installmentDiv.style.display = 'block';
    }
}

// Re-add required attributes when user starts typing
document.addEventListener('DOMContentLoaded', function() {
    const totalAmount = document.querySelector('input[name="total_amount"]');
    const paymentType = document.querySelector('select[name="payment_type"]');

    if (totalAmount) {
        totalAmount.addEventListener('input', function() {
            if (this.value) {
                this.setAttribute('required', 'required');
            }
        });
    }

    if (paymentType) {
        paymentType.addEventListener('change', function() {
            if (this.value) {
                this.setAttribute('required', 'required');
            }
        });
    }
});
</script>

    @if(isset($paymentData['contract']))
    <!-- Payment Management -->
    <div class="bg-white rounded-2xl shadow-lg border govt-card">
        <div class="border-b border-gray-200 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="calendar" class="w-6 h-6 mr-3 text-blue-600"></i>
                To'lov boshqaruvi
            </h2>
         <div class="flex space-x-3">
                @if($paymentData['contract']['payment_type'] === 'installment')
                    <a href="{{ route('contracts.create-schedule', $paymentData['contract']['id']) }}" class="btn btn-primary">
                        <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                        Jadval tuzish
                    </a>
                    <button onclick="showAddPaymentModal('initial')" class="btn btn-purple">
                        <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                        Boshlang'ich to'lov
                    </button>
                    <button onclick="showAddPaymentModal('quarterly')" class="btn btn-success">
                        <i data-feather="calendar" class="w-4 h-4 mr-2"></i>
                        Chorak to'lovi
                    </button>
                @else
                    <button onclick="showAddPaymentModal('full')" class="btn btn-success">
                        <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                        To'lov qo'shish
                    </button>
                @endif
                <a href="{{ route('contracts.amendments.create', $paymentData['contract']['id']) }}" class="btn btn-warning">
                    <i data-feather="file-plus" class="w-4 h-4 mr-2"></i>
                    Qo'shimcha kelishuv
                </a>
            </div>
        </div>

        <div class="p-8">
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="info-gradient rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i data-feather="target" class="w-6 h-6 text-blue-600"></i>
        </div>
        <p class="text-sm font-medium text-blue-800">JAMI PLAN</p>
        <p class="text-2xl font-bold text-blue-900">{{ $paymentData['summary_cards']['total_plan_formatted'] }}</p>
        <div class="mt-2 text-xs text-blue-600">
            Foiz: {{ $paymentData['summary_cards']['completion_percent'] }}%
        </div>
    </div>

    <div class="success-gradient rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i data-feather="dollar-sign" class="w-6 h-6 text-green-600"></i>
        </div>
        <p class="text-sm font-medium text-green-800">TO'LANDI</p>
        <p class="text-2xl font-bold text-green-900">{{ $paymentData['summary_cards']['total_paid_formatted'] }}</p>
        <div class="mt-2 text-xs text-green-600">
            Jami to'lovlar
        </div>
    </div>

    @if($paymentData['contract']['payment_type'] === 'installment')
    <!-- Show these cards only for installment -->
    <div class="initial-gradient rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i data-feather="star" class="w-6 h-6 text-purple-600"></i>
        </div>
        <p class="text-sm font-medium text-purple-800">BOSHLANG'ICH</p>
        <p class="text-2xl font-bold text-purple-900">{{ $paymentData['summary_cards']['initial_payment_paid_formatted'] }}</p>
        <div class="mt-2 text-xs text-purple-600">
            Plan: {{ $paymentData['summary_cards']['initial_payment_plan_formatted'] }}
        </div>
    </div>

    <div class="success-gradient rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
        </div>
        <p class="text-sm font-medium text-green-800">CHORAK TO'LOVLARI</p>
        <p class="text-2xl font-bold text-green-900">{{ $paymentData['summary_cards']['quarterly_paid_formatted'] }}</p>
        <div class="mt-2 text-xs text-green-600">
            Plan: {{ $paymentData['summary_cards']['quarterly_plan_formatted'] }}
        </div>
    </div>
    @endif

    <div class="warning-gradient rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i data-feather="clock" class="w-6 h-6 text-yellow-600"></i>
        </div>
        <p class="text-sm font-medium text-yellow-800">
            @if($paymentData['contract']['payment_type'] === 'full')
                QOLGAN
            @else
                JORIY QARZ
            @endif
        </p>
        <p class="text-2xl font-bold text-yellow-900">{{ $paymentData['summary_cards']['current_debt_formatted'] }}</p>
        <div class="mt-2 text-xs text-yellow-600">
            @if($paymentData['contract']['payment_type'] === 'full')
                To'lanmagan summa
            @else
                Kelajakdagi to'lovlar
            @endif
        </div>
    </div>

    @if($paymentData['contract']['payment_type'] === 'installment')
    <div class="danger-gradient rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
        </div>
        <p class="text-sm font-medium text-red-800">MUDDATI O'TGAN</p>
        <p class="text-2xl font-bold text-red-900">{{ $paymentData['summary_cards']['overdue_debt_formatted'] }}</p>
        <div class="mt-2 text-xs text-red-600">Tezda to'lash kerak</div>
    </div>
    @endif
</div>

<!-- Edit Payment Modal -->
<div id="editPaymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">To'lovni tahrirlash</h3>
                <button onclick="hideEditPaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="editPaymentForm" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="editPaymentId" name="payment_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov sanasi *</label>
                    <input type="date" id="editPaymentDate" name="payment_date" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov summasi (so'm) *</label>
                    <input type="number" id="editPaymentAmount" name="payment_amount" step="0.01" min="0.01" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 text-lg font-medium">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hujjat raqami</label>
                    <input type="text" id="editPaymentNumber" name="payment_number" maxlength="50"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                    <textarea id="editPaymentNotes" name="payment_notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideEditPaymentModal()"
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Bekor qilish
                    </button>
                    <button type="submit"
                           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Saqlash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

            <!-- Initial Payment Section -->
<!-- Initial Payment Section - Only show for installment payments -->
@if($paymentData['contract']['payment_type'] === 'installment' && isset($paymentData['initial_payments']) && !is_null($paymentData['initial_payments']))
<div class="mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-900 flex items-center">
            <i data-feather="star" class="w-5 h-5 mr-2 text-purple-600"></i>
            Boshlang'ich to'lov
        </h3>
        <button onclick="showAddPaymentModal('initial')" class="btn btn-purple">
            <i data-feather="plus" class="w-4 h-4 mr-2"></i>
            To'lov qo'shish
        </button>
    </div>

    <div class="initial-payment-card {{ $paymentData['initial_payments']['status_class'] }}">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-lg font-semibold text-purple-900">Boshlang'ich to'lov holati</h4>
            <span class="status-badge status-{{ $paymentData['initial_payments']['status_class'] }}">
                {{ $paymentData['initial_payments']['status'] }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="text-center">
                <div class="text-sm text-purple-700">Plan</div>
                <div class="text-xl font-bold text-purple-900">{{ $paymentData['initial_payments']['plan_amount_formatted'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-purple-700">To'langan</div>
                <div class="text-xl font-bold text-green-700">{{ $paymentData['initial_payments']['total_paid_formatted'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-purple-700">Qolgan</div>
                <div class="text-xl font-bold text-red-700">{{ $paymentData['initial_payments']['remaining_formatted'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-purple-700">Foiz</div>
                <div class="text-xl font-bold text-purple-900">{{ $paymentData['initial_payments']['payment_percent'] }}%</div>
            </div>
        </div>

        <div class="w-full bg-purple-200 rounded-full h-3 mb-4">
            <div class="bg-purple-600 h-3 rounded-full transition-all"
                 style="width: {{ min(100, $paymentData['initial_payments']['payment_percent']) }}%"></div>
        </div>

        @if(count($paymentData['initial_payments']['payments']) > 0)
        <div class="mt-4">
            <h5 class="text-sm font-medium text-purple-800 mb-2">
                To'lovlar ({{ $paymentData['initial_payments']['payments_count'] }} ta):
            </h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($paymentData['initial_payments']['payments'] as $payment)
                <div class="bg-white border border-purple-200 rounded-lg p-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-medium text-purple-900">{{ $payment['amount_formatted'] }}</div>
                            <div class="text-sm text-purple-600">{{ $payment['date'] }}</div>
                            @if($payment['payment_number'])
                            <div class="text-xs text-purple-500">{{ $payment['payment_number'] }}</div>
                            @endif
                        </div>
                        <div class="flex space-x-1">
                            <button onclick="editPayment({{ $payment['id'] }})"
                                   class="p-1 bg-purple-100 text-purple-600 rounded hover:bg-purple-200"
                                   title="Tahrirlash">
                                <i data-feather="edit-2" class="w-3 h-3"></i>
                            </button>
                            <button onclick="deletePayment({{ $payment['id'] }})"
                                   class="p-1 bg-red-100 text-red-600 rounded hover:bg-red-200"
                                   title="O'chirish">
                                <i data-feather="trash-2" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endif
            <!-- Quarterly Breakdown -->
            @forelse($paymentData['quarterly_breakdown'] as $year => $yearData)
            <div class="mb-8 bg-gray-50 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">{{ $year }} yil</h3>
                    <div class="flex space-x-6 text-sm">
                        <div class="text-center">
                            <div class="text-gray-600">Plan</div>
                            <div class="font-bold text-blue-600">{{ $yearData['totals']['plan_formatted'] }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-600">To'langan</div>
                            <div class="font-bold text-green-600">{{ $yearData['totals']['paid_formatted'] }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-600">Foiz</div>
                            <div class="font-bold">{{ $yearData['totals']['percent'] }}%</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($yearData['quarters'] as $quarter => $quarterData)
                    <div class="quarter-item {{ $quarterData['status_class'] }}" onclick="openQuarterDetails({{ $year }}, {{ $quarter }})">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold">{{ $quarter }}-chorak</h4>
                            <div class="flex items-center space-x-2">
                                <span class="status-badge status-{{ $quarterData['status_class'] }}">
                                    {{ $quarterData['status'] }}
                                </span>
                                @if($quarterData['is_amendment_based'])
                                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded" title="Qo'shimcha kelishuv asosida">QK</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Plan:</span>
                                <span class="font-semibold">{{ $quarterData['plan_amount_formatted'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">To'langan:</span>
                                <span class="font-semibold text-green-600">{{ $quarterData['fact_total_formatted'] }}</span>
                            </div>
                            @if($quarterData['debt'] > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Qarz:</span>
                                <span class="font-semibold {{ $quarterData['is_overdue'] ? 'text-red-600' : 'text-yellow-600' }}">
                                    {{ $quarterData['debt_formatted'] }}
                                </span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Foiz:</span>
                                <span class="font-bold">{{ $quarterData['payment_percent'] }}%</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all {{ $quarterData['progress_color'] }}"
                                     style="width: {{ min(100, $quarterData['payment_percent']) }}%"></div>
                            </div>

                            <div class="mt-3 flex justify-between items-center">
                                <div class="text-xs text-gray-500">
                                    @if(count($quarterData['payments']) > 0)
                                        {{ count($quarterData['payments']) }} ta to'lov
                                    @else
                                        To'lov yo'q
                                    @endif
                                </div>
                                <div class="flex space-x-1">
                                    <button onclick="addQuarterPayment({{ $year }}, {{ $quarter }})"
                                           class="p-1 bg-green-100 text-green-600 rounded hover:bg-green-200"
                                           title="To'lov qo'shish">
                                        <i data-feather="plus" class="w-4 h-4"></i>
                                    </button>
                                    @if(count($quarterData['payments']) > 0)
                                    <button onclick="showQuarterPayments({{ $year }}, {{ $quarter }})"
                                           class="p-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200"
                                           title="To'lovlarni ko'rish">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <i data-feather="calendar" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">To'lov jadvali mavjud emas</h3>
                <p class="text-gray-600 mb-6">Choraklar bo'yicha to'lov jadvalini yaratish kerak</p>
                <a href="{{ route('contracts.create-schedule', $paymentData['contract']['id']) }}" class="btn btn-primary">
                    <i data-feather="calendar" class="w-5 h-5 mr-2"></i>
                    Jadval tuzish
                </a>
            </div>
            @endforelse

            <!-- Payment History -->
            @if(count($paymentData['payment_history']['payments']) > 0)
            <div class="mt-8 pt-8 border-t border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">To'lovlar tarixi</h3>
                    <div class="text-sm text-gray-500">
                        Jami: {{ $paymentData['payment_history']['total_count'] }} ta •
                        Boshlang'ich: {{ $paymentData['payment_history']['initial_payments_count'] }} ta •
                        Chorak: {{ $paymentData['payment_history']['quarterly_payments_count'] }} ta
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach(array_slice($paymentData['payment_history']['payments'], 0, 9) as $payment)
                    <div class="payment-card {{ $payment['is_initial_payment'] ? 'border-purple-200' : 'border-gray-200' }}"
                         onclick="showPaymentDetails({{ $payment['id'] }})">
                        <div class="flex justify-between items-start mb-2">
                            <div class="font-medium text-lg">{{ $payment['amount_formatted'] }}</div>
                            <div class="text-xs {{ $payment['is_initial_payment'] ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-600' }} px-2 py-1 rounded">
                                {{ $payment['quarter_info'] }}
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">
                            <i data-feather="calendar" class="w-4 h-4 inline mr-1"></i>
                            {{ $payment['payment_date'] }}
                        </div>
                        @if($payment['payment_number'])
                        <div class="text-sm text-gray-500 mb-2">
                            <i data-feather="hash" class="w-4 h-4 inline mr-1"></i>
                            {{ $payment['payment_number'] }}
                        </div>
                        @endif
                        @if($payment['notes'])
                        <div class="text-xs text-gray-400 truncate">
                            {{ $payment['notes'] }}
                        </div>
                        @endif
                        <div class="mt-2 flex justify-between items-center text-xs text-gray-500">
                            <span>{{ $payment['created_at_human'] }}</span>
                            @if(in_array('edit', $payment['actions']))
                            <div class="flex space-x-1">
                                <button onclick="editPayment({{ $payment['id'] }})" class="text-blue-600 hover:text-blue-800">
                                    <i data-feather="edit-2" class="w-3 h-3"></i>
                                </button>
                                <button onclick="deletePayment({{ $payment['id'] }})" class="text-red-600 hover:text-red-800">
                                    <i data-feather="trash-2" class="w-3 h-3"></i>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(count($paymentData['payment_history']['payments']) > 9)
                <div class="mt-4 text-center">
                    <button onclick="showAllPayments()" class="text-blue-600 hover:text-blue-800">
                        Barcha to'lovlarni ko'rish ({{ $paymentData['payment_history']['total_count'] }})
                    </button>
                </div>
                @endif
            </div>
            @endif

            <!-- Amendments Section -->
            @if(count($paymentData['amendments']) > 0)
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Qo'shimcha kelishuvlar</h3>
                <div class="space-y-4">
                    @foreach($paymentData['amendments'] as $amendment)
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-purple-900">
                                    Kelishuv #{{ $amendment['amendment_number'] }}
                                </h4>
                                <p class="text-sm text-purple-700 mt-1">
                                    {{ $amendment['amendment_date'] }} • {{ $amendment['reason'] }}
                                </p>
                                @if($amendment['new_total_amount'])
                                <p class="text-sm text-gray-600 mt-2">
                                    Yangi summa: {{ $amendment['new_total_amount_formatted'] }}
                                </p>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="status-badge status-{{ $amendment['status_class'] }}">
                                    {{ $amendment['status_text'] }}
                                </span>
                                <a href="{{ route('contracts.amendments.show', [$paymentData['contract']['id'], $amendment['id']]) }}"
                                      class="p-1 bg-purple-100 text-purple-600 rounded hover:bg-purple-200">
                                    <i data-feather="eye" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>


            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Add Payment Modal -->
<div id="addPaymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Yangi to'lov qo'shish</h3>
                <button onclick="hideAddPaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="addPaymentForm" class="space-y-4">
                @csrf
                <input type="hidden" id="paymentCategory" name="payment_category" value="quarterly">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov sanasi *</label>
                    <input type="date" id="modalPaymentDate" name="payment_date" required
                           min="{{ $paymentData['contract']['contract_date'] ?? date('Y-m-d') }}"
                           value="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov summasi (so'm) *</label>
                    <input type="number" id="modalPaymentAmount" name="payment_amount" step="0.01" min="0.01" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 text-lg font-medium">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hujjat raqami</label>
                        <input type="text" id="modalPaymentNumber" name="payment_number" maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chorak (avtomatik)</label>
                        <input type="text" id="modalQuarterInfo" readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                    <textarea id="modalPaymentNotes" name="payment_notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideAddPaymentModal()"
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Bekor qilish
                    </button>
                    <button type="submit"
                           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        To'lovni qo'shish
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Modal date change handler
    const modalDateInput = document.getElementById('modalPaymentDate');
    if (modalDateInput) {
        modalDateInput.addEventListener('change', function() {
            updateQuarterInfo(this.value);
        });
        // Initialize quarter info
        updateQuarterInfo(modalDateInput.value);
    }

    // Add payment form submission
    const addPaymentForm = document.getElementById('addPaymentForm');
    if (addPaymentForm) {
        addPaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAddPayment();
        });
    }
});

function togglePaymentType(select) {
    const installmentDiv = document.getElementById('installmentSettings');
    if (select.value === 'full') {
        installmentDiv.style.display = 'none';
    } else {
        installmentDiv.style.display = 'block';
    }
}

function showAddPaymentModal(type = 'quarterly', targetYear = null, targetQuarter = null) {
    const modal = document.getElementById('addPaymentModal');
    const modalTitle = document.getElementById('modalTitle');
    const paymentCategory = document.getElementById('paymentCategory');

    modal.classList.remove('hidden');

    if (type === 'initial') {
        modalTitle.textContent = 'Boshlang\'ich to\'lov qo\'shish';
        paymentCategory.value = 'initial';
        document.getElementById('modalQuarterInfo').value = 'Boshlang\'ich to\'lov';
    } else if (type === 'full') {
        modalTitle.textContent = 'To\'lov qo\'shish';
        paymentCategory.value = 'full';
        document.getElementById('modalQuarterInfo').value = 'To\'liq to\'lov';
    } else {
        modalTitle.textContent = 'Chorak to\'lovi qo\'shish';
        paymentCategory.value = 'quarterly';

        if (targetYear && targetQuarter) {
            const middleMonth = (targetQuarter - 1) * 3 + 2;
            const suggestedDate = `${targetYear}-${String(middleMonth).padStart(2, '0')}-15`;
            document.getElementById('modalPaymentDate').value = suggestedDate;
            updateQuarterInfo(suggestedDate);
        }
    }

    feather.replace();
}
function hideAddPaymentModal() {
    document.getElementById('addPaymentModal').classList.add('hidden');
    document.getElementById('addPaymentForm').reset();
    document.getElementById('paymentCategory').value = 'quarterly';
}

function addQuarterPayment(year, quarter) {
    showAddPaymentModal('quarterly', year, quarter);
}

function updateQuarterInfo(dateStr) {
    const paymentCategory = document.getElementById('paymentCategory').value;

    if (paymentCategory === 'initial') {
        document.getElementById('modalQuarterInfo').value = 'Boshlang\'ich to\'lov';
        return;
    }

    if (!dateStr) return;

    const date = new Date(dateStr);
    const year = date.getFullYear();
    const quarter = Math.ceil((date.getMonth() + 1) / 3);

    document.getElementById('modalQuarterInfo').value = `${quarter}-chorak ${year}`;
}

function submitAddPayment() {
    const form = document.getElementById('addPaymentForm');
    const formData = new FormData(form);

    // Add contract ID
    const contractId = {{ $paymentData['contract']['id'] ?? 0 }};

    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Saqlanmoqda...';

    fetch(`/contracts/${contractId}/store-payment`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddPaymentModal();
            showSuccessMessage(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showErrorMessage(data.message || 'Noma\'lum xatolik');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('To\'lov qo\'shishda xatolik yuz berdi');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function openQuarterDetails(year, quarter) {
    const contractId = {{ $paymentData['contract']['id'] ?? 0 }};
    window.location.href = `/contracts/${contractId}/quarter-details/${year}/${quarter}`;
}

function showQuarterPayments(year, quarter) {
    // Show quarter payments in modal or navigate to detailed page
    openQuarterDetails(year, quarter);
}

function showPaymentDetails(paymentId) {
    // Show payment details modal
    fetch(`/contracts/payments/${paymentId}/details`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display payment details in a modal or popup
            alert(`To'lov tafsilotlari:\nSumma: ${data.payment.amount_formatted}\nSana: ${data.payment.payment_date}\nTuri: ${data.payment.quarter_info}`);
        } else {
            showErrorMessage('To\'lov ma\'lumotlari topilmadi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('To\'lov ma\'lumotlarini yuklashda xatolik');
    });
}

function editPayment(paymentId) {
    // Open edit payment modal
    console.log('Edit payment:', paymentId);
    // TODO: Implement edit payment modal
}

function deletePayment(paymentId) {
    if (confirm('Bu to\'lovni o\'chirishni tasdiqlaysizmi?')) {
        fetch(`/contracts/payments/${paymentId}/delete`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorMessage(data.message || 'To\'lovni o\'chirishda xatolik');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('To\'lovni o\'chirishda xatolik yuz berdi');
        });
    }
}

function showAllPayments() {
    // Show all payments modal or page
    console.log('Show all payments');
    // TODO: Implement show all payments functionality
}

// Utility functions
function showSuccessMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-[60] transform transition-all duration-300 max-w-md';
    alert.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-sm">${message}</span>
        </div>
    `;

    document.body.appendChild(alert);

    // Animate in
    setTimeout(() => alert.classList.remove('translate-x-full', 'opacity-0'), 100);

    // Remove after delay
    setTimeout(() => {
        alert.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => alert.remove(), 300);
    }, 4000);
}

function showErrorMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-[60] transform transition-all duration-300 max-w-md';
    alert.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <span class="text-sm">${message}</span>
        </div>
    `;

    document.body.appendChild(alert);

    // Remove after delay
    setTimeout(() => {
        alert.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

// Edit payment function
function editPayment(paymentId) {
    // Fetch payment details
    fetch(`/payments/${paymentId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payment = data.payment;

                // Populate edit form
                document.getElementById('editPaymentId').value = payment.id;
                document.getElementById('editPaymentDate').value = payment.payment_date_iso;
                document.getElementById('editPaymentAmount').value = payment.amount;
                document.getElementById('editPaymentNumber').value = payment.payment_number || '';
                document.getElementById('editPaymentNotes').value = payment.notes || '';

                // Show modal
                document.getElementById('editPaymentModal').classList.remove('hidden');
                feather.replace();
            } else {
                showErrorMessage(data.message || 'To\'lov ma\'lumotlarini yuklab bo\'lmadi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('To\'lov ma\'lumotlarini yuklashda xatolik');
        });
}

function hideEditPaymentModal() {
    document.getElementById('editPaymentModal').classList.add('hidden');
    document.getElementById('editPaymentForm').reset();
}

// Handle edit form submission
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editPaymentForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const paymentId = document.getElementById('editPaymentId').value;
            const formData = new FormData(this);

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Saqlanmoqda...';

            fetch(`/payments/${paymentId}/update`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideEditPaymentModal();
                    showSuccessMessage(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showErrorMessage(data.message || 'Yangilashda xatolik');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('To\'lovni yangilashda xatolik yuz berdi');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});

// Delete payment function (already exists but ensure it works)
function deletePayment(paymentId) {
    if (confirm('Bu to\'lovni o\'chirishni tasdiqlaysizmi?')) {
        fetch(`/payments/${paymentId}/delete`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorMessage(data.message || 'To\'lovni o\'chirishda xatolik');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('To\'lovni o\'chirishda xatolik yuz berdi');
        });
    }
}
</script>
@endsection
