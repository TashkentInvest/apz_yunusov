@extends('layouts.app')

@section('title', 'Создать заказчика - АПЗ Система')
@section('page-title', 'Создание нового заказчика')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('subjects.index') }}"
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2 inline"></i>
        Назад к списку
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('subjects.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Тип заказчика -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Тип заказчика</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative">
                    <input type="radio" name="is_legal_entity" value="1" {{ old('is_legal_entity', '1') == '1' ? 'checked' : '' }}
                           onchange="toggleEntityType()" class="sr-only">
                    <div class="entity-type-card p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                <i data-feather="briefcase" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Юридическое лицо</p>
                                <p class="text-sm text-gray-500">Компании, ООО, АО и другие</p>
                            </div>
                        </div>
                    </div>
                </label>

                <label class="relative">
                    <input type="radio" name="is_legal_entity" value="0" {{ old('is_legal_entity') == '0' ? 'checked' : '' }}
                           onchange="toggleEntityType()" class="sr-only">
                    <div class="entity-type-card p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-300 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                                <i data-feather="user" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Физическое лицо</p>
                                <p class="text-sm text-gray-500">Частные лица, ИП</p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Информация о юридическом лице -->
        <div id="legalEntityForm" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Информация о юридическом лице</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название компании *</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder='ООО "Название компании"'>
                    @error('company_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Организационная форма</label>
                    <select name="org_form_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите форму</option>
                        @foreach(\App\Models\OrgForm::where('is_active', true)->get() as $orgForm)
                            <option value="{{ $orgForm->id }}" {{ old('org_form_id') == $orgForm->id ? 'selected' : '' }}>
                                {{ $orgForm->name_ru }}
                            </option>
                        @endforeach
                    </select>
                    @error('org_form_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ИНН *</label>
                    <input type="text" name="inn" value="{{ old('inn') }}" maxlength="9"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="123456789">
                    @error('inn')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ОКЭД</label>
                    <input type="text" name="oked" value="{{ old('oked') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="12345">
                    @error('oked')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Юридический адрес</label>
                <textarea name="legal_address" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('legal_address') }}</textarea>
                @error('legal_address')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Информация о физическом лице -->
        <div id="physicalPersonForm" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" style="display: none;">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Информация о физическом лице</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип документа *</label>
                    <select name="document_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите тип документа</option>
                        <option value="passport" {{ old('document_type') == 'passport' ? 'selected' : '' }}>Паспорт</option>
                        <option value="id_card" {{ old('document_type') == 'id_card' ? 'selected' : '' }}>ID карта</option>
                        <option value="birth_certificate" {{ old('document_type') == 'birth_certificate' ? 'selected' : '' }}>Свидетельство о рождении</option>
                    </select>
                    @error('document_type')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Серия документа</label>
                    <input type="text" name="document_series" value="{{ old('document_series') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="AA">
                    @error('document_series')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Номер документа *</label>
                    <input type="text" name="document_number" value="{{ old('document_number') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="1234567">
                    @error('document_number')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ПИНФЛ *</label>
                    <input type="text" name="pinfl" value="{{ old('pinfl') }}" maxlength="14"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="12345678901234">
                    @error('pinfl')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Кем выдан</label>
                    <input type="text" name="issued_by" value="{{ old('issued_by') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="МВД РУз">
                    @error('issued_by')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата выдачи</label>
                    <input type="date" name="issued_date" value="{{ old('issued_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('issued_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Банковские реквизиты -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Банковские реквизиты</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название банка</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="АКБ Узпромстройбанк">
                    @error('bank_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">МФО банка</label>
                    <input type="text" name="bank_code" value="{{ old('bank_code') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="00123">
                    @error('bank_code')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Расчетный счет</label>
                    <input type="text" name="bank_account" value="{{ old('bank_account') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="20208000500123456789">
                    @error('bank_account')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Контактная информация -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Контактная информация</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="+998 90 123 45 67">
                    @error('phone')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="example@email.com">
                    @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Физический адрес</label>
                <textarea name="physical_address" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="г. Ташкент, ул. Примерная, дом 1">{{ old('physical_address') }}</textarea>
                @error('physical_address')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Дополнительная информация -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Дополнительная информация</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="is_resident" value="1" {{ old('is_resident', '1') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Резидент Узбекистана</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Страна</label>
                    <select name="country_code"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="UZ" {{ old('country_code', 'UZ') == 'UZ' ? 'selected' : '' }}>Узбекистан</option>
                        <option value="RU" {{ old('country_code') == 'RU' ? 'selected' : '' }}>Россия</option>
                        <option value="KZ" {{ old('country_code') == 'KZ' ? 'selected' : '' }}>Казахстан</option>
                        <option value="KG" {{ old('country_code') == 'KG' ? 'selected' : '' }}>Кыргызстан</option>
                        <option value="TJ" {{ old('country_code') == 'TJ' ? 'selected' : '' }}>Таджикистан</option>
                        <option value="TM" {{ old('country_code') == 'TM' ? 'selected' : '' }}>Туркменистан</option>
                    </select>
                    @error('country_code')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Кнопки -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('subjects.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Отмена
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-feather="save" class="w-4 h-4 mr-2 inline"></i>
                Создать заказчика
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleEntityType() {
    const isLegalEntity = document.querySelector('input[name="is_legal_entity"]:checked').value === '1';
    const legalEntityForm = document.getElementById('legalEntityForm');
    const physicalPersonForm = document.getElementById('physicalPersonForm');

    if (isLegalEntity) {
        legalEntityForm.style.display = 'block';
        physicalPersonForm.style.display = 'none';

        // Update required fields
        setFieldRequired('company_name', true);
        setFieldRequired('inn', true);
        setFieldRequired('document_type', false);
        setFieldRequired('document_number', false);
        setFieldRequired('pinfl', false);
    } else {
        legalEntityForm.style.display = 'none';
        physicalPersonForm.style.display = 'block';

        // Update required fields
        setFieldRequired('company_name', false);
        setFieldRequired('inn', false);
        setFieldRequired('document_type', true);
        setFieldRequired('document_number', true);
        setFieldRequired('pinfl', true);
    }

    // Update card styles
    updateCardStyles();
}

function setFieldRequired(fieldName, required) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (field) {
        if (required) {
            field.setAttribute('required', 'required');
        } else {
            field.removeAttribute('required');
        }
    }
}

function updateCardStyles() {
    const cards = document.querySelectorAll('.entity-type-card');
    const checkedInput = document.querySelector('input[name="is_legal_entity"]:checked');

    cards.forEach((card, index) => {
        const input = card.parentElement.querySelector('input[type="radio"]');
        if (input.checked) {
            if (input.value === '1') {
                card.classList.add('border-blue-500', 'bg-blue-50');
                card.classList.remove('border-gray-200');
            } else {
                card.classList.add('border-green-500', 'bg-green-50');
                card.classList.remove('border-gray-200');
            }
        } else {
            card.classList.remove('border-blue-500', 'bg-blue-50', 'border-green-500', 'bg-green-50');
            card.classList.add('border-gray-200');
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleEntityType();

    // Format phone input
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('998')) {
                value = value.substring(3);
            }
            if (value.length > 0) {
                let formatted = '+998';
                if (value.length > 0) formatted += ' ' + value.substring(0, 2);
                if (value.length > 2) formatted += ' ' + value.substring(2, 5);
                if (value.length > 5) formatted += ' ' + value.substring(5, 7);
                if (value.length > 7) formatted += ' ' + value.substring(7, 9);
                e.target.value = formatted;
            }
        });
    }

    // Format INN input
    const innInput = document.querySelector('input[name="inn"]');
    if (innInput) {
        innInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 9);
        });
    }

    // Format PINFL input
    const pinflInput = document.querySelector('input[name="pinfl"]');
    if (pinflInput) {
        pinflInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 14);
        });
    }
});
</script>
@endpush
