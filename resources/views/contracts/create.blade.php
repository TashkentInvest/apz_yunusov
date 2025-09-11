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
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           onchange="calculateCompletionDate()">
                    @error('contract_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tugatish sanasi</label>
                    <input type="date" name="completion_date" value="{{ old('completion_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100" readonly>
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
                    <select name="subject_id" required id="subjectSelect"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Mulk egasini tanlang</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->display_name }} ({{ $subject->identifier }})
                            </option>
                        @endforeach
                    </select>
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
                    <select name="object_id" required id="objectSelect" onchange="updateObjectVolume()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Obyektni tanlang</option>
                        @foreach($objects as $object)
                            <option value="{{ $object->id }}"
                                    data-volume="{{ $object->construction_volume ?? 0 }}"
                                    data-above-permit="{{ $object->above_permit_volume ?? 0 }}"
                                    data-parking="{{ $object->parking_volume ?? 0 }}"
                                    data-technical="{{ $object->technical_rooms_volume ?? 0 }}"
                                    data-common="{{ $object->common_area_volume ?? 0 }}"
                                    data-subject="{{ $object->subject_id }}"
                                    data-construction-type="{{ $object->construction_type_id ?? 1 }}"
                                    data-object-type="{{ $object->object_type_id ?? 5 }}"
                                    data-zone="{{ $object->territorial_zone_id ?? 3 }}"
                                    data-location="{{ $object->location_type ?? 'other_locations' }}"
                                    {{ old('object_id') == $object->id ? 'selected' : '' }}>
                                {{ $object->address }} ({{ $object->district->name_uz ?? 'N/A' }}) - {{ number_format($object->construction_volume ?? 0, 2) }} m³
                            </option>
                        @endforeach
                    </select>
                    @error('object_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Hisoblash summasi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Shartnoma summasi hisoblash</h3>

            <!-- Obyekt hajmlari -->
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4">Obyekt hajmlari</h4>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-4 bg-gray-50 rounded-lg">
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

            <!-- Koeffitsientlar -->
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4">Koeffitsientlar</h4>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-4 bg-gray-100 rounded-lg">
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
                        <div id="display_total_coef" class="text-lg font-bold text-blue-600 bg-white rounded px-3 py-2 border-2 border-blue-200">1.0</div>
                    </div>
                </div>
            </div>

            <!-- Asosiy hisoblash -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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

            <!-- To'lov turi -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov turi *</label>
                    <select name="payment_type" required onchange="togglePaymentFields()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="installment" {{ old('payment_type') == 'installment' ? 'selected' : '' }}>Bo'lib to'lash</option>
                        <option value="full" {{ old('payment_type') == 'full' ? 'selected' : '' }}>To'liq to'lash</option>
                    </select>
                    @error('payment_type')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="initial_payment_field">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Boshlang'ich to'lov foizi *</label>
                    <input type="number" name="initial_payment_percent" value="{{ old('initial_payment_percent', 20) }}" required
                           min="0" max="100" onchange="calculateTotal()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('initial_payment_percent')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="construction_period_field">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Qurilish muddati (yil) *</label>
                    <input type="number" name="construction_period_years" value="{{ old('construction_period_years', 2) }}" required
                           min="1" max="10" onchange="calculateCompletionDate(); calculateTotal();"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('construction_period_years')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Yakuniy summa -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg p-6 text-center">
                <h4 class="text-lg font-medium mb-3">Shartnoma umumiy summasi</h4>
                <div id="total_amount_display" class="text-4xl font-bold mb-2">0 so'm</div>
                <p class="text-sm opacity-80">Barcha koeffitsientlar hisobga olingan</p>
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

<!-- Subject Modal -->
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
                    <!-- Entity Type Selection -->
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hisob raqami</label>
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

<!-- Object Modal -->
<div id="objectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-xl shadow-xl transform transition-all max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <form id="objectModalForm">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700">
                    <h3 class="text-xl font-bold text-white">Yangi obyekt yaratish</h3>
                    <p class="text-sm text-blue-100 mt-1">Obyekt ma'lumotlarini to'ldiring</p>
                </div>

                <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                    <!-- Basic Information -->
                    <div class="mb-6">
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
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name_uz }}</option>
                                    @endforeach
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
                                    <input type="text" name="geolocation" placeholder="41.2995, 69.2401"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Construction Volumes -->
                    <div class="mb-6">
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

                    <!-- Coefficients -->
                    <div class="mb-6">
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
                                    @foreach($constructionTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name_uz }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Obyekt turi (Ko)</label>
                                <select name="object_type_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tanlang</option>
                                    @foreach($objectTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name_uz }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hududiy zona (Kz)</label>
                                <select name="territorial_zone_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tanlang</option>
                                    @foreach($territorialZones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name_uz }}</option>
                                    @endforeach
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
                </div>

                <!-- Modal Buttons -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
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

<script>
// =======================
// PRODUCTION-READY CONTRACT CREATION SYSTEM
// =======================

// Coefficient configuration
const coefficients = {
    construction_type: { 1: 1.0, 2: 1.0, 3: 0.0, 4: 0.0 },
    object_type: { 1: 0.5, 2: 0.5, 3: 0.5, 4: 0.5, 5: 1.0 },
    territorial_zone: { 1: 1.40, 2: 1.25, 3: 1.00, 4: 0.75, 5: 0.50 },
    location: { 'metro_radius_200m_outside': 0.6, 'other_locations': 1.0 }
};

// =======================
// CORE CALCULATION FUNCTIONS
// =======================
function updateObjectVolume() {
    const objectSelect = document.querySelector('select[name="object_id"]');
    if (!objectSelect?.value) {
        resetCalculationDisplays();
        return;
    }

    const selectedOption = objectSelect.options[objectSelect.selectedIndex];

    try {
        // Get volume data with safe parsing
        const volumes = {
            hb: parseFloat(selectedOption.dataset.volume) || 0,
            hyu: parseFloat(selectedOption.dataset.abovePermit) || 0,
            ha: parseFloat(selectedOption.dataset.parking) || 0,
            ht: parseFloat(selectedOption.dataset.technical) || 0,
            hu: parseFloat(selectedOption.dataset.common) || 0
        };

        // Update displays
        updateVolumeDisplays(volumes);

        // Calculate contract volume
        const contractVolume = Math.max(0, (volumes.hb + volumes.hyu) - (volumes.ha + volumes.ht + volumes.hu));
        const volumeInput = document.querySelector('input[name="contract_volume"]');
        if (volumeInput) volumeInput.value = contractVolume.toFixed(2);

        // Calculate and display coefficients
        calculateAndDisplayCoefficients(selectedOption);
        calculateTotal();

    } catch (error) {
        console.error('Error updating object volume:', error);
        showNotification('Obyekt ma\'lumotlarini yangilashda xatolik', 'error');
    }
}

function updateVolumeDisplays(volumes) {
    const displayMap = {
        'display_hb': volumes.hb, 'display_hyu': volumes.hyu, 'display_ha': volumes.ha,
        'display_ht': volumes.ht, 'display_hu': volumes.hu
    };

    Object.entries(displayMap).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = formatNumber(value) + ' m³';
    });
}

function calculateAndDisplayCoefficients(selectedOption) {
    const types = {
        construction: selectedOption.dataset.constructionType || '1',
        object: selectedOption.dataset.objectType || '5',
        zone: selectedOption.dataset.zone || '3',
        location: selectedOption.dataset.location || 'other_locations'
    };

    const coefs = {
        kt: coefficients.construction_type[types.construction] || 1.0,
        ko: coefficients.object_type[types.object] || 1.0,
        kz: coefficients.territorial_zone[types.zone] || 1.0,
        kj: coefficients.location[types.location] || 1.0
    };

    const totalCoef = coefs.kt * coefs.ko * coefs.kz * coefs.kj;

    const displayMap = {
        'display_kt': coefs.kt, 'display_ko': coefs.ko, 'display_kz': coefs.kz,
        'display_kj': coefs.kj, 'display_total_coef': totalCoef
    };

    Object.entries(displayMap).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value.toFixed(2);
    });
}

function calculateTotal() {
    try {
        const elements = {
            baseAmountSelect: document.querySelector('select[name="base_amount_id"]'),
            volumeInput: document.querySelector('input[name="contract_volume"]'),
            calculatedBhInput: document.querySelector('input[name="calculated_bh"]'),
            totalDisplay: document.getElementById('total_amount_display'),
            totalCoefElement: document.getElementById('display_total_coef')
        };

        if (!elements.baseAmountSelect || !elements.volumeInput || !elements.totalDisplay) return;

        const selectedOption = elements.baseAmountSelect.options[elements.baseAmountSelect.selectedIndex];
        const baseAmount = selectedOption?.dataset.amount ? parseFloat(selectedOption.dataset.amount) : 0;
        const volume = parseFloat(elements.volumeInput.value) || 0;
        const totalCoef = elements.totalCoefElement ? parseFloat(elements.totalCoefElement.textContent) || 1 : 1;

        const calculatedBh = baseAmount * totalCoef;
        if (elements.calculatedBhInput) elements.calculatedBhInput.value = calculatedBh.toFixed(2);

        const totalAmount = calculatedBh * volume;
        elements.totalDisplay.textContent = formatNumber(totalAmount) + ' so\'m';

    } catch (error) {
        console.error('Error in calculateTotal:', error);
        showNotification('Hisoblashda xatolik yuz berdi', 'error');
    }
}

function resetCalculationDisplays() {
    const displays = [
        'display_hb', 'display_hyu', 'display_ha', 'display_ht', 'display_hu',
        'display_kt', 'display_ko', 'display_kz', 'display_kj', 'display_total_coef'
    ];

    displays.forEach(id => {
        const element = document.getElementById(id);
        if (element) element.textContent = id.includes('display_k') ? '1.0' : '0 m³';
    });

    ['total_amount_display'].forEach(id => {
        const element = document.getElementById(id);
        if (element) element.textContent = '0 so\'m';
    });

    ['contract_volume', 'calculated_bh'].forEach(name => {
        const element = document.querySelector(`input[name="${name}"]`);
        if (element) element.value = '';
    });
}

// =======================
// PAYMENT TYPE FUNCTIONS
// =======================
function togglePaymentFields() {
    try {
        const paymentTypeSelect = document.querySelector('select[name="payment_type"]');
        if (!paymentTypeSelect) return;

        const isFullPayment = paymentTypeSelect.value === 'full';
        const fields = {
            initialPaymentField: document.getElementById('initial_payment_field'),
            constructionPeriodField: document.getElementById('construction_period_field'),
            initialPercentInput: document.querySelector('input[name="initial_payment_percent"]')
        };

        if (isFullPayment) {
            if (fields.initialPaymentField) fields.initialPaymentField.style.display = 'none';
            if (fields.constructionPeriodField) fields.constructionPeriodField.style.display = 'none';
            if (fields.initialPercentInput) fields.initialPercentInput.value = 100;
        } else {
            if (fields.initialPaymentField) fields.initialPaymentField.style.display = 'block';
            if (fields.constructionPeriodField) fields.constructionPeriodField.style.display = 'block';
            if (fields.initialPercentInput && fields.initialPercentInput.value == 100) {
                fields.initialPercentInput.value = 20;
            }
        }

        calculateTotal();
    } catch (error) {
        console.error('Error toggling payment fields:', error);
    }
}

function calculateCompletionDate() {
    const elements = {
        contractDate: document.querySelector('input[name="contract_date"]'),
        years: document.querySelector('input[name="construction_period_years"]'),
        completionDate: document.querySelector('input[name="completion_date"]')
    };

    if (!elements.contractDate?.value || !elements.years?.value || !elements.completionDate) return;

    try {
        const contractDate = new Date(elements.contractDate.value);
        const years = parseInt(elements.years.value) || 2;

        if (isNaN(contractDate.getTime())) return;

        const completionDate = new Date(contractDate);
        completionDate.setFullYear(completionDate.getFullYear() + years);

        elements.completionDate.value = completionDate.toISOString().split('T')[0];
    } catch (error) {
        console.error('Error calculating completion date:', error);
    }
}

// =======================
// MODAL FUNCTIONS
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

function openObjectModal() {
    const subjectSelect = document.querySelector('select[name="subject_id"]');
    if (!subjectSelect?.value) {
        showNotification('Avval Mulk egasini tanlang', 'warning');
        return;
    }

    const modal = document.getElementById('objectModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeObjectModal() {
    const modal = document.getElementById('objectModal');
    const form = document.getElementById('objectModalForm');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    if (form) form.reset();
}

function toggleEntityFields() {
    const legalEntityRadio = document.querySelector('input[name="is_legal_entity"]:checked');
    if (!legalEntityRadio) return;

    const isLegalEntity = legalEntityRadio.value === '1';
    const fields = {
        legal: document.getElementById('legalEntityFields'),
        physical: document.getElementById('physicalPersonFields')
    };

    // Update visual cards
    document.querySelectorAll('.entity-type-card').forEach(card => {
        const input = card.parentElement.querySelector('input[type="radio"]');
        card.className = 'entity-type-card p-3 border-2 rounded-lg cursor-pointer transition-all';

        if (input?.checked) {
            card.classList.add(input.value === '1' ? 'border-blue-500' : 'border-green-500', 'bg-blue-50');
        } else {
            card.classList.add('border-gray-200');
        }
    });

    // Toggle field visibility and requirements
    if (fields.legal && fields.physical) {
        if (isLegalEntity) {
            fields.legal.classList.remove('hidden');
            fields.physical.classList.add('hidden');
            setRequiredFields(fields.legal, ['company_name', 'inn'], true);
            setRequiredFields(fields.physical, ['document_type', 'document_number', 'pinfl'], false);
        } else {
            fields.legal.classList.add('hidden');
            fields.physical.classList.remove('hidden');
            setRequiredFields(fields.legal, ['company_name', 'inn'], false);
            setRequiredFields(fields.physical, ['document_type', 'document_number', 'pinfl'], true);
        }
    }
}

function setRequiredFields(container, fieldNames, required) {
    fieldNames.forEach(name => {
        const field = container.querySelector(`[name="${name}"]`);
        if (field) {
            if (required) field.setAttribute('required', 'required');
            else field.removeAttribute('required');
        }
    });
}

// =======================
// FORM SUBMISSION
// =======================
function handleFormSubmissions() {
    // Subject form submission
    const subjectForm = document.getElementById('subjectModalForm');
    if (subjectForm) {
        subjectForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await submitForm(this, '/contracts/create-subject', handleSubjectSuccess);
        });
    }

    // Object form submission
    const objectForm = document.getElementById('objectModalForm');
    if (objectForm) {
        objectForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const subjectId = document.querySelector('select[name="subject_id"]')?.value;
            if (!subjectId) {
                showNotification('Avval Mulk egasini tanlang', 'error');
                return;
            }

            const formData = new FormData(this);
            formData.append('subject_id', subjectId);
            await submitFormData(formData, '/contracts/create-object', handleObjectSuccess);
        });
    }

    // Contract form submission
    const contractForm = document.getElementById('contractForm');
    if (contractForm) {
        contractForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate form before submission
            const errors = validateContractForm();
            if (errors.length > 0) {
                showNotification(errors[0], 'error');
                return;
            }

            await submitForm(this, this.action, handleContractSuccess);
        });
    }
}

async function submitForm(form, url, successHandler) {
    const formData = new FormData(form);
    await submitFormData(formData, url, successHandler);
}

async function submitFormData(formData, url, successHandler) {
    const submitButton = document.querySelector('button[type="submit"]');
    toggleLoading(submitButton, true);

    try {
        const csrfToken = getCsrfToken();
        const response = await fetch(url, {
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
            successHandler(result);
        } else {
            throw new Error(result.message || 'Xato yuz berdi');
        }
    } catch (error) {
        console.error('Form submission error:', error);
        showNotification(error.message, 'error');
    } finally {
        toggleLoading(submitButton, false);
    }
}

function handleSubjectSuccess(result) {
    const subjectSelect = document.getElementById('subjectSelect');
    if (subjectSelect && result.subject) {
        const newOption = new Option(result.subject.text, result.subject.id, true, true);
        subjectSelect.add(newOption);
        subjectSelect.value = result.subject.id;
    }
    closeSubjectModal();
}

function handleObjectSuccess(result) {
    const objectSelect = document.getElementById('objectSelect');
    if (objectSelect && result.object) {
        const newOption = new Option(result.object.text, result.object.id, true, true);

        // Set data attributes for calculations
        ['volume', 'above_permit_volume', 'parking_volume', 'technical_rooms_volume',
         'common_area_volume', 'construction_type_id', 'object_type_id',
         'territorial_zone_id', 'location_type'].forEach(attr => {
            newOption.dataset[attr.replace(/_/g, '')] = result.object[attr] || '';
        });

        objectSelect.add(newOption);
        objectSelect.value = result.object.id;
    }
    closeObjectModal();
    updateObjectVolume();
}

function handleContractSuccess(result) {
    if (result.redirect) {
        setTimeout(() => window.location.href = result.redirect, 2000);
    }
}

function validateContractForm() {
    const requiredFields = [
        { name: 'contract_number', label: 'Shartnoma raqami' },
        { name: 'contract_date', label: 'Shartnoma sanasi' },
        { name: 'subject_id', label: 'Mulk egasi' },
        { name: 'object_id', label: 'Obyekt' },
        { name: 'status_id', label: 'Status' },
        { name: 'base_amount_id', label: 'Bazaviy hisoblash miqdori' }
    ];

    const errors = [];
    requiredFields.forEach(field => {
        const element = document.querySelector(`[name="${field.name}"]`);
        if (!element?.value?.trim()) {
            errors.push(`${field.label} to'ldirilishi shart`);
        }
    });

    // Validate calculated values
    const contractVolume = parseFloat(document.querySelector('input[name="contract_volume"]')?.value || 0);
    const calculatedBh = parseFloat(document.querySelector('input[name="calculated_bh"]')?.value || 0);

    if (contractVolume <= 0) errors.push('Hisoblash hajmi 0 dan katta bo\'lishi kerak');
    if (calculatedBh <= 0) errors.push('Hisobga olinadigan Bh 0 dan katta bo\'lishi kerak');

    return errors;
}

// =======================
// UTILITY FUNCTIONS
// =======================
function formatNumber(num) {
    if (isNaN(num) || num == null) return '0';
    return new Intl.NumberFormat('uz-UZ').format(Math.round(num));
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ||
           document.querySelector('input[name="_token"]')?.value;
}

function toggleLoading(button, loading) {
    if (!button) return;

    if (loading) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>Kutilmoqda...`;
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || button.innerHTML;
        setTimeout(() => feather?.replace(), 100);
    }
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.notification-toast').forEach(n => n.remove());

    const notification = document.createElement('div');
    notification.className = 'notification-toast';

    const typeClasses = {
        success: 'bg-green-600 text-white',
        error: 'bg-red-600 text-white',
        warning: 'bg-yellow-600 text-white',
        info: 'bg-blue-600 text-white'
    };

    notification.className += ` fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full opacity-0 ${typeClasses[type] || typeClasses.info}`;

    notification.innerHTML = `
        <div class="flex items-center">
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg font-bold opacity-75 hover:opacity-100">&times;</button>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => notification.classList.remove('translate-x-full', 'opacity-0'), 100);

    // Auto remove
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

// =======================
// EVENT LISTENERS SETUP
// =======================
function setupEventListeners() {
    try {
        // Base amount and volume changes
        const baseAmountSelect = document.querySelector('select[name="base_amount_id"]');
        if (baseAmountSelect) baseAmountSelect.addEventListener('change', calculateTotal);

        // Payment type and related fields
        const paymentTypeSelect = document.querySelector('select[name="payment_type"]');
        if (paymentTypeSelect) paymentTypeSelect.addEventListener('change', togglePaymentFields);

        const initialPercentInput = document.querySelector('input[name="initial_payment_percent"]');
        if (initialPercentInput) initialPercentInput.addEventListener('input', debounce(calculateTotal, 300));

        const constructionYearsInput = document.querySelector('input[name="construction_period_years"]');
        if (constructionYearsInput) {
            constructionYearsInput.addEventListener('input', debounce(() => {
                calculateCompletionDate();
                calculateTotal();
            }, 300));
        }

        // Contract date change
        const contractDateInput = document.querySelector('input[name="contract_date"]');
        if (contractDateInput) contractDateInput.addEventListener('change', calculateCompletionDate);

        // Object selection
        const objectSelect = document.querySelector('select[name="object_id"]');
        if (objectSelect) objectSelect.addEventListener('change', updateObjectVolume);

        // Modal escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ['subjectModal', 'objectModal'].forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && !modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });

        console.log('Event listeners setup successfully');
    } catch (error) {
        console.error('Error setting up event listeners:', error);
    }
}

// =======================
// INITIALIZATION
// =======================
function initializeApplication() {
    try {
        console.log('Initializing contract creation application...');

        // Initialize form handlers
        handleFormSubmissions();
        setupEventListeners();

        // Initialize calculations
        togglePaymentFields();
        toggleEntityFields();
        calculateTotal();
        calculateCompletionDate();

        // Initialize feather icons
        if (typeof feather !== 'undefined') feather.replace();

        console.log('Contract creation application initialized successfully');
    } catch (error) {
        console.error('Error initializing application:', error);
        showNotification('Dastur ishga tushirishda xato yuz berdi', 'error');
    }
}

// =======================
// DOM READY
// =======================
document.addEventListener('DOMContentLoaded', initializeApplication);

// Global error handling
window.addEventListener('error', function(e) {
    console.error('Global JavaScript error:', e.error);
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
});

console.log('Production-ready contract creation system loaded successfully');
</script>
@endsection
