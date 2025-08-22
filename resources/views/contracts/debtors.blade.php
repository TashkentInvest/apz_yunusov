@extends('layouts.app')

@section('title', 'Должники - АПЗ Система')
@section('page-title', 'Список должников')

@section('header-actions')
<div class="flex space-x-3">
    <button onclick="exportDebtors()"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
        <i data-feather="download" class="w-4 h-4 mr-2 inline"></i>
        Экспорт должников
    </button>
    <button onclick="sendNotifications()"
            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
        <i data-feather="bell" class="w-4 h-4 mr-2 inline"></i>
        Отправить уведомления
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Всего должников</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $debtors->total() }}</p>
                </div>
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                    <i data-feather="alert-triangle" class="w-5 h-5 text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Общая задолженность</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">
                        {{ number_format($debtors->sum('remaining_debt') / 1000000, 1) }}М
                    </p>
                </div>
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                    <i data-feather="dollar-sign" class="w-5 h-5 text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Средняя задолженность</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">
                        {{ $debtors->count() > 0 ? number_format($debtors->sum('remaining_debt') / $debtors->count() / 1000000, 1) : 0 }}М
                    </p>
                </div>
                <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
                    <i data-feather="trending-down" class="w-5 h-5 text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Критические долги</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">
                        {{ $debtors->filter(function($debtor) { return $debtor->remaining_debt > 100000000; })->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                    <i data-feather="alert-circle" class="w-5 h-5 text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('contracts.debtors') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Номер договора</label>
                <input type="text"
                       name="contract_number"
                       value="{{ request('contract_number') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="АПЗ-">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Район</label>
                <select name="district_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Все районы</option>
                    @foreach(\App\Models\District::where('is_active', true)->get() as $district)
                        <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>
                            {{ $district->name_ru }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Сумма долга от</label>
                <input type="number"
                       name="debt_from"
                       value="{{ request('debt_from') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="0">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="search" class="w-4 h-4 mr-2 inline"></i>
                    Фильтр
                </button>
                <a href="{{ route('contracts.debtors') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i data-feather="x" class="w-4 h-4 mr-2 inline"></i>
                    Сброс
                </a>
            </div>
        </form>
    </div>

    <!-- Debtors Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Список должников</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Договор</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заказчик</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Контакты</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Район</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма договора</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Оплачено</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Задолженность</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($debtors as $index => $contract)
                        <tr class="hover:bg-gray-50 transition-colors {{ $contract->remaining_debt > 100000000 ? 'bg-red-25' : '' }}">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ ($debtors->currentPage() - 1) * $debtors->perPage() + $index + 1 }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $contract->contract_number }}</div>
                                <div class="text-sm text-gray-500">{{ $contract->contract_date->format('d.m.Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $contract->subject->display_name }}</div>
                                <div class="text-sm text-gray-500">{{ $contract->subject->identifier }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($contract->subject->phone)
                                    <div class="text-sm text-gray-900">{{ $contract->subject->phone }}</div>
                                @endif
                                @if($contract->subject->email)
                                    <div class="text-sm text-gray-500">{{ $contract->subject->email }}</div>
                                @endif
                                @if(!$contract->subject->phone && !$contract->subject->email)
                                    <span class="text-sm text-gray-400">Не указано</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $contract->object->district->name_ru ?? 'Не указан' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ number_format($contract->total_amount) }} сум
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($contract->total_amount / 1000000, 1) }}М
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-green-600">
                                    {{ number_format($contract->total_paid) }} сум
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-green-600 h-2 rounded-full"
                                         style="width: {{ $contract->payment_percent }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ $contract->payment_percent }}%</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-red-600">
                                    {{ number_format($contract->remaining_debt) }} сум
                                </div>
                                <div class="text-sm text-red-500">
                                    {{ number_format($contract->remaining_debt / 1000000, 1) }}М
                                </div>
                                @if($contract->remaining_debt > 100000000)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 mt-1">
                                        Критический
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('contracts.show', $contract) }}"
                                       class="text-gray-400 hover:text-gray-600" title="Просмотр">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('documents.demand-notice', $contract) }}" target="_blank"
                                       class="text-gray-400 hover:text-red-600" title="Требование">
                                        <i data-feather="alert-triangle" class="w-4 h-4"></i>
                                    </a>
                                    <button onclick="callDebtor('{{ $contract->subject->phone }}')"
                                            class="text-gray-400 hover:text-blue-600" title="Позвонить"
                                            {{ !$contract->subject->phone ? 'disabled' : '' }}>
                                        <i data-feather="phone" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="sendSMS({{ $contract->id }})"
                                            class="text-gray-400 hover:text-green-600" title="Отправить SMS"
                                            {{ !$contract->subject->phone ? 'disabled' : '' }}>
                                        <i data-feather="message-square" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i data-feather="check-circle" class="w-12 h-12 text-green-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2">Должников не найдено</p>
                                    <p class="text-gray-500 mb-4">Все платежи выполнены в срок</p>
                                    <a href="{{ route('contracts.index') }}"
                                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        Вернуться к договорам
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($debtors->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Показано {{ $debtors->firstItem() }}-{{ $debtors->lastItem() }} из {{ $debtors->total() }} должников
                    </div>
                    <div class="flex space-x-1">
                        {{ $debtors->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Actions Panel -->
    @if($debtors->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Массовые действия</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button onclick="generateAllDemands()"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i data-feather="file-text" class="w-4 h-4 mr-2 inline"></i>
                Сгенерировать все требования
            </button>
            <button onclick="sendAllNotifications()"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                <i data-feather="bell" class="w-4 h-4 mr-2 inline"></i>
                Отправить уведомления всем
            </button>
            <button onclick="createDebtorsReport()"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i data-feather="bar-chart" class="w-4 h-4 mr-2 inline"></i>
                Создать отчет
            </button>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function exportDebtors() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `{{ route('api.export.debtors') }}?${params.toString()}`;
}

function sendNotifications() {
    if (confirm('Отправить уведомления всем должникам?')) {
        showSuccessMessage('Уведомления отправлены в очередь обработки');
    }
}

function callDebtor(phone) {
    if (phone) {
        if (confirm(`Позвонить по номеру ${phone}?`)) {
            window.open(`tel:${phone}`);
        }
    } else {
        alert('Номер телефона не указан');
    }
}

function sendSMS(contractId) {
    if (confirm('Отправить SMS-уведомление о задолженности?')) {
        // Здесь будет AJAX запрос на отправку SMS
        fetch(`/contracts/${contractId}/send-sms`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('SMS отправлено успешно');
            } else {
                handleAjaxError({ responseJSON: { message: data.message } });
            }
        })
        .catch(error => {
            handleAjaxError({ responseJSON: { message: 'Ошибка при отправке SMS' } });
        });
    }
}

function generateAllDemands() {
    if (confirm('Сгенерировать требования для всех должников? Это может занять некоторое время.')) {
        const button = event.target;
        toggleLoading(button, true);

        // Simulate generation process
        setTimeout(() => {
            toggleLoading(button, false);
            showSuccessMessage('Требования успешно сгенерированы');
        }, 3000);
    }
}

function sendAllNotifications() {
    if (confirm('Отправить уведомления всем должникам?')) {
        showSuccessMessage('Уведомления добавлены в очередь отправки');
    }
}

function createDebtorsReport() {
    const button = event.target;
    toggleLoading(button, true);

    // Simulate report generation
    setTimeout(() => {
        toggleLoading(button, false);
        showSuccessMessage('Отчет по должникам создан и отправлен на email');
    }, 2000);
}

// Auto-refresh every 5 minutes to get updated debt information
setInterval(() => {
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 300000);
</script>
@endpush
