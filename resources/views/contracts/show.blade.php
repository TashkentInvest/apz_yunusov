@extends('layouts.app')

@section('title', 'Договор ' . $contract->contract_number . ' - АПЗ Система')
@section('page-title', 'Детали договора')

@section('header-actions')
<div class="flex space-x-3">
    @if($contract->remaining_debt > 0)
        <a href="{{ route('documents.demand-notice', $contract) }}" target="_blank"
           class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
            <i data-feather="alert-triangle" class="w-4 h-4 mr-2 inline"></i>
            Требование
        </a>
    @endif
    <button onclick="createAmendment()"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="file-plus" class="w-4 h-4 mr-2 inline"></i>
        Доп. соглашение
    </button>
    <a href="{{ route('contracts.edit', $contract) }}"
       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="edit-2" class="w-4 h-4 mr-2 inline"></i>
        Редактировать
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Contract Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $contract->contract_number }}</h2>
                <p class="text-gray-600 mt-1">от {{ $contract->contract_date->format('d.m.Y') }}</p>
            </div>
            <div class="text-right">
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                      style="background-color: {{ $contract->status->color }}20; color: {{ $contract->status->color }}">
                    {{ $contract->status->name_ru }}
                </span>
                @if($contract->completion_date)
                    <p class="text-sm text-gray-500 mt-1">
                        Завершение: {{ $contract->completion_date->format('d.m.Y') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600">Общая сумма</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    {{ number_format($contract->total_amount) }} сум
                </p>
                <p class="text-sm text-gray-500">{{ number_format($contract->total_amount / 1000000, 1) }} млн</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600">Оплачено</p>
                <p class="text-2xl font-bold text-green-600 mt-1">
                    {{ number_format($contract->total_paid) }} сум
                </p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $contract->payment_percent }}%"></div>
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ $contract->payment_percent }}% выполнено</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600">Остаток к доплате</p>
                <p class="text-2xl font-bold {{ $contract->remaining_debt > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                    {{ number_format($contract->remaining_debt) }} сум
                </p>
                @if($penalties['total_penalty'] > 0)
                    <p class="text-sm text-red-600 mt-1">
                        +{{ number_format($penalties['total_penalty']) }} пеня
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Contract Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Subject Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Информация о заказчике</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-600">Название/ФИО</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $contract->subject->display_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-600">{{ $contract->subject->is_legal_entity ? 'ИНН' : 'ПИНФЛ' }}</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $contract->subject->identifier }}</dd>
                </div>
                @if($contract->subject->phone)
                <div>
                    <dt class="text-sm font-medium text-gray-600">Телефон</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $contract->subject->phone }}</dd>
                </div>
                @endif
                @if($contract->subject->email)
                <div>
                    <dt class="text-sm font-medium text-gray-600">Email</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $contract->subject->email }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-600">Адрес</dt>
                    <dd class="text-sm text-gray-900 mt-1">
                        {{ $contract->subject->legal_address ?: $contract->subject->physical_address }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Object Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Информация об объекте</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-600">Район</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $contract->object->district->name_ru }}</dd>
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
                    <dd class="text-sm text-gray-900 mt-1">{{ number_format($contract->contract_volume, 2) }} м³</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-600">Коэффициент</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $contract->coefficient }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Payment Schedule -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">График платежей</h3>
                <button onclick="addPayment()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
                    Добавить платеж
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Период</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Сумма по плану</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Фактически</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Остаток</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($contract->paymentSchedules as $schedule)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $schedule->quarter }} кв. {{ $schedule->year }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ number_format($schedule->quarter_amount) }} сум
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ number_format($schedule->paid_amount) }} сум
                            </td>
                            <td class="px-6 py-4 text-sm {{ $schedule->remaining_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($schedule->remaining_amount) }} сум
                            </td>
                            <td class="px-6 py-4">
                                @if($schedule->remaining_amount <= 0)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Оплачено
                                    </span>
                                @elseif($schedule->is_overdue)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Просрочено
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        В процессе
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">История платежей</h3>
        </div>

        <div class="p-6">
            @forelse($contract->actualPayments->take(10) as $payment)
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                            <i data-feather="credit-card" class="w-4 h-4 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $payment->payment_number }}</p>
                            <p class="text-xs text-gray-500">{{ $payment->quarter }} кв. {{ $payment->year }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-green-600">+{{ number_format($payment->amount) }} сум</p>
                        <p class="text-xs text-gray-500">{{ $payment->payment_date->format('d.m.Y') }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <i data-feather="credit-card" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                    <p class="text-gray-500">Платежи отсутствуют</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Amendments -->
    @if($contract->amendments->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Дополнительные соглашения</h3>
        </div>

        <div class="p-6 space-y-4">
            @foreach($contract->amendments as $amendment)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium text-gray-900">
                            Соглашение №{{ $amendment->amendment_number }}
                        </h4>
                        <span class="text-sm text-gray-500">{{ $amendment->amendment_date->format('d.m.Y') }}</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">{{ $amendment->reason }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        @if($amendment->old_volume != $amendment->new_volume)
                        <div>
                            <span class="text-gray-500">Объем:</span>
                            <span class="text-red-600">{{ number_format($amendment->old_volume, 2) }}</span>
                            →
                            <span class="text-green-600">{{ number_format($amendment->new_volume, 2) }} м³</span>
                        </div>
                        @endif

                        @if($amendment->old_coefficient != $amendment->new_coefficient)
                        <div>
                            <span class="text-gray-500">Коэффициент:</span>
                            <span class="text-red-600">{{ $amendment->old_coefficient }}</span>
                            →
                            <span class="text-green-600">{{ $amendment->new_coefficient }}</span>
                        </div>
                        @endif

                        @if($amendment->old_amount != $amendment->new_amount)
                        <div>
                            <span class="text-gray-500">Сумма:</span>
                            <span class="text-red-600">{{ number_format($amendment->old_amount / 1000000, 1) }}М</span>
                            →
                            <span class="text-green-600">{{ number_format($amendment->new_amount / 1000000, 1) }}М</span>
                        </div>
                        @endif
                    </div>

                    <div class="mt-3 flex justify-end">
                        <a href="{{ route('documents.amendment', [$contract, $amendment]) }}" target="_blank"
                           class="text-blue-600 hover:text-blue-700 text-sm">
                            <i data-feather="file-text" class="w-4 h-4 mr-1 inline"></i>
                            Скачать документ
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Penalty Calculation -->
    @if(count($penalties['penalties']) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Расчет пени</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Период</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Сумма к доплате</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Просрочено дней</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Пеня</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Итого</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($penalties['penalties'] as $penalty)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $penalty['quarter'] }} кв. {{ $penalty['year'] }}</td>
                            <td class="px-6 py-4 text-sm text-red-600">{{ number_format($penalty['unpaid_amount']) }} сум</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $penalty['overdue_days'] }}</td>
                            <td class="px-6 py-4 text-sm text-red-600">{{ number_format($penalty['penalty_amount']) }} сум</td>
                            <td class="px-6 py-4 text-sm font-medium text-red-600">
                                {{ number_format($penalty['unpaid_amount'] + $penalty['penalty_amount']) }} сум
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-sm font-medium text-gray-900">Общая сумма пени:</td>
                        <td class="px-6 py-3 text-sm font-bold text-red-600">
                            {{ number_format($penalties['total_penalty']) }} сум
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
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
                    <input type="hidden" name="contract_id" value="{{ $contract->id }}">

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

<!-- Create Amendment Modal -->
<div id="createAmendmentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-2xl sm:w-full">
            <form id="createAmendmentForm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Создать дополнительное соглашение</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <input type="hidden" name="contract_id" value="{{ $contract->id }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Причина изменения</label>
                        <textarea name="reason" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Укажите причину создания дополнительного соглашения..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Новый объем (текущий: {{ number_format($contract->contract_volume, 2) }} м³)
                            </label>
                            <input type="number" name="new_volume" step="0.01"
                                   value="{{ $contract->contract_volume }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Новый коэффициент (текущий: {{ $contract->coefficient }})
                            </label>
                            <input type="number" name="new_coefficient" step="0.01"
                                   value="{{ $contract->coefficient }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Изменения в банковских реквизитах</label>
                        <textarea name="bank_changes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Укажите изменения в банковских реквизитах (если есть)..."></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('createAmendmentModal')"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Создать соглашение
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

function createAmendment() {
    document.getElementById('createAmendmentModal').classList.remove('hidden');
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
        const response = await fetch('{{ route("payments.store") }}', {
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

document.getElementById('createAmendmentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');

    toggleLoading(submitButton, true);

    try {
        const response = await fetch('{{ route("contracts.amendments.store", $contract) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeModal('createAmendmentModal');
            showSuccessMessage(result.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Ошибка при создании дополнительного соглашения');
        }
    } catch (error) {
        console.error('Error:', error);
        handleAjaxError({ responseJSON: { message: error.message } });
    } finally {
        toggleLoading(submitButton, false);
    }
});
</script>
@endpush
