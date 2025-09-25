@extends('layouts.app')

@section('title', 'Qo\'shimcha kelishuvni tahrirlash')
@section('page-title', 'Qo\'shimcha kelishuvni tahrirlash')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- <form method="POST" action="{{ route('contracts.amendments.update', [$contract, $amendment]) }}">
        @csrf
        @method('PUT')

        <!-- Copy the form fields from create-amendment.blade.php -->
        <!-- Pre-fill with $amendment values -->

        <div class="space-y-6">
            <!-- Amendment Number -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kelishuv raqami *
                </label>
                <input type="text"
                       name="amendment_number"
                       value="{{ old('amendment_number', $amendment->amendment_number) }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                       required>
            </div>

            <!-- Amendment Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kelishuv sanasi *
                </label>
                <input type="date"
                       name="amendment_date"
                       value="{{ old('amendment_date', $amendment->amendment_date->format('Y-m-d')) }}"
                       min="{{ $contract->contract_date->format('Y-m-d') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                       required>
            </div>

            <!-- New Total Amount -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Yangi jami summa (so'm)
                </label>
                <input type="number"
                       name="new_total_amount"
                       value="{{ old('new_total_amount', $amendment->new_total_amount) }}"
                       step="0.01"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- New Initial Payment Percent -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Yangi boshlang'ich to'lov (%)
                </label>
                <input type="number"
                       name="new_initial_payment_percent"
                       value="{{ old('new_initial_payment_percent', $amendment->new_initial_payment_percent) }}"
                       min="0"
                       max="100"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- New Quarters Count -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Yangi choraklar soni
                </label>
                <input type="number"
                       name="new_quarters_count"
                       value="{{ old('new_quarters_count', $amendment->new_quarters_count) }}"
                       min="1"
                       max="20"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- New Completion Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Yangi yakunlash sanasi
                </label>
                <input type="date"
                       name="new_completion_date"
                       value="{{ old('new_completion_date', $amendment->new_completion_date?->format('Y-m-d')) }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Reason -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    O'zgartirish sababi *
                </label>
                <textarea name="reason"
                          rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                          required>{{ old('reason', $amendment->reason) }}</textarea>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qo'shimcha ma'lumot
                </label>
                <textarea name="description"
                          rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('description', $amendment->description) }}</textarea>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('contracts.amendments.show', [$contract, $amendment]) }}"
                   class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Bekor qilish
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Saqlash
                </button>
            </div>
        </div>
    </form> --}}

<form method="POST" action="{{ route('contracts.amendments.update', [$contract, $amendment]) }}">
    @csrf
    @method('PUT')

    <div class="space-y-6">
        <!-- Amendment Number -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Kelishuv raqami *
            </label>
            <input type="text"
                   name="amendment_number"
                   value="{{ old('amendment_number', $amendment->amendment_number) }}"
                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                   required>
        </div>

        <!-- Amendment Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Kelishuv sanasi *
            </label>
            <input type="date"
                   name="amendment_date"
                   value="{{ old('amendment_date', $amendment->amendment_date->format('Y-m-d')) }}"
                   min="{{ $contract->contract_date->format('Y-m-d') }}"
                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                   required>
        </div>

        <!-- New Total Amount -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Yangi jami summa (so'm)
            </label>
            <input type="number"
                   id="new_total_amount"
                   name="new_total_amount"
                   value="{{ old('new_total_amount', $amendment->new_total_amount) }}"
                   step="0.01"
                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                   oninput="calculateAmendmentFromPercent()">
        </div>

        <!-- New Initial Payment with Dual Input -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Yangi boshlang'ich to'lov</label>

            <!-- Percentage input -->
            <div class="mb-3">
                <label class="block text-xs text-gray-600 mb-1">Foizda (%)</label>
                <input type="number"
                       name="new_initial_payment_percent"
                       id="new_initial_payment_percent"
                       value="{{ old('new_initial_payment_percent', $amendment->new_initial_payment_percent) }}"
                       min="0"
                       max="100"
                       step="any"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                       oninput="calculateAmendmentFromPercent()">
            </div>

            <!-- Amount input -->
            <div>
                <label class="block text-xs text-gray-600 mb-1">So'mda</label>
                <input type="number"
                       id="new_initial_payment_amount"
                       name="new_initial_payment_amount"
                       value="{{ old('new_initial_payment_amount', $amendment->new_initial_payment_amount) }}"
                       step="0.01"
                       min="0"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Boshlang'ich to'lov summasi"
                       oninput="calculateAmendmentFromAmount()">
            </div>
        </div>

        <!-- New Quarters Count -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Yangi choraklar soni
            </label>
            <input type="number"
                   name="new_quarters_count"
                   value="{{ old('new_quarters_count', $amendment->new_quarters_count) }}"
                   min="1"
                   max="20"
                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- New Completion Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Yangi yakunlash sanasi
            </label>
            <input type="date"
                   name="new_completion_date"
                   value="{{ old('new_completion_date', $amendment->new_completion_date?->format('Y-m-d')) }}"
                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Reason -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                O'zgartirish sababi *
            </label>
            <textarea name="reason"
                      rows="3"
                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      required>{{ old('reason', $amendment->reason) }}</textarea>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Qo'shimcha ma'lumot
            </label>
            <textarea name="description"
                      rows="3"
                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('description', $amendment->description) }}</textarea>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('contracts.amendments.show', [$contract, $amendment]) }}"
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Bekor qilish
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Saqlash
            </button>
        </div>
    </div>
</form>

<script>
function calculateAmendmentFromPercent() {
    const totalAmount = document.getElementById('new_total_amount').value;
    const percent = document.getElementById('new_initial_payment_percent').value;

    if (totalAmount && percent) {
        const amount = (parseFloat(totalAmount) * parseFloat(percent)) / 100;
        document.getElementById('new_initial_payment_amount').value = amount.toFixed(2);
    } else if (!percent) {
        document.getElementById('new_initial_payment_amount').value = '';
    }
}

function calculateAmendmentFromAmount() {
    const totalAmount = document.getElementById('new_total_amount').value;
    const amount = document.getElementById('new_initial_payment_amount').value;

    if (totalAmount && amount) {
        const percent = (parseFloat(amount) / parseFloat(totalAmount)) * 100;
        document.getElementById('new_initial_payment_percent').value = percent.toFixed(8);
    } else if (!amount) {
        document.getElementById('new_initial_payment_percent').value = '';
    }
}

// Initialize calculation on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateAmendmentFromPercent();
});
</script>
</div>
@endsection
