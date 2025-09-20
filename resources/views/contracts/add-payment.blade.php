@extends('layouts.app')

@section('title', 'To\'lov qo\'shish - ' . $contract->contract_number)
@section('page-title', 'To\'lov qo\'shish')

@section('header-actions')
<a href="{{ route('contracts.payment_update', $contract) }}"
   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
    <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
    Ortga qaytish
</a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-8">
    <!-- Contract Summary -->
    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
        <h3 class="text-lg font-bold text-blue-900 mb-4">Shartnoma ma'lumotlari</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-blue-700">Shartnoma raqami</div>
                <div class="font-bold text-blue-900">{{ $contract->contract_number }}</div>
            </div>
            <div>
                <div class="text-sm text-blue-700">Shartnoma sanasi</div>
                <div class="font-bold text-blue-900">{{ $contract->contract_date->format('d.m.Y') }}</div>
            </div>
        </div>

        @if(isset($year) && isset($quarter))
        <div class="mt-4 p-3 bg-green-100 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <i data-feather="target" class="w-4 h-4 text-green-600 mr-2"></i>
                <span class="text-sm font-medium text-green-800">
                    Bu to'lov {{ $quarter }}-chorak {{ $year }} yil uchun qo'shiladi
                </span>
            </div>
        </div>
        @endif
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

    <!-- Payment Form -->
    <div class="bg-white rounded-2xl shadow-lg border">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="credit-card" class="w-6 h-6 mr-3 text-green-600"></i>
                Yangi to'lov ma'lumotlari
            </h2>
        </div>

        <form method="POST" action="{{ route('contracts.store-payment', $contract) }}" class="p-8 space-y-6">
            @csrf

            @if(isset($year) && isset($quarter))
                <input type="hidden" name="target_year" value="{{ $year }}">
                <input type="hidden" name="target_quarter" value="{{ $quarter }}">
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To'lov sanasi *</label>
                <input type="date" name="payment_date" required
                       value="{{ old('payment_date', $suggestedDate ?? date('Y-m-d')) }}"
                       min="{{ $contract->contract_date->format('Y-m-d') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('payment_date') border-red-300 @enderror">
                @error('payment_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    Eng erta sana: {{ $contract->contract_date->format('d.m.Y') }} (Shartnoma sanasi)
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To'lov summasi (so'm) *</label>
                <input type="number" name="payment_amount" step="0.01" min="0.01" required
                       value="{{ old('payment_amount') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-lg font-medium @error('payment_amount') border-red-300 @enderror"
                       placeholder="0.00">
                @error('payment_amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hujjat raqami</label>
                <input type="text" name="payment_number" maxlength="50"
                       value="{{ old('payment_number') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('payment_number') border-red-300 @enderror"
                       placeholder="Chek, spravka raqami">
                @error('payment_number')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                <textarea name="payment_notes" rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('payment_notes') border-red-300 @enderror"
                          placeholder="Qo'shimcha ma'lumot, izohlar">{{ old('payment_notes') }}</textarea>
                @error('payment_notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Quarter Information (if specified) -->
            @if(isset($year) && isset($quarter))
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="font-medium text-gray-900 mb-3">Chorak ma'lumotlari</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Chorak:</span>
                        <span class="font-medium text-gray-900">{{ $quarter }}-chorak {{ $year }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Muddat:</span>
                        <span class="font-medium text-gray-900">
                            {{ \Carbon\Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->format('M') }} -
                            {{ \Carbon\Carbon::create($year, $quarter * 3, 1)->format('M Y') }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Current Quarter Summary -->
            @if(isset($paymentData['summary_cards']))
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h4 class="font-medium text-blue-900 mb-3">Umumiy holat</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-blue-700">Jami plan:</span>
                        <span class="font-bold text-blue-900">{{ $paymentData['summary_cards']['total_plan_formatted'] }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">To'langan:</span>
                        <span class="font-bold text-green-700">{{ $paymentData['summary_cards']['total_paid_formatted'] }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">Joriy qarz:</span>
                        <span class="font-bold text-yellow-700">{{ $paymentData['summary_cards']['current_debt_formatted'] }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">Muddati o'tgan:</span>
                        <span class="font-bold text-red-700">{{ $paymentData['summary_cards']['overdue_debt_formatted'] }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('contracts.payment_update', $contract) }}"
                   class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Bekor qilish
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                    To'lovni qo'shish
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Payments (if any) -->
    @if(isset($paymentData['payment_history']) && count($paymentData['payment_history']['payments']) > 0)
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="border-b border-gray-200 p-4">
            <h3 class="text-lg font-medium text-gray-900">So'nggi to'lovlar</h3>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                @foreach(array_slice($paymentData['payment_history']['payments'], 0, 5) as $payment)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <div class="font-medium text-gray-900">{{ $payment['amount_formatted'] }}</div>
                        <div class="text-sm text-gray-600">
                            {{ $payment['payment_date'] }} • {{ $payment['quarter_info'] }}
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $payment['created_at_human'] }}
                    </div>
                </div>
                @endforeach
            </div>

            @if(count($paymentData['payment_history']['payments']) > 5)
            <div class="mt-4 text-center">
                <a href="{{ route('contracts.payment_update', $contract) }}"
                   class="text-sm text-blue-600 hover:text-blue-800">
                    Barcha to'lovlarni ko'rish ({{ $paymentData['payment_history']['total_count'] }} ta)
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/feather-icons"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Payment amount formatting
    const amountInput = document.querySelector('input[name="payment_amount"]');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            // Remove any non-numeric characters except decimal point
            let value = this.value.replace(/[^\d.]/g, '');

            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            this.value = value;
        });

        amountInput.addEventListener('blur', function() {
            if (this.value && !isNaN(this.value)) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    }

    // Date validation
    const dateInput = document.querySelector('input[name="payment_date"]');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const contractDate = new Date('{{ $contract->contract_date->format('Y-m-d') }}');

            if (selectedDate < contractDate) {
                this.setCustomValidity('To\'lov sanasi shartnoma sanasidan oldin bo\'lishi mumkin emas');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Form submission validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const amount = parseFloat(document.querySelector('input[name="payment_amount"]').value);

            if (!amount || amount <= 0) {
                e.preventDefault();
                alert('To\'lov summasini to\'g\'ri kiriting');
                return false;
            }

            // Disable submit button to prevent double submission
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i data-feather="loader" class="w-4 h-4 mr-2 animate-spin"></i>To\'lov qo\'shilmoqda...';
            }
        });
    }
});
</script>
@endpush
