<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Имя *</label>
        <input type="text" name="name" required
               value="{{ old('name', isset($user) ? $user->name : '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 @error('name') border-red-300 @enderror">
        @error('name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
        <input type="email" name="email" required
               value="{{ old('email', isset($user) ? $user->email : '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 @error('email') border-red-300 @enderror">
        @error('email')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ isset($user) ? 'Новый пароль' : 'Пароль' }} {{ !isset($user) ? '*' : '' }}
        </label>
        <input type="password" name="password" {{ !isset($user) ? 'required' : '' }}
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 @error('password') border-red-300 @enderror">
        @if(isset($user))
            <p class="text-sm text-gray-500 mt-1">Оставьте пустым, если не хотите изменять пароль</p>
        @endif
        @error('password')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ isset($user) ? 'Подтвердите новый пароль' : 'Подтвердите пароль' }} {{ !isset($user) ? '*' : '' }}
        </label>
        <input type="password" name="password_confirmation" {{ !isset($user) ? 'required' : '' }}
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Роль *</label>
        <select name="role" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 @error('role') border-red-300 @enderror">
            <option value="">Выберите роль</option>
            <option value="admin" {{ old('role', isset($user) ? $user->role : '') === 'admin' ? 'selected' : '' }}>
                Администратор
            </option>
            <option value="manager" {{ old('role', isset($user) ? $user->role : '') === 'manager' ? 'selected' : '' }}>
                Менеджер
            </option>
            <option value="employee" {{ old('role', isset($user) ? $user->role : '') === 'employee' ? 'selected' : '' }}>
                Сотрудник
            </option>
        </select>
        @error('role')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
        <input type="text" name="phone"
               value="{{ old('phone', isset($user) ? $user->phone : '') }}"
               placeholder="+998 xx xxx xx xx"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 @error('phone') border-red-300 @enderror">
        @error('phone')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Отдел</label>
    <input type="text" name="department"
           value="{{ old('department', isset($user) ? $user->department : '') }}"
           placeholder="Например: IT отдел, Договоры, Платежи"
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 @error('department') border-red-300 @enderror">
    @error('department')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <div class="flex items-center">
        <input type="checkbox" name="is_active" id="is_active" value="1"
               {{ old('is_active', isset($user) ? $user->is_active : true) ? 'checked' : '' }}
               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
        <label for="is_active" class="ml-2 block text-sm text-gray-900">
            Активный пользователь
        </label>
    </div>
    <p class="text-sm text-gray-500 mt-1">Неактивные пользователи не могут войти в систему</p>
</div>
