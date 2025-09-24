@extends('layouts.app')

@section('title', 'Добавить пользователя')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
        Назад к списку
    </a>
</div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Добавить пользователя</h1>

        @include('partials.flash-messages')

        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf
            @include('users.form', ['user' => new \App\Models\User()])

            <div class="flex justify-end space-x-3">
                <a href="{{ route('users.index') }}" class="btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Отмена
                </a>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save" class="w-4 h-4 mr-2"></i>
                    Создать пользователя
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/feather-icons"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>
@endsection
