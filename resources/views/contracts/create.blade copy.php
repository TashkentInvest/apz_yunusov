@extends('layouts.app')

@section('title', 'Yangi Shartnoma Yaratish - Tashkent Invest')
@section('page-title', 'Yangi Shartnoma Yaratish')

@section('header-actions')
<div class="flex items-center space-x-3">
    <button onclick="toggleSidebar()" class="p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition-colors">
        <i data-feather="help-circle" class="w-5 h-5"></i>
    </button>
    <a href="{{ route('contracts.index') }}"
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2 inline"></i>
        Orqaga qaytish
    </a>
</div>
@endsection

@section('content')
<!-- Documentation Sidebar -->
<div id="documentationSidebar" class="fixed inset-y-0 right-0 z-50 w-80 bg-white shadow-xl border-l border-gray-200 transform translate-x-full transition-transform duration-300">
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Qo'llanma va Hujjatlar</h2>
        <button onclick="toggleSidebar()" class="p-2 rounded-lg text-gray-400 hover:text-gray-500 transition-colors">
            <i data-feather="x" class="w-5 h-5"></i>
        </button>
    </div>

    <div class="p-4 space-y-6 h-full overflow-y-auto">
        <!-- Legal Documents -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Qonuniy hujjatlar</h3>
            <div class="space-y-3">
                <a href="https://lex.uz/ru/docs/-6993957" target="_blank"
                   class="block p-3 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all">
                    <div class="flex items-start">
                        <i data-feather="file-text" class="w-4 h-4 mt-0.5 mr-3 text-blue-600 flex-shrink-0"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900">VI-104-94-14-0-K/24-son</p>
                            <p class="text-xs text-gray-600 mt-1">Xalq deputatlari Toshkent shahar Kengashining qarori</p>
                            <p class="text-xs text-gray-500 mt-1">02.07.2024</p>
                        </div>
                    </div>
                </a>

                <a href="https://lex.uz/uz/docs/-6851920" target="_blank"
                   class="block p-3 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all">
                    <div class="flex items-start">
                        <i data-feather="file-text" class="w-4 h-4 mt-0.5 mr-3 text-green-600 flex-shrink-0"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900">149-son</p>
                            <p class="text-xs text-gray-600 mt-1">O'zbekiston Respublikasi Vazirlar Mahkamasining qarori</p>
                            <p class="text-xs text-gray-500 mt-1">25.03.2024</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Calculation Formula -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Hisoblash formulasi</h3>
            <div class="text-sm text-gray-700 space-y-2">
                <div class="bg-white p-3 rounded border">
                    <p class="font-medium text-gray-900 mb-1">Ti = Hisobga olinadigan Bh × Hisoblash hajmi</p>
                </div>
                <div class="text-xs space-y-1">
                    <p><strong>Bu yerda:</strong></p>
                    <p>• Hisobga olinadigan Bh = Bh × (Kt × Ko × Kz × Kj)</p>
                    <p>• Hisoblash hajmi = (Hb + Hyu) - (Ha + Ht + Hu)</p>
                </div>
            </div>
        </div>

        <!-- Zone Coefficients -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Zona koeffitsientlari</h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                    <span class="font-medium">1-zona</span>
                    <span class="font-semibold">1.40</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                    <span class="font-medium">2-zona</span>
                    <span class="font-semibold">1.25</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                    <span class="font-medium">3-zona</span>
                    <span class="font-semibold">1.00</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                    <span class="font-medium">4-zona</span>
                    <span class="font-semibold">0.75</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                    <span class="font-medium">5-zona</span>
                    <span class="font-semibold">0.50</span>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Foydalanish qo'llanmasi</h3>
            <div class="text-xs text-gray-600 space-y-2">
                <p>• Xaritadan joy tanlash orqali zona avtomatik aniqlanadi</p>
                <p>• Barcha hajm ma'lumotlari to'ldirilganda summa avtomatik hisoblanadi</p>
                <p>• To'lov jadvali qurilish muddati asosida yaratiladi</p>
                <p>• Koordinatalarni qo'lda ham kiritish mumkin</p>
            </div>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleSidebar()"></div>

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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tugatish sanasi *</label>
                    <input type="date" name="completion_date" value="{{ old('completion_date') }}" required readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100">
                    @error('completion_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Qurilish muddati asosida avtomatik hisoblanadi</p>
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

        <!-- Mulk egasi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Mulk egasi</h3>
                <button type="button" onclick="openSubjectModal()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
                    Yangi Mulkegasi
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mulk egasi *</label>
                    <div class="relative">
                        <input type="text" id="subjectSearch" placeholder="Mulk egasi qidirish..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               onkeyup="searchSubjects()" autocomplete="off">
                        <select name="subject_id" required id="subjectSelect" style="display: none;"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Mulk egasini tanlang</option>
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

        <!-- Obyekt -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Obyekt</h3>
                <button type="button" onclick="openObjectModal()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
                    Yangi obyekt
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

<!-- Mulk egasi yaratish modali -->
<div id="subjectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block bg-white rounded-xl shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
            <form id="subjectModalForm">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Yangi Mulk egasi yaratish</h3>
                </div>
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <!-- Shaxs turi tanlash -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shaxs turi</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative">
                                <input type="radio" name="is_legal_entity" value="1" checked onchange="toggleEntityFields()" class="sr-only">
                                <div class="entity-type-card p-3 border-2 border-blue-500 bg-blue-50 rounded-lg cursor-pointer transition-all">
                                    <div class="text-center">
                                        <i data-feather="briefcase" class="w-6 h-6 mx-auto text-blue-600 mb-1"></i>
                                        <p class="font-medium text-gray-900">Yuridik shaxs</p>
                                    </div>
                                </div>
                            </label>
                            <label class="relative">
                                <input type="radio" name="is_legal_entity" value="0" onchange="toggleEntityFields()" class="sr-only">
                                <div class="entity-type-card p-3 border-2 border-gray-200 rounded-lg cursor-pointer transition-all">
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
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-xl shadow-xl transform transition-all max-w-6xl w-full max-h-[90vh] overflow-hidden">
            <form id="objectModalForm">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700">
                    <h3 class="text-xl font-bold text-white">Yangi obyekt yaratish</h3>
                    <p class="text-sm text-blue-100 mt-1">Obyekt ma'lumotlarini to'ldiring</p>
                </div>

                <div class="flex h-[calc(90vh-200px)]">
                    <!-- Chap panel - Form -->
                    <div class="w-1/2 border-r border-gray-200 p-6 overflow-y-auto">
                        <!-- Asosiy ma'lumotlar -->
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i data-feather="map-pin" class="w-5 h-5 inline mr-2 text-blue-600"></i>
                                Asosiy ma'lumotlar
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tuman *</label>
                                    <select name="district_id" required
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Manzil *</label>
                                    <textarea name="address" rows="3" required
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="To'liq manzilni kiriting"></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Kadastr raqami</label>
                                        <input type="text" name="cadastre_number"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Koordinatalar</label>
                                        <input type="text" name="geolocation" id="coordinatesInput"
                                               placeholder="41.2995, 69.2401" readonly
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Qurilish hajmlari -->
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i data-feather="home" class="w-5 h-5 inline mr-2 text-green-600"></i>
                                Qurilish hajmlari (m³)
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Umumiy hajm (Hb) *</label>
                                    <input type="number" name="construction_volume" step="0.01" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ruxsat etilganidan yuqori (Hyu)</label>
                                    <input type="number" name="above_permit_volume" step="0.01" value="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Avtoturargoh (Ha)</label>
                                    <input type="number" name="parking_volume" step="0.01" value="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Texnik xonalar (Ht)</label>
                                    <input type="number" name="technical_rooms_volume" step="0.01" value="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Umumiy foydalanish (Hu)</label>
                                    <input type="number" name="common_area_volume" step="0.01" value="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Koeffitsientlar -->
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i data-feather="settings" class="w-5 h-5 inline mr-2 text-purple-600"></i>
                                Koeffitsientlar
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Qurilish turi (Kt)</label>
                                    <select name="construction_type_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Tanlang</option>
                                        <option value="1">Yangi kapital qurilish (1.0)</option>
                                        <option value="2">Obyektni rekonstruksiya qilish (1.0)</option>
                                        <option value="3">Ekspertiza talab etilmaydigan rekonstruksiya (0.0)</option>
                                        <option value="4">Hajm o'zgarmaydigan rekonstruksiya (0.0)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Obyekt turi (Ko)</label>
                                    <select name="object_type_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Tanlang</option>
                                        <option value="1">Ijtimoiy infratuzilma va turizm obyektlari (0.5)</option>
                                        <option value="2">Davlat ulushi 50% dan ortiq (0.5)</option>
                                        <option value="3">Ishlab chiqarish korxonalari (0.5)</option>
                                        <option value="4">Omborxonalar (0.5)</option>
                                        <option value="5">Boshqa obyektlar (1.0)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Hududiy zona (Kz)</label>
                                    <select name="territorial_zone_id" id="modalTerritorialZone"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Avtomatik aniqlanadi</option>
                                        <option value="1">1-zona (1.40)</option>
                                        <option value="2">2-zona (1.25)</option>
                                        <option value="3">3-zona (1.00)</option>
                                        <option value="4">4-zona (0.75)</option>
                                        <option value="5">5-zona (0.50)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Joylashuv (Kj)</label>
                                    <select name="location_type"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="other_locations">Oddiy joylashuv (1.0)</option>
                                        <option value="metro_radius_200m_outside">Metro stantsiyasi yaqinida (0.6)</option>
                                    </select>
                                </div>
                            </div>
                        </div>


     <!-- Hisoblash summasi -->
<div class="bg-white rounded-lg shadow border border-gray-300 p-6">
    <h3 class="text-xl font-semibold text-gray-900 mb-8 pb-3 border-b border-gray-200">Shartnoma summasi hisoblash</h3>

    <!-- Obyekt hajmlari ko'rsatkich -->
    <div class="mb-8">
        <h4 class="text-lg font-medium text-gray-800 mb-4">Obyekt hajmlari</h4>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-5 bg-gray-50 rounded border border-gray-200">
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-600 mb-2">Umumiy hajm (Hb)</label>
                <div id="display_hb" class="text-lg font-semibold text-gray-900 bg-white rounded px-3 py-2 border">0 m³</div>
            </div>
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-600 mb-2">Ruxsatdan yuqori (Hyu)</label>
                <div id="display_hyu" class="text-lg font-semibold text-gray-900 bg-white rounded px-3 py-2 border">0 m³</div>
            </div>
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-600 mb-2">Avtoturargoh (Ha)</label>
                <div id="display_ha" class="text-lg font-semibold text-gray-700 bg-white rounded px-3 py-2 border">0 m³</div>
            </div>
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-600 mb-2">Texnik xonalar (Ht)</label>
                <div id="display_ht" class="text-lg font-semibold text-gray-700 bg-white rounded px-3 py-2 border">0 m³</div>
            </div>
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-600 mb-2">Umumiy foydalanish (Hu)</label>
                <div id="display_hu" class="text-lg font-semibold text-gray-700 bg-white rounded px-3 py-2 border">0 m³</div>
            </div>
        </div>
    </div>

    <!-- Hisoblash hajmi va koeffitsientlar -->
    <div class="mb-8">
        <h4 class="text-lg font-medium text-gray-800 mb-4">Hisoblash hajmi va koeffitsientlar</h4>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 p-5 bg-gray-100 rounded border border-gray-200">
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hisoblash hajmi</label>
                <div id="display_calculated_volume" class="text-lg font-bold text-gray-900 bg-white rounded px-3 py-2 border">0 m³</div>
                <p class="text-xs text-gray-600 mt-1">(Hb + Hyu) - (Ha + Ht + Hu)</p>
            </div>
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-700 mb-2">Qurilish turi (Kt)</label>
                <div id="display_kt" class="text-lg font-bold text-gray-900 bg-white rounded px-3 py-2 border">1.0</div>
            </div>
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-700 mb-2">Obyekt turi (Ko)</label>
                <div id="display_ko" class="text-lg font-bold text-gray-900 bg-white rounded px-3 py-2 border">1.0</div>
            </div>
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hududiy zona (Kz)</label>
                <div id="display_kz" class="text-lg font-bold text-gray-900 bg-white rounded px-3 py-2 border">1.0</div>
            </div>
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-700 mb-2">Joylashuv (Kj)</label>
                <div id="display_kj" class="text-lg font-bold text-gray-900 bg-white rounded px-3 py-2 border">1.0</div>
            </div>
            <div class="text-center">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jami koeffitsient</label>
                <div id="display_total_coef" class="text-lg font-bold text-gray-900 bg-white rounded px-3 py-2 border border-gray-400">1.0</div>
            </div>
        </div>
    </div>

    <!-- Asosiy hisoblash maydonlari -->
    <div class="mb-8">
        <h4 class="text-lg font-medium text-gray-800 mb-4">Asosiy hisoblash</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bazaviy hisoblash miqdori (Bh) *</label>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Hisoblash hajmi (m³) *</label>
                <input type="number" name="contract_volume" step="0.01" value="{{ old('contract_volume') }}" required readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700">
                @error('contract_volume')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Obyekt tanlangandan keyin avtomatik to'ldiriladi</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hisobga olinadigan Bh *</label>
                <input type="number" name="calculated_bh" step="0.01" value="{{ old('calculated_bh') }}" required readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700">
                @error('calculated_bh')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Bh × (Kt × Ko × Kz × Kj)</p>
            </div>
        </div>
    </div>

    <!-- Formula va natija -->
    <div class="mb-6">
        <div class="bg-gray-50 border border-gray-300 rounded-lg p-6">
            <div class="text-center">
                <h4 class="text-lg font-medium text-gray-800 mb-4">Hisoblash formulasi</h4>
                <div class="bg-white border border-gray-200 rounded p-4 mb-4">
                    <p class="text-lg font-mono text-gray-800">Ti = Hisobga olinadigan Bh × Hisoblash hajmi</p>
                </div>
                <div id="formula_display" class="text-sm text-gray-600 mb-4">
                    Ti = Hisobga olinadigan Bh × Hisoblash hajmi
                </div>
            </div>
        </div>
    </div>

    <!-- Yakuniy summa -->
    <div class="bg-gray-800 text-white rounded-lg p-6 text-center">
        <h4 class="text-lg font-medium mb-3">Shartnoma umumiy summasi</h4>
        <div id="total_amount_display" class="text-4xl font-bold mb-2">0 so'm</div>
        <p class="text-sm opacity-80">(Barcha soliq va yig'imlarni o'z ichiga oladi)</p>
    </div>

    <!-- Hisoblash tushuntirishi -->
    <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
        <h5 class="font-medium text-gray-800 mb-3">Hisoblash tushuntirishi:</h5>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
            <div>
                <p class="mb-2"><strong>1. Hisoblash hajmi:</strong></p>
                <p class="mb-4">Umumiy hajm + Ruxsatdan yuqori hajm - (Avtoturargoh + Texnik xonalar + Umumiy foydalanish)</p>

                <p class="mb-2"><strong>2. Koeffitsientlar:</strong></p>
                <ul class="list-disc list-inside space-y-1 ml-2">
                    <li>Kt - Qurilish turi koeffitsienti</li>
                    <li>Ko - Obyekt turi koeffitsienti</li>
                    <li>Kz - Hududiy zona koeffitsienti</li>
                    <li>Kj - Joylashuv koeffitsienti</li>
                </ul>
            </div>
            <div>
                <p class="mb-2"><strong>3. Yakuniy hisoblash:</strong></p>
                <p class="mb-4">Bazaviy miqdor × Jami koeffitsient × Hisoblash hajmi</p>

                <p class="mb-2"><strong>4. Natija:</strong></p>
                <p>Olingan summa shartnoma bo'yicha to'lanishi kerak bo'lgan umumiy miqdordir.</p>
            </div>
        </div>
    </div>
</div>

    </div>

                    <!-- O'ng panel - Xarita -->
                    <div class="w-1/2 flex flex-col">
                        <div class="p-6 border-b border-gray-200 bg-gray-50">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                <i data-feather="map" class="w-5 h-5 inline mr-2 text-red-600"></i>
                                Xarita va zona aniqlash
                            </h4>
                            <p class="text-sm text-gray-600">Xaritada obyekt joylashuvini belgilang</p>
                        </div>
                        <div class="flex-1 p-6">
                            <div id="objectMap" class="w-full h-full rounded-lg border-2 border-gray-200"></div>
                            <div id="zoneInfo" class="mt-4 p-4 border-l-4 rounded-lg hidden">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900">Aniqlangan zona:</p>
                                        <p id="detectedZone" class="text-xl font-bold"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600">Koeffitsient:</p>
                                        <p id="zoneCoefficient" class="text-xl font-bold"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-center text-sm text-gray-500 bg-blue-50 p-3 rounded-lg">
                                <i data-feather="mouse-pointer" class="w-4 h-4 inline mr-1"></i>
                                Xaritadan obyekt joylashuvini tanlang
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal tugmalari -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 flex justify-end space-x-3">
                    <button type="button" onclick="closeObjectModal()"
                            class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i data-feather="x" class="w-4 h-4 mr-2 inline"></i>
                        Bekor qilish
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
                        Obyekt yaratish
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@push('scripts')
<script>
// Laravel routes
window.appRoutes = {
    createSubject: "{{ route('createSubject') }}",
    createObject: "{{ route('createObject') }}",
    contractStore: "{{ route('contracts.store') }}"
};
</script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// =======================
// GLOBAL VARIABLES
// =======================
let objectMap = null;
let mapMarker = null;
let currentZones = null;
let zoneBoundaries = {};
let kmlLoaded = false;

// Zone data with correct coefficients
const zoneData = {
    '1': { name: 'ЗОНА-1', coefficient: 1.40, color: '#dc2626' },
    '2': { name: 'ЗОНА-2', coefficient: 1.25, color: '#ea580c' },
    '3': { name: 'ЗОНА-3', coefficient: 1.00, color: '#ca8a04' },
    '4': { name: 'ЗОНА-4', coefficient: 0.75, color: '#16a34a' },
    '5': { name: 'ЗОНА-5', coefficient: 0.50, color: '#0891b2' }
};

// Coefficient configuration
const coefficients = {
    construction_type: {
        1: 1.0,    // Yangi kapital qurilish
        2: 1.0,    // Obyektni rekonstruksiya qilish
        3: 0.0,    // Ekspertiza talab etilmaydigan rekonstruksiya
        4: 0.0     // Hajm o'zgarmaydigan rekonstruksiya
    },
    object_type: {
        1: 0.5,    // Ijtimoiy infratuzilma va turizm obyektlari
        2: 0.5,    // Davlat ulushi 50% dan ortiq
        3: 0.5,    // Ishlab chiqarish korxonalari
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

// =======================
// KML LOADING FUNCTIONS
// =======================
async function loadZoneKML() {
    try {
        const response = await fetch('/zone.kml');
        if (!response.ok) {
            throw new Error(`KML file not found: ${response.status}`);
        }

        const kmlText = await response.text();
        const parser = new DOMParser();
        const kmlDoc = parser.parseFromString(kmlText, 'text/xml');

        const placemarks = kmlDoc.querySelectorAll('Placemark');

        placemarks.forEach((placemark, index) => {
            try {
                const schemaData = placemark.querySelector('SchemaData');
                let zoneName = '';

                if (schemaData) {
                    const soniData = schemaData.querySelector('SimpleData[name="SONI"]');
                    if (soniData) {
                        zoneName = soniData.textContent.trim();
                    }
                }

                const zoneMatch = zoneName.match(/(?:ZONA|ЗОНА)[_-]?(\d+)/i);
                if (!zoneMatch) return;

                const zoneId = zoneMatch[1];
                const multiGeometry = placemark.querySelector('MultiGeometry');
                const polygons = multiGeometry ?
                    multiGeometry.querySelectorAll('Polygon') :
                    placemark.querySelectorAll('Polygon');

                if (polygons.length === 0) return;

                polygons.forEach((polygon) => {
                    const outerRing = polygon.querySelector('outerBoundaryIs LinearRing coordinates');
                    if (!outerRing) return;

                    const coordinatesText = outerRing.textContent.trim();
                    if (coordinatesText) {
                        const coords = coordinatesText.split(/[\s,]+/)
                            .filter(coord => coord.trim() && !isNaN(parseFloat(coord)))
                            .reduce((acc, curr, index, arr) => {
                                if (index % 2 === 0 && index + 1 < arr.length) {
                                    const lng = parseFloat(curr);
                                    const lat = parseFloat(arr[index + 1]);
                                    if (!isNaN(lat) && !isNaN(lng) && lat > 40 && lat < 42 && lng > 68 && lng < 71) {
                                        acc.push([lat, lng]);
                                    }
                                }
                                return acc;
                            }, []);

                        if (coords.length > 2) {
                            if (!zoneBoundaries[zoneId]) {
                                zoneBoundaries[zoneId] = [];
                            }
                            zoneBoundaries[zoneId].push(coords);
                        }
                    }
                });
            } catch (error) {
                console.error(`Error processing placemark ${index + 1}:`, error);
            }
        });

        kmlLoaded = true;
        console.log('Zone boundaries loaded successfully:', Object.keys(zoneBoundaries));
    } catch (error) {
        console.error('Error loading KML zones:', error);
        kmlLoaded = false;
        showNotification('Zona xaritasi yuklanmadi. Manual tanlash mumkin.', 'warning');
    }
}

// =======================
// MAP FUNCTIONS
// =======================
function initializeMap() {
    if (!objectMap && typeof L !== 'undefined') {
        try {
            objectMap = L.map('objectMap').setView([41.2995, 69.2401], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(objectMap);

            currentZones = L.layerGroup().addTo(objectMap);

            // Load KML and add zones to map
            loadZoneKML().then(() => {
                if (kmlLoaded && Object.keys(zoneBoundaries).length > 0) {
                    Object.keys(zoneBoundaries).forEach(zoneId => {
                        const zoneInfo = zoneData[zoneId];
                        const polygons = zoneBoundaries[zoneId];

                        if (zoneInfo && polygons) {
const paneName = `zone${zoneId}`;
                            if (!objectMap.getPane(paneName)) {
                                objectMap.createPane(paneName);
                                objectMap.getPane(paneName).style.zIndex = 6000 - parseInt(zoneId, 10);
                            }

                            polygons.forEach((coords) => {
                                try {
                                    const polygon = L.polygon(coords, {
                                        pane: paneName,
                                        color: zoneInfo.color,
                                        fillColor: zoneInfo.color,
                                        fillOpacity: 0.3,
                                        weight: 2,
                                        opacity: 0.8
                                    }).bindPopup(`${zoneInfo.name} (K=${zoneInfo.coefficient})`);

                                    currentZones.addLayer(polygon);
                                } catch (error) {
                                    console.error(`Error creating polygon for zone ${zoneId}:`, error);
                                }
                            });
                        }
                    });

                    if (currentZones.getLayers().length > 0) {
                        const group = new L.featureGroup(currentZones.getLayers());
                        objectMap.fitBounds(group.getBounds().pad(0.1));
                    }
                }
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

                detectZoneByCoordinates(e.latlng.lat, e.latlng.lng);
            });

            console.log('Map initialized successfully');
        } catch (error) {
            console.error('Error initializing map:', error);
        }
    }
}

function detectZoneByCoordinates(lat, lng) {
    let detectedZone = null;

    if (kmlLoaded && Object.keys(zoneBoundaries).length > 0) {
        for (const [zoneId, polygons] of Object.entries(zoneBoundaries)) {
            for (const polygon of polygons) {
                if (isPointInPolygon([lat, lng], polygon)) {
                    detectedZone = zoneId;
                    break;
                }
            }
            if (detectedZone) break;
        }
    }

    if (!detectedZone && lat > 41.15 && lat < 41.45 && lng > 69.1 && lng < 69.5) {
        detectedZone = '3'; // Default zone for Tashkent
    }

    if (detectedZone && zoneData[detectedZone]) {
        const zone = zoneData[detectedZone];
        showZoneInfo(detectedZone, zone.name, zone.coefficient);

        const zoneSelect = document.getElementById('modalTerritorialZone');
        if (zoneSelect) {
            zoneSelect.value = detectedZone;
        }
    } else {
        hideZoneInfo();
    }
}

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

function showZoneInfo(zoneId, zoneName, coefficient) {
    const zoneInfo = document.getElementById('zoneInfo');
    if (!zoneInfo) return;

    const zoneColor = zoneData[zoneId]?.color || '#6b7280';
    zoneInfo.style.borderColor = zoneColor;
    zoneInfo.style.backgroundColor = zoneColor + '20';
    zoneInfo.classList.remove('hidden');

    const detectedZoneEl = document.getElementById('detectedZone');
    const zoneCoefficientEl = document.getElementById('zoneCoefficient');

    if (detectedZoneEl) detectedZoneEl.textContent = zoneName;
    if (zoneCoefficientEl) zoneCoefficientEl.textContent = coefficient;
}

function hideZoneInfo() {
    const zoneInfo = document.getElementById('zoneInfo');
    if (zoneInfo) {
        zoneInfo.classList.add('hidden');
    }
}

// =======================
// SEARCH FUNCTIONS
// =======================
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
        div.className = 'p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100';
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

// =======================
// CALCULATION FUNCTIONS
// =======================
function calculateTotal() {
    const baseAmountSelect = document.querySelector('select[name="base_amount_id"]');
    const volumeInput = document.querySelector('input[name="contract_volume"]');
    const calculatedBhInput = document.querySelector('input[name="calculated_bh"]');
    const totalDisplay = document.getElementById('total_amount_display');
    const formulaDisplay = document.getElementById('formula_display');

    if (!baseAmountSelect || !volumeInput || !totalDisplay) return;

    const selectedOption = baseAmountSelect.options[baseAmountSelect.selectedIndex];
    const baseAmount = selectedOption ? parseFloat(selectedOption.dataset.amount) : 0;
    const volume = parseFloat(volumeInput.value) || 0;

    let calculatedBh = 0;
    if (calculatedBhInput) {
        calculatedBh = parseFloat(calculatedBhInput.value) || 0;
    } else {
        calculatedBh = baseAmount;
    }

    const totalAmount = calculatedBh * volume;

    totalDisplay.textContent = formatNumber(totalAmount) + ' so\'m';

    if (formulaDisplay) {
        if (calculatedBh && volume) {
            formulaDisplay.textContent =
                `Ti = ${formatNumber(calculatedBh)} × ${formatNumber(volume)} m³ = ${formatNumber(totalAmount)} so'm`;
        } else {
            formulaDisplay.textContent = 'Ti = Hisobga olinadigan Bh × Hisoblash hajmi';
        }
    }

    calculatePaymentSchedule();
}

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
        const contractVolume = Math.max(0, (hb + hyu) - (ha + ht + hu));
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

        let kj = 1.0;
        if (location === 'metro_radius_200m_outside') {
            kj = coefficients.location['metro_radius_200m_outside'];
        } else {
            kj = coefficients.location['other_locations'];
        }

        const totalCoef = kt * ko * kz * kj;

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

        // Calculate Bh with coefficient
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

// =======================
// PAYMENT FUNCTIONS
// =======================
function calculateCompletionDate() {
    const contractDateInput = document.querySelector('input[name="contract_date"]');
    const yearsInput = document.querySelector('input[name="construction_period_years"]');
    const completionDateInput = document.querySelector('input[name="completion_date"]');

    if (contractDateInput && yearsInput && completionDateInput) {
        const contractDate = new Date(contractDateInput.value);
        const years = parseInt(yearsInput.value) || 2;

        if (contractDate instanceof Date && !isNaN(contractDate)) {
            const completionDate = new Date(contractDate);
            completionDate.setFullYear(completionDate.getFullYear() + years);

            const year = completionDate.getFullYear();
            const month = String(completionDate.getMonth() + 1).padStart(2, '0');
            const day = String(completionDate.getDate()).padStart(2, '0');

            completionDateInput.value = `${year}-${month}-${day}`;
        }
    }
}

function calculatePaymentSchedule() {
    const paymentTypeSelect = document.querySelector('select[name="payment_type"]');
    const totalAmountDisplay = document.getElementById('total_amount_display');

    if (!paymentTypeSelect || !totalAmountDisplay) return;

    const paymentType = paymentTypeSelect.value;
    const totalAmountText = totalAmountDisplay.textContent;
    const totalAmount = parseFloat(totalAmountText.replace(/[^\d]/g, '')) || 0;

    if (totalAmount <= 0) return;

    const initialPercentInput = document.querySelector('input[name="initial_payment_percent"]');
    const yearsInput = document.querySelector('input[name="construction_period_years"]');

    const initialPercent = initialPercentInput ? parseInt(initialPercentInput.value) || 20 : 20;
    const years = yearsInput ? parseInt(yearsInput.value) || 2 : 2;
    const quarters = years * 4;

    const elements = {
        initialPayment: document.getElementById('initial_payment_amount'),
        remainingAmount: document.getElementById('remaining_amount'),
        quarterlyPayment: document.getElementById('quarterly_payment'),
        quartersTable: document.getElementById('quarters_tbody')
    };

    if (paymentType === 'full') {
        if (elements.initialPayment) elements.initialPayment.textContent = formatNumber(totalAmount) + ' so\'m';
        if (elements.remainingAmount) elements.remainingAmount.textContent = '0 so\'m';
        if (elements.quarterlyPayment) elements.quarterlyPayment.textContent = '0 so\'m';

        if (elements.quartersTable) {
            elements.quartersTable.innerHTML = `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">${new Date().getFullYear()}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">To'liq to'lov</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${formatNumber(totalAmount)} so'm</td>
                </tr>
            `;
        }
    } else {
        const initialPayment = totalAmount * (initialPercent / 100);
        const remainingAmount = totalAmount - initialPayment;
        const quarterlyPayment = remainingAmount / quarters;

        if (elements.initialPayment) elements.initialPayment.textContent = formatNumber(initialPayment) + ' so\'m';
        if (elements.remainingAmount) elements.remainingAmount.textContent = formatNumber(remainingAmount) + ' so\'m';
        if (elements.quarterlyPayment) elements.quarterlyPayment.textContent = formatNumber(quarterlyPayment) + ' so\'m';

        let tbody = `
            <tr class="bg-green-50 hover:bg-green-100">
                <td class="px-4 py-3 text-sm font-semibold text-gray-900">${new Date().getFullYear()}</td>
                <td class="px-4 py-3 text-sm font-semibold text-gray-900">Boshlang'ich to'lov</td>
                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">${formatNumber(initialPayment)} so'm</td>
            </tr>
        `;

        const startYear = new Date().getFullYear();
        const startQuarter = Math.ceil((new Date().getMonth() + 1) / 3);

        for (let i = 0; i < quarters; i++) {
            const currentQuarter = ((startQuarter - 1 + i) % 4) + 1;
            const currentYear = startYear + Math.floor((startQuarter - 1 + i) / 4);

            tbody += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">${currentYear}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${currentQuarter}-chorak</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">${formatNumber(quarterlyPayment)} so'm</td>
                </tr>
            `;
        }

        if (elements.quartersTable) {
            elements.quartersTable.innerHTML = tbody;
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

// =======================
// UI FUNCTIONS
// =======================
function toggleSidebar() {
    const sidebar = document.getElementById('documentationSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebar && overlay) {
        sidebar.classList.toggle('translate-x-full');
        overlay.classList.toggle('hidden');
    }
}

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

    // Update visual cards
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

    // Toggle fields
    if (legalFields && physicalFields) {
        if (isLegalEntity) {
            legalFields.classList.remove('hidden');
            physicalFields.classList.add('hidden');

            // Set required fields
            legalFields.querySelectorAll('input[name="company_name"], input[name="inn"]').forEach(input => {
                input.setAttribute('required', 'required');
            });
            physicalFields.querySelectorAll('input').forEach(input => {
                input.removeAttribute('required');
            });
        } else {
            legalFields.classList.add('hidden');
            physicalFields.classList.remove('hidden');

            // Set required fields
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
        showNotification('Avval Mulk egasini tanlang', 'warning');
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

            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }, 300);
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

    if (mapMarker && objectMap) {
        objectMap.removeLayer(mapMarker);
        mapMarker = null;
    }
}

// =======================
// FORM SUBMISSION FUNCTIONS
// =======================
function handleSubjectFormSubmission() {
    const form = document.getElementById('subjectModalForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');

        toggleLoading(submitButton, true);

        try {
            const csrfToken = getCsrfToken();

            const response = await fetch('/contracts/create-subject', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
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

function handleObjectFormSubmission() {
    const form = document.getElementById('objectModalForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const subjectSelect = document.querySelector('select[name="subject_id"]');
        if (!subjectSelect || !subjectSelect.value) {
            showNotification('Avval Mulk egasini tanlang', 'error');
            return;
        }

        // Validate required fields
        const requiredFields = [
            { name: 'district_id', label: 'Tuman' },
            { name: 'address', label: 'Manzil' },
            { name: 'construction_volume', label: 'Umumiy hajm' }
        ];

        let isValid = true;
        for (const field of requiredFields) {
            const input = form.querySelector(`[name="${field.name}"]`);
            if (!input || !input.value.trim()) {
                showNotification(`${field.label} kiritilishi shart`, 'error');
                isValid = false;
                break;
            }
        }

        if (!isValid) return;

        const formData = new FormData(this);
        formData.append('subject_id', subjectSelect.value);

        const submitButton = this.querySelector('button[type="submit"]');
        toggleLoading(submitButton, true);

        try {
            const csrfToken = getCsrfToken();

            const response = await fetch('/contracts/create-object', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                const objectSelect = document.getElementById('objectSelect');
                const objectSearch = document.getElementById('objectSearch');

                if (objectSelect) {
                    const newOption = new Option(result.object.text, result.object.id, true, true);

                    // Set data attributes for calculation
                    newOption.dataset.volume = result.object.construction_volume;
                    newOption.dataset.abovePermit = result.object.above_permit_volume;
                    newOption.dataset.parking = result.object.parking_volume;
                    newOption.dataset.technical = result.object.technical_rooms_volume;
                    newOption.dataset.common = result.object.common_area_volume;
                    newOption.dataset.constructionType = result.object.construction_type_id;
                    newOption.dataset.objectType = result.object.object_type_id;
                    newOption.dataset.zone = result.object.territorial_zone_id;
                    newOption.dataset.location = result.object.location_type;

                    objectSelect.add(newOption);
                    objectSelect.value = result.object.id;
                }

                if (objectSearch) {
                    objectSearch.value = result.object.text;
                }

                closeObjectModal();
                updateObjectVolume();
                showNotification(result.message, 'success');
            } else {
                throw new Error(result.message || 'Obyekt yaratishda xato');
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
            const csrfToken = getCsrfToken();

            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
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
                    }, 2000);
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

// =======================
// HELPER FUNCTIONS
// =======================
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ||
           document.querySelector('input[name="_token"]')?.value;
}

function formatNumber(num) {
    if (isNaN(num)) return '0';
    return new Intl.NumberFormat('uz-UZ').format(Math.round(num));
}

function toggleLoading(button, loading) {
    if (!button) return;

    if (loading) {
        button.disabled = true;
        const originalText = button.innerHTML;
        button.dataset.originalText = originalText;
        button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Kutilmoqda...';
    } else {
        button.disabled = false;
        const originalText = button.dataset.originalText;

        if (originalText) {
            button.innerHTML = originalText;
        } else if (button.closest('#subjectModalForm')) {
            button.innerHTML = 'Yaratish';
        } else if (button.closest('#objectModalForm')) {
            button.innerHTML = '<i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>Obyekt yaratish';
        } else if (button.closest('#contractForm')) {
            button.innerHTML = '<i data-feather="save" class="w-4 h-4 mr-2 inline"></i>Shartnoma yaratish';
        }

        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const typeClasses = {
        'success': 'bg-green-600 text-white',
        'error': 'bg-red-600 text-white',
        'warning': 'bg-yellow-600 text-white',
        'info': 'bg-blue-600 text-white'
    };

    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full opacity-0 ${typeClasses[type] || typeClasses['info']}`;

    notification.innerHTML = `
        <div class="flex items-center">
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg font-bold opacity-75 hover:opacity-100">&times;</button>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// =======================
// EVENT LISTENERS SETUP
// =======================
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
        initialPercentInput.addEventListener('input', debounce(calculatePaymentSchedule, 500));
    }

    if (constructionYearsInput) {
        constructionYearsInput.addEventListener('input', debounce(() => {
            calculateCompletionDate();
            calculatePaymentSchedule();
        }, 500));
    }

    // Contract date change
    const contractDateInput = document.querySelector('input[name="contract_date"]');
    if (contractDateInput) {
        contractDateInput.addEventListener('change', calculateCompletionDate);
    }

    // Outside click listeners for dropdowns
    document.addEventListener('click', function(e) {
        const subjectDropdown = document.getElementById('subjectDropdown');
        const objectDropdown = document.getElementById('objectDropdown');

        if (subjectDropdown && !e.target.closest('#subjectSearch') && !e.target.closest('#subjectDropdown')) {
            subjectDropdown.classList.add('hidden');
        }

        if (objectDropdown && !e.target.closest('#objectSearch') && !e.target.closest('#objectDropdown')) {
            objectDropdown.classList.add('hidden');
        }
    });

    // Prevent form submission on enter key in search inputs
    ['subjectSearch', 'objectSearch'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        }
    });

    // Modal escape key handling
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const subjectModal = document.getElementById('subjectModal');
            const objectModal = document.getElementById('objectModal');

            if (subjectModal && !subjectModal.classList.contains('hidden')) {
                closeSubjectModal();
            }
            if (objectModal && !objectModal.classList.contains('hidden')) {
                closeObjectModal();
            }
        }
    });
}

// =======================
// INITIALIZATION
// =======================
function initializeApplication() {
    try {
        console.log('Initializing contract creation application...');

        // Initialize calculations
        calculateTotal();
        togglePaymentFields();
        toggleEntityFields();
        calculatePaymentSchedule();
        calculateCompletionDate();

        // Setup all event listeners
        setupEventListeners();

        // Setup form submissions
        handleSubjectFormSubmission();
        handleObjectFormSubmission();
        handleContractFormSubmission();

        // Initialize icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Log successful initialization
        console.log('Contract creation application initialized successfully');

        // Optional: Show initialization success
        // showNotification('Dastur muvaffaqiyatli yuklandi', 'success');

    } catch (error) {
        console.error('Error initializing application:', error);
        showNotification('Dastur ishga tushirishda xato yuz berdi', 'error');
    }
}

// =======================
// DOM READY AND ERROR HANDLING
// =======================
document.addEventListener('DOMContentLoaded', function() {
    try {
        initializeApplication();
    } catch (error) {
        console.error('DOMContentLoaded error:', error);
        showNotification('Sahifa yuklanishida xato', 'error');
    }
});

// Global error handling
window.addEventListener('error', function(e) {
    console.error('Global JavaScript error:', e.error);
    showNotification('Kutilmagan xato yuz berdi', 'error');
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    showNotification('Server bilan aloqa xatosi', 'error');
});

// Prevent form resubmission on page reload
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

console.log('Production-ready contract creation system loaded successfully');
</script>
@endpush
