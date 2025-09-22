@extends('layouts.app')

@section('title', 'Договоры - АПЗ Система')
@section('page-title', 'Управление договорами')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.create') }}"
       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
        Новый договор
    </a>
    <button onclick="exportContracts()"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="download" class="w-4 h-4 mr-2 inline"></i>
        Экспорт
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('contracts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    @foreach($districts ?? [] as $district)
                        <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>
                            {{ $district->name_ru }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                <select name="status_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Все статусы</option>
                    @foreach($statuses ?? [] as $status)
                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                            {{ $status->name_ru }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="search" class="w-4 h-4 mr-2 inline"></i>
                    Поиск
                </button>
                <a href="{{ route('contracts.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i data-feather="x" class="w-4 h-4 mr-2 inline"></i>
                    Сброс
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Найдено договоров</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $contracts->total() }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="file-text" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Общая сумма</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">
                        @php
                            $totalAmount = 0;
                            foreach($contracts->items() as $contract) {
                                $totalAmount += $contract->total_amount;
                            }
                        @endphp
                        {{ number_format($totalAmount , 0) }}
                        <!-- {{ number_format($totalAmount / 1000000000, 1) }}Б -->
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
                    <p class="text-sm font-medium text-gray-600">Активные</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">
                        @php
                            $activeCount = 0;
                            foreach($contracts->items() as $contract) {
                                if($contract->status && $contract->status->code === 'ACTIVE') {
                                    $activeCount++;
                                }
                            }
                        @endphp
                        {{ $activeCount }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Список договоров</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер договора</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заказчик</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Район</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Оплачено</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($contracts as $index => $contract)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <!-- Row Number -->
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ ($contracts->currentPage() - 1) * $contracts->perPage() + $loop->iteration }}
                            </td>

                            <!-- Contract Number -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('contracts.show', $contract) }}"
                                       class="text-blue-600 hover:text-blue-800">
                                        {{ $contract->contract_number }}
                                    </a>
                                </div>
                                <div class="text-sm text-gray-500">
                                    ID: {{ $contract->id }}
                                </div>
                            </td>

                            <!-- Subject/Customer -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $contract->subject->display_name ?? $contract->subject->company_name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $contract->subject->is_legal_entity ? 'ИНН: ' . $contract->subject->inn : 'ПИНФЛ: ' . $contract->subject->pinfl }}
                                </div>
                            </td>

                            <!-- District -->
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $contract->object->district->name_ru ?? 'Не указан' }}
                            </td>

                            <!-- Contract Amount -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ number_format($contract->total_amount, 0, '.', ' ') }} сум
                                    <!-- {{ number_format($contract->total_amount, 0, '.', ' ') }} сум -->
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($contract->total_amount / 1000000, 1) }} млн
                                </div>
                            </td>

                            <!-- Payment Progress -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ number_format($contract->total_paid, 0, '.', ' ') }} сум
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                         style="width: {{ min(100, $contract->payment_percent) }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ number_format($contract->payment_percent, 1) }}%
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      style="background-color: {{ $contract->status->color ?? '#6b7280' }}20; color: {{ $contract->status->color ?? '#6b7280' }}">
                                    {{ $contract->status->name_ru ?? 'Не указан' }}
                                </span>
                            </td>

                            <!-- Contract Date -->
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $contract->contract_date ? $contract->contract_date->format('d.m.Y') : 'Не указана' }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    {{-- <a href="{{ route('contracts.show', $contract) }}"
                                       class="text-gray-400 hover:text-gray-600" title="Просмотр">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </a> --}}
                                    <a href="{{ route('contracts.payment_update', $contract) }}"
                                       class="text-gray-400 hover:text-blue-600" title="Редактировать">
                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    {{-- @if($contract->remaining_debt > 0)
                                        <button onclick="generateDemandNotice({{ $contract->id }})"
                                                class="text-gray-400 hover:text-red-600" title="Требование">
                                            <i data-feather="alert-triangle" class="w-4 h-4"></i>
                                        </button>
                                    @endif --}}
                                    {{-- <div class="relative">
                                        <button onclick="toggleDropdown({{ $contract->id }})"
                                                class="text-gray-400 hover:text-gray-600" title="Еще">
                                            <i data-feather="more-horizontal" class="w-4 h-4"></i>
                                        </button>
                                        <div id="dropdown-{{ $contract->id }}"
                                             class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                            <div class="py-1">
                                                <button onclick="createAmendment({{ $contract->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-feather="file-plus" class="w-4 h-4 mr-2 inline"></i>
                                                    Доп. соглашение
                                                </button>
                                                <button onclick="addPayment({{ $contract->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-feather="credit-card" class="w-4 h-4 mr-2 inline"></i>
                                                    Добавить платеж
                                                </button>
                                                <button onclick="cancelContract({{ $contract->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                    <i data-feather="x-circle" class="w-4 h-4 mr-2 inline"></i>
                                                    Отменить договор
                                                </button>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i data-feather="file-text" class="w-12 h-12 text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2">Договоры не найдены</p>
                                    <p class="text-gray-500 mb-4">Попробуйте изменить параметры поиска</p>
                                    <a href="{{ route('contracts.create') }}"
                                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        Создать новый договор
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{-- @if($contracts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Показано {{ $contracts->firstItem() }}-{{ $contracts->lastItem() }} из {{ $contracts->total() }} результатов
                    </div>
                    <div class="flex space-x-1">
                        {{ $contracts->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif --}}

@if($contracts->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Показано {{ $contracts->firstItem() }}-{{ $contracts->lastItem() }} из {{ $contracts->total() }} результатов
            </div>
            <div class="flex space-x-1">
                {{-- Custom pagination --}}
                @foreach ($contracts->links()->elements as $element)
                    @if (is_string($element))
                        <span class="px-3 py-1 text-gray-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $contracts->currentPage())
                                <span class="px-3 py-1 bg-blue-600 text-white rounded-lg">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="px-3 py-1 text-gray-700 hover:bg-gray-200 rounded-lg">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
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
                    <input type="hidden" id="payment_contract_id" name="contract_id">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Номер платежа</label>
                        <input type="text" name="payment_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Дата платежа</label>
                            <input type="date" name="payment_date" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Сумма</label>
                            <input type="number" name="amount" step="0.01" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Год</label>
                            <input type="number" name="year" min="2020" max="2050" required
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
function toggleDropdown(contractId) {
    const dropdown = document.getElementById(`dropdown-${contractId}`);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');

    // Close all other dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== `dropdown-${contractId}`) {
            d.classList.add('hidden');
        }
    });

    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('[onclick^="toggleDropdown"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.classList.add('hidden'));
    }
});

function addPayment(contractId) {
    document.getElementById('payment_contract_id').value = contractId;
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

function generateDemandNotice(contractId) {
    window.open(`/contracts/${contractId}/demand-notice`, '_blank');
}

function createAmendment(contractId) {
    // Redirect to amendment creation page
    window.location.href = `/contracts/${contractId}/amendments/create`;
}

function cancelContract(contractId) {
    if (confirm('Вы уверены, что хотите отменить этот договор?')) {
        // Redirect to cancellation page
        window.location.href = `/contracts/${contractId}/cancel`;
    }
}

function exportContracts() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = `{{ route('contracts.index') }}?${params.toString()}`;
}
</script>
@endpush
