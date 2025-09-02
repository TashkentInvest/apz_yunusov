@extends('layouts.app')

@section('title', 'Управление платежами - ' . $contract->contract_number)
@section('page-title', 'Управление платежами')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.show', $contract) }}"
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2 inline"></i>
        Назад к договору
    </a>
    <button onclick="openPlanPaymentModal()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
        Добавить план
    </button>
    <button onclick="openFactPaymentModal()"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
        Добавить факт
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Contract Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-900">{{ $contract->contract_number }}</h2>
            <p class="text-sm text-gray-600">от {{ $contract->contract_date->format('d.m.Y') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Customer Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Информация о заказчике</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Название/ФИО</dt>
                        <dd class="text-sm text-gray-900 mt-1 font-medium">
                            {{ $contract->subject->is_legal_entity ? $contract->subject->company_name : ($contract->subject->last_name . ' ' . $contract->subject->first_name . ' ' . $contract->subject->middle_name) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-600">{{ $contract->subject->is_legal_entity ? 'ИНН' : 'ПИНФЛ' }}</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $contract->subject->is_legal_entity ? $contract->subject->inn : $contract->subject->pinfl }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Адрес</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            {{ $contract->subject->legal_address ?: $contract->subject->physical_address }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Object Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Информация об объекте</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Район</dt>
                        <dd class="text-sm text-gray-900 mt-1 font-medium">{{ $contract->object->district->name_ru ?? $contract->object->district->name_uz }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Адрес</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $contract->object->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Кадастровый номер</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $contract->object->cadastre_number ?: 'Не указан' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Объем строительства</dt>
                        <dd class="text-sm text-gray-900 mt-1 font-medium">{{ number_format($contract->contract_volume, 2) }} м³</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Коэффициент</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $contract->coefficient }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Payment Summary -->
    @php
        $summary = $contract->paymentSummary;
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Сводка по платежам</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm font-medium text-blue-600">План всего</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">
                    {{ number_format($summary['plan_total'], 0, '.', ' ') }} сум
                </p>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <p class="text-sm font-medium text-green-600">Факт всего</p>
                <p class="text-2xl font-bold text-green-900 mt-1">
                    {{ number_format($summary['fact_total'], 0, '.', ' ') }} сум
                </p>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
                <p class="text-sm font-medium text-red-600">Задолженность</p>
                <p class="text-2xl font-bold text-red-900 mt-1">
                    {{ number_format($summary['debt'], 0, '.', ' ') }} сум
                </p>
                <div class="w-full bg-red-200 rounded-full h-2 mt-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(100, $summary['payment_percent']) }}%"></div>
                </div>
                <p class="text-sm text-red-700 mt-1">{{ number_format($summary['payment_percent'], 1) }}% оплачено</p>
            </div>
        </div>
    </div>

    <!-- Payment Schedule -->
    @foreach($paymentSummary as $year => $quarters)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $year }} год</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Квартал</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">План</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Факт</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Долг</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Прогресс</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @for($quarter = 1; $quarter <= 4; $quarter++)
                        @php $quarterData = $quarters[$quarter] @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $quarter }} квартал
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-blue-600 font-medium">
                                    {{ number_format($quarterData['plan_amount'], 0, '.', ' ') }} сум
                                </div>
                                @if($quarterData['plan'] && $quarterData['plan']->amendment_id === null)
                                    <button onclick="deletePlanPayment({{ $quarterData['plan']->id }})"
                                            class="mt-1 text-red-500 hover:text-red-700 text-xs">
                                        <i data-feather="trash-2" class="w-3 h-3 inline mr-1"></i>Удалить
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-green-600 font-medium">
                                    {{ number_format($quarterData['fact_total'], 0, '.', ' ') }} сум
                                </div>
                                @if($quarterData['fact_payments']->count() > 0)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $quarterData['fact_payments']->count() }} платеж(ей)
                                        <button onclick="showFactPayments({{ $year }}, {{ $quarter }})"
                                                class="text-blue-500 hover:text-blue-700 ml-1">
                                            <i data-feather="eye" class="w-3 h-3 inline"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm {{ $quarterData['debt'] > 0 ? 'text-red-600' : 'text-green-600' }} font-medium">
                                {{ number_format($quarterData['debt'], 0, '.', ' ') }} сум
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all"
                                         style="width: {{ min(100, $quarterData['payment_percent']) }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ number_format($quarterData['payment_percent'], 1) }}%
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="editPlanPayment({{ $year }}, {{ $quarter }}, {{ $quarterData['plan_amount'] }})"
                                            class="text-blue-600 hover:text-blue-700" title="Редактировать план">
                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="addFactPaymentForQuarter({{ $year }}, {{ $quarter }})"
                                            class="text-green-600 hover:text-green-700" title="Добавить факт платеж">
                                        <i data-feather="plus-circle" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @if(empty($paymentSummary))
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <i data-feather="calendar" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Нет графика платежей</h3>
        <p class="text-gray-500 mb-6">Начните с добавления плановых платежей</p>
        <button onclick="openPlanPaymentModal()"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Добавить плановый платеж
        </button>
    </div>
    @endif

    <!-- Recent Fact Payments -->
    @if($contract->actualPayments->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Последние фактические платежи</h3>
        </div>
        <div class="p-6">
            @foreach($contract->actualPayments->take(10) as $payment)
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                            <i data-feather="credit-card" class="w-4 h-4 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $payment->payment_number ?: 'Без номера' }}</p>
                            <p class="text-xs text-gray-500">{{ $payment->quarter }} кв. {{ $payment->year }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-green-600">{{ number_format($payment->amount, 0, '.', ' ') }} сум</p>
                        <p class="text-xs text-gray-500">{{ $payment->payment_date->format('d.m.Y') }}</p>
                        <button onclick="deleteFactPayment({{ $payment->id }})"
                                class="text-red-500 hover:text-red-700 text-xs mt-1">
                            <i data-feather="trash-2" class="w-3 h-3 inline mr-1"></i>Удалить
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Plan Payment Modal -->
<div id="planPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form id="planPaymentForm">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Плановый платеж</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Год</label>
                            <input type="number" name="year" min="2020" max="2050" required
                                   value="{{ date('Y') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Квартал</label>
                            <select name="quarter" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="1">1 квартал</option>
                                <option value="2">2 квартал</option>
                                <option value="3">3 квартал</option>
                                <option value="4">4 квартал</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Сумма</label>
                        <input type="number" name="amount" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Введите сумму">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closePlanPaymentModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
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
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form id="factPaymentForm">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Фактический платеж</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Дата платежа</label>
                        <input type="date" name="payment_date" required
                               value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Сумма</label>
                        <input type="number" name="amount" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Введите сумму">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Номер платежа</label>
                        <input type="text" name="payment_number" maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Номер документа платежа">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Примечание</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Дополнительная информация о платеже"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeFactPaymentModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Добавить платеж
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fact Payments Details Modal -->
<div id="factPaymentsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-2xl sm:w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Детали фактических платежей</h3>
                <p class="text-sm text-gray-600" id="factPaymentsTitle"></p>
            </div>
            <div class="px-6 py-4">
                <div id="factPaymentsList"></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeFactPaymentsModal()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
const contractId = {{ $contract->id }};

// Modal functions
function openPlanPaymentModal() {
    document.getElementById('planPaymentModal').classList.remove('hidden');
    // Reset form
    document.getElementById('planPaymentForm').reset();
    document.querySelector('input[name="year"]').value = new Date().getFullYear();
    const currentQuarter = Math.ceil((new Date().getMonth() + 1) / 3);
    document.querySelector('select[name="quarter"]').value = currentQuarter;
}

function closePlanPaymentModal() {
    document.getElementById('planPaymentModal').classList.add('hidden');
}

function openFactPaymentModal() {
    document.getElementById('factPaymentModal').classList.remove('hidden');
    // Reset form and set current date
    document.getElementById('factPaymentForm').reset();
    document.querySelector('input[name="payment_date"]').value = new Date().toISOString().split('T')[0];
}

function closeFactPaymentModal() {
    document.getElementById('factPaymentModal').classList.add('hidden');
}

function closeFactPaymentsModal() {
    document.getElementById('factPaymentsModal').classList.add('hidden');
}

// Edit functions
function editPlanPayment(year, quarter, currentAmount) {
    openPlanPaymentModal();
    document.querySelector('input[name="year"]').value = year;
    document.querySelector('select[name="quarter"]').value = quarter;
    document.querySelector('input[name="amount"]').value = currentAmount;
}

function addFactPaymentForQuarter(year, quarter) {
    openFactPaymentModal();
    // Set date to first day of the quarter
    const firstMonth = (quarter - 1) * 3 + 1;
    const date = new Date(year, firstMonth - 1, 1);
    document.querySelector('input[name="payment_date"]').value = date.toISOString().split('T')[0];
}

// Show fact payments for specific quarter
function showFactPayments(year, quarter) {
    const factPayments = @json($contract->actualPayments->groupBy(function($payment) {
        return $payment->year . '-' . $payment->quarter;
    }));

    const key = year + '-' + quarter;
    const payments = factPayments[key] || [];

    document.getElementById('factPaymentsTitle').textContent = `${quarter} квартал ${year} года`;

    if (payments.length === 0) {
        document.getElementById('factPaymentsList').innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <i data-feather="calendar" class="w-12 h-12 mx-auto mb-4"></i>
                <p>Фактических платежей не найдено</p>
            </div>
        `;
    } else {
        let html = '<div class="space-y-3">';
        payments.forEach(payment => {
            const paymentDate = new Date(payment.payment_date).toLocaleDateString('ru-RU');
            html += `
                <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium text-gray-900">${payment.payment_number || 'Без номера'}</p>
                        <p class="text-sm text-gray-600">${paymentDate}</p>
                        ${payment.notes ? `<p class="text-xs text-gray-500 mt-1">${payment.notes}</p>` : ''}
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-green-600">${new Intl.NumberFormat('ru-RU').format(payment.amount)} сум</p>
                        <button onclick="deleteFactPayment(${payment.id})"
                                class="text-red-500 hover:text-red-700 text-sm mt-1">
                            <i data-feather="trash-2" class="w-3 h-3 inline mr-1"></i>Удалить
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        document.getElementById('factPaymentsList').innerHTML = html;
    }

    // Re-initialize feather icons
    feather.replace();
    document.getElementById('factPaymentsModal').classList.remove('hidden');
}

// Form submissions
document.getElementById('planPaymentForm').addEventListener('submit', async function(e) {
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
            closePlanPaymentModal();
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
            setTimeout(() => location.reload(), 1000);
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
async function deletePlanPayment(id) {
    if (!confirm('Вы уверены, что хотите удалить этот плановый платеж?')) {
        return;
    }

    try {
        const response = await fetch(`{{ url('contracts/plan-payment') }}/${id}`, {
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
            throw new Error(result.message || 'Ошибка при удалении планового платежа');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage(error.message);
    }
}

async function deleteFactPayment(id) {
    if (!confirm('Вы уверены, что хотите удалить этот фактический платеж?')) {
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
            throw new Error(result.message || 'Ошибка при удалении фактического платежа');
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
        button.innerHTML = '<i data-feather="loader" class="w-4 h-4 mr-2 inline animate-spin"></i>Загрузка...';
    } else {
        button.disabled = false;
        const originalText = button.getAttribute('data-original-text') || button.textContent;
        button.innerHTML = originalText;
    }
    feather.replace();
}

function showSuccessMessage(message) {
    // You can replace this with your preferred notification system
    alert(message);
}

function showErrorMessage(message) {
    // You can replace this with your preferred notification system
    alert('Ошибка: ' + message);
}

// Close modals on background click
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});

// Initialize feather icons on page load
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
@endpush
