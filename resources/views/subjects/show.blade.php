@extends('layouts.app')

@section('title', $subject->display_name . ' - АПЗ Система')
@section('page-title', 'Информация о заказчике')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('subjects.edit', $subject) }}"
       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="edit-2" class="w-4 h-4 mr-2 inline"></i>
        Редактировать
    </a>
    <a href="{{ route('contracts.create') }}?subject_id={{ $subject->id }}"
       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
        Новый договор
    </a>
    <a href="{{ route('subjects.index') }}"
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2 inline"></i>
        К списку
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br {{ $subject->is_legal_entity ? 'from-blue-500 to-blue-600' : 'from-green-500 to-green-600' }} rounded-xl flex items-center justify-center">
                    <i data-feather="{{ $subject->is_legal_entity ? 'briefcase' : 'user' }}" class="w-8 h-8 text-white"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $subject->display_name }}</h2>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $subject->is_legal_entity ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                            {{ $subject->is_legal_entity ? 'Юридическое лицо' : 'Физическое лицо' }}
                        </span>
                        <span class="text-sm text-gray-500">
                            {{ $subject->is_legal_entity ? 'ИНН' : 'ПИНФЛ' }}: {{ $subject->identifier }}
                        </span>
                        <span class="text-sm text-gray-500">
                            Создан: {{ $subject->created_at->format('d.m.Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="text-right">
                @if($subject->contracts->where('is_active', true)->count() > 0)
                    <div class="text-sm font-medium text-green-600">
                        {{ $subject->contracts->where('is_active', true)->count() }} активных договоров
                    </div>
                @else
                    <div class="text-sm text-gray-500">Нет активных договоров</div>
                @endif
                <div class="text-xs text-gray-500 mt-1">
                    Всего договоров: {{ $subject->contracts->count() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Main Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Основная информация</h3>

            <dl class="space-y-4">
                @if($subject->is_legal_entity)
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Название компании</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $subject->company_name }}</dd>
                    </div>

                    @if($subject->orgForm)
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Организационная форма</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $subject->orgForm->name_ru }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-600">ИНН</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $subject->inn }}</dd>
                    </div>

                    @if($subject->oked)
                        <div>
                            <dt class="text-sm font-medium text-gray-600">ОКЭД</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $subject->oked }}</dd>
                        </div>
                    @endif
                @else
                    <div>
                        <dt class="text-sm font-medium text-gray-600">ПИНФЛ</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $subject->pinfl }}</dd>
                    </div>

                    @if($subject->document_type)
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Документ</dt>
                            <dd class="text-sm text-gray-900 mt-1">
                                {{ ucfirst($subject->document_type) }}
                                @if($subject->document_series)
                                    {{ $subject->document_series }}
                                @endif
                                {{ $subject->document_number }}
                            </dd>
                        </div>
                    @endif

                    @if($subject->issued_by)
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Выдан</dt>
                            <dd class="text-sm text-gray-900 mt-1">
                                {{ $subject->issued_by }}
                                @if($subject->issued_date)
                                    ({{ $subject->issued_date->format('d.m.Y') }})
                                @endif
                            </dd>
                        </div>
                    @endif
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-600">Резидентство</dt>
                    <dd class="text-sm text-gray-900 mt-1">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $subject->is_resident ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $subject->is_resident ? 'Резидент' : 'Нерезидент' }}
                        </span>
                        ({{ $subject->country_code }})
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Контактная информация</h3>

            <dl class="space-y-4">
                @if($subject->phone)
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Телефон</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            <a href="tel:{{ $subject->phone }}" class="text-blue-600 hover:text-blue-700">
                                {{ $subject->phone }}
                            </a>
                            <button onclick="callSubject('{{ $subject->phone }}')"
                                    class="ml-2 text-green-600 hover:text-green-700" title="Позвонить">
                                <i data-feather="phone" class="w-4 h-4 inline"></i>
                            </button>
                        </dd>
                    </div>
                @endif

                @if($subject->email)
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Email</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            <a href="mailto:{{ $subject->email }}" class="text-blue-600 hover:text-blue-700">
                                {{ $subject->email }}
                            </a>
                            <button onclick="emailSubject('{{ $subject->email }}')"
                                    class="ml-2 text-purple-600 hover:text-purple-700" title="Написать письмо">
                                <i data-feather="mail" class="w-4 h-4 inline"></i>
                            </button>
                        </dd>
                    </div>
                @endif

                @if($subject->legal_address)
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Юридический адрес</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $subject->legal_address }}</dd>
                    </div>
                @endif

                @if($subject->physical_address)
                    <div>
                        <dt class="text-sm font-medium text-gray-600">Физический адрес</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $subject->physical_address }}</dd>
                    </div>
                @endif

                @if(!$subject->phone && !$subject->email && !$subject->legal_address && !$subject->physical_address)
                    <div class="text-center py-8">
                        <i data-feather="info" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                        <p class="text-gray-500">Контактная информация не указана</p>
                        <a href="{{ route('subjects.edit', $subject) }}"
                           class="text-blue-600 hover:text-blue-700 text-sm">
                            Добавить контакты
                        </a>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Banking Information -->
    @if($subject->bank_name || $subject->bank_code || $subject->bank_account)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Банковские реквизиты</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if($subject->bank_name)
                <div>
                    <dt class="text-sm font-medium text-gray-600">Название банка</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $subject->bank_name }}</dd>
                </div>
            @endif

            @if($subject->bank_code)
                <div>
                    <dt class="text-sm font-medium text-gray-600">МФО</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $subject->bank_code }}</dd>
                </div>
            @endif

            @if($subject->bank_account)
                <div>
                    <dt class="text-sm font-medium text-gray-600">Расчетный счет</dt>
                    <dd class="text-sm text-gray-900 mt-1 font-mono">{{ $subject->bank_account }}</dd>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Contracts -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Договоры заказчика</h3>
                <a href="{{ route('contracts.create') }}?subject_id={{ $subject->id }}"
                   class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                    <i data-feather="plus" class="w-4 h-4 mr-1 inline"></i>
                    Новый договор
                </a>
            </div>
        </div>

        @if($subject->contracts->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Номер</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Район</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Сумма</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Оплачено</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($subject->contracts->sortByDesc('created_at') as $contract)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $contract->contract_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $contract->contract_volume }} м³</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $contract->contract_date->format('d.m.Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $contract->object->district->name_ru ?? 'Не указан' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ number_format($contract->total_amount / 1000000, 1) }}М
                                    </div>
                                    <div class="text-xs text-gray-500">{{ number_format($contract->total_amount) }} сум</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-green-600">
                                        {{ number_format($contract->total_paid / 1000000, 1) }}М
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                        <div class="bg-green-600 h-1.5 rounded-full"
                                             style="width: {{ $contract->payment_percent }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $contract->payment_percent }}%</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          style="background-color: {{ $contract->status->color }}20; color: {{ $contract->status->color }}">
                                        {{ $contract->status->name_ru }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('contracts.show', $contract) }}"
                                           class="text-gray-400 hover:text-gray-600" title="Просмотр">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('contracts.edit', $contract) }}"
                                           class="text-gray-400 hover:text-blue-600" title="Редактировать">
                                            <i data-feather="edit-2" class="w-4 h-4"></i>
                                        </a>
                                        @if($contract->remaining_debt > 0)
                                            <a href="{{ route('documents.demand-notice', $contract) }}" target="_blank"
                                               class="text-gray-400 hover:text-red-600" title="Требование">
                                                <i data-feather="alert-triangle" class="w-4 h-4"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <i data-feather="file-text" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                <p class="text-lg font-medium text-gray-900 mb-2">Договоры отсутствуют</p>
                <p class="text-gray-500 mb-4">У данного заказчика пока нет договоров</p>
                <a href="{{ route('contracts.create') }}?subject_id={{ $subject->id }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Создать первый договор
                </a>
            </div>
        @endif
    </div>

    <!-- Statistics -->
    @if($subject->contracts->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Общая сумма договоров</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">
                        {{ number_format($subject->contracts->sum('total_amount') / 1000000, 1) }}М
                    </p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="dollar-sign" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Всего оплачено</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        {{ number_format($subject->contracts->sum('total_paid') / 1000000, 1) }}М
                    </p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Задолженность</p>
                    <p class="text-2xl font-bold {{ $subject->contracts->sum('remaining_debt') > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                        {{ number_format($subject->contracts->sum('remaining_debt') / 1000000, 1) }}М
                    </p>
                </div>
                <div class="w-10 h-10 {{ $subject->contracts->sum('remaining_debt') > 0 ? 'bg-red-50' : 'bg-green-50' }} rounded-lg flex items-center justify-center">
                    <i data-feather="{{ $subject->contracts->sum('remaining_debt') > 0 ? 'alert-triangle' : 'check-circle' }}"
                       class="w-5 h-5 {{ $subject->contracts->sum('remaining_debt') > 0 ? 'text-red-600' : 'text-green-600' }}"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Действия</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('subjects.edit', $subject) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center">
                <i data-feather="edit-2" class="w-4 h-4 mr-2 inline"></i>
                Редактировать
            </a>

            <button onclick="duplicateSubject()"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i data-feather="copy" class="w-4 h-4 mr-2 inline"></i>
                Дублировать
            </button>

            <button onclick="generateSubjectReport()"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i data-feather="file-text" class="w-4 h-4 mr-2 inline"></i>
                Отчет
            </button>

            <button onclick="deleteSubject()"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i data-feather="trash-2" class="w-4 h-4 mr-2 inline"></i>
                Удалить
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function callSubject(phone) {
    if (confirm(`Позвонить по номеру ${phone}?`)) {
        window.open(`tel:${phone}`);
    }
}

function emailSubject(email) {
    if (confirm(`Отправить письмо на ${email}?`)) {
        window.open(`mailto:${email}`);
    }
}

function duplicateSubject() {
    if (confirm('Создать копию этого заказчика?')) {
        window.location.href = `{{ route('subjects.create') }}?duplicate={{ $subject->id }}`;
    }
}

function generateSubjectReport() {
    const button = event.target;
    toggleLoading(button, true);

    // Simulate report generation
    setTimeout(() => {
        toggleLoading(button, false);
        showSuccessMessage('Отчет по заказчику сгенерирован и отправлен на email');

        // Open report in new window
        window.open(`/subjects/{{ $subject->id }}/report`, '_blank');
    }, 2000);
}

function deleteSubject() {
    const hasActiveContracts = {{ $subject->contracts->where('is_active', true)->count() }};

    if (hasActiveContracts > 0) {
        alert('Нельзя удалить заказчика с активными договорами. Сначала завершите или отмените все договоры.');
        return;
    }

    if (confirm('Вы уверены, что хотите удалить этого заказчика? Это действие нельзя отменить.')) {
        fetch(`{{ route('subjects.destroy', $subject) }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('Заказчик успешно удален');
                setTimeout(() => {
                    window.location.href = '{{ route("subjects.index") }}';
                }, 1000);
            } else {
                throw new Error(data.message || 'Ошибка при удалении заказчика');
            }
        })
        .catch(error => {
            handleAjaxError({ responseJSON: { message: error.message } });
        });
    }
}
</script>
@endpush
