@extends('layouts.app')

@section('title', 'Yangi Shartnoma Yaratish - Tashkent Invest')
@section('page-title', 'Yangi Shartnoma Yaratish')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.index') }}"
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2 inline"></i>
        Orqaga qaytish
    </a>
</div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <form action="{{ route('contracts.store') }}" method="POST" class="space-y-6" id="contractForm">
        @csrf

        <!-- Asosiy ma'lumotlar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Asosiy ma'lumotlar</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shartnoma raqami *</label>
                    <input type="text" name="contract_number" value="{{ old('contract_number', 'APZ-' . date('md') . '-' . rand(100, 999)) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="APZ-001/2024">
                    @error('contract_number')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shartnoma sanasi *</label>
                    <input type="date" name="contract_date" value="{{ old('contract_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('contract_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tugatish sanasi</label>
                    <input type="date" name="completion_date" value="{{ old('completion_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('completion_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Statusni tanlang</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->name_uz }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Buyurtmachi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Buyurtmachi</h3>
                <button type="button" onclick="openSubjectModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
                    Yangi buyurtmachi
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buyurtmachi *</label>
                    <div class="relative">
                        <input type="text" id="subjectSearch" placeholder="Buyurtmachi qidirish..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               onkeyup="searchSubjects()" autocomplete="off">
                        <select name="subject_id" required id="subjectSelect" style="display: none;"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Buyurtmachini tanlang</option>
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

        <!-- Obyekt va Hisoblash -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Obyekt va Shartnoma Hisoblash</h3>
                <button type="button" onclick="openObjectModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
                    Yangi obyekt yaratish
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Obyekt *</label>
                    <div class="relative">
                        <input type="text" id="objectSearch" placeholder="Obyekt qidirish..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               onkeyup="searchObjects()" autocomplete="off">
                        <select name="object_id" required id="objectSelect" style="display: none;" onchange="updateObjectVolume()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Obyektni tanlang</option>
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
                                        data-text="{{ $object->address }} ({{ $object->district->name_uz ?? '' }}) - {{ number_format($object->construction_volume, 2) }} m³"
                                        {{ old('object_id') == $object->id ? 'selected' : '' }}>
                                    {{ $object->address }} ({{ $object->district->name_uz ?? '' }}) - {{ number_format($object->construction_volume, 2) }} m³
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

        <!-- Hisoblash summasi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Shartnoma summasi hisoblash</h3>

            <!-- Obyekt hajmlari ko'rsatish -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Umumiy hajm (Hb)</p>
                    <p id="display_hb" class="font-semibold text-blue-600">0 m³</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Ruxsatdan yuqori (Hyu)</p>
                    <p id="display_hyu" class="font-semibold text-blue-600">0 m³</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Avtoturargoh (Ha)</p>
                    <p id="display_ha" class="font-semibold text-red-600">0 m³</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Texnik (Ht)</p>
                    <p id="display_ht" class="font-semibold text-red-600">0 m³</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Umumiy foyd. (Hu)</p>
                    <p id="display_hu" class="font-semibold text-red-600">0 m³</p>
                </div>
            </div>

            <!-- Hisoblash hajmi va koeffitsientlar -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6 p-4 bg-blue-50 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Hisoblash hajmi</p>
                    <p id="display_calculated_volume" class="font-bold text-green-600">0 m³</p>
                    <p class="text-xs text-gray-500">(Hb + Hyu) - (Ha + Ht + Hu)</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Kt (Qurilish)</p>
                    <p id="display_kt" class="font-bold text-purple-600">1.0</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Ko (Obyekt)</p>
                    <p id="display_ko" class="font-bold text-purple-600">1.0</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Kz (Zona)</p>
                    <p id="display_kz" class="font-bold text-purple-600">1.0</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Kj (Joy)</p>
                    <p id="display_kj" class="font-bold text-purple-600">1.0</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Jami koef.</p>
                    <p id="display_total_coef" class="font-bold text-orange-600">1.0</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bazaviy hisoblash miqdori (Bh) *</label>
                    <select name="base_amount_id" required onchange="calculateTotal()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Bazaviy miqdorni tanlang</option>
                        @foreach($baseAmounts as $baseAmount)
                            <option value="{{ $baseAmount->id }}" data-amount="{{ $baseAmount->amount }}" {{ old('base_amount_id') == $baseAmount->id ? 'selected' : '' }}>
                                {{ number_format($baseAmount->amount) }} so'm ({{ $baseAmount->effective_from->format('d.m.Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('base_amount_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hisoblash hajmi (m³) *</label>
                    <input type="number" name="contract_volume" step="0.01" value="{{ old('contract_volume') }}" required readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100">
                    @error('contract_volume')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hisobga olinadigan Bh (koef. bilan) *</label>
                    <input type="number" name="calculated_bh" step="0.01" value="{{ old('calculated_bh') }}" required readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100">
                    @error('calculated_bh')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 p-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg">
                <div class="text-center">
                    <p class="text-sm opacity-90 mb-2">Shartnoma umumiy summasi (Ti):</p>
                    <p id="total_amount_display" class="text-3xl font-bold">0 so'm</p>
                    <p id="formula_display" class="text-sm opacity-75 mt-2"></p>
                </div>
            </div>
        </div>

        <!-- To'lov shartlari -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">To'lov shartlari</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To'lov turi *</label>
                    <select name="payment_type" required onchange="togglePaymentFields(); calculatePaymentSchedule()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="full" {{ old('payment_type') == 'full' ? 'selected' : '' }}>To'liq to'lov</option>
                        <option value="installment" {{ old('payment_type', 'installment') == 'installment' ? 'selected' : '' }}>Bo'lib to'lash</option>
                    </select>
                </div>

                <div id="initial_payment_field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Boshlang'ich to'lov (%)</label>
                    <input type="number" name="initial_payment_percent" min="0" max="100" value="{{ old('initial_payment_percent', 20) }}"
                           onchange="calculatePaymentSchedule()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div id="construction_period_field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Qurilish muddati (yil)</label>
                    <input type="number" name="construction_period_years" min="1" max="10" value="{{ old('construction_period_years', 2) }}"
                           onchange="calculatePaymentSchedule()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- To'lov jadvali -->
            <div id="payment_schedule_display" class="mt-6 p-4 bg-green-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-4">To'lov jadvali</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="text-center p-3 bg-white rounded">
                        <p class="text-sm text-gray-600">Boshlang'ich to'lov</p>
                        <p id="initial_payment_amount" class="font-bold text-green-600">0 so'm</p>
                    </div>
                    <div class="text-center p-3 bg-white rounded">
                        <p class="text-sm text-gray-600">Qolgan summa</p>
                        <p id="remaining_amount" class="font-bold text-orange-600">0 so'm</p>
                    </div>
                    <div class="text-center p-3 bg-white rounded">
                        <p class="text-sm text-gray-600">Choraklik to'lov</p>
                        <p id="quarterly_payment" class="font-bold text-blue-600">0 so'm</p>
                    </div>
                </div>
                <div id="quarters_table" class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300 rounded">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border text-left">Yil</th>
                                <th class="px-4 py-2 border text-left">Chorak</th>
                                <th class="px-4 py-2 border text-right">To'lov summasi</th>
                            </tr>
                        </thead>
                        <tbody id="quarters_tbody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tugmalar -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('contracts.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Bekor qilish
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-feather="save" class="w-4 h-4 mr-2 inline"></i>
                Shartnoma yaratish
            </button>
        </div>
    </form>
</div>

<!-- Buyurtmachi yaratish modali -->
<div id="subjectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
            <form id="subjectModalForm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Yangi buyurtmachi yaratish</h3>
                </div>
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <!-- Shaxs turi tanlash -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shaxs turi</label>
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

                    <!-- Yuridik shaxs maydonlari -->
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hisob raqami</label>
                                <input type="text" name="bank_account" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Jismoniy shaxs maydonlari -->
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

                    <!-- Umumiy maydonlar -->
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

<!-- Obyekt yaratish modali -->
<div id="objectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-2">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all max-w-[98vw] w-full max-h-[98vh] overflow-y-auto">
            <form id="objectModalForm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Yangi obyekt yaratish va shartnoma hisoblash</h3>
                    <p class="text-sm text-gray-600 mt-1">Obyekt ma'lumotlarini kiriting, zona avtomatik aniqlanadi va shartnoma summasi hisoblanadi</p>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-3 gap-6">
                        <!-- Chap ustun - Asosiy ma'lumotlar -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900 border-b pb-2 flex items-center">
                                <i data-feather="home" class="w-4 h-4 mr-2"></i>
                                Asosiy ma'lumotlar
                            </h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tuman *</label>
                                <select name="district_id" required onchange="updateDistrictInfo()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tumanni tanlang</option>
                                    <option value="01">Учтепинский</option>
                                    <option value="02">Бектемирский</option>
                                    <option value="03">Чиланзарский</option>
                                    <option value="04">Яшнабадский</option>
                                    <option value="05">Яккасарайский</option>
                                    <option value="06">Сергелийский</option>
                                    <option value="07">Юнусабадский</option>
                                    <option value="08">Олмазарский</option>
                                    <option value="09">Мирзо Улугбекский</option>
                                    <option value="10">Шайхантахурский</option>
                                    <option value="11">Мирабадский</option>
                                    <option value="12">Янгихаётский</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Obyekt manzili *</label>
                                <textarea name="address" rows="2" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Toshkent sh., Ko'cha nomi, Uy raqami"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kadastr raqami</label>
                                    <input type="text" name="cadastre_number"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Koordinatalar</label>
                                    <input type="text" name="geolocation" id="coordinatesInput" placeholder="41.2995, 69.2401"
                                           onblur="detectZoneFromCoordinatesInput()"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Qurilish hajmlari -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h5 class="font-medium text-gray-900 mb-3 flex items-center">
                                    <i data-feather="box" class="w-4 h-4 mr-2"></i>
                                    Qurilish hajmi (m³)
                                </h5>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Umumiy hajm (Hb) *</label>
                                        <input type="number" name="construction_volume" step="0.01" required
                                               onchange="calculateModalEverything()" id="modal_hb"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ruxsat etilganidan yuqori (Hyu)</label>
                                        <input type="number" name="above_permit_volume" step="0.01"
                                               onchange="calculateModalEverything()" id="modal_hyu"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Avtoturargoh (Ha)</label>
                                        <input type="number" name="parking_volume" step="0.01"
                                               onchange="calculateModalEverything()" id="modal_ha"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Texnik qavatlari (Ht)</label>
                                        <input type="number" name="technical_rooms_volume" step="0.01"
                                               onchange="calculateModalEverything()" id="modal_ht"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Umumiy foydalanish (Hu)</label>
                                        <input type="number" name="common_area_volume" step="0.01"
                                               onchange="calculateModalEverything()" id="modal_hu"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                <!-- Hajm xulasasi -->
                                <div class="mt-4 p-3 bg-white rounded-lg border-2 border-blue-200">
                                    <div class="text-center">
                                        <p class="text-sm font-medium text-gray-700">Hisoblash hajmi:</p>
                                        <p class="text-xl font-bold text-blue-600" id="calculated_volume_modal">0.00 m³</p>
                                        <p class="text-xs text-gray-500 mt-1">Formula: (Hb + Hyu) - (Ha + Ht + Hu)</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Koeffitsientlar -->
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h5 class="font-medium text-gray-900 mb-3 flex items-center">
                                    <i data-feather="percent" class="w-4 h-4 mr-2"></i>
                                    Koeffitsientlar
                                </h5>
                                
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Qurilish turi (Kt)</label>
                                        <select name="construction_type_id" onchange="calculateModalEverything()"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Tanlang</option>
                                            <option value="1" data-coef="1.0">Yangi qurilish (1.0)</option>
                                            <option value="2" data-coef="1.0">Rekonstruksiya (1.0)</option>
                                            <option value="3" data-coef="0.0">Ekspertiza talab etilmaydigan (0.0)</option>
                                            <option value="4" data-coef="0.0">Hajmni o'zgartirmagan (0.0)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Obyekt turi (Ko)</label>
                                        <select name="object_type_id" onchange="calculateModalEverything()"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Tanlang</option>
                                            <option value="1" data-coef="0.5">Ijtimoiy infratuzilma (0.5)</option>
                                            <option value="2" data-coef="0.5">Davlat ulushi 50%+ (0.5)</option>
                                            <option value="3" data-coef="0.5">Ishlab chiqarish (0.5)</option>
                                            <option value="4" data-coef="0.5">Omborxonalar (0.5)</option>
                                            <option value="5" data-coef="1.0">Boshqa obyektlar (1.0)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Hududiy zona (Kz)</label>
                                        <select name="territorial_zone_id" id="modalTerritorialZone" onchange="calculateModalEverything()"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Zonani tanlang</option>
                                            <option value="1" data-coef="1.40">1-zona (1.40)</option>
                                            <option value="2" data-coef="1.25">2-zona (1.25)</option>
                                            <option value="3" data-coef="1.00">3-zona (1.00)</option>
                                            <option value="4" data-coef="0.75">4-zona (0.75)</option>
                                            <option value="5" data-coef="0.50">5-zona (0.50)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Joylashuv (Kj)</label>
                                        <select name="location_type" onchange="calculateModalEverything()"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="metro_radius_200m_outside" data-coef="0.6">Metro 200m radiusidan tashqari (0.6)</option>
                                            <option value="other_locations" data-coef="1.0">Boshqa joylar (1.0)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Koeffitsientlar ko'rsatish -->
                                <div class="mt-4 p-3 bg-white rounded-lg">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-gray-600">Kt: <span id="modal_kt" class="font-semibold text-purple-600">1.00</span></p>
                                            <p class="text-gray-600">Ko: <span id="modal_ko" class="font-semibold text-purple-600">1.00</span></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600">Kz: <span id="modal_kz" class="font-semibold text-purple-600">1.00</span></p>
                                            <p class="text-gray-600">Kj: <span id="modal_kj" class="font-semibold text-purple-600">1.00</span></p>
                                        </div>
                                        <div class="col-span-2 text-center pt-2 border-t">
                                            <p class="text-gray-700 font-medium">Jami koeffitsient: <span id="modal_total_coef" class="font-bold text-orange-600">1.00</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- O'rta ustun - Xarita -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900 border-b pb-2 flex items-center">
                                <i data-feather="map" class="w-4 h-4 mr-2"></i>
                                Xarita va zona aniqlash
                            </h4>

                            <div>
                                <div id="objectMap" style="height: 600px; width: 100%;" class="border rounded-lg"></div>
                                <div id="zoneInfo" class="mt-2 p-3 border-l-4 rounded hidden">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-900">Aniqlangan zona:</p>
                                            <p id="detectedZone" class="text-lg font-bold"></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600">Koeffitsient:</p>
                                            <p id="zoneCoefficient" class="text-lg font-bold"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-500 text-center">
                                    Xaritadan bosing yoki koordinatalar kiriting
                                </div>
                            </div>
                        </div>

                        <!-- O'ng ustun - Shartnoma hisoblash -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900 border-b pb-2 flex items-center">
                                <i data-feather="file-text" class="w-4 h-4 mr-2"></i>
                                Shartnoma hisoblash
                            </h4>

                            <!-- Bazaviy miqdor tanlash -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bazaviy hisoblash miqdori (Bh)</label>
                                <select id="modal_base_amount" onchange="calculateModalEverything()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tanlang</option>
                                    <option value="412000" selected>412,000 so'm (2024)</option>
                                    <option value="375000">375,000 so'm (2023)</option>
                                </select>
                            </div>

                            <!-- Shartnoma summasi ko'rsatish -->
                            <div class="p-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg">
                                <div class="text-center">
                                    <p class="text-sm opacity-90 mb-2">Shartnoma summasi (Ti)</p>
                                    <p id="modal_total_amount" class="text-3xl font-bold">0 so'm</p>
                                    <div class="mt-3 text-sm opacity-75">
                                        <p id="modal_formula_display">Ti = Calculated_Bh × Hajm</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Hisobga olinadigan Bh -->
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-700">Hisobga olinadigan Bh:</p>
                                    <p id="modal_calculated_bh" class="text-lg font-bold text-yellow-800">0 so'm</p>
                                    <p class="text-xs text-gray-500 mt-1">Bh × Koeffitsient</p>
                                </div>
                            </div>

                            <!-- To'lov shartlari -->
                            <div class="space-y-3">
                                <h5 class="font-medium text-gray-900 flex items-center">
                                    <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                                    To'lov shartlari
                                </h5>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">To'lov turi</label>
                                    <select id="modal_payment_type" onchange="calculateModalPaymentSchedule()"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="full">To'liq to'lov</option>
                                        <option value="installment" selected>Bo'lib to'lash</option>
                                    </select>
                                </div>

                                <div id="modal_installment_fields">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Boshlang'ich (%)</label>
                                            <input type="number" id="modal_initial_percent" min="0" max="100" value="20"
                                                   onchange="calculateModalPaymentSchedule()"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Muddat (yil)</label>
                                            <input type="number" id="modal_construction_years" min="1" max="10" value="2"
                                                   onchange="calculateModalPaymentSchedule()"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- To'lov xulasasi -->
                                <div class="grid grid-cols-1 gap-2">
                                    <div class="p-2 bg-green-50 border border-green-200 rounded text-center">
                                        <p class="text-xs text-green-700">Boshlang'ich to'lov</p>
                                        <p id="modal_initial_amount" class="font-semibold text-green-800">0 so'm</p>
                                    </div>
                                    <div class="p-2 bg-orange-50 border border-orange-200 rounded text-center">
                                        <p class="text-xs text-orange-700">Qoldiq summa</p>
                                        <p id="modal_remaining_amount" class="font-semibold text-orange-800">0 so'm</p>
                                    </div>
                                    <div class="p-2 bg-blue-50 border border-blue-200 rounded text-center">
                                        <p class="text-xs text-blue-700">Choraklik to'lov</p>
                                        <p id="modal_quarterly_payment" class="font-semibold text-blue-800">0 so'm</p>
                                    </div>
                                </div>
                            </div>

                            <!-- To'lov jadvali -->
                            <div id="modal_payment_schedule" class="max-h-64 overflow-y-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-2 py-1 border text-left">Yil</th>
                                            <th class="px-2 py-1 border text-left">Chorak</th>
                                            <th class="px-2 py-1 border text-right">Summa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal_quarters_tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
                    <div class="text-sm text-gray-600">
                        <p>Shartnoma summasi: <span id="modal_summary_amount" class="font-semibold text-blue-600">0 so'm</span></p>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeObjectModal()"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Bekor qilish
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Obyekt yaratish
                        </button>
                    </div>
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
// Global variables
let objectMap = null;
let mapMarker = null;
let currentZones = null;

// Zone data with coefficients
const zoneData = {
    '1': { name: 'ЗОНА-1', coefficient: 1.40, color: '#ef4444' },
    '2': { name: 'ЗОНА-2', coefficient: 1.25, color: '#f97316' },
    '3': { name: 'ЗОНА-3', coefficient: 1.00, color: '#eab308' },
    '4': { name: 'ЗОНА-4', coefficient: 0.75, color: '#22c55e' },
    '5': { name: 'ЗОНА-5', coefficient: 0.50, color: '#06b6d4' }
};

// Coefficient configuration (correct as per invest.toshkentinvest.uz logic)
const coefficients = {
    construction_type: {
        1: 1.0,    // Yangi qurilish
        2: 1.0,    // Rekonstruksiya
        3: 0.0,    // Ekspertiza talab etilmaydigan
        4: 0.0     // Hajmni o'zgartirmagan
    },
    object_type: {
        1: 0.5,    // Ijtimoiy infratuzilma
        2: 0.5,    // Davlat ulushi 50%+
        3: 0.5,    // Ishlab chiqarish
        4: 0.5,    // Omborxonalar
        5: 1.0     // Boshqa obyektlar
    },
    territorial_zone: {
        1: 1.40,   // 1-zona
        2: 1.25,   // 2-zona
        3: 1.00,   // 3-zona
        4: 0.75,   // 4-zona
        5: 0.50    // 5-zona
    },
    location: {
        'metro_radius_200m_outside': 0.6,
        'other_locations': 1.0
    }
};

// Zone boundaries for Tashkent (simplified polygons)
const zoneBoundaries = {
    '1': [
        [41.330, 69.200], [41.360, 69.200], [41.360, 69.280], [41.330, 69.280]
    ],
    '2': [
        [41.280, 69.230], [41.330, 69.230], [41.330, 69.310], [41.280, 69.310]
    ],
    '3': [
        [41.250, 69.260], [41.300, 69.260], [41.300, 69.340], [41.250, 69.340]
    ],
    '4': [
        [41.220, 69.290], [41.270, 69.290], [41.270, 69.370], [41.220, 69.370]
    ],
    '5': [
        [41.190, 69.320], [41.240, 69.320], [41.240, 69.400], [41.190, 69.400]
    ]
};

// Initialize map with zone layers
function initializeMap() {
    if (!objectMap && typeof L !== 'undefined') {
        try {
            objectMap = L.map('objectMap').setView([41.2995, 69.2401], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(objectMap);

            // Add zone polygons to map
            currentZones = L.layerGroup().addTo(objectMap);
            
            Object.keys(zoneBoundaries).forEach(zoneId => {
                const zoneInfo = zoneData[zoneId];
                const bounds = zoneBoundaries[zoneId];
                
                const polygon = L.polygon(bounds, {
                    color: zoneInfo.color,
                    fillColor: zoneInfo.color,
                    fillOpacity: 0.2,
                    weight: 2
                }).bindPopup(`${zoneInfo.name} (K=${zoneInfo.coefficient})`);
                
                currentZones.addLayer(polygon);
            });

            // Map click event
            objectMap.on('click', function(e) {
                if (mapMarker) {
                    objectMap.removeLayer(mapMarker);
                }

                mapMarker = L.marker(e.latlng).addTo(objectMap);
                const coordsInput = document.getElementById('coordinatesInput');
                if (coordsInput) {
                    coordsInput.value = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
                }

                // Detect zone
                detectZoneByCoordinates(e.latlng.lat, e.latlng.lng);
            });

            console.log('Map initialized successfully');
        } catch (error) {
            console.error('Error initializing map:', error);
        }
    }
}

// Zone detection by coordinates using polygon boundaries
function detectZoneByCoordinates(lat, lng) {
    let detectedZone = null;
    
    // Check each zone boundary
    for (const [zoneId, bounds] of Object.entries(zoneBoundaries)) {
        if (isPointInPolygon([lat, lng], bounds)) {
            detectedZone = zoneId;
            break;
        }
    }

    // Default to zone 3 if no zone found but within general Tashkent area
    if (!detectedZone && lat > 41.15 && lat < 41.45 && lng > 69.1 && lng < 69.5) {
        detectedZone = '3';
    }

    if (detectedZone && zoneData[detectedZone]) {
        const zone = zoneData[detectedZone];
        showZoneInfo(detectedZone, zone.name, zone.coefficient);
        
        // Auto select zone
        const zoneSelect = document.getElementById('modalTerritorialZone');
        if (zoneSelect) {
            zoneSelect.value = detectedZone;
            calculateModalEverything();
        }
    } else {
        hideZoneInfo();
    }
}

// Point in polygon algorithm
function isPointInPolygon(point, polygon) {
    const [x, y] = point;
    let inside = false;
    
    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        const [xi, yi] = polygon[i];
        const [xj, yj] = polygon[j];
        
        if (((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) {
            inside = !inside;
        }
    }
    
    return inside;
}

// Detect zone from coordinates input
function detectZoneFromCoordinatesInput() {
    const coordsInput = document.getElementById('coordinatesInput');
    if (!coordsInput) return;
    
    const coords = coordsInput.value.trim();
    if (!coords) return;
    
    const parts = coords.split(',');
    if (parts.length !== 2) {
        showNotification('Koordinatalar noto\'g\'ri formatda. Misol: 41.2995, 69.2401', 'warning');
        return;
    }
    
    const lat = parseFloat(parts[0].trim());
    const lng = parseFloat(parts[1].trim());
    
    if (isNaN(lat) || isNaN(lng)) {
        showNotification('Koordinatalar noto\'g\'ri formatda. Misol: 41.2995, 69.2401', 'warning');
        return;
    }
    
    if (mapMarker && objectMap) {
        objectMap.removeLayer(mapMarker);
    }
    
    if (objectMap && typeof L !== 'undefined') {
        mapMarker = L.marker([lat, lng]).addTo(objectMap);
        objectMap.setView([lat, lng], 13);
    }
    
    detectZoneByCoordinates(lat, lng);
}

// Show zone info
function showZoneInfo(zoneId, zoneName, coefficient) {
    const zoneInfo = document.getElementById('zoneInfo');
    if (!zoneInfo) return;
    
    const zoneClass = `zone-${zoneId}`;
    
    zoneInfo.className = `mt-2 p-3 border-l-4 rounded ${zoneClass}`;
    if (zoneData[zoneId]) {
        zoneInfo.style.borderColor = zoneData[zoneId].color;
        zoneInfo.style.backgroundColor = zoneData[zoneId].color + '20';
    }
    zoneInfo.classList.remove('hidden');
    
    const detectedZoneEl = document.getElementById('detectedZone');
    const zoneCoefficientEl = document.getElementById('zoneCoefficient');
    
    if (detectedZoneEl) detectedZoneEl.textContent = zoneName;
    if (zoneCoefficientEl) zoneCoefficientEl.textContent = coefficient;
}

// Hide zone info
function hideZoneInfo() {
    const zoneInfo = document.getElementById('zoneInfo');
    if (zoneInfo) {
        zoneInfo.classList.add('hidden');
    }
}

// Search functions
function searchSubjects() {
    const searchInput = document.getElementById('subjectSearch');
    const dropdown = document.getElementById('subjectDropdown');
    const select = document.getElementById('subjectSelect');
    
    if (!searchInput || !dropdown || !select) return;
    
    const searchTerm = searchInput.value.toLowerCase();

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
    const searchInput = document.getElementById('objectSearch');
    const dropdown = document.getElementById('objectDropdown');
    const select = document.getElementById('objectSelect');
    
    if (!searchInput || !dropdown || !select) return;
    
    const searchTerm = searchInput.value.toLowerCase();

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
    if (!dropdown) return;
    
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
        const searchInput = document.getElementById('subjectSearch');
        const select = document.getElementById('subjectSelect');
        const dropdown = document.getElementById('subjectDropdown');
        
        if (searchInput) searchInput.value = option.textContent;
        if (select) select.value = option.value;
        if (dropdown) dropdown.classList.add('hidden');
    } else {
        const searchInput = document.getElementById('objectSearch');
        const select = document.getElementById('objectSelect');
        const dropdown = document.getElementById('objectDropdown');
        
        if (searchInput) searchInput.value = option.textContent;
        if (select) select.value = option.value;
        if (dropdown) dropdown.classList.add('hidden');
        updateObjectVolume();
    }
}

// CORRECTED CALCULATION LOGIC (as per invest.toshkentinvest.uz)
function calculateTotal() {
    const baseAmountSelect = document.querySelector('select[name="base_amount_id"]');
    const volumeInput = document.querySelector('input[name="contract_volume"]');
    const calculatedBhInput = document.querySelector('input[name="calculated_bh"]');
    const totalDisplay = document.getElementById('total_amount_display');
    const formulaDisplay = document.getElementById('formula_display');

    if (!baseAmountSelect || !volumeInput || !calculatedBhInput || !totalDisplay) return;

    const selectedOption = baseAmountSelect.options[baseAmountSelect.selectedIndex];
    const baseAmount = selectedOption ? parseFloat(selectedOption.dataset.amount) : 0;
    const volume = parseFloat(volumeInput.value) || 0;
    const calculatedBh = parseFloat(calculatedBhInput.value) || 0;

    // CORRECT FORMULA: Ti = Calculated_Bh × Volume (NOT base × volume × coef)
    const totalAmount = calculatedBh * volume;

    totalDisplay.textContent = formatNumber(totalAmount) + ' so\'m';

    if (formulaDisplay) {
        if (calculatedBh && volume) {
            formulaDisplay.textContent =
                `Ti = ${formatNumber(calculatedBh)} × ${formatNumber(volume)} m³ = ${formatNumber(totalAmount)} so'm`;
        } else {
            formulaDisplay.textContent = '';
        }
    }

    calculatePaymentSchedule();
}

// Update object volume when object is selected
function updateObjectVolume() {
    const objectSelect = document.querySelector('select[name="object_id"]');
    if (!objectSelect) return;
    
    const selectedOption = objectSelect.options[objectSelect.selectedIndex];

    if (selectedOption && selectedOption.dataset.volume) {
        // Display object volumes
        const hb = parseFloat(selectedOption.dataset.volume) || 0;
        const hyu = parseFloat(selectedOption.dataset.abovePermit) || 0;
        const ha = parseFloat(selectedOption.dataset.parking) || 0;
        const ht = parseFloat(selectedOption.dataset.technical) || 0;
        const hu = parseFloat(selectedOption.dataset.common) || 0;

        const displayElements = {
            'display_hb': hb,
            'display_hyu': hyu,
            'display_ha': ha,
            'display_ht': ht,
            'display_hu': hu
        };

        Object.keys(displayElements).forEach(id => {
            const element = document.getElementById(id);
            if (element) element.textContent = formatNumber(displayElements[id]) + ' m³';
        });

        // Calculate contract volume: (Hb + Hyu) - (Ha + Ht + Hu)
        const contractVolume = (hb + hyu) - (ha + ht + hu);
        const displayCalcVolume = document.getElementById('display_calculated_volume');
        if (displayCalcVolume) {
            displayCalcVolume.textContent = formatNumber(contractVolume) + ' m³';
        }

        const volumeInput = document.querySelector('input[name="contract_volume"]');
        if (volumeInput) {
            volumeInput.value = contractVolume.toFixed(2);
        }

        // Calculate coefficients
        const constructionType = selectedOption.dataset.constructionType;
        const objectType = selectedOption.dataset.objectType;
        const zone = selectedOption.dataset.zone;
        const location = selectedOption.dataset.location;

        const kt = coefficients.construction_type[constructionType] || 1.0;
        const ko = coefficients.object_type[objectType] || 1.0;
        const kz = coefficients.territorial_zone[zone] || 1.0;
        const kj = coefficients.location[location] || 1.0;

        let totalCoef = kt * ko * kz * kj;
        
        // Apply coefficient limits as per logic
        if (kt === 0 || ko === 0) {
            totalCoef = 0.0; // If construction or object type is 0, total is 0
        } else {
            totalCoef = Math.max(0.5, Math.min(2.0, totalCoef));
        }

        // Display coefficients
        const coefficientElements = {
            'display_kt': kt.toFixed(2),
            'display_ko': ko.toFixed(2),
            'display_kz': kz.toFixed(2),
            'display_kj': kj.toFixed(2),
            'display_total_coef': totalCoef.toFixed(2)
        };

        Object.keys(coefficientElements).forEach(id => {
            const element = document.getElementById(id);
            if (element) element.textContent = coefficientElements[id];
        });

        // CORRECT CALCULATION: Calculated_Bh = Base_Amount × Coefficient
        const baseAmountSelect = document.querySelector('select[name="base_amount_id"]');
        const calculatedBhInput = document.querySelector('input[name="calculated_bh"]');
        
        if (baseAmountSelect && calculatedBhInput) {
            const selectedBaseOption = baseAmountSelect.options[baseAmountSelect.selectedIndex];
            const baseAmount = selectedBaseOption ? parseFloat(selectedBaseOption.dataset.amount) : 0;
            const calculatedBh = baseAmount * totalCoef;
            calculatedBhInput.value = calculatedBh.toFixed(2);
        }

        calculateTotal();
    }
}

// Modal calculation functions
function calculateModalEverything() {
    calculateModalVolume();
    calculateModalCoefficients();
    calculateModalContractSum();
    calculateModalPaymentSchedule();
}

function calculateModalVolume() {
    const hb = parseFloat(safeGetValue('modal_hb')) || 0;
    const hyu = parseFloat(safeGetValue('modal_hyu')) || 0;
    const ha = parseFloat(safeGetValue('modal_ha')) || 0;
    const ht = parseFloat(safeGetValue('modal_ht')) || 0;
    const hu = parseFloat(safeGetValue('modal_hu')) || 0;

    const calculatedVolume = (hb + hyu) - (ha + ht + hu);
    const displayEl = document.getElementById('calculated_volume_modal');
    if (displayEl) {
        displayEl.textContent = calculatedVolume.toFixed(2) + ' m³';
    }
}

function calculateModalCoefficients() {
    const constructionType = document.querySelector('select[name="construction_type_id"]');
    const objectType = document.querySelector('select[name="object_type_id"]');
    const territorialZone = document.querySelector('select[name="territorial_zone_id"]');
    const locationType = document.querySelector('select[name="location_type"]');

    const kt = constructionType && constructionType.selectedIndex > 0 ?
        parseFloat(constructionType.options[constructionType.selectedIndex].dataset.coef) : 1.0;
    const ko = objectType && objectType.selectedIndex > 0 ?
        parseFloat(objectType.options[objectType.selectedIndex].dataset.coef) : 1.0;
    const kz = territorialZone && territorialZone.selectedIndex > 0 ?
        parseFloat(territorialZone.options[territorialZone.selectedIndex].dataset.coef) : 1.0;
    const kj = locationType && locationType.selectedIndex > 0 ?
        parseFloat(locationType.options[locationType.selectedIndex].dataset.coef) : 1.0;

    let totalCoef = kt * ko * kz * kj;
    
    // Apply coefficient rules
    if (kt === 0 || ko === 0) {
        totalCoef = 0.0;
    } else {
        totalCoef = Math.max(0.5, Math.min(2.0, totalCoef));
    }

    // Update displays
    const coefficientDisplays = {
        'modal_kt': kt.toFixed(2),
        'modal_ko': ko.toFixed(2),
        'modal_kz': kz.toFixed(2),
        'modal_kj': kj.toFixed(2),
        'modal_total_coef': totalCoef.toFixed(2)
    };

    Object.keys(coefficientDisplays).forEach(id => {
        const element = document.getElementById(id);
        if (element) element.textContent = coefficientDisplays[id];
    });
}

function calculateModalContractSum() {
    const baseAmountSelect = document.getElementById('modal_base_amount');
    const baseAmount = baseAmountSelect ? parseFloat(baseAmountSelect.value) || 0 : 0;

    const volumeEl = document.getElementById('calculated_volume_modal');
    const volumeText = volumeEl ? volumeEl.textContent : '0 m³';
    const volume = parseFloat(volumeText.replace(' m³', '')) || 0;

    const coefficientEl = document.getElementById('modal_total_coef');
    const coefficientText = coefficientEl ? coefficientEl.textContent : '1.00';
    const coefficient = parseFloat(coefficientText) || 1;

    if (baseAmount > 0 && volume > 0) {
        // CORRECT CALCULATION: Calculated_Bh = Base × Coefficient, then Ti = Calculated_Bh × Volume
        const calculatedBh = baseAmount * coefficient;
        const totalAmount = calculatedBh * volume;

        const totalAmountEl = document.getElementById('modal_total_amount');
        const summaryAmountEl = document.getElementById('modal_summary_amount');
        const formulaEl = document.getElementById('modal_formula_display');
        const calculatedBhEl = document.getElementById('modal_calculated_bh');

        if (totalAmountEl) totalAmountEl.textContent = formatNumber(totalAmount) + ' so\'m';
        if (summaryAmountEl) summaryAmountEl.textContent = formatNumber(totalAmount) + ' so\'m';
        if (calculatedBhEl) calculatedBhEl.textContent = formatNumber(calculatedBh) + ' so\'m';

        if (formulaEl) {
            formulaEl.textContent = `Ti = ${formatNumber(calculatedBh)} × ${volume.toFixed(2)} = ${formatNumber(totalAmount)} so'm`;
        }
    } else {
        const totalAmountEl = document.getElementById('modal_total_amount');
        const summaryAmountEl = document.getElementById('modal_summary_amount');
        const formulaEl = document.getElementById('modal_formula_display');
        const calculatedBhEl = document.getElementById('modal_calculated_bh');

        if (totalAmountEl) totalAmountEl.textContent = '0 so\'m';
        if (summaryAmountEl) summaryAmountEl.textContent = '0 so\'m';
        if (calculatedBhEl) calculatedBhEl.textContent = '0 so\'m';
        if (formulaEl) formulaEl.textContent = 'Ti = Calculated_Bh × Hajm';
    }
}

function calculateModalPaymentSchedule() {
    const paymentTypeEl = document.getElementById('modal_payment_type');
    const totalAmountEl = document.getElementById('modal_total_amount');
    
    if (!paymentTypeEl || !totalAmountEl) return;
    
    const paymentType = paymentTypeEl.value;
    const totalAmountText = totalAmountEl.textContent;
    const totalAmount = parseFloat(totalAmountText.replace(/[^\d]/g, '')) || 0;

    if (totalAmount <= 0) {
        clearModalPaymentDisplay();
        return;
    }

    const initialPercentEl = document.getElementById('modal_initial_percent');
    const yearsEl = document.getElementById('modal_construction_years');
    
    const initialPercent = initialPercentEl ? parseInt(initialPercentEl.value) || 20 : 20;
    const years = yearsEl ? parseInt(yearsEl.value) || 2 : 2;
    const quarters = years * 4;

    const installmentFields = document.getElementById('modal_installment_fields');

    if (paymentType === 'full') {
        if (installmentFields) installmentFields.style.display = 'none';
        
        const initialAmountEl = document.getElementById('modal_initial_amount');
        const remainingAmountEl = document.getElementById('modal_remaining_amount');
        const quarterlyPaymentEl = document.getElementById('modal_quarterly_payment');
        const quartersTableEl = document.getElementById('modal_quarters_tbody');

        if (initialAmountEl) initialAmountEl.textContent = formatNumber(totalAmount) + ' so\'m';
        if (remainingAmountEl) remainingAmountEl.textContent = '0 so\'m';
        if (quarterlyPaymentEl) quarterlyPaymentEl.textContent = '0 so\'m';

        if (quartersTableEl) {
            quartersTableEl.innerHTML = `
                <tr class="bg-green-50">
                    <td class="px-2 py-1 border font-semibold">${new Date().getFullYear()}</td>
                    <td class="px-2 py-1 border font-semibold">To'liq</td>
                    <td class="px-2 py-1 border text-right font-semibold">${formatNumber(totalAmount)} so'm</td>
                </tr>
            `;
        }
    } else {
        if (installmentFields) installmentFields.style.display = 'block';

        const initialPayment = totalAmount * (initialPercent / 100);
        const remainingAmount = totalAmount - initialPayment;
        const quarterlyPayment = remainingAmount / quarters;

        const initialAmountEl = document.getElementById('modal_initial_amount');
        const remainingAmountEl = document.getElementById('modal_remaining_amount');
        const quarterlyPaymentEl = document.getElementById('modal_quarterly_payment');

        if (initialAmountEl) initialAmountEl.textContent = formatNumber(initialPayment) + ' so\'m';
        if (remainingAmountEl) remainingAmountEl.textContent = formatNumber(remainingAmount) + ' so\'m';
        if (quarterlyPaymentEl) quarterlyPaymentEl.textContent = formatNumber(quarterlyPayment) + ' so\'m';

        // Create payment schedule
        let tbody = `
            <tr class="bg-green-50">
                <td class="px-2 py-1 border font-semibold">${new Date().getFullYear()}</td>
                <td class="px-2 py-1 border font-semibold">Boshlang'ich</td>
                <td class="px-2 py-1 border text-right font-semibold">${formatNumber(initialPayment)} so'm</td>
            </tr>
        `;

        const startYear = new Date().getFullYear();
        const startQuarter = Math.ceil((new Date().getMonth() + 1) / 3);

        for (let i = 0; i < quarters; i++) {
            const currentQuarter = ((startQuarter - 1 + i) % 4) + 1;
            const currentYear = startYear + Math.floor((startQuarter - 1 + i) / 4);

            tbody += `
                <tr>
                    <td class="px-2 py-1 border">${currentYear}</td>
                    <td class="px-2 py-1 border">${currentQuarter}-chorak</td>
                    <td class="px-2 py-1 border text-right">${formatNumber(quarterlyPayment)} so'm</td>
                </tr>
            `;
        }

        const quartersTableEl = document.getElementById('modal_quarters_tbody');
        if (quartersTableEl) {
            quartersTableEl.innerHTML = tbody;
        }
    }
}

function clearModalPaymentDisplay() {
    const elements = {
        'modal_initial_amount': '0 so\'m',
        'modal_remaining_amount': '0 so\'m',
        'modal_quarterly_payment': '0 so\'m'
    };

    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) element.textContent = elements[id];
    });

    const quartersTableEl = document.getElementById('modal_quarters_tbody');
    if (quartersTableEl) {
        quartersTableEl.innerHTML = '';
    }
}

// Payment schedule calculation for main form
function calculatePaymentSchedule() {
    const paymentTypeSelect = document.querySelector('select[name="payment_type"]');
    const totalAmountDisplay = document.getElementById('total_amount_display');
    
    if (!paymentTypeSelect || !totalAmountDisplay) return;
    
    const paymentType = paymentTypeSelect.value;
    const totalAmountText = totalAmountDisplay.textContent;
    const totalAmount = parseFloat(totalAmountText.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;

    if (totalAmount <= 0) return;

    const initialPercentInput = document.querySelector('input[name="initial_payment_percent"]');
    const yearsInput = document.querySelector('input[name="construction_period_years"]');
    
    const initialPercent = initialPercentInput ? parseInt(initialPercentInput.value) || 20 : 20;
    const years = yearsInput ? parseInt(yearsInput.value) || 2 : 2;
    const quarters = years * 4;

    const initialPaymentEl = document.getElementById('initial_payment_amount');
    const remainingAmountEl = document.getElementById('remaining_amount');
    const quarterlyPaymentEl = document.getElementById('quarterly_payment');
    const quartersTableEl = document.getElementById('quarters_tbody');

    if (paymentType === 'full') {
        if (initialPaymentEl) initialPaymentEl.textContent = formatNumber(totalAmount) + ' so\'m';
        if (remainingAmountEl) remainingAmountEl.textContent = '0 so\'m';
        if (quarterlyPaymentEl) quarterlyPaymentEl.textContent = '0 so\'m';

        if (quartersTableEl) {
            quartersTableEl.innerHTML = `
                <tr>
                    <td class="px-4 py-2 border">${new Date().getFullYear()}</td>
                    <td class="px-4 py-2 border">To'liq to'lov</td>
                    <td class="px-4 py-2 border text-right">${formatNumber(totalAmount)} so'm</td>
                </tr>
            `;
        }
    } else {
        const initialPayment = totalAmount * (initialPercent / 100);
        const remainingAmount = totalAmount - initialPayment;
        const quarterlyPayment = remainingAmount / quarters;

        if (initialPaymentEl) initialPaymentEl.textContent = formatNumber(initialPayment) + ' so\'m';
        if (remainingAmountEl) remainingAmountEl.textContent = formatNumber(remainingAmount) + ' so\'m';
        if (quarterlyPaymentEl) quarterlyPaymentEl.textContent = formatNumber(quarterlyPayment) + ' so\'m';

        // Create quarterly schedule
        let tbody = `
            <tr class="bg-green-50">
                <td class="px-4 py-2 border font-semibold">${new Date().getFullYear()}</td>
                <td class="px-4 py-2 border font-semibold">Boshlang'ich to'lov</td>
                <td class="px-4 py-2 border text-right font-semibold">${formatNumber(initialPayment)} so'm</td>
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
                    <td class="px-4 py-2 border text-right">${formatNumber(quarterlyPayment)} so'm</td>
                </tr>
            `;
        }

        if (quartersTableEl) {
            quartersTableEl.innerHTML = tbody;
        }
    }
}

function togglePaymentFields() {
    const paymentTypeSelect = document.querySelector('select[name="payment_type"]');
    if (!paymentTypeSelect) return;
    
    const paymentType = paymentTypeSelect.value;
    const initialPaymentField = document.getElementById('initial_payment_field');
    const constructionPeriodField = document.getElementById('construction_period_field');
    const initialPercentInput = document.querySelector('input[name="initial_payment_percent"]');

    if (paymentType === 'full') {
        if (initialPaymentField) initialPaymentField.style.display = 'none';
        if (constructionPeriodField) constructionPeriodField.style.display = 'none';
        if (initialPercentInput) initialPercentInput.value = 100;
    } else {
        if (initialPaymentField) initialPaymentField.style.display = 'block';
        if (constructionPeriodField) constructionPeriodField.style.display = 'block';
        if (initialPercentInput && initialPercentInput.value == 100) {
            initialPercentInput.value = 20;
        }
    }
    calculatePaymentSchedule();
}

// Modal functions
function openSubjectModal() {
    const modal = document.getElementById('subjectModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeSubjectModal() {
    const modal = document.getElementById('subjectModal');
    const form = document.getElementById('subjectModalForm');
    
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    if (form) {
        form.reset();
        toggleEntityFields();
    }
}
function toggleEntityFields() {
    const legalEntityRadio = document.querySelector('input[name="is_legal_entity"]:checked');
    if (!legalEntityRadio) return;
    
    const isLegalEntity = legalEntityRadio.value === '1';
    const legalFields = document.getElementById('legalEntityFields');
    const physicalFields = document.getElementById('physicalPersonFields');

    // Update card styles
    document.querySelectorAll('.entity-type-card').forEach((card) => {
        const input = card.parentElement.querySelector('input[type="radio"]');
        card.classList.remove('border-blue-500', 'bg-blue-50', 'border-green-500', 'bg-green-50', 'border-gray-200');

        if (input && input.checked) {
            if (input.value === '1') {
                card.classList.add('border-blue-500', 'bg-blue-50');
            } else {
                card.classList.add('border-green-500', 'bg-green-50');
            }
        } else {
            card.classList.add('border-gray-200');
        }
    });

    if (legalFields && physicalFields) {
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
}

function openObjectModal() {
    const subjectSelect = document.querySelector('select[name="subject_id"]');
    if (!subjectSelect || !subjectSelect.value) {
        showNotification('Avval buyurtmachini tanlang', 'warning');
        return;
    }

    const modal = document.getElementById('objectModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            initializeMap();
            if (objectMap) {
                objectMap.invalidateSize();
            }
            calculateModalEverything();
        }, 200);
    }
}

function closeObjectModal() {
    const modal = document.getElementById('objectModal');
    const form = document.getElementById('objectModalForm');
    const zoneInfo = document.getElementById('zoneInfo');
    
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    if (form) {
        form.reset();
    }
    
    if (zoneInfo) {
        zoneInfo.classList.add('hidden');
    }

    // Reset all displays
    const resetElements = {
        'calculated_volume_modal': '0.00 m³',
        'modal_kt': '1.00',
        'modal_ko': '1.00',
        'modal_kz': '1.00',
        'modal_kj': '1.00',
        'modal_total_coef': '1.00',
        'modal_total_amount': '0 so\'m',
        'modal_summary_amount': '0 so\'m'
    };

    Object.keys(resetElements).forEach(id => {
        const element = document.getElementById(id);
        if (element) element.textContent = resetElements[id];
    });

    clearModalPaymentDisplay();

    if (mapMarker && objectMap) {
        objectMap.removeLayer(mapMarker);
        mapMarker = null;
    }
}

function updateDistrictInfo() {
    calculateModalEverything();
}

// Form submission handlers
function handleSubjectFormSubmission() {
    const form = document.getElementById('subjectModalForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');

        toggleLoading(submitButton, true);

        try {
            const response = await fetch('/contracts/create-subject', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                const subjectSelect = document.getElementById('subjectSelect');
                const subjectSearch = document.getElementById('subjectSearch');
                
                if (subjectSelect) {
                    const newOption = new Option(result.subject.text, result.subject.id, true, true);
                    subjectSelect.add(newOption);
                    subjectSelect.value = result.subject.id;
                }
                
                if (subjectSearch) {
                    subjectSearch.value = result.subject.text;
                }

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
}

function handleContractFormSubmission() {
    const form = document.getElementById('contractForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');

        toggleLoading(submitButton, true);

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
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
}

// Helper functions
function formatNumber(num) {
    if (isNaN(num)) return '0';
    return new Intl.NumberFormat('uz-UZ').format(Math.round(num));
}

function toggleLoading(button, loading) {
    if (!button) return;
    
    if (loading) {
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Kutilmoqda...';
    } else {
        button.disabled = false;
        
        // Restore original button text based on context
        if (button.closest('#subjectModalForm') || button.closest('#objectModalForm')) {
            button.innerHTML = 'Yaratish';
        } else if (button.closest('#contractForm')) {
            button.innerHTML = '<i data-feather="save" class="w-4 h-4 mr-2 inline"></i>Shartnoma yaratish';
        } else {
            button.innerHTML = 'Yaratish';
        }

        // Refresh feather icons if available
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const typeClasses = {
        'success': 'bg-green-500 text-white',
        'error': 'bg-red-500 text-white',
        'warning': 'bg-yellow-500 text-black',
        'info': 'bg-blue-500 text-white'
    };
    
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${typeClasses[type] || typeClasses['info']}`;

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

function safeGetValue(elementId) {
    const element = document.getElementById(elementId);
    return element ? element.value : '';
}

// Event listeners for outside clicks
function setupOutsideClickListeners() {
    document.addEventListener('click', function(e) {
        const subjectSearch = document.getElementById('subjectSearch');
        const subjectDropdown = document.getElementById('subjectDropdown');
        const objectSearch = document.getElementById('objectSearch');
        const objectDropdown = document.getElementById('objectDropdown');

        if (subjectSearch && subjectDropdown && 
            !e.target.closest('#subjectSearch') && 
            !e.target.closest('#subjectDropdown')) {
            subjectDropdown.classList.add('hidden');
        }
        
        if (objectSearch && objectDropdown && 
            !e.target.closest('#objectSearch') && 
            !e.target.closest('#objectDropdown')) {
            objectDropdown.classList.add('hidden');
        }
    });
}

// Setup event listeners
function setupEventListeners() {
    // Payment type change
    const paymentTypeSelect = document.querySelector('select[name="payment_type"]');
    if (paymentTypeSelect) {
        paymentTypeSelect.addEventListener('change', () => {
            togglePaymentFields();
            calculatePaymentSchedule();
        });
    }

    // Base amount change
    const baseAmountSelect = document.querySelector('select[name="base_amount_id"]');
    if (baseAmountSelect) {
        baseAmountSelect.addEventListener('change', calculateTotal);
    }

    // Payment inputs change
    const initialPercentInput = document.querySelector('input[name="initial_payment_percent"]');
    const constructionYearsInput = document.querySelector('input[name="construction_period_years"]');
    
    if (initialPercentInput) {
        initialPercentInput.addEventListener('input', calculatePaymentSchedule);
    }
    
    if (constructionYearsInput) {
        constructionYearsInput.addEventListener('input', calculatePaymentSchedule);
    }

    // Modal base amount
    const modalBaseAmount = document.getElementById('modal_base_amount');
    if (modalBaseAmount && modalBaseAmount.options.length > 1) {
        modalBaseAmount.selectedIndex = modalBaseAmount.options.length - 1;
    }
}

// Main initialization
function initializeApplication() {
    try {
        calculateTotal();
        togglePaymentFields();
        toggleEntityFields();
        calculatePaymentSchedule();
        setupEventListeners();
        setupOutsideClickListeners();
        handleSubjectFormSubmission();
        handleContractFormSubmission();
        
        console.log('Application initialized successfully');
    } catch (error) {
        console.error('Error initializing application:', error);
    }
}

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApplication();
});

// CSS styles
const styles = `
<style>
.zone-1 { 
    border-color: #ef4444 !important; 
    background-color: rgba(239, 68, 68, 0.1) !important; 
}
.zone-2 { 
    border-color: #f97316 !important; 
    background-color: rgba(249, 115, 22, 0.1) !important; 
}
.zone-3 { 
    border-color: #eab308 !important; 
    background-color: rgba(234, 179, 8, 0.1) !important; 
}
.zone-4 { 
    border-color: #22c55e !important; 
    background-color: rgba(34, 197, 94, 0.1) !important; 
}
.zone-5 { 
    border-color: #06b6d4 !important; 
    background-color: rgba(6, 182, 212, 0.1) !important; 
}

.entity-type-card {
    transition: all 0.3s ease;
}

.entity-type-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@media(max-width: 768px) {
    .card-body {
        overflow-x: auto;
    }
    
    #objectModal .grid-cols-3 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .max-w-[98vw] {
        max-width: 95vw;
    }
}

/* Loading states */
.loading {
    pointer-events: none;
    opacity: 0.6;
}

/* Notification animations */
.notification-enter {
    transform: translateX(100%);
    opacity: 0;
}

.notification-enter-active {
    transform: translateX(0);
    opacity: 1;
    transition: all 300ms ease-in-out;
}

/* Custom scrollbar for dropdowns */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
`;

// Inject styles into head
if (document.head) {
    document.head.insertAdjacentHTML('beforeend', styles);
}

// Global error handlers
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
});

// Final initialization check
document.addEventListener('DOMContentLoaded', function() {
    // Check if required libraries are loaded
    if (typeof L === 'undefined') {
        console.warn('Leaflet library not loaded - map functionality will be limited');
    }
    
    if (typeof feather === 'undefined') {
        console.warn('Feather icons not loaded - icons may not display correctly');
    } else {
        feather.replace();
    }
    
    console.log('Contract creation system fully loaded and ready');
});

console.log('Contract creation script loaded successfully');
</script>
@endpush     