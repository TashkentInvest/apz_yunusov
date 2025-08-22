@extends('layouts.app')

@section('title', 'Создать объект - АПЗ Система')
@section('page-title', 'Создание нового объекта')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('objects.index') }}"
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2 inline"></i>
        Назад к списку
    </a>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <form action="{{ route('objects.store') }}" method="POST" class="space-y-6" id="objectForm">
        @csrf

        <!-- Основная информация -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Основная информация об объекте</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Заказчик *</label>
                    <select name="subject_id" required onchange="updateObjectInfo()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите заказчика</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $selectedSubjectId) == $subject->id ? 'selected' : '' }}>
                                {{ $subject->display_name }} ({{ $subject->identifier }})
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Район *</label>
                    <select name="district_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите район</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->id }}" {{ old('district_id') == $district->id ? 'selected' : '' }}>
                                {{ $district->name_ru }}
                            </option>
                        @endforeach
                    </select>
                    @error('district_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Адрес объекта *</label>
                    <textarea name="address" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="г. Ташкент, ул. Примерная, дом 1">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Кадастровый номер</label>
                    <input type="text" name="cadastre_number" value="{{ old('cadastre_number') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="01:01:001:001">
                    @error('cadastre_number')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Координаты (геолокация)</label>
                    <input type="text" name="geolocation" value="{{ old('geolocation') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="41.2995, 69.2401">
                    @error('geolocation')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Характеристики объекта -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Характеристики объекта</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип объекта</label>
                    <select name="object_type_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите тип</option>
                        @foreach($objectTypes as $objectType)
                            <option value="{{ $objectType->id }}" {{ old('object_type_id') == $objectType->id ? 'selected' : '' }}>
                                {{ $objectType->name_ru }}
                            </option>
                        @endforeach
                    </select>
                    @error('object_type_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип строительства</label>
                    <select name="construction_type_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите тип</option>
                        @foreach($constructionTypes as $constructionType)
                            <option value="{{ $constructionType->id }}" {{ old('construction_type_id') == $constructionType->id ? 'selected' : '' }}>
                                {{ $constructionType->name_ru }}
                            </option>
                        @endforeach
                    </select>
                    @error('construction_type_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Территориальная зона</label>
                    <select name="territorial_zone_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите зону</option>
                        @foreach($territorialZones as $zone)
                            <option value="{{ $zone->id }}" {{ old('territorial_zone_id') == $zone->id ? 'selected' : '' }}
                                    data-coefficient="{{ $zone->coefficient }}">
                                {{ $zone->name_ru }} (коэф. {{ $zone->coefficient }})
                            </option>
                        @endforeach
                    </select>
                    @error('territorial_zone_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип расположения</label>
                    <input type="text" name="location_type" value="{{ old('location_type') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Центральная часть">
                    @error('location_type')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Объемы строительства -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Объемы строительства</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Общий объем строительства (м³) *
                    </label>
                    <input type="number" name="construction_volume" step="0.01" value="{{ old('construction_volume') }}" required
                           onchange="calculateTotalVolume()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('construction_volume')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Сверх разрешенного (м³)
                    </label>
                    <input type="number" name="above_permit_volume" step="0.01" value="{{ old('above_permit_volume', 0) }}"
                           onchange="calculateTotalVolume()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('above_permit_volume')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Автостоянка (м³)
                    </label>
                    <input type="number" name="parking_volume" step="0.01" value="{{ old('parking_volume', 0) }}"
                           onchange="calculateTotalVolume()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('parking_volume')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Технические помещения (м³)
                    </label>
                    <input type="number" name="technical_rooms_volume" step="0.01" value="{{ old('technical_rooms_volume', 0) }}"
                           onchange="calculateTotalVolume()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('technical_rooms_volume')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Общие площади (м³)
                    </label>
                    <input type="number" name="common_area_volume" step="0.01" value="{{ old('common_area_volume', 0) }}"
                           onchange="calculateTotalVolume()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('common_area_volume')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Итого расчетный объем:</p>
                    <p id="total_volume_display" class="text-2xl font-bold text-blue-600">0.00 м³</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Основной объем + дополнительные объемы
                    </p>
                </div>
            </div>
        </div>

        <!-- Информация о разрешении -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Информация о разрешении</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Номер заявления</label>
                    <input type="text" name="application_number" value="{{ old('application_number') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="№123/2024">
                    @error('application_number')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата заявления</label>
                    <input type="date" name="application_date" value="{{ old('application_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('application_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип разрешения</label>
                    <select name="permit_type_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите тип</option>
                        @foreach($permitTypes as $permitType)
                            <option value="{{ $permitType->id }}" {{ old('permit_type_id') == $permitType->id ? 'selected' : '' }}>
                                {{ $permitType->name_ru }}
                            </option>
                        @endforeach
                    </select>
                    @error('permit_type_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Выдавший орган</label>
                    <select name="issuing_authority_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите орган</option>
                        @foreach($issuingAuthorities as $authority)
                            <option value="{{ $authority->id }}" {{ old('issuing_authority_id') == $authority->id ? 'selected' : '' }}>
                                {{ $authority->name_ru }}
                            </option>
                        @endforeach
                    </select>
                    @error('issuing_authority_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Номер разрешения</label>
                    <input type="text" name="permit_number" value="{{ old('permit_number') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Р-123/2024">
                    @error('permit_number')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата выдачи</label>
                    <input type="date" name="permit_date" value="{{ old('permit_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('permit_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название разрешающего документа</label>
                    <input type="text" name="permit_document_name" value="{{ old('permit_document_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Разрешение на строительство">
                    @error('permit_document_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип работ</label>
                    <input type="text" name="work_type" value="{{ old('work_type') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Строительство жилого дома">
                    @error('work_type')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Дополнительная информация -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Дополнительная информация</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Примечания</label>
                <textarea name="additional_info" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Дополнительная информация об объекте...">{{ old('additional_info') }}</textarea>
                @error('additional_info')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Кнопки -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('objects.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Отмена
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-feather="save" class="w-4 h-4 mr-2 inline"></i>
                Создать объект
            </button>
        </div>
    </form>
</div>

<!-- Object Creation Modal (for AJAX) -->
<div id="objectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
            <form id="objectModalForm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Создать новый объект</h3>
                </div>
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <!-- Simplified form for modal -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Район *</label>
                            <select name="modal_district_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите район</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}">{{ $district->name_ru }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Адрес *</label>
                            <textarea name="modal_address" rows="2" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="г. Ташкент, ул. Примерная, дом 1"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Объем строительства (м³) *</label>
                                <input type="number" name="modal_construction_volume" step="0.01" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Кадастровый номер</label>
                                <input type="text" name="modal_cadastre_number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeObjectModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Создать объект
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calculateTotalVolume() {
    const constructionVolume = parseFloat(document.querySelector('input[name="construction_volume"]').value) || 0;
    const abovePermitVolume = parseFloat(document.querySelector('input[name="above_permit_volume"]').value) || 0;
    const parkingVolume = parseFloat(document.querySelector('input[name="parking_volume"]').value) || 0;
    const technicalRoomsVolume = parseFloat(document.querySelector('input[name="technical_rooms_volume"]').value) || 0;
    const commonAreaVolume = parseFloat(document.querySelector('input[name="common_area_volume"]').value) || 0;

    const totalVolume = constructionVolume + abovePermitVolume + parkingVolume + technicalRoomsVolume + commonAreaVolume;

    document.getElementById('total_volume_display').textContent = totalVolume.toFixed(2) + ' м³';
}

function updateObjectInfo() {
    const subjectId = document.querySelector('select[name="subject_id"]').value;
    if (subjectId) {
        // Here you can load subject's existing objects or update form based on subject
        console.log('Selected subject:', subjectId);
    }
}

// Modal functions for AJAX object creation
function openObjectModal(subjectId = null) {
    document.getElementById('objectModal').classList.remove('hidden');

    // Set subject if provided
    if (subjectId) {
        // Pre-fill or filter based on subject
        console.log('Creating object for subject:', subjectId);
    }
}

function closeObjectModal() {
    document.getElementById('objectModal').classList.add('hidden');
    document.getElementById('objectModalForm').reset();
}

// Handle modal form submission
document.getElementById('objectModalForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData();
    const subjectId = document.querySelector('select[name="subject_id"]').value;

    // Add form data
    formData.append('subject_id', subjectId);
    formData.append('district_id', document.querySelector('select[name="modal_district_id"]').value);
    formData.append('address', document.querySelector('textarea[name="modal_address"]').value);
    formData.append('construction_volume', document.querySelector('input[name="modal_construction_volume"]').value);
    formData.append('cadastre_number', document.querySelector('input[name="modal_cadastre_number"]').value);

    const submitButton = this.querySelector('button[type="submit"]');
    toggleLoading(submitButton, true);

    try {
        const response = await fetch('{{ route("objects.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Add new object to select
            const objectSelect = document.querySelector('select[name="object_id"]');
            const newOption = new Option(result.object.text, result.object.id, true, true);
            objectSelect.add(newOption);
            objectSelect.value = result.object.id;

            closeObjectModal();
            showSuccessMessage(result.message);
        } else {
            throw new Error(result.message || 'Ошибка при создании объекта');
        }
    } catch (error) {
        console.error('Error:', error);
        handleAjaxError({ responseJSON: { message: error.message } });
    } finally {
        toggleLoading(submitButton, false);
    }
});

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotalVolume();

    // Add event listeners for volume calculations
    const volumeInputs = document.querySelectorAll('input[name$="_volume"]');
    volumeInputs.forEach(input => {
        input.addEventListener('input', calculateTotalVolume);
    });
});

// Global function for contract creation page
function createNewObject() {
    const subjectId = document.querySelector('select[name="subject_id"]').value;
    if (!subjectId) {
        alert('Сначала выберите заказчика');
        return;
    }
    openObjectModal(subjectId);
}
</script>
@endpush
