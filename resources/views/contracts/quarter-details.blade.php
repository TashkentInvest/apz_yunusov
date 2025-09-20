@extends('layouts.app')

@section('title', $quarter . '-chorak ' . $year . ' tafsilotlari - ' . $contract->contract_number)
@section('page-title', $quarter . '-chorak ' . $year . ' tafsilotlari')

@php
    // Get quarter data from paymentData
    $quarterData = $paymentData['quarterly_breakdown'][$year]['quarters'][$quarter] ?? null;

    function formatMoney($amount) {
        return number_format($amount, 0, '.', ' ') . ' so\'m';
    }

    function formatDate($date) {
        return $date ? \Carbon\Carbon::parse($date)->format('d.m.Y') : '';
    }
@endphp

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.payment_update', $contract) }}"
       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
        Shartnomaga qaytish
    </a>

    <a href="{{ route('contracts.add-quarter-payment', [$contract, $year, $quarter]) }}"
       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="plus" class="w-4 h-4 mr-2"></i>
        To'lov qo'shish
    </a>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-8">

    @include('partials.flash-messages')

    @if(!$quarterData)
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
        <i data-feather="alert-triangle" class="w-12 h-12 text-red-400 mx-auto mb-3"></i>
        <h3 class="text-lg font-medium text-red-800 mb-2">Ma'lumot topilmadi</h3>
        <p class="text-red-600 mb-4">{{ $quarter }}-chorak {{ $year }} uchun ma'lumot mavjud emas.</p>
        <a href="{{ route('contracts.payment_update', $contract) }}"
           class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
            Ortga qaytish
        </a>
    </div>
    @else

    <!-- Quarter Header -->
    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-blue-900">{{ $quarter }}-chorak {{ $year }}</h1>
                <p class="text-blue-700 mt-1">{{ $contract->contract_number }}</p>
            </div>
            <div class="text-right">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $quarterData['status_class'] === 'completed' ? 'bg-green-100 text-green-800' :
                       ($quarterData['status_class'] === 'partial' ? 'bg-yellow-100 text-yellow-800' :
                        ($quarterData['status_class'] === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                    {{ $quarterData['status'] }}
                </div>
                <div class="text-sm text-blue-600 mt-1">
                    {{ $quarterData['quarter_info']['start_date'] }} - {{ $quarterData['quarter_info']['end_date'] }}
                </div>
            </div>
        </div>
    </div>

    <!-- Quarter Summary -->
    <div class="bg-white rounded-2xl shadow-lg border">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="bar-chart-2" class="w-6 h-6 mr-3 text-blue-600"></i>
                Chorak xulosasi
            </h2>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Plan Amount -->
                <div class="bg-blue-50 rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="target" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div class="text-sm font-medium text-blue-800 uppercase tracking-wide mb-1">PLAN</div>
                    <div class="text-2xl font-bold text-blue-900">{{ $quarterData['plan_amount_formatted'] }}</div>
                </div>

                <!-- Paid Amount -->
                <div class="bg-green-50 rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <div class="text-sm font-medium text-green-800 uppercase tracking-wide mb-1">TO'LANGAN</div>
                    <div class="text-2xl font-bold text-green-900">{{ $quarterData['fact_total_formatted'] }}</div>
                </div>

                <!-- Debt Amount -->
                @php
                    $debtClass = $quarterData['debt'] > 0 ? ($quarterData['is_overdue'] ? 'red' : 'yellow') : 'gray';
                @endphp
                <div class="bg-{{ $debtClass }}-50 rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-{{ $debtClass }}-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="{{ $quarterData['debt'] > 0 ? 'alert-triangle' : 'check' }}" class="w-6 h-6 text-{{ $debtClass }}-600"></i>
                    </div>
                    <div class="text-sm font-medium text-{{ $debtClass }}-800 uppercase tracking-wide mb-1">
                        {{ $quarterData['debt'] > 0 ? ($quarterData['is_overdue'] ? 'MUDDATI O\'TGAN' : 'QARZ') : 'QARZ YO\'Q' }}
                    </div>
                    <div class="text-2xl font-bold text-{{ $debtClass }}-900">
                        {{ $quarterData['debt'] > 0 ? $quarterData['debt_formatted'] : '0 so\'m' }}
                    </div>
                </div>

                <!-- Progress -->
                <div class="bg-indigo-50 rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="pie-chart" class="w-6 h-6 text-indigo-600"></i>
                    </div>
                    <div class="text-sm font-medium text-indigo-800 uppercase tracking-wide mb-1">BAJARILISH</div>
                    <div class="text-2xl font-bold text-indigo-900">{{ $quarterData['payment_percent'] }}%</div>
                    <div class="w-full bg-indigo-200 rounded-full h-2 mt-2">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: {{ min(100, $quarterData['payment_percent']) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Amendment Info (if applicable) -->
    @if($quarterData['is_amendment_based'])
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <div class="flex items-center">
            <i data-feather="file-plus" class="w-5 h-5 text-purple-600 mr-2"></i>
            <div>
                <h4 class="font-medium text-purple-900">Qo'shimcha kelishuv asosida</h4>
                <p class="text-sm text-purple-700">
                    Bu chorak {{ $quarterData['amendment_info']['number'] }} raqamli qo'shimcha kelishuv ({{ $quarterData['amendment_info']['date'] }}) asosida yaratilgan
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Payments List -->
    <div class="bg-white rounded-2xl shadow-lg border">
        <div class="border-b border-gray-200 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="credit-card" class="w-6 h-6 mr-3 text-green-600"></i>
                To'lovlar ro'yxati
                <span class="ml-2 text-lg text-gray-500">({{ count($quarterData['payments']) }})</span>
            </h2>
            <a href="{{ route('contracts.add-quarter-payment', [$contract, $year, $quarter]) }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                To'lov qo'shish
            </a>
        </div>

        <div class="p-8">
            @forelse($quarterData['payments'] as $payment)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-4 last:mb-0">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900">
                            {{ $payment['amount_formatted'] }}
                        </h4>
                        <p class="text-sm text-gray-600">
                            {{ formatDate($payment['date']) }}
                            @if($payment['payment_number'])
                                • Hujjat: {{ $payment['payment_number'] }}
                            @endif
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="viewPaymentDetails({{ $payment['id'] }})"
                                class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors"
                                title="Ko'rish">
                            <i data-feather="eye" class="w-4 h-4"></i>
                        </button>
                        <button class="edit-payment-btn p-2 bg-yellow-100 text-yellow-600 rounded-lg hover:bg-yellow-200 transition-colors"
                                title="Tahrirlash"
                                data-payment-id="{{ $payment['id'] }}"
                                data-payment-amount="{{ $payment['amount'] }}"
                                data-payment-formatted="{{ $payment['amount_formatted'] }}"
                                data-payment-date="{{ formatDate($payment['date']) }}"
                                data-payment-date-iso="{{ \Carbon\Carbon::parse($payment['date'])->format('Y-m-d') }}"
                                data-payment-number="{{ $payment['payment_number'] ?? '' }}"
                                data-payment-notes="{{ $payment['notes'] ?? '' }}"
                                data-quarter-info="{{ $quarter }}-chorak {{ $year }}">
                            <i data-feather="edit-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                @if($payment['notes'])
                <div class="bg-white border border-gray-200 rounded p-3">
                    <div class="text-sm text-gray-600 font-medium mb-1">Izoh:</div>
                    <div class="text-gray-700">{{ $payment['notes'] }}</div>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-12">
                <i data-feather="credit-card" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">To'lovlar mavjud emas</h3>
                <p class="text-gray-600 mb-6">Bu chorak uchun hech qanday to'lov qayd etilmagan</p>
                <a href="{{ route('contracts.add-quarter-payment', [$contract, $year, $quarter]) }}"
                   class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-feather="plus" class="w-5 h-5 mr-2"></i>
                    Birinchi to'lovni qo'shish
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Quarter Navigation -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Boshqa choraklar</h3>
                <p class="text-sm text-gray-600">{{ $contract->contract_number }} shartnomasi choraklari</p>
            </div>
            <div class="flex space-x-2">
                @if(isset($paymentData['quarterly_breakdown'][$year]))
                    @foreach($paymentData['quarterly_breakdown'][$year]['quarters'] as $q => $data)
                        <a href="{{ route('contracts.quarter-details', [$contract, $year, $q]) }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium transition-colors
                               {{ $q == $quarter ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Q{{ $q }}
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Contract Summary -->
    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
        <h3 class="text-lg font-bold text-blue-900 mb-4">Umumiy shartnoma holati</h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-sm text-blue-700">Jami plan</div>
                <div class="font-bold text-blue-900">{{ $paymentData['summary_cards']['total_plan_formatted'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-blue-700">To'langan</div>
                <div class="font-bold text-green-700">{{ $paymentData['summary_cards']['total_paid_formatted'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-blue-700">Qarz</div>
                <div class="font-bold text-yellow-700">{{ $paymentData['summary_cards']['current_debt_formatted'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-blue-700">Bajarilish</div>
                <div class="font-bold text-indigo-700">{{ $paymentData['summary_cards']['completion_percent'] }}%</div>
            </div>
        </div>
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
                @method('POST')
                <input type="hidden" id="editPaymentId" name="payment_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov sanasi *</label>
                    <input type="date" id="editPaymentDate" name="payment_date" required
                           min="{{ $contract->contract_date->format('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov summasi (so'm) *</label>
                    <input type="number" id="editPaymentAmount" name="payment_amount" step="0.01" min="0.01" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 text-lg font-medium">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hujjat raqami</label>
                        <input type="text" id="editPaymentNumber" name="payment_number" maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chorak</label>
                        <input type="text" id="editQuarterInfo" readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                    <textarea id="editPaymentNotes" name="payment_notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Current vs New Comparison -->
                <div id="editComparisonSection" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hidden">
                    <h4 class="font-medium text-yellow-900 mb-2">O'zgarishlar</h4>
                    <div class="text-sm text-yellow-800 space-y-1">
                        <div class="flex justify-between">
                            <span>Eski summa:</span>
                            <span id="oldAmount" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Yangi summa:</span>
                            <span id="newAmount" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between border-t border-yellow-300 pt-1">
                            <span>Farq:</span>
                            <span id="amountDifference" class="font-bold"></span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideEditPaymentModal()"
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Bekor qilish
                    </button>
                    <button type="button" onclick="deleteCurrentPayment()"
                           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        O'chirish
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

<!-- Payment Details Modal -->
<div id="paymentDetailsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">To'lov tafsilotlari</h3>
                <button onclick="hidePaymentDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div id="paymentDetailsContent">
                <!-- Payment details will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/feather-icons"></script>
<script>
// ========== GLOBAL VARIABLES ==========
let currentPaymentData = null;

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Attach edit button handlers
    attachEditButtonHandlers();

    // Setup modal close handlers
    setupModalHandlers();

    // Setup edit form handler
    setupEditFormHandler();
});

// ========== EDIT BUTTON HANDLERS ==========
function attachEditButtonHandlers() {
    document.querySelectorAll('.edit-payment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const paymentData = {
                id: this.dataset.paymentId,
                amount: this.dataset.paymentAmount,
                amount_formatted: this.dataset.paymentFormatted,
                payment_date: this.dataset.paymentDate,
                payment_date_iso: this.dataset.paymentDateIso,
                payment_number: this.dataset.paymentNumber || '',
                notes: this.dataset.paymentNotes || '',
                quarter_info: this.dataset.quarterInfo
            };
            showEditPaymentModal(paymentData);
        });
    });
}

// ========== MODAL FUNCTIONS ==========
function showEditPaymentModal(paymentData) {
    currentPaymentData = paymentData;

    // Populate form fields
    document.getElementById('editPaymentId').value = paymentData.id;
    document.getElementById('editPaymentDate').value = paymentData.payment_date_iso;
    document.getElementById('editPaymentAmount').value = paymentData.amount;
    document.getElementById('editPaymentNumber').value = paymentData.payment_number || '';
    document.getElementById('editPaymentNotes').value = paymentData.notes || '';
    document.getElementById('editQuarterInfo').value = paymentData.quarter_info;

    // Show original amount for comparison
    document.getElementById('oldAmount').textContent = paymentData.amount_formatted;

    // Show modal
    document.getElementById('editPaymentModal').classList.remove('hidden');

    // Setup amount change listener
    const amountInput = document.getElementById('editPaymentAmount');
    amountInput.removeEventListener('input', updateAmountComparison); // Remove previous listener
    amountInput.addEventListener('input', updateAmountComparison);

    // Re-initialize feather icons
    feather.replace();
}

function hideEditPaymentModal() {
    document.getElementById('editPaymentModal').classList.add('hidden');
    document.getElementById('editPaymentForm').reset();
    document.getElementById('editComparisonSection').classList.add('hidden');
    currentPaymentData = null;
}

function showPaymentDetailsModal() {
    document.getElementById('paymentDetailsModal').classList.remove('hidden');
}

function hidePaymentDetailsModal() {
    document.getElementById('paymentDetailsModal').classList.add('hidden');
}

// ========== COMPARISON FUNCTIONS ==========
function updateAmountComparison() {
    if (!currentPaymentData) return;

    const newAmount = parseFloat(document.getElementById('editPaymentAmount').value) || 0;
    const oldAmount = parseFloat(currentPaymentData.amount);
    const difference = newAmount - oldAmount;

    const comparisonSection = document.getElementById('editComparisonSection');
    const newAmountElement = document.getElementById('newAmount');
    const differenceElement = document.getElementById('amountDifference');

    if (Math.abs(difference) > 0.01) {
        comparisonSection.classList.remove('hidden');
        newAmountElement.textContent = formatCurrency(newAmount);

        if (difference > 0) {
            differenceElement.textContent = `+${formatCurrency(difference)}`;
            differenceElement.className = 'font-bold text-green-600';
        } else {
            differenceElement.textContent = formatCurrency(difference);
            differenceElement.className = 'font-bold text-red-600';
        }
    } else {
        comparisonSection.classList.add('hidden');
    }
}

// ========== FORM HANDLING ==========
function setupEditFormHandler() {
    const form = document.getElementById('editPaymentForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitEditPayment();
    });
}

function submitEditPayment() {
    if (!currentPaymentData) return;

    const form = document.getElementById('editPaymentForm');
    const formData = new FormData(form);
    const paymentId = formData.get('payment_id');

    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Saqlanmoqda...';

    fetch(`/contracts/payments/${paymentId}/update`, {
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
            showErrorMessage(data.message || 'To\'lov yangilashda xatolik yuz berdi');
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('To\'lov yangilashda xatolik yuz berdi');
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// ========== DELETE FUNCTIONS ==========
function deleteCurrentPayment() {
    if (!currentPaymentData) return;

    const confirmMessage = `Bu to'lovni o'chirishni tasdiqlaysizmi?\n\n` +
                          `Summa: ${currentPaymentData.amount_formatted}\n` +
                          `Sana: ${currentPaymentData.payment_date}`;

    if (!confirm(confirmMessage)) {
        return;
    }

    fetch(`/contracts/payments/${currentPaymentData.id}/delete`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideEditPaymentModal();
            showSuccessMessage(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showErrorMessage(data.message || 'To\'lovni o\'chirishda xatolik yuz berdi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('To\'lovni o\'chirishda xatolik yuz berdi');
    });
}

// ========== PAYMENT DETAILS ==========
function viewPaymentDetails(paymentId) {
    fetch(`/contracts/payments/${paymentId}/details`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const payment = data.payment;
            document.getElementById('paymentDetailsContent').innerHTML = `
                <div class="space-y-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-900">${payment.amount_formatted}</div>
                            <div class="text-blue-700">${payment.payment_date}</div>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Chorak:</span>
                            <span class="font-medium">${payment.quarter_info}</span>
                        </div>
                        ${payment.payment_number ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600">Hujjat raqami:</span>
                            <span class="font-medium">${payment.payment_number}</span>
                        </div>
                        ` : ''}
                        <div class="flex justify-between">
                            <span class="text-gray-600">Yaratilgan:</span>
                            <span class="font-medium">${payment.created_at}</span>
                        </div>
                        ${payment.updated_at !== payment.created_at ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600">O'zgartirilgan:</span>
                            <span class="font-medium">${payment.updated_at}</span>
                        </div>
                        ` : ''}
                        ${payment.notes ? `
                        <div class="pt-2 border-t">
                            <div class="text-gray-600 mb-1">Izoh:</div>
                            <div class="text-gray-900">${payment.notes}</div>
                        </div>
                        ` : ''}
                    </div>

                    ${payment.can_edit ? `
                    <div class="pt-4 border-t flex space-x-2">
                        <button onclick="hidePaymentDetailsModal(); showEditPaymentModal({
                            id: '${payment.id}',
                            amount: '${payment.amount}',
                            amount_formatted: '${payment.amount_formatted}',
                            payment_date: '${payment.payment_date}',
                            payment_date_iso: '${payment.payment_date_iso}',
                            payment_number: '${payment.payment_number || ''}',
                            notes: '${payment.notes || ''}',
                            quarter_info: '${payment.quarter_info}'
                        })" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Tahrirlash
                        </button>
                        <button onclick="confirmDeleteFromDetails(${payment.id})"
                               class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            O'chirish
                        </button>
                    </div>
                    ` : `
                    <div class="pt-4 border-t text-center text-sm text-gray-500">
                        Bu to'lovni tahrirlash mumkin emas (30 kundan ortiq)
                    </div>
                    `}
                </div>
            `;

            showPaymentDetailsModal();
            feather.replace();
        } else {
            showErrorMessage('To\'lov ma\'lumotlari yuklanmadi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('To\'lov ma\'lumotlarini yuklashda xatolik');
    });
}

function confirmDeleteFromDetails(paymentId) {
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
                hidePaymentDetailsModal();
                showSuccessMessage(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorMessage(data.message || 'To\'lovni o\'chirishda xatolik yuz berdi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('To\'lovni o\'chirishda xatolik yuz berdi');
        });
    }
}

// ========== MODAL SETUP ==========
function setupModalHandlers() {
    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.id === 'editPaymentModal') {
            hideEditPaymentModal();
        }
        if (e.target.id === 'paymentDetailsModal') {
            hidePaymentDetailsModal();
        }
    });

    // Handle escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideEditPaymentModal();
            hidePaymentDetailsModal();
        }
    });
}

// ========== UTILITY FUNCTIONS ==========
function formatCurrency(amount) {
    return new Intl.NumberFormat('uz-UZ', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' so\'m';
}

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
    setTimeout(() => alert.classList.add('animate-pulse'), 100);

    // Remove after delay
    setTimeout(() => {
        alert.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => alert.remove(), 300);
    }, 3000);
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
</script>
@endpush
