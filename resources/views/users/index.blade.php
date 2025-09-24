@extends('layouts.app')

@section('title', 'Управление пользователями')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i data-feather="plus" class="w-4 h-4 mr-2"></i>
        Добавить пользователя
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Управление пользователями</h1>

        <!-- Filters -->
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Имя, email, отдел..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Роль</label>
                <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <option value="all">Все роли</option>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <option value="all">Все статусы</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Активные</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Неактивные</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Отдел</label>
                <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <option value="all">Все отделы</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}" {{ request('department') === $department ? 'selected' : '' }}>
                            {{ $department }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="btn btn-secondary">
                    <i data-feather="filter" class="w-4 h-4 mr-1"></i>
                    Фильтр
                </button>
                <a href="{{ route('users.index') }}" class="btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                    <i data-feather="x" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    @include('partials.flash-messages')

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Пользователь
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Роль
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Отдел
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Контакты
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Статус
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Создан
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Действия
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' :
                                   ($user->role === 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $roles[$user->role] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->department ?? 'Не указан' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->phone ?? 'Не указан' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Активен' : 'Неактивен' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Редактировать">
                                    <i data-feather="edit-2" class="w-4 h-4"></i>
                                </a>

                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                           class="{{ $user->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}"
                                           title="{{ $user->is_active ? 'Деактивировать' : 'Активировать' }}">
                                        <i data-feather="{{ $user->is_active ? 'user-x' : 'user-check' }}" class="w-4 h-4"></i>
                                    </button>
                                </form>

                                <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                       class="text-red-600 hover:text-red-900"
                                       title="Удалить">
                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Пользователи не найдены
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<script src="https://unpkg.com/feather-icons"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});

function deleteUser(userId, userName) {
    if (confirm(`Вы уверены, что хотите удалить пользователя "${userName}"?\n\nЭто действие необратимо!`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/users/${userId}`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="DELETE">
        `;

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
