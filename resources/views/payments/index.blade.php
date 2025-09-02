@extends('layouts.app')

@section('title', 'Платежи - АПЗ Система')
@section('page-title', 'Управление платежами')

@section('header-actions')
<div class="flex space-x-3">
    <button onclick="addPayment()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
        Добавить платеж
    </button>
    <button onclick="exportPayments()"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="download" class="w-4 h-4 mr-2 inline"></i>
        Экспорт
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Всего платежей</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $payments->total() }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="credit-card" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Сумма за период</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        {{ number_format($payments->sum('amount') / 1000000, 1) }}М
                    </p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <i data-feather="dollar-sign" class="w-5 h-5 text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Средний платеж</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">
                        {{ $payments->count() > 0 ? number_format($payments->sum('amount') / $payments->count() / 1000000, 1) : 0 }}М
                    </p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="trending-up" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">За сегодня</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">
                        {{ number_format(\App\Models\ActualPayment::whereDate('payment_date', today())->sum('amount') / 1000000, 1) }}М
                    </p>
                </div>
                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                    <i data-feather="calendar" class="w-5 h-5 text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('payments.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Номер договора</label>
                <input type="text"
                       name="contract_number"
                       value="{{ request('contract_number') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="АПЗ-">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Год</label>
                <select name="year"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Все годы</option>
                    @for($year = date('Y'); $year >= 2020; $year--)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Квартал</label>
                <select name="quarter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Все кварталы</option>
                    <option value="1" {{ request('quarter') == '1' ? 'selected' : '' }}>1 квартал</option>
                    <option value="2" {{ request('quarter') == '2' ? 'selected' : '' }}>2 квартал</option>
                    <option value="3" {{ request('quarter') == '3' ? 'selected' : '' }}>3 квартал</option>
                    <option value="4" {{ request('quarter') == '4' ? 'selected' : '' }}>4 квартал</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Период</label>
                <select name="period"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Весь период</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Сегодня</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Эта неделя</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Этот месяц</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="search" class="w-4 h-4 mr-2 inline"></i>
                    Поиск
                </button>
                <a href="{{ route('contracts.payments.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i data-feather="x" class="w-4 h-4 mr-2 inline"></i>
                    Сброс
                </a>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">История платежей</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер платежа</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Договор</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заказчик</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Период</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата платежа</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Примечание</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($payments as $index => $payment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ ($payments->currentPage() - 1) * $payments->perPage() + $index + 1 }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $payment->payment_number }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $payment->id }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('contracts.show', $payment->contract) }}"
                                   class="text-sm font-medium text-blue-600 hover:text-blue-700">
                                    {{ $payment->contract->contract_number }}
                                </a>
                                <div class="text-xs text-gray-500">{{ $payment->contract->contract_date->format('d.m.Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $payment->contract->subject->display_name }}</div>
                                <div class="text-xs text-gray-500">{{ $payment->contract->subject->identifier }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-green-600">
                                    {{ number_format($payment->amount) }} сум
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ number_format($payment->amount / 1000000, 1) }}М
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $payment->quarter }} кв. {{ $payment->year }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $payment->payment_date->format('d.m.Y') }}
                                <div class="text-xs text-gray-500">{{ $payment->payment_date->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($payment->notes)
                                    <div class="text-sm text-gray-900 truncate max-w-32" title="{{ $payment->notes }}">
                                        {{ $payment->notes }}
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button onclick="viewPayment({{ $payment->id }})"
                                            class="text-gray-400 hover:text-gray-600" title="Просмотр">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="editPayment({{ $payment->id }})"
                                            class="text-gray-400 hover:text-blue-600" title="Редактировать">
                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="printReceipt({{ $payment->id }})"
                                            class="text-gray-400 hover:text-green-600" title="Печать квитанции">
                                        <i data-feather="printer" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="deletePayment({{ $payment->id }})"
                                            class="text-gray-400 hover:text-red-600" title="Удалить">
                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i data-feather="credit-card" class="w-12 h-12 text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2">Платежи не найдены</p>
                                    <p class="text-gray-500 mb-4">Попробуйте изменить параметры поиска</p>
                                    <button onclick="addPayment()"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        Добавить первый платеж
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Показано {{ $payments->firstItem() }}-{{ $payments->lastItem() }} из {{ $payments->total() }} платежей
                    </div>
                    <div class="flex space-x-1">
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modals -->
@push('modals')
<!-- Add Payment Modal -->
<div id="addPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form id="addPaymentForm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Добавить платеж</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Договор *</label>
                        <select name="contract_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Выберите договор</option>
                            @foreach(\App\Models\Contract::with('subject')->where('is_active', true)->get() as $contract)
                                <option value="{{ $contract->id }}">
                                    {{ $contract->contract_number }} - {{ $contract->subject->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Номер платежа *</label>
                        <input type="text" name="payment_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="№123456">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Дата платежа *</label>
                            <input type="date" name="payment_date" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Сумма *</label>
                            <input type="number" name="amount" step="0.01" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Год *</label>
                            <input type="number" name="year" min="2020" max="2050" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Квартал *</label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Примечание</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('addPaymentModal')"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Добавить платеж
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection

@push('scripts')
<script>
function addPayment() {
    document.getElementById('addPaymentModal').classList.remove('hidden');

    // Set current date
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="payment_date"]').value = today;

    // Set current year and quarter
    const currentYear = new Date().getFullYear();
    const currentQuarter = Math.ceil((new Date().getMonth() + 1) / 3);
    document.querySelector('input[name="year"]').value = currentYear;
    document.querySelector('select[name="quarter"]').value = currentQuarter;
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function viewPayment(paymentId) {
    // В реальном приложении здесь будет модал с деталями платежа
    alert(`Просмотр платежа ID: ${paymentId}`);
}

function editPayment(paymentId) {
    // В реальном приложении здесь будет форма редактирования
    alert(`Редактирование платежа ID: ${paymentId}`);
}

function printReceipt(paymentId) {
    window.open(`/payments/${paymentId}/receipt`, '_blank');
}

function deletePayment(paymentId) {
    if (confirm('Вы уверены, что хотите удалить этот платеж?')) {
        fetch(`/payments/${paymentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('Платеж успешно удален');
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Ошибка при удалении платежа');
            }
        })
        .catch(error => {
            handleAjaxError({ responseJSON: { message: error.message } });
        });
    }
}

function exportPayments() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `{{ route('api.export.payments') }}?${params.toString()}`;
}

document.getElementById('addPaymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');

    toggleLoading(submitButton, true);

    try {
        const response = await fetch('{{ route("contracts.payments.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeModal('addPaymentModal');
            showSuccessMessage(result.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Ошибка при добавлении платежа');
        }
    } catch (error) {
        console.error('Error:', error);
        handleAjaxError({ responseJSON: { message: error.message } });
    } finally {
        toggleLoading(submitButton, false);
    }
});

// Auto-refresh every 30 seconds
setInterval(() => {
    if (document.visibilityState === 'visible') {
        // Обновляем только статистику без перезагрузки страницы
        updatePaymentStats();
    }
}, 30000);

function updatePaymentStats() {
    fetch('/api/payments/stats')
        .then(response => response.json())
        .then(data => {
            // Обновляем статистику на странице
            console.log('Stats updated:', data);
        })
        .catch(error => console.error('Error updating stats:', error));
}
</script>
@endpush
