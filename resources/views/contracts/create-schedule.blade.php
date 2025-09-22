@extends('layouts.app')

@section('title', 'To\'lov jadvali tuzish - ' . $contract->contract_number)
@section('page-title', 'To\'lov jadvali tuzish')

@php
    // Calculate values directly in Blade
    $totalAmount = (float) $contract->total_amount;
    $initialPaymentPercent = (float) ($contract->initial_payment_percent ?? 20);
    $initialPaymentAmount = $totalAmount * ($initialPaymentPercent / 100);
    $remainingAmount = $totalAmount - $initialPaymentAmount;
    $quartersCount = (int) ($contract->quarters_count ?? 8);
    $quarterlyAmount = $quartersCount > 0 ? $remainingAmount / $quartersCount : 0;

    // Contract date info
    $contractDate = \Carbon\Carbon::parse($contract->contract_date);
    $contractYear = $contractDate->year;
    $contractQuarter = ceil($contractDate->month / 3);

    // Generate quarters data
    $quartersData = [];
    $currentYear = $contractYear;
    $currentQuarter = $contractQuarter;

    for ($i = 1; $i <= $quartersCount; $i++) {
        $quartersData[] = [
            'index' => $i,
            'year' => $currentYear,
            'quarter' => $currentQuarter,
            'label' => $currentQuarter . '-chorak ' . $currentYear,
            'is_first' => $i === 1
        ];

        $currentQuarter++;
        if ($currentQuarter > 4) {
            $currentQuarter = 1;
            $currentYear++;
        }
    }

    // Format currency function
    function formatMoney($amount) {
        return number_format($amount, 0, '.', ' ') . ' so\'m';
    }
@endphp

@section('header-actions')
<a href="{{ route('contracts.payment_update', $contract) }}"
   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
    <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
    Ortga qaytish
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Contract Summary -->
    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
        <h3 class="text-lg font-bold text-blue-900 mb-4">Shartnoma ma'lumotlari</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-sm text-blue-700">Shartnoma raqami</div>
                <div class="font-bold text-blue-900">{{ $contract->contract_number }}</div>
            </div>
            <div>
                <div class="text-sm text-blue-700">Jami summa</div>
                <div class="font-bold text-blue-900">{{ formatMoney($totalAmount) }}</div>
            </div>
            <div>
                <div class="text-sm text-blue-700">Taqsimlanadigan summa</div>
                <div class="font-bold text-green-900">{{ formatMoney($remainingAmount) }}</div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="mt-4 pt-4 border-t border-blue-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-blue-700">Boshlang'ich to'lov:</span>
                    <span class="font-semibold">{{ formatMoney($initialPaymentAmount) }} ({{ $initialPaymentPercent }}%)</span>
                </div>
                <div>
                    <span class="text-blue-700">Chorak to'lovi:</span>
                    <span class="font-semibold">{{ formatMoney($quarterlyAmount) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <i data-feather="alert-triangle" class="w-5 h-5 text-red-400 mr-3"></i>
            <div>
                <h3 class="text-sm font-medium text-red-800">Xatoliklarni tuzating:</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    @if($remainingAmount <= 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <i data-feather="alert-circle" class="w-5 h-5 text-yellow-400 mr-3"></i>
            <div>
                <h3 class="text-sm font-medium text-yellow-800">Diqqat!</h3>
                <p class="mt-2 text-sm text-yellow-700">
                    Taqsimlanadigan summa 0 yoki manfiy. Shartnoma ma'lumotlarini tekshiring.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Schedule Creation Form -->
    <div class="bg-white rounded-2xl shadow-lg border">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900">To'lov jadvali parametrlari</h2>
        </div>

        @if($remainingAmount > 0)
        <form method="POST" action="{{ route('contracts.store-schedule', $contract) }}" class="p-8 space-y-8" novalidate>
            @csrf

            <!-- Schedule Type Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Jadval turi</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="schedule-type-label flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="schedule_type" value="auto" checked class="text-blue-600 mr-3" onchange="toggleScheduleType()">
                        <div>
                            <span class="font-medium">Avtomatik taqsimlash</span>
                            <p class="text-sm text-gray-600">Barcha choraklar uchun teng miqdorda</p>
                        </div>
                    </label>
                    <label class="schedule-type-label flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="schedule_type" value="custom" class="text-blue-600 mr-3" onchange="toggleScheduleType()">
                        <div>
                            <span class="font-medium">Qo'lda belgilash</span>
                            <p class="text-sm text-gray-600">Har bir chorak uchun alohida miqdor</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Parameters -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choraklar soni *</label>
                    <input type="number" name="quarters_count" min="1" max="20" step="1"
                           value="{{ $quartersCount }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           required onchange="updatePreview()">
                    <p class="text-xs text-gray-500 mt-1">
                        Shartnomada {{ $quartersCount }} ta chorak belgilangan
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Taqsimlanadigan summa (so'm) *</label>
                    <input type="number" name="total_schedule_amount" step="0.01" min="0.01"
                           value="{{ $remainingAmount }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-gray-50"
                           readonly>
                    <p class="text-xs text-green-600 mt-1 font-medium">
                        Boshlang'ich to'lovdan keyingi qolgan summa
                    </p>
                </div>
            </div>

            <!-- Custom Schedule Grid -->
           <div id="customScheduleGrid" class="hidden">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-medium text-gray-900">Choraklar bo'yicha taqsimlash</h4>

                    <!-- Toggle between Percent and Amount -->
                    <div class="flex items-center space-x-2 bg-gray-100 rounded-lg p-1">
                        <button type="button" id="percentModeBtn" onclick="switchInputMode('percent')"
                                class="px-3 py-1 text-sm rounded-md transition-colors bg-blue-600 text-white">
                            Foiz (%)
                        </button>
                        <button type="button" id="amountModeBtn" onclick="switchInputMode('amount')"
                                class="px-3 py-1 text-sm rounded-md transition-colors text-gray-600 hover:bg-white">
                            Summa (so'm)
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php $equalPercent = 100 / $quartersCount; @endphp
                    @foreach($quartersData as $quarterData)
                    <div class="bg-white border border-gray-300 rounded-lg p-3">
                        <div class="text-sm font-medium text-gray-700 mb-2">{{ $quarterData['label'] }}</div>

                        <!-- Percent Input -->
                        <div class="percent-input-wrapper">
                            <div class="flex items-center">
                                <input type="number"
                                    name="quarter_{{ $quarterData['index'] }}_percent"
                                    data-quarter="{{ $quarterData['index'] }}"
                                    value="{{ number_format($equalPercent, 2) }}"
                                    min="0" max="100" step="0.01"
                                    class="custom-percent-input w-full px-2 py-1 border border-gray-300 rounded text-center"
                                    onchange="onPercentChange(this)">
                                <span class="ml-1 text-sm text-gray-500">%</span>
                            </div>
                        </div>

                        <!-- Amount Input (hidden by default) -->
                        <div class="amount-input-wrapper hidden">
                            <div class="flex items-center">
                                <input type="number"
                                    name="quarter_{{ $quarterData['index'] }}_amount"
                                    data-quarter="{{ $quarterData['index'] }}"
                                    value="{{ number_format($remainingAmount / $quartersCount, 0, '', '') }}"
                                    min="0" step="0.01"
                                    class="custom-amount-input w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                    onchange="onAmountChange(this)">
                            </div>
                            <div class="text-xs text-gray-500 mt-1 quarter-percent-display">
                                {{ number_format($equalPercent, 1) }}%
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Summary -->
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-yellow-800">
                                <i data-feather="info" class="w-4 h-4 inline mr-1"></i>
                                <span id="validationMessage">Barcha foizlar yig'indisi 100% bo'lishi kerak</span>
                            </p>
                            <div id="percentageTotal" class="mt-1 font-medium text-yellow-900">
                                Jami: 100.00%
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-yellow-700">Jami summa:</div>
                            <div id="totalAmountDisplay" class="font-bold text-yellow-900">
                                {{ formatMoney($remainingAmount) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Preview -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Jadval ko'rinishi</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3" id="previewGrid">
                    @foreach($quartersData as $quarterData)
                    <div class="bg-white border-2 {{ $quarterData['is_first'] ? 'border-green-400 bg-green-50' : 'border-gray-200' }} rounded-lg p-3 text-center">
                        <div class="text-sm font-medium text-blue-600 mb-1">{{ $quarterData['label'] }}</div>
                        <div class="text-lg font-bold text-blue-900">{{ formatMoney($quarterlyAmount) }}</div>
                        <div class="text-xs text-gray-500">{{ number_format(100 / $quartersCount, 1) }}%</div>
                        @if($quarterData['is_first'])
                        <div class="text-xs text-green-600 font-bold mt-1">BOSHLANISH</div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 text-center">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-lg">
                        <span class="font-medium">Boshlanadi: {{ $contractDate->format('d.m.Y') }}</span>
                        <span class="ml-2 text-sm">({{ $contractQuarter }}-chorak {{ $contractYear }})</span>
                    </div>
                </div>
            </div>

            <!-- Validation Messages -->
            <div id="validationMessage" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <i data-feather="alert-triangle" class="w-5 h-5 text-red-500 mr-2"></i>
                    <div class="text-sm text-red-800">
                        <span id="validationText"></span>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <button type="button" onclick="resetForm()"
                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Tozalash
                </button>
                <button type="submit" id="submitBtn"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="calendar" class="w-4 h-4 mr-2"></i>
                    Jadvalni saqlash
                </button>
            </div>
        </form>
        @else
        <!-- Error state when remaining amount is 0 or negative -->
        <div class="p-8 text-center">
            <div class="text-gray-500 mb-4">
                <i data-feather="alert-triangle" class="w-16 h-16 mx-auto mb-2"></i>
                <p class="text-lg">Jadval yaratib bo'lmaydi</p>
                <p class="text-sm">Taqsimlanadigan summa nol yoki manfiy</p>
            </div>
            <a href="{{ route('contracts.edit', $contract) }}"
               class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                <i data-feather="edit" class="w-4 h-4 mr-2"></i>
                Shartnomani tahrirlash
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Hidden data for JS -->
<script type="application/json" id="contractData">
{
    "remainingAmount": {{ $remainingAmount }},
    "quartersCount": {{ $quartersCount }},
    "quartersData": @json($quartersData)
}
</script>
@endsection

@push('scripts')
<script src="https://unpkg.com/feather-icons"></script>


<script>
let currentInputMode = 'percent';
const remainingAmount = {{ $remainingAmount }};
const quartersCount = {{ $quartersCount }};

function switchInputMode(mode) {
    currentInputMode = mode;

    // Update button styles
    const percentBtn = document.getElementById('percentModeBtn');
    const amountBtn = document.getElementById('amountModeBtn');

    if (mode === 'percent') {
        percentBtn.className = 'px-3 py-1 text-sm rounded-md transition-colors bg-blue-600 text-white';
        amountBtn.className = 'px-3 py-1 text-sm rounded-md transition-colors text-gray-600 hover:bg-white';

        document.querySelectorAll('.percent-input-wrapper').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('.amount-input-wrapper').forEach(el => el.classList.add('hidden'));

        document.getElementById('validationMessage').textContent = 'Barcha foizlar yig\'indisi 100% bo\'lishi kerak';
    } else {
        amountBtn.className = 'px-3 py-1 text-sm rounded-md transition-colors bg-blue-600 text-white';
        percentBtn.className = 'px-3 py-1 text-sm rounded-md transition-colors text-gray-600 hover:bg-white';

        document.querySelectorAll('.percent-input-wrapper').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.amount-input-wrapper').forEach(el => el.classList.remove('hidden'));

        document.getElementById('validationMessage').textContent = 'Barcha summalar yig\'indisi plan summaga teng bo\'lishi kerak';
    }

    validateInputs();
}

function onPercentChange(input) {
    const quarter = input.dataset.quarter;
    const percent = parseFloat(input.value) || 0;
    const amount = (remainingAmount * percent) / 100;

    // Update corresponding amount input
    const amountInput = document.querySelector(`input[name="quarter_${quarter}_amount"]`);
    if (amountInput) {
        amountInput.value = Math.round(amount);
    }

    validateInputs();
    updatePreview();
}

function onAmountChange(input) {
    const quarter = input.dataset.quarter;
    const amount = parseFloat(input.value) || 0;
    const percent = (amount / remainingAmount) * 100;

    // Update corresponding percent input
    const percentInput = document.querySelector(`input[name="quarter_${quarter}_percent"]`);
    if (percentInput) {
        percentInput.value = percent.toFixed(2);
    }

    // Update percent display
    const percentDisplay = input.closest('.amount-input-wrapper').querySelector('.quarter-percent-display');
    if (percentDisplay) {
        percentDisplay.textContent = `${percent.toFixed(1)}%`;
    }

    validateInputs();
    updatePreview();
}

function validateInputs() {
    let totalPercent = 0;
    let totalAmount = 0;

    document.querySelectorAll('.custom-percent-input').forEach(input => {
        totalPercent += parseFloat(input.value) || 0;
    });

    document.querySelectorAll('.custom-amount-input').forEach(input => {
        totalAmount += parseFloat(input.value) || 0;
    });

    const percentElement = document.getElementById('percentageTotal');
    const amountElement = document.getElementById('totalAmountDisplay');

    const isPercentValid = Math.abs(totalPercent - 100) < 0.1;
    const isAmountValid = Math.abs(totalAmount - remainingAmount) < 1;

    // Update displays
    if (percentElement) {
        percentElement.textContent = `Jami: ${totalPercent.toFixed(2)}%`;
        percentElement.className = `mt-1 font-medium ${isPercentValid ? 'text-green-700' : 'text-red-700'}`;
    }

    if (amountElement) {
        amountElement.textContent = formatCurrency(totalAmount);
        amountElement.className = `font-bold ${isAmountValid ? 'text-green-900' : 'text-red-900'}`;
    }

    // Update submit button
    const isValid = currentInputMode === 'percent' ? isPercentValid : isAmountValid;
    updateSubmitButton(isValid, currentInputMode === 'percent' ? totalPercent : totalAmount);

    return isValid;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('uz-UZ', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' so\'m';
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Initialize
    toggleScheduleType();
});

const contractData = JSON.parse(document.getElementById('contractData').textContent);

function toggleScheduleType() {
    const isCustom = document.querySelector('input[name="schedule_type"]:checked').value === 'custom';
    const customGrid = document.getElementById('customScheduleGrid');
    const labels = document.querySelectorAll('.schedule-type-label');

    // Update visual styles
    labels.forEach(label => {
        const radio = label.querySelector('input[type="radio"]');
        if (radio.checked) {
            label.classList.add('border-blue-500', 'bg-blue-50');
        } else {
            label.classList.remove('border-blue-500', 'bg-blue-50');
        }
    });

    // Show/hide custom inputs
    if (isCustom) {
        customGrid.classList.remove('hidden');
        enableCustomInputs();
    } else {
        customGrid.classList.add('hidden');
        disableCustomInputs();
    }

    updatePreview();
}

function enableCustomInputs() {
    const inputs = document.querySelectorAll('.custom-percent-input');
    inputs.forEach(input => {
        input.removeAttribute('disabled');
        input.removeAttribute('tabindex');
    });
}

function disableCustomInputs() {
    const inputs = document.querySelectorAll('.custom-percent-input');
    inputs.forEach(input => {
        input.setAttribute('disabled', 'disabled');
        input.setAttribute('tabindex', '-1');
    });
}

function validateCustomPercentages() {
    const inputs = document.querySelectorAll('.custom-percent-input');
    let totalPercent = 0;

    inputs.forEach(input => {
        totalPercent += parseFloat(input.value) || 0;
    });

    const totalElement = document.getElementById('percentageTotal');
    const isValid = Math.abs(totalPercent - 100) < 0.1;

    if (totalElement) {
        totalElement.textContent = `Jami: ${totalPercent.toFixed(2)}%`;
        totalElement.className = `mt-2 font-medium ${isValid ? 'text-green-700' : 'text-red-700'}`;
    }

    // Update submit button and validation message
    updateSubmitButton(isValid, totalPercent);
    updatePreview();

    return isValid;
}

function updateSubmitButton(isValid, totalPercent) {
    const submitBtn = document.getElementById('submitBtn');
    const validationDiv = document.getElementById('validationMessage');
    const validationText = document.getElementById('validationText');

    if (document.querySelector('input[name="schedule_type"]:checked').value === 'custom' && !isValid) {
        submitBtn.disabled = true;
        submitBtn.className = 'px-6 py-3 bg-gray-400 text-gray-200 rounded-lg cursor-not-allowed';

        validationDiv.classList.remove('hidden');
        validationText.textContent = `Foizlar yig'indisi ${totalPercent.toFixed(2)}%. 100% bo'lishi kerak.`;
    } else {
        submitBtn.disabled = false;
        submitBtn.className = 'px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors';

        validationDiv.classList.add('hidden');
    }
}

function updatePreview() {
    const scheduleType = document.querySelector('input[name="schedule_type"]:checked').value;
    const quartersCount = parseInt(document.querySelector('input[name="quarters_count"]').value) || contractData.quartersCount;
    const remainingAmount = contractData.remainingAmount;

    let html = '';

    contractData.quartersData.forEach((quarterData, index) => {
        if (index >= quartersCount) return;

        let amount = 0;
        let percent = 0;

        if (scheduleType === 'auto') {
            percent = 100 / quartersCount;
            amount = remainingAmount / quartersCount;
        } else {
            const input = document.querySelector(`input[name="quarter_${quarterData.index}_percent"]`);
            percent = input ? parseFloat(input.value) || 0 : 0;
            amount = remainingAmount * (percent / 100);
        }

        html += `
            <div class="bg-white border-2 ${quarterData.is_first ? 'border-green-400 bg-green-50' : 'border-gray-200'} rounded-lg p-3 text-center">
                <div class="text-sm font-medium text-blue-600 mb-1">${quarterData.label}</div>
                <div class="text-lg font-bold text-blue-900">${formatCurrency(amount)}</div>
                <div class="text-xs text-gray-500">${percent.toFixed(1)}%</div>
                ${quarterData.is_first ? '<div class="text-xs text-green-600 font-bold mt-1">BOSHLANISH</div>' : ''}
            </div>
        `;
    });

    document.getElementById('previewGrid').innerHTML = html;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('uz-UZ', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' so\'m';
}

function resetForm() {
    const form = document.querySelector('form');
    if (form) {
        form.reset();
        document.querySelector('input[name="schedule_type"][value="auto"]').checked = true;
        toggleScheduleType();
    }
}

// Form submission validation
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.querySelector('input[name="schedule_type"]')) {
        const scheduleType = form.querySelector('input[name="schedule_type"]:checked').value;

        if (scheduleType === 'custom') {
            if (!validateCustomPercentages()) {
                e.preventDefault();
                return false;
            }
        }
    }
});
</script>
@endpush
