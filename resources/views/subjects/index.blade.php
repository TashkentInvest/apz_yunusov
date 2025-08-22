@extends('layouts.app')

@section('title', 'Заказчики - АПЗ Система')
@section('page-title', 'Управление заказчиками')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('subjects.create') }}"
       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
        Новый заказчик
    </a>
    <button onclick="exportSubjects()"
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
                    <p class="text-sm font-medium text-gray-600">Всего заказчиков</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $subjects->total() }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="users" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Юридические лица</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">
                        {{ $subjects->where('is_legal_entity', true)->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="briefcase" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Физические лица</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        {{ $subjects->where('is_legal_entity', false)->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <i data-feather="user" class="w-5 h-5 text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">С активными договорами</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">
                        {{ $subjects->filter(function($subject) { return $subject->contracts->where('is_active', true)->count() > 0; })->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                    <i data-feather="file-text" class="w-5 h-5 text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('subjects.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Название, ИНН, ПИНФЛ">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Тип заказчика</label>
                <select name="is_legal_entity"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Все типы</option>
                    <option value="1" {{ request('is_legal_entity') === '1' ? 'selected' : '' }}>Юридические лица</option>
                    <option value="0" {{ request('is_legal_entity') === '0' ? 'selected' : '' }}>Физические лица</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Сортировка</label>
                <select name="sort"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>По дате создания ↓</option>
                    <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>По дате создания ↑</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>По названию ↑</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>По названию ↓</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="search" class="w-4 h-4 mr-2 inline"></i>
                    Поиск
                </button>
                <a href="{{ route('subjects.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i data-feather="x" class="w-4 h-4 mr-2 inline"></i>
                    Сброс
                </a>
            </div>
        </form>
    </div>

    <!-- Subjects Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Список заказчиков</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название/ФИО</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ИНН/ПИНФЛ</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Контакты</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Договоры</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Создан</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($subjects as $index => $subject)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ ($subjects->currentPage() - 1) * $subjects->perPage() + $index + 1 }}
                            </td>
                            <td class="px-6 py-4">
                                @if($subject->is_legal_entity)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <i data-feather="briefcase" class="w-3 h-3 mr-1"></i>
                                        Юр. лицо
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i data-feather="user" class="w-3 h-3 mr-1"></i>
                                        Физ. лицо
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $subject->display_name }}</div>
                                @if($subject->is_legal_entity && $subject->org_form_id)
                                    <div class="text-xs text-gray-500">{{ $subject->orgForm->name_ru ?? '' }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $subject->identifier }}</div>
                                @if($subject->is_legal_entity && $subject->oked)
                                    <div class="text-xs text-gray-500">ОКЭД: {{ $subject->oked }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($subject->phone)
                                    <div class="text-sm text-gray-900">
                                        <i data-feather="phone" class="w-3 h-3 inline mr-1"></i>
                                        {{ $subject->phone }}
                                    </div>
                                @endif
                                @if($subject->email)
                                    <div class="text-sm text-gray-500">
                                        <i data-feather="mail" class="w-3 h-3 inline mr-1"></i>
                                        {{ $subject->email }}
                                    </div>
                                @endif
                                @if(!$subject->phone && !$subject->email)
                                    <span class="text-sm text-gray-400">Не указано</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $activeContracts = $subject->contracts->where('is_active', true)->count();
                                    $totalContracts = $subject->contracts->count();
                                @endphp
                                <div class="text-sm font-medium text-gray-900">{{ $activeContracts }} активных</div>
                                <div class="text-xs text-gray-500">Всего: {{ $totalContracts }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $subject->created_at->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('subjects.show', $subject) }}"
                                       class="text-gray-400 hover:text-gray-600" title="Просмотр">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('subjects.edit', $subject) }}"
                                       class="text-gray-400 hover:text-blue-600" title="Редактировать">
                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    @if($subject->phone)
                                        <button onclick="callSubject('{{ $subject->phone }}')"
                                                class="text-gray-400 hover:text-green-600" title="Позвонить">
                                            <i data-feather="phone" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                    @if($subject->email)
                                        <button onclick="emailSubject('{{ $subject->email }}')"
                                                class="text-gray-400 hover:text-purple-600" title="Написать email">
                                            <i data-feather="mail" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                    <div class="relative">
                                        <button onclick="toggleDropdown({{ $subject->id }})"
                                                class="text-gray-400 hover:text-gray-600" title="Еще">
                                            <i data-feather="more-horizontal" class="w-4 h-4"></i>
                                        </button>
                                        <div id="dropdown-{{ $subject->id }}"
                                             class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                            <div class="py-1">
                                                <a href="{{ route('contracts.create') }}?subject_id={{ $subject->id }}"
                                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
                                                    Создать договор
                                                </a>
                                                <button onclick="duplicateSubject({{ $subject->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-feather="copy" class="w-4 h-4 mr-2 inline"></i>
                                                    Дублировать
                                                </button>
                                                <hr class="my-1">
                                                <button onclick="deleteSubject({{ $subject->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                    <i data-feather="trash-2" class="w-4 h-4 mr-2 inline"></i>
                                                    Удалить
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i data-feather="users" class="w-12 h-12 text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2">Заказчики не найдены</p>
                                    <p class="text-gray-500 mb-4">Попробуйте изменить параметры поиска</p>
                                    <a href="{{ route('subjects.create') }}"
                                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        Создать первого заказчика
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($subjects->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Показано {{ $subjects->firstItem() }}-{{ $subjects->lastItem() }} из {{ $subjects->total() }} заказчиков
                    </div>
                    <div class="flex space-x-1">
                        {{ $subjects->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleDropdown(subjectId) {
    const dropdown = document.getElementById(`dropdown-${subjectId}`);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');

    // Close all other dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== `dropdown-${subjectId}`) {
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

function duplicateSubject(subjectId) {
    if (confirm('Создать копию этого заказчика?')) {
        window.location.href = `/subjects/create?duplicate=${subjectId}`;
    }
}

function deleteSubject(subjectId) {
    if (confirm('Вы уверены, что хотите удалить этого заказчика? Это действие нельзя отменить.')) {
        fetch(`/subjects/${subjectId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('Заказчик успешно удален');
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Ошибка при удалении заказчика');
            }
        })
        .catch(error => {
            handleAjaxError({ responseJSON: { message: error.message } });
        });
    }
}

function exportSubjects() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `/export/subjects?${params.toString()}`;
}
</script>
@endpush
