@extends('layouts.app')

@section('title', 'Создать договор - АПЗ Система')
@section('page-title', 'Создание нового договора')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.index') }}"
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2 inline"></i>
        Назад к списку
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('contracts.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Основная информация -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Основная информация</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Номер договора *</label>
                    <input type="text" name="contract_number" value="{{ old('contract_number') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="АПЗ-001/2024">
                    @error('contract_number')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата договора *</label>
                    <input type="date" name="contract_date" value="{{ old('contract_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('contract_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата завершения</label>
                    <input type="date" name="completion_date" value="{{ old('completion_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('completion_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Статус *</label>
                    <select name="status_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите статус</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->name_ru }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Заказчик -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Заказчик</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Заказчик *</label>
                    <select name="subject_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите заказчика</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->display_name }} ({{ $subject->identifier }})
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <a href="{{ route('subjects.create') }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors mt-6">
                        <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                        Создать нового заказчика
                    </a>
                </div>
            </div>
        </div>

        <!-- Объект -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Объект</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Объект *</label>
                    <select name="object_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите объект</option>
                        @foreach($objects as $object)
                            <option value="{{ $object->id }}" {{ old('object_id') == $object->id ? 'selected' : '' }}>
                                {{ $object->address }} ({{ $object->district->name_ru ?? '' }}) - {{ number_format($object->construction_volume, 2) }} м³
                            </option>
                        @endforeach
                    </select>
                    @error('object_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="button" onclick="createNewObject()"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors mt-6">
                        <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                        Создать новый объект
                    </button>
                </div>
            </div>
        </div>

        <!-- Расчет суммы -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Расчет суммы договора</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Базовая расчетная величина *</label>
                    <select name="base_amount_id" required onchange="calculateTotal()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите базовую величину</option>
                        @foreach($baseAmounts as $baseAmount)
                            <option value="{{ $baseAmount->id }}" data-amount="{{ $baseAmount->amount }}" {{ old('base_amount_id') == $baseAmount->id ? 'selected' : '' }}>
                                {{ number_format($baseAmount->amount) }} сум (с {{ $baseAmount->effective_from->format('d.m.Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('base_amount_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Объем строительства (м³) *</label>
                    <input type="number" name="contract_volume" step="0.01" value="{{ old('contract_volume') }}" required
                           onchange="calculateTotal()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('contract_volume')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Коэффициент *</label>
                    <input type="number" name="coefficient" step="0.01" value="{{ old('coefficient', '1.00') }}" required
                           onchange="calculateTotal()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('coefficient')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Общая сумма договора:</p>
                    <p id="total_amount_display" class="text-2xl font-bold text-blue-600">0 сум</p>
                    <p id="formula_display" class="text-sm text-gray-500 mt-2"></p>
                </div>
            </div>
        </div>

        <!-- Условия оплаты -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Условия оплаты</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип оплаты *</label>
                    <select name="payment_type" required onchange="togglePaymentFields()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="full" {{ old('payment_type') == 'full' ? 'selected' : '' }}>Полная оплата</option>
                        <option value="installment" {{ old('payment_type', 'installment') == 'installment' ? 'selected' : '' }}>Рассрочка</option>
                    </select>
                    @error('payment_type')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="initial_payment_field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Первоначальный взнос (%)</label>
                    <input type="number" name="initial_payment_percent" min="0" max="100" value="{{ old('initial_payment_percent', 20) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('initial_payment_percent')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="construction_period_field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Период строительства (лет)</label>
                    <input type="number" name="construction_period_years" min="1" max="10" value="{{ old('construction_period_years', 2) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('construction_period_years')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Кнопки -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('contracts.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Отмена
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-feather="save" class="w-4 h-4 mr-2 inline"></i>
                Создать договор
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function calculateTotal() {
    const baseAmountSelect = document.querySelector('select[name="base_amount_id"]');
    const volumeInput = document.querySelector('input[name="contract_volume"]');
    const coefficientInput = document.querySelector('input[name="coefficient"]');

    const selectedOption = baseAmountSelect.options[baseAmountSelect.selectedIndex];
    const baseAmount = selectedOption ? parseFloat(selectedOption.dataset.amount) : 0;
    const volume = parseFloat(volumeInput.value) || 0;
    const coefficient = parseFloat(coefficientInput.value) || 1;

    const totalAmount = baseAmount * volume * coefficient;

    document.getElementById('total_amount_display').textContent = formatNumber(totalAmount) + ' сум';

    if (baseAmount && volume && coefficient) {
        document.getElementById('formula_display').textContent =
            `${formatNumber(volume)} м³ × ${formatNumber(baseAmount)} сум × ${coefficient} = ${formatNumber(totalAmount)} сум`;
    } else {
        document.getElementById('formula_display').textContent = '';
    }
}

function togglePaymentFields() {
    const paymentType = document.querySelector('select[name="payment_type"]').value;
    const initialPaymentField = document.getElementById('initial_payment_field');
    const constructionPeriodField = document.getElementById('construction_period_field');

    if (paymentType === 'full') {
        initialPaymentField.style.display = 'none';
        constructionPeriodField.style.display = 'none';
    } else {
        initialPaymentField.style.display = 'block';
        constructionPeriodField.style.display = 'block';
    }
}

function createNewObject() {
    // В реальном приложении здесь можно открыть модал для создания объекта
    alert('Функция создания нового объекта будет добавлена позже');
}

function formatNumber(num) {
    return new Intl.NumberFormat('ru-RU').format(num);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
    togglePaymentFields();
});
</script>
@endpush
