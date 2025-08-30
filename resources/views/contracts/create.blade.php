@extends('layouts.app')

@section('title', 'Создать договор - АПЗ Система')
@section('page-title', 'Создание нового договора')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.index') }}"
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2 inline"></i>
        Назад к списку
    </a>
</div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <form action="{{ route('contracts.store') }}" method="POST" class="space-y-6" id="contractForm">
        @csrf

        <!-- Основная информация -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Основная информация</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Номер договора *</label>
                    <input type="text" name="contract_number" value="{{ old('contract_number', 'АПЗ-' . date('md') . '-' . rand(100, 999)) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="АПЗ-001/2024">
                    @error('contract_number')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата договора *</label>
                    <input type="date" name="contract_date" value="{{ old('contract_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('contract_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата завершения</label>
                    <input type="date" name="completion_date" value="{{ old('completion_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('completion_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Статус *</label>
                    <select name="status_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите статус</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->name_ru }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Заказчик -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Заказчик</h3>
                <button type="button" onclick="openSubjectModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
                    Создать заказчика
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Заказчик *</label>
                    <div class="relative">
                        <input type="text" id="subjectSearch" placeholder="Поиск заказчика..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               onkeyup="searchSubjects()" autocomplete="off">
                        <select name="subject_id" required id="subjectSelect" style="display: none;"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Выберите заказчика</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}
                                        data-text="{{ $subject->display_name }} ({{ $subject->identifier }})">
                                    {{ $subject->display_name }} ({{ $subject->identifier }})
                                </option>
                            @endforeach
                        </select>
                        <div id="subjectDropdown" class="absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                        </div>
                    </div>
                    @error('subject_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Объект -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Объект</h3>
                <button type="button" onclick="openObjectModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
                    Создать объект
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Объект *</label>
                    <div class="relative">
                        <input type="text" id="objectSearch" placeholder="Поиск объекта..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               onkeyup="searchObjects()" autocomplete="off">
                        <select name="object_id" required id="objectSelect" style="display: none;" onchange="updateObjectVolume()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Выберите объект</option>
                            @foreach($objects as $object)
                                <option value="{{ $object->id }}"
                                        data-volume="{{ $object->construction_volume }}"
                                        data-above-permit="{{ $object->above_permit_volume }}"
                                        data-parking="{{ $object->parking_volume }}"
                                        data-technical="{{ $object->technical_rooms_volume }}"
                                        data-common="{{ $object->common_area_volume }}"
                                        data-subject="{{ $object->subject_id }}"
                                        data-construction-type="{{ $object->construction_type_id }}"
                                        data-object-type="{{ $object->object_type_id }}"
                                        data-zone="{{ $object->territorial_zone_id }}"
                                        data-location="{{ $object->location_type }}"
                                        data-text="{{ $object->address }} ({{ $object->district->name_ru ?? '' }}) - {{ number_format($object->construction_volume, 2) }} м³"
                                        {{ old('object_id') == $object->id ? 'selected' : '' }}>
                                    {{ $object->address }} ({{ $object->district->name_ru ?? '' }}) - {{ number_format($object->construction_volume, 2) }} м³
                                </option>
                            @endforeach
                        </select>
                        <div id="objectDropdown" class="absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                        </div>
                    </div>
                    @error('object_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Расчет суммы -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Расчет суммы договора</h3>

            <!-- Отображение объемов объекта -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Общий объем (Hb)</p>
                    <p id="display_hb" class="font-semibold text-blue-600">0 м³</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Сверх этажей (Hyu)</p>
                    <p id="display_hyu" class="font-semibold text-blue-600">0 м³</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Автостоянка (Ha)</p>
                    <p id="display_ha" class="font-semibold text-red-600">0 м³</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Техническое (Ht)</p>
                    <p id="display_ht" class="font-semibold text-red-600">0 м³</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Общее польз. (Hu)</p>
                    <p id="display_hu" class="font-semibold text-red-600">0 м³</p>
                </div>
            </div>

            <!-- Расчетный объем и коэффициенты -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6 p-4 bg-blue-50 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Расчетный объем</p>
                    <p id="display_calculated_volume" class="font-bold text-green-600">0 м³</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Kt (Строит.)</p>
                    <p id="display_kt" class="font-bold text-purple-600">1.0</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Ko (Объект)</p>
                    <p id="display_ko" class="font-bold text-purple-600">1.0</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Kz (Зона)</p>
                    <p id="display_kz" class="font-bold text-purple-600">1.0</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Kj (Место)</p>
                    <p id="display_kj" class="font-bold text-purple-600">1.0</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Итого коэф.</p>
                    <p id="display_total_coef" class="font-bold text-orange-600">1.0</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Базовая расчетная величина (Bh) *</label>
                    <select name="base_amount_id" required onchange="calculateTotal()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите базовую величину</option>
                        @foreach($baseAmounts as $baseAmount)
                            <option value="{{ $baseAmount->id }}" data-amount="{{ $baseAmount->amount }}" {{ old('base_amount_id') == $baseAmount->id ? 'selected' : '' }}>
                                {{ number_format($baseAmount->amount) }} сум (с {{ $baseAmount->effective_from->format('d.m.Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('base_amount_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Расчетный объем (м³) *</label>
                    <input type="number" name="contract_volume" step="0.01" value="{{ old('contract_volume') }}" required readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100">
                    @error('contract_volume')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Итоговый коэффициент *</label>
                    <input type="number" name="coefficient" step="0.01" value="{{ old('coefficient', '1.00') }}" required readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100">
                    @error('coefficient')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Общая сумма договора (Ti):</p>
                    <p id="total_amount_display" class="text-3xl font-bold text-blue-600">0 сум</p>
                    <p id="formula_display" class="text-sm text-gray-500 mt-2"></p>
                </div>
            </div>
        </div>

        <!-- Условия оплаты -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Условия оплаты</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип оплаты *</label>
                    <select name="payment_type" required onchange="togglePaymentFields(); calculatePaymentSchedule()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="full" {{ old('payment_type') == 'full' ? 'selected' : '' }}>Полная оплата</option>
                        <option value="installment" {{ old('payment_type', 'installment') == 'installment' ? 'selected' : '' }}>Рассрочка</option>
                    </select>
                </div>

                <div id="initial_payment_field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Первоначальный взнос (%)</label>
                    <input type="number" name="initial_payment_percent" min="0" max="100" value="{{ old('initial_payment_percent', 20) }}"
                           onchange="calculatePaymentSchedule()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div id="construction_period_field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Период строительства (лет)</label>
                    <input type="number" name="construction_period_years" min="1" max="10" value="{{ old('construction_period_years', 2) }}"
                           onchange="calculatePaymentSchedule()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- График платежей -->
            <div id="payment_schedule_display" class="mt-6 p-4 bg-green-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-4">График платежей</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="text-center p-3 bg-white rounded">
                        <p class="text-sm text-gray-600">Первоначальный взнос</p>
                        <p id="initial_payment_amount" class="font-bold text-green-600">0 сум</p>
                    </div>
                    <div class="text-center p-3 bg-white rounded">
                        <p class="text-sm text-gray-600">Остаток к доплате</p>
                        <p id="remaining_amount" class="font-bold text-orange-600">0 сум</p>
                    </div>
                    <div class="text-center p-3 bg-white rounded">
                        <p class="text-sm text-gray-600">Ежеквартальный платеж</p>
                        <p id="quarterly_payment" class="font-bold text-blue-600">0 сум</p>
                    </div>
                </div>
                <div id="quarters_table" class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300 rounded">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border text-left">Год</th>
                                <th class="px-4 py-2 border text-left">Квартал</th>
                                <th class="px-4 py-2 border text-right">Сумма к доплате</th>
                            </tr>
                        </thead>
                        <tbody id="quarters_tbody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Кнопки -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('contracts.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Отмена
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-feather="save" class="w-4 h-4 mr-2 inline"></i>
                Создать договор
            </button>
        </div>
    </form>
</div>

<!-- Subject Creation Modal -->
<div id="subjectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
            <form id="subjectModalForm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Создать нового заказчика</h3>
                </div>
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <!-- Entity Type Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Тип субъекта</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative">
                                <input type="radio" name="is_legal_entity" value="1" checked onchange="toggleEntityFields()" class="sr-only">
                                <div class="entity-type-card p-3 border-2 border-blue-500 bg-blue-50 rounded-lg cursor-pointer">
                                    <div class="text-center">
                                        <i data-feather="briefcase" class="w-6 h-6 mx-auto text-blue-600 mb-1"></i>
                                        <p class="font-medium text-gray-900">Yuridik shaxs</p>
                                    </div>
                                </div>
                            </label>
                            <label class="relative">
                                <input type="radio" name="is_legal_entity" value="0" onchange="toggleEntityFields()" class="sr-only">
                                <div class="entity-type-card p-3 border-2 border-gray-200 rounded-lg cursor-pointer">
                                    <div class="text-center">
                                        <i data-feather="user" class="w-6 h-6 mx-auto text-green-600 mb-1"></i>
                                        <p class="font-medium text-gray-900">Jismoniy shaxs</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Legal Entity Fields -->
                    <div id="legalEntityFields" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kompaniya nomi *</label>
                                <input type="text" name="company_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">STIR *</label>
                                <input type="text" name="inn" maxlength="9" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bank nomi</label>
                                <input type="text" name="bank_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bank MFO</label>
                                <input type="text" name="bank_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hisob raqam</label>
                                <input type="text" name="bank_account" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Physical Person Fields -->
                    <div id="physicalPersonFields" class="space-y-4 hidden">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hujjat turi *</label>
                                <select name="document_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tanlang</option>
                                    <option value="passport">Pasport</option>
                                    <option value="id_card">ID karta</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PINFL *</label>
                                <input type="text" name="pinfl" maxlength="14" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hujjat seriyasi</label>
                                <input type="text" name="document_series" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hujjat raqami *</label>
                                <input type="text" name="document_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Common Fields -->
                    <div class="space-y-4 mt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                                <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Manzil</label>
                            <textarea name="physical_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>
                </div>
              <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeSubjectModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Bekor qilish</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Yaratish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Object Creation Modal -->
<div id="objectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-7xl sm:w-full max-h-[90vh] overflow-y-auto">
            <form id="objectModalForm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Yangi obyekt yaratish</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tuman *</label>
                                <select name="district_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tumanni tanlang</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name_ru }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Obyekt manzili *</label>
                                <textarea name="address" rows="2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Toshkent sh., Ko'cha nomi, Uy raqami"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kadastr raqami</label>
                                    <input type="text" name="cadastre_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Koordinatalar</label>
                                    <input type="text" name="geolocation" placeholder="41.2995, 69.2401" onblur="detectZoneFromCoordinates()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Volume Fields -->
                            <div class="space-y-3">
                                <h4 class="font-medium text-gray-900">Qurilish hajmi (m³)</h4>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Binoning umumiy hajmi (Hb) *</label>
                                    <input type="number" name="construction_volume" step="0.01" required onchange="calculateModalVolume()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ruxsat etilgan qavatlar sonidan yuqori (Hyu)</label>
                                    <input type="number" name="above_permit_volume" step="0.01" onchange="calculateModalVolume()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Avtoturargoh hajmi (Ha)</label>
                                    <input type="number" name="parking_volume" step="0.01" onchange="calculateModalVolume()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Texnik qavatlari hajmi (Ht)</label>
                                    <input type="number" name="technical_rooms_volume" step="0.01" onchange="calculateModalVolume()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Umumiy foydalanish hajmi (Hu)</label>
                                    <input type="number" name="common_area_volume" step="0.01" onchange="calculateModalVolume()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="p-3 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-gray-600">Hisoblash hajmi: (Hb + Hyu) - (Ha + Ht + Hu)</p>
                                    <p id="calculated_volume_modal" class="font-bold text-blue-600">0.00 m³</p>
                                </div>
                            </div>

                            <!-- Type Fields -->
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Obyekt turi</label>
                                    <select name="object_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Tanlang</option>
                                        @foreach($objectTypes as $objectType)
                                            <option value="{{ $objectType->id }}" data-coefficient="{{ $objectType->coefficient }}">{{ $objectType->name_ru }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Qurilish turi</label>
                                    <select name="construction_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Tanlang</option>
                                        @foreach($constructionTypes as $constructionType)
                                            <option value="{{ $constructionType->id }}" data-coefficient="{{ $constructionType->coefficient }}">{{ $constructionType->name_ru }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hududiy zona</label>
                                    <select name="territorial_zone_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Tanlang</option>
                                        @foreach($territorialZones as $zone)
                                            <option value="{{ $zone->id }}" data-coefficient="{{ $zone->coefficient }}">
                                                {{ $zone->name_ru }} (коэф. {{ $zone->coefficient }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Obyekt joylashuvi</label>
                                    <select name="location_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Tanlang</option>
                                        <option value="metro_radius_200m_outside" data-coefficient="0.6">Metro 200m radiusidan tashqarida</option>
                                        <option value="other_locations" data-coefficient="1.0">Boshqa joylar</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Map -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Xaritada joylashuvni tanlang</label>
                                <div id="objectMap" style="height: 500px; width: 100%;" class="border rounded-lg"></div>
                                <div id="zoneInfo" class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded hidden">
                                    <p class="text-sm text-yellow-800">Zona: <span id="detectedZone"></span></p>
                                    <p class="text-sm text-yellow-800">Koeffitsient: <span id="zoneCoefficient"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeObjectModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Bekor qilish</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Yaratish</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let objectMap;
let mapMarker;
let zonePolygons = [];

// Coefficient data based on Uzbek regulations
const coefficients = {
    construction_type: {
        1: 1.0,    // Yangi qurilish
        2: 0.85    // Rekonstruksiya
    },
    object_type: {
        1: 0.8,    // Ijtimoiy xususiy obyektlar
        2: 0.5,    // Davlat ulushi 50% dan ortiq
        3: 0.5,    // Sanoat maqsadlari
        4: 1.0     // Boshqa obyektlar
    },
    territorial_zone: {
        1: 2.0,    // 1-zona
        2: 1.8,    // 2-zona
        3: 1.53,   // 3-zona
        4: 1.34,   // 4-zona
        5: 1.23    // 5-zona
    },
    location: {
        'metro_radius_200m_outside': 0.6,
        'other_locations': 1.0
    }
};

// Initialize map when modal opens
function initializeMap() {
    if (!objectMap) {
        objectMap = L.map('objectMap').setView([41.2995, 69.2401], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(objectMap);

        // Load zone KML
        loadZoneKML();

        objectMap.on('click', function(e) {
            if (mapMarker) {
                objectMap.removeLayer(mapMarker);
            }

            mapMarker = L.marker(e.latlng).addTo(objectMap);
            document.querySelector('input[name="geolocation"]').value = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);

            // Detect zone
            detectZoneByCoordinates(e.latlng.lat, e.latlng.lng);
        });
    }
}

function loadZoneKML() {
    fetch('/zone.kml')  // Updated path
        .then(response => {
            if (!response.ok) {
                throw new Error('KML file not found');
            }
            return response.text();
        })
        .then(kmlString => {
            const parser = new DOMParser();
            const kml = parser.parseFromString(kmlString, 'text/xml');
            const placemarks = kml.querySelectorAll('Placemark');

            placemarks.forEach(placemark => {
                const coordinates = placemark.querySelector('coordinates');
                if (coordinates) {
                    const coordString = coordinates.textContent.trim();
                    const polygon = parseKMLCoordinates(coordString);

                    if (polygon.length > 0) {
                        const zoneNameElement = placemark.querySelector('SimpleData[name="SONI"]');
                        const zoneName = zoneNameElement ? zoneNameElement.textContent : 'Unknown';

                        const layer = L.polygon(polygon, {
                            color: getZoneColor(zoneName),
                            fillOpacity: 0.3,
                            weight: 2
                        }).addTo(objectMap);

                        layer.bindPopup(`<strong>${zoneName}</strong><br>Koeffitsient: ${getZoneCoefficient(zoneName)}`);
                        zonePolygons.push({ layer: layer, name: zoneName, polygon: polygon });
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading KML:', error);
            showNotification('Zona ma\'lumotlari yuklanmadi', 'warning');
        });
}

function parseKMLCoordinates(coordString) {
    const points = coordString.trim().split(' ');
    const latLngs = [];

    points.forEach(point => {
        const coords = point.split(',');
        if (coords.length >= 2) {
            latLngs.push([parseFloat(coords[1]), parseFloat(coords[0])]);
        }
    });

    return latLngs;
}

function getZoneColor(zoneName) {
    const colors = {
        'ЗОНА-1': '#ff0000',
        'ЗОНА-2': '#ff8800',
        'ЗОНА-3': '#ffff00',
        'ЗОНА-4': '#88ff00',
        'ЗОНА-5': '#00ff00'
    };
    return colors[zoneName] || '#888888';
}

function getZoneCoefficient(zoneName) {
    const coefficients = {
        'ЗОНА-1': 2.0,
        'ЗОНА-2': 1.8,
        'ЗОНА-3': 1.53,
        'ЗОНА-4': 1.34,
        'ЗОНА-5': 1.23
    };
    return coefficients[zoneName] || 1.00;
}

function detectZoneFromCoordinates() {
    const geoInput = document.querySelector('input[name="geolocation"]');
    if (geoInput.value) {
        const coords = geoInput.value.split(',');
        if (coords.length === 2) {
            const lat = parseFloat(coords[0].trim());
            const lng = parseFloat(coords[1].trim());
            detectZoneByCoordinates(lat, lng);
        }
    }
}

function detectZoneByCoordinates(lat, lng) {
    const point = [lng, lat];
    let detectedZone = null;

    // Check each zone polygon
    zonePolygons.forEach(zoneData => {
        if (pointInPolygon(point, zoneData.polygon.map(p => [p[1], p[0]]))) {
            detectedZone = {
                name: zoneData.name,
                coefficient: getZoneCoefficient(zoneData.name)
            };
        }
    });

    if (detectedZone) {
        document.getElementById('zoneInfo').classList.remove('hidden');
        document.getElementById('detectedZone').textContent = detectedZone.name;
        document.getElementById('zoneCoefficient').textContent = detectedZone.coefficient;

        // Auto-select territorial zone
        const zoneSelect = document.querySelector('select[name="territorial_zone_id"]');
        for (let option of zoneSelect.options) {
            if (option.text.includes(detectedZone.name)) {
                option.selected = true;
                break;
            }
        }
    } else {
        document.getElementById('zoneInfo').classList.add('hidden');
    }
}

function pointInPolygon(point, polygon) {
    let x = point[0], y = point[1];
    let inside = false;

    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        let xi = polygon[i][0], yi = polygon[i][1];
        let xj = polygon[j][0], yj = polygon[j][1];

        if (((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) {
            inside = !inside;
        }
    }
    return inside;
}

// Live search functions
function searchSubjects() {
    const searchTerm = document.getElementById('subjectSearch').value.toLowerCase();
    const select = document.getElementById('subjectSelect');
    const dropdown = document.getElementById('subjectDropdown');

    if (searchTerm.length < 2) {
        dropdown.classList.add('hidden');
        return;
    }

    let results = [];
    Array.from(select.options).forEach(option => {
        if (option.value && option.textContent.toLowerCase().includes(searchTerm)) {
            results.push(option);
        }
    });

    displaySearchResults(results, dropdown, 'subject');
}

function searchObjects() {
    const searchTerm = document.getElementById('objectSearch').value.toLowerCase();
    const select = document.getElementById('objectSelect');
    const dropdown = document.getElementById('objectDropdown');

    if (searchTerm.length < 2) {
        dropdown.classList.add('hidden');
        return;
    }

    let results = [];
    Array.from(select.options).forEach(option => {
        if (option.value && option.textContent.toLowerCase().includes(searchTerm)) {
            results.push(option);
        }
    });

    displaySearchResults(results, dropdown, 'object');
}

function displaySearchResults(results, dropdown, type) {
    dropdown.innerHTML = '';

    if (results.length === 0) {
        dropdown.innerHTML = '<div class="p-3 text-gray-500">Hech narsa topilmadi</div>';
        dropdown.classList.remove('hidden');
        return;
    }

    results.forEach(option => {
        const div = document.createElement('div');
        div.className = 'p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100';
        div.textContent = option.textContent;
        div.onclick = () => selectSearchResult(option, type);
        dropdown.appendChild(div);
    });

    dropdown.classList.remove('hidden');
}

function selectSearchResult(option, type) {
    if (type === 'subject') {
        document.getElementById('subjectSearch').value = option.textContent;
        document.getElementById('subjectSelect').value = option.value;
        document.getElementById('subjectDropdown').classList.add('hidden');
    } else {
        document.getElementById('objectSearch').value = option.textContent;
        document.getElementById('objectSelect').value = option.value;
        document.getElementById('objectDropdown').classList.add('hidden');
        updateObjectVolume();
    }
}

// Hide dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#subjectSearch') && !e.target.closest('#subjectDropdown')) {
        document.getElementById('subjectDropdown').classList.add('hidden');
    }
    if (!e.target.closest('#objectSearch') && !e.target.closest('#objectDropdown')) {
        document.getElementById('objectDropdown').classList.add('hidden');
    }
});

function calculateTotal() {
    const baseAmountSelect = document.querySelector('select[name="base_amount_id"]');
    const volumeInput = document.querySelector('input[name="contract_volume"]');
    const coefficientInput = document.querySelector('input[name="coefficient"]');

    const selectedOption = baseAmountSelect.options[baseAmountSelect.selectedIndex];
    const baseAmount = selectedOption ? parseFloat(selectedOption.dataset.amount) : 0;
    const volume = parseFloat(volumeInput.value) || 0;
    const coefficient = parseFloat(coefficientInput.value) || 1;

    const totalAmount = baseAmount * volume * coefficient;

    document.getElementById('total_amount_display').textContent = formatNumber(totalAmount) + ' сум';

    if (baseAmount && volume && coefficient) {
        document.getElementById('formula_display').textContent =
            `Ti = ${formatNumber(baseAmount)} × ${formatNumber(volume)} m³ × ${coefficient} = ${formatNumber(totalAmount)} сум`;
    } else {
        document.getElementById('formula_display').textContent = '';
    }

    calculatePaymentSchedule();
}

function updateObjectVolume() {
    const objectSelect = document.querySelector('select[name="object_id"]');
    const selectedOption = objectSelect.options[objectSelect.selectedIndex];

    if (selectedOption && selectedOption.dataset.volume) {
        // Display object volumes
        const hb = parseFloat(selectedOption.dataset.volume) || 0;
        const hyu = parseFloat(selectedOption.dataset.abovePermit) || 0;
        const ha = parseFloat(selectedOption.dataset.parking) || 0;
        const ht = parseFloat(selectedOption.dataset.technical) || 0;
        const hu = parseFloat(selectedOption.dataset.common) || 0;

        document.getElementById('display_hb').textContent = formatNumber(hb) + ' m³';
        document.getElementById('display_hyu').textContent = formatNumber(hyu) + ' m³';
        document.getElementById('display_ha').textContent = formatNumber(ha) + ' m³';
        document.getElementById('display_ht').textContent = formatNumber(ht) + ' m³';
        document.getElementById('display_hu').textContent = formatNumber(hu) + ' m³';

        // Calculate contract volume using formula: (Hb + Hyu) - (Ha + Ht + Hu)
        const contractVolume = (hb + hyu) - (ha + ht + hu);
        document.getElementById('display_calculated_volume').textContent = formatNumber(contractVolume) + ' m³';

        const volumeInput = document.querySelector('input[name="contract_volume"]');
        volumeInput.value = contractVolume.toFixed(2);

        // Calculate and display coefficients
        const constructionType = selectedOption.dataset.constructionType;
        const objectType = selectedOption.dataset.objectType;
        const zone = selectedOption.dataset.zone;
        const location = selectedOption.dataset.location;

        const kt = coefficients.construction_type[constructionType] || 1.0;
        const ko = coefficients.object_type[objectType] || 1.0;
        const kz = coefficients.territorial_zone[zone] || 1.0;
        const kj = coefficients.location[location] || 1.0;

        let totalCoef = kt * ko * kz * kj;
        totalCoef = Math.max(0.5, totalCoef); // Minimum 0.5

        document.getElementById('display_kt').textContent = kt.toFixed(2);
        document.getElementById('display_ko').textContent = ko.toFixed(2);
        document.getElementById('display_kz').textContent = kz.toFixed(2);
        document.getElementById('display_kj').textContent = kj.toFixed(2);
        document.getElementById('display_total_coef').textContent = totalCoef.toFixed(2);

        const coefficientInput = document.querySelector('input[name="coefficient"]');
        coefficientInput.value = totalCoef.toFixed(2);

        calculateTotal();
    }
}

function calculateModalVolume() {
    const hb = parseFloat(document.querySelector('input[name="construction_volume"]').value) || 0;
    const hyu = parseFloat(document.querySelector('input[name="above_permit_volume"]').value) || 0;
    const ha = parseFloat(document.querySelector('input[name="parking_volume"]').value) || 0;
    const ht = parseFloat(document.querySelector('input[name="technical_rooms_volume"]').value) || 0;
    const hu = parseFloat(document.querySelector('input[name="common_area_volume"]').value) || 0;

    const calculatedVolume = (hb + hyu) - (ha + ht + hu);
    document.getElementById('calculated_volume_modal').textContent = calculatedVolume.toFixed(2) + ' m³';
}

function calculatePaymentSchedule() {
    const paymentType = document.querySelector('select[name="payment_type"]').value;
    const totalAmountText = document.getElementById('total_amount_display').textContent;
    const totalAmount = parseFloat(totalAmountText.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;

    if (totalAmount <= 0) return;

    const initialPercent = parseInt(document.querySelector('input[name="initial_payment_percent"]').value) || 20;
    const years = parseInt(document.querySelector('input[name="construction_period_years"]').value) || 2;
    const quarters = years * 4;

    if (paymentType === 'full') {
        document.getElementById('initial_payment_amount').textContent = formatNumber(totalAmount) + ' сум';
        document.getElementById('remaining_amount').textContent = '0 сум';
        document.getElementById('quarterly_payment').textContent = '0 сум';

        document.getElementById('quarters_tbody').innerHTML = `
            <tr>
                <td class="px-4 py-2 border">${new Date().getFullYear()}</td>
                <td class="px-4 py-2 border">Toliq to'lov</td>
                <td class="px-4 py-2 border text-right">${formatNumber(totalAmount)} сум</td>
            </tr>
        `;
    } else {
        const initialPayment = totalAmount * (initialPercent / 100);
        const remainingAmount = totalAmount - initialPayment;
        const quarterlyPayment = remainingAmount / quarters;

        document.getElementById('initial_payment_amount').textContent = formatNumber(initialPayment) + ' сум';
        document.getElementById('remaining_amount').textContent = formatNumber(remainingAmount) + ' сум';
        document.getElementById('quarterly_payment').textContent = formatNumber(quarterlyPayment) + ' сум';

        // Generate quarterly schedule
        let tbody = `
            <tr class="bg-green-50">
                <td class="px-4 py-2 border font-semibold">${new Date().getFullYear()}</td>
                <td class="px-4 py-2 border font-semibold">Boshlang'ich to'lov</td>
                <td class="px-4 py-2 border text-right font-semibold">${formatNumber(initialPayment)} сум</td>
            </tr>
        `;

        const startYear = new Date().getFullYear();
        const startQuarter = Math.ceil((new Date().getMonth() + 1) / 3);

        for (let i = 0; i < quarters; i++) {
            const currentQuarter = ((startQuarter - 1 + i) % 4) + 1;
            const currentYear = startYear + Math.floor((startQuarter - 1 + i) / 4);

            tbody += `
                <tr>
                    <td class="px-4 py-2 border">${currentYear}</td>
                    <td class="px-4 py-2 border">${currentQuarter}-chorak</td>
                    <td class="px-4 py-2 border text-right">${formatNumber(quarterlyPayment)} сум</td>
                </tr>
            `;
        }

        document.getElementById('quarters_tbody').innerHTML = tbody;
    }
}

function togglePaymentFields() {
    const paymentType = document.querySelector('select[name="payment_type"]').value;
    const initialPaymentField = document.getElementById('initial_payment_field');
    const constructionPeriodField = document.getElementById('construction_period_field');

    if (paymentType === 'full') {
        initialPaymentField.style.display = 'none';
        constructionPeriodField.style.display = 'none';
        document.querySelector('input[name="initial_payment_percent"]').value = 100;
    } else {
        initialPaymentField.style.display = 'block';
        constructionPeriodField.style.display = 'block';
        if (document.querySelector('input[name="initial_payment_percent"]').value == 100) {
            document.querySelector('input[name="initial_payment_percent"]').value = 20;
        }
    }
    calculatePaymentSchedule();
}

// Subject Modal Functions
function openSubjectModal() {
    document.getElementById('subjectModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeSubjectModal() {
    document.getElementById('subjectModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('subjectModalForm').reset();
    toggleEntityFields();
}

function toggleEntityFields() {
    const isLegalEntity = document.querySelector('input[name="is_legal_entity"]:checked').value === '1';
    const legalFields = document.getElementById('legalEntityFields');
    const physicalFields = document.getElementById('physicalPersonFields');

    // Update card styles
    document.querySelectorAll('.entity-type-card').forEach((card) => {
        const input = card.parentElement.querySelector('input[type="radio"]');
        card.classList.remove('border-blue-500', 'bg-blue-50', 'border-green-500', 'bg-green-50', 'border-gray-200');

        if (input.checked) {
            if (input.value === '1') {
                card.classList.add('border-blue-500', 'bg-blue-50');
            } else {
                card.classList.add('border-green-500', 'bg-green-50');
            }
        } else {
            card.classList.add('border-gray-200');
        }
    });

    if (isLegalEntity) {
        legalFields.classList.remove('hidden');
        physicalFields.classList.add('hidden');

        legalFields.querySelectorAll('input[name="company_name"], input[name="inn"]').forEach(input => {
            input.setAttribute('required', 'required');
        });
        physicalFields.querySelectorAll('input').forEach(input => {
            input.removeAttribute('required');
        });
    } else {
        legalFields.classList.add('hidden');
        physicalFields.classList.remove('hidden');

        physicalFields.querySelectorAll('select[name="document_type"], input[name="document_number"], input[name="pinfl"]').forEach(input => {
            input.setAttribute('required', 'required');
        });
        legalFields.querySelectorAll('input').forEach(input => {
            input.removeAttribute('required');
        });
    }
}

// Object Modal Functions
function openObjectModal() {
    const subjectId = document.querySelector('select[name="subject_id"]').value;
    if (!subjectId) {
        showNotification('Avval buyurtmachini tanlang', 'warning');
        return;
    }

    document.getElementById('objectModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        initializeMap();
        if (objectMap) {
            objectMap.invalidateSize();
        }
    }, 200);
}

function closeObjectModal() {
    document.getElementById('objectModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('objectModalForm').reset();
    document.getElementById('zoneInfo').classList.add('hidden');
    document.getElementById('calculated_volume_modal').textContent = '0.00 m³';

    if (mapMarker) {
        objectMap.removeLayer(mapMarker);
        mapMarker = null;
    }
}

// Form Submission Handlers
document.getElementById('subjectModalForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');

    toggleLoading(submitButton, true);

    try {
        const response = await fetch('/contracts/create-subject', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            const subjectSelect = document.getElementById('subjectSelect');
            const newOption = new Option(result.subject.text, result.subject.id, true, true);
            subjectSelect.add(newOption);
            subjectSelect.value = result.subject.id;

            document.getElementById('subjectSearch').value = result.subject.text;

            closeSubjectModal();
            showNotification(result.message, 'success');
        } else {
            throw new Error(result.message || 'Xato yuz berdi');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification(error.message, 'error');
    } finally {
        toggleLoading(submitButton, false);
    }
});

document.getElementById('objectModalForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const subjectId = document.querySelector('select[name="subject_id"]').value;
    formData.append('subject_id', subjectId);

    const submitButton = this.querySelector('button[type="submit"]');

    toggleLoading(submitButton, true);

    try {
        const response = await fetch('/contracts/create-object', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            const objectSelect = document.getElementById('objectSelect');
            const newOption = new Option(result.object.text, result.object.id, true, true);

            // Add data attributes
            newOption.setAttribute('data-volume', result.object.construction_volume);
            newOption.setAttribute('data-above-permit', result.object.above_permit_volume || 0);
            newOption.setAttribute('data-parking', result.object.parking_volume || 0);
            newOption.setAttribute('data-technical', result.object.technical_rooms_volume || 0);
            newOption.setAttribute('data-common', result.object.common_area_volume || 0);
            newOption.setAttribute('data-subject', result.object.subject_id);

            objectSelect.add(newOption);
            objectSelect.value = result.object.id;

            document.getElementById('objectSearch').value = result.object.text;

            updateObjectVolume();

            closeObjectModal();
            showNotification(result.message, 'success');
        } else {
            throw new Error(result.message || 'Xato yuz berdi');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification(error.message, 'error');
    } finally {
        toggleLoading(submitButton, false);
    }
});

// Contract Form Submission
document.getElementById('contractForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');

    toggleLoading(submitButton, true);

    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');
            if (result.redirect) {
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1500);
            }
        } else {
            throw new Error(result.message || 'Shartnoma yaratishda xato');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification(error.message, 'error');
    } finally {
        toggleLoading(submitButton, false);
    }
});

// Utility Functions
function formatNumber(num) {
    return new Intl.NumberFormat('ru-RU').format(Math.round(num));
}

function toggleLoading(button, loading) {
    if (loading) {
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Kutilmoqda...';
    } else {
        button.disabled = false;
        const originalTexts = {
            'Yaratish': 'Yaratish',
            'Shartnoma yaratish': '<i data-feather="save" class="w-4 h-4 mr-2 inline"></i>Создать договор'
        };

        if (button.innerHTML.includes('Kutilmoqda')) {
            if (button.closest('#subjectModalForm') || button.closest('#objectModalForm')) {
                button.innerHTML = 'Yaratish';
            } else {
                button.innerHTML = '<i data-feather="save" class="w-4 h-4 mr-2 inline"></i>Создать договор';
            }
        }

        if (window.feather) {
            feather.replace();
        }
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-black' :
        'bg-blue-500 text-white'
    }`;

    notification.innerHTML = `
        <div class="flex items-center">
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg font-bold">&times;</button>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
    togglePaymentFields();
    toggleEntityFields();
    calculatePaymentSchedule();

    // Add event listeners for live calculations
    document.querySelector('select[name="base_amount_id"]').addEventListener('change', calculateTotal);
    document.querySelector('select[name="payment_type"]').addEventListener('change', () => {
        togglePaymentFields();
        calculatePaymentSchedule();
    });
    document.querySelector('input[name="initial_payment_percent"]').addEventListener('input', calculatePaymentSchedule);
    document.querySelector('input[name="construction_period_years"]').addEventListener('input', calculatePaymentSchedule);
});
</script>
@endpush
