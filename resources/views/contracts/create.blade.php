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
                <div class="flex justify-between items-center p-2 bg-red-50 rounded text-sm border-l-4 border-red-500">
                    <span class="font-medium">1-zona</span>
                    <span class="font-semibold text-red-600">1.40</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-orange-50 rounded text-sm border-l-4 border-orange-500">
                    <span class="font-medium">2-zona</span>
                    <span class="font-semibold text-orange-600">1.25</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-yellow-50 rounded text-sm border-l-4 border-yellow-500">
                    <span class="font-medium">3-zona</span>
                    <span class="font-semibold text-yellow-600">1.00</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-green-50 rounded text-sm border-l-4 border-green-500">
                    <span class="font-medium">4-zona</span>
                    <span class="font-semibold text-green-600">0.75</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-blue-50 rounded text-sm border-l-4 border-blue-500">
                    <span class="font-medium">5-zona</span>
                    <span class="font-semibold text-blue-600">0.50</span>
                </div>
            </div>
        </div>

        <!-- Base Amounts -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Bazaviy miqdorlar</h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                    <span class="font-medium">2024</span>
                    <span class="font-semibold">340,000 so'm</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                    <span class="font-medium">2025</span>
                    <span class="font-semibold">375,000 so'm</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm">
                    <span class="font-medium">2026</span>
                    <span class="font-semibold">412,000 so'm</span>
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
                                    data-abovepermit="{{ $object->above_permit_volume ?? 0 }}"
                                    data-parking="{{ $object->parking_volume ?? 0 }}"
                                    data-technical="{{ $object->technical_rooms_volume ?? 0 }}"
                                    data-common="{{ $object->common_area_volume ?? 0 }}"
                                    data-subject="{{ $object->subject_id }}"
                                    data-constructiontype="{{ $object->construction_type_id ?? 1 }}"
                                    data-objecttype="{{ $object->object_type_id ?? 5 }}"
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

        <!-- Hisoblash summasi - REDESIGNED SECTION -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="flex items-center mb-8">
                <i data-feather="calculator" class="w-6 h-6 mr-3 text-blue-600"></i>
                <h3 class="text-2xl font-bold text-gray-900">Shartnoma summasi hisoblash</h3>
            </div>

            <!-- Obyekt hajmlari -->
            <div class="mb-10">
                <div class="flex items-center mb-6">
                    <i data-feather="home" class="w-5 h-5 mr-2 text-gray-600"></i>
                    <h4 class="text-lg font-semibold text-gray-800">Obyekt hajmlari</h4>
                </div>

                <!-- Main volumes grid -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <div class="text-sm font-medium text-blue-700 mb-2">Umumiy hajm</div>
                        <div class="text-xs text-blue-600 mb-3">(Hb)</div>
                        <div id="display_hb" class="text-2xl font-bold text-blue-900">0 m³</div>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <div class="text-sm font-medium text-green-700 mb-2">Ruxsatdan yuqori</div>
                        <div class="text-xs text-green-600 mb-3">(Hyu)</div>
                        <div id="display_hyu" class="text-2xl font-bold text-green-900">0 m³</div>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <div class="text-sm font-medium text-red-700 mb-2">Avtoturargoh</div>
                        <div class="text-xs text-red-600 mb-3">(Ha)</div>
                        <div id="display_ha" class="text-2xl font-bold text-red-900">0 m³</div>
                    </div>

                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                        <div class="text-sm font-medium text-orange-700 mb-2">Texnik xonalar</div>
                        <div class="text-xs text-orange-600 mb-3">(Ht)</div>
                        <div id="display_ht" class="text-2xl font-bold text-orange-900">0 m³</div>
                    </div>

                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <div class="text-sm font-medium text-purple-700 mb-2">Umumiy foydalanish</div>
                        <div class="text-xs text-purple-600 mb-3">(Hu)</div>
                        <div id="display_hu" class="text-2xl font-bold text-purple-900">0 m³</div>
                    </div>
                </div>

                <!-- Calculation result -->
                <div class="bg-gray-900 text-white rounded-lg p-6 text-center">
                    <div class="text-sm font-medium mb-2">Hisoblash hajmi</div>
                    <div id="display_calculated_volume" class="text-3xl font-bold mb-2">0 m³</div>
                    <div class="text-sm opacity-75">(Hb + Hyu) - (Ha + Ht + Hu)</div>
                </div>
            </div>

            <!-- Koeffitsientlar -->
            <div class="mb-10">
                <div class="flex items-center mb-6">
                    <i data-feather="settings" class="w-5 h-5 mr-2 text-gray-600"></i>
                    <h4 class="text-lg font-semibold text-gray-800">Koeffitsientlar</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="border border-gray-200 rounded-lg p-4 text-center bg-white">
                        <div class="text-sm font-medium text-gray-700 mb-2">Qurilish turi</div>
                        <div class="text-xs text-gray-500 mb-3">(Kt)</div>
                        <div id="display_kt" class="text-2xl font-bold text-gray-900">1.00</div>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4 text-center bg-white">
                        <div class="text-sm font-medium text-gray-700 mb-2">Obyekt turi</div>
                        <div class="text-xs text-gray-500 mb-3">(Ko)</div>
                        <div id="display_ko" class="text-2xl font-bold text-gray-900">1.00</div>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4 text-center bg-white">
                        <div class="text-sm font-medium text-gray-700 mb-2">Hududiy zona</div>
                        <div class="text-xs text-gray-500 mb-3">(Kz)</div>
                        <div id="display_kz" class="text-2xl font-bold text-gray-900">1.00</div>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4 text-center bg-white">
                        <div class="text-sm font-medium text-gray-700 mb-2">Joylashuv</div>
                        <div class="text-xs text-gray-500 mb-3">(Kj)</div>
                        <div id="display_kj" class="text-2xl font-bold text-gray-900">1.00</div>
                    </div>

                    <div class="bg-blue-600 text-white rounded-lg p-4 text-center">
                        <div class="text-sm font-medium mb-2">Jami koeffitsient</div>
                        <div class="text-xs opacity-75 mb-3">(Kt×Ko×Kz×Kj)</div>
                        <div id="display_total_coef" class="text-2xl font-bold">1.00</div>
                    </div>
                </div>
            </div>

            <!-- Asosiy hisoblash -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bazaviy hisoblash miqdori (Bh) *</label>
                    <select name="base_amount_id" required onchange="calculateTotal()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Bazaviy miqdorni tanlang</option>
                        <option value="1" data-amount="340000">340,000 so'm (2024)</option>
                        <option value="2" data-amount="375000">375,000 so'm (2025)</option>
                        <option value="3" data-amount="412000">412,000 so'm (2026)</option>
                    </select>
                    @error('base_amount_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hisoblash hajmi (m³) *</label>
                    <input type="number" name="contract_volume" step="0.01" value="{{ old('contract_volume') }}" required readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-700">
                    @error('contract_volume')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Obyekt tanlangandan keyin avtomatik to'ldiriladi</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hisobga olinadigan Bh *</label>
                    <input type="number" name="calculated_bh" step="0.01" value="{{ old('calculated_bh') }}" required readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-700">
                    @error('calculated_bh')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Bh × (Kt × Ko × Kz × Kj)</p>
                </div>
            </div>

            <!-- To'lov turi -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov turi *</label>
                    <select name="payment_type" required onchange="togglePaymentFields()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('initial_payment_percent')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="construction_period_field">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Qurilish muddati (yil) *</label>
                    <input type="number" name="construction_period_years" value="{{ old('construction_period_years', 2) }}" required
                           min="1" max="10" onchange="calculateCompletionDate(); calculateTotal();"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('construction_period_years')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Yakuniy summa -->
            <div class="bg-white border-2 border-blue-600 rounded-lg p-8 text-center">
                <div class="flex items-center justify-center mb-4">
                    <i data-feather="dollar-sign" class="w-6 h-6 mr-2 text-blue-600"></i>
                    <h4 class="text-xl font-semibold text-gray-900">Shartnoma umumiy summasi</h4>
                </div>
                <div id="total_amount_display" class="text-4xl font-bold text-blue-600 mb-4">0 so'm</div>
                <p class="text-sm text-gray-600 mb-4">Barcha koeffitsientlar hisobga olingan</p>
                <div id="formula_display" class="text-sm text-gray-500 font-mono bg-gray-100 p-3 rounded">
                    Ti = Hisobga olinadigan Bh × Hisoblash hajmi
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

<!-- Object Modal with Improved Map -->
<div id="objectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-xl shadow-xl transform transition-all max-w-7xl w-full max-h-[95vh] overflow-hidden">
            <form id="objectModalForm">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-600">
                    <h3 class="text-xl font-bold text-white">Yangi obyekt yaratish</h3>
                    <p class="text-sm text-blue-100 mt-1">Obyekt ma'lumotlarini to'ldiring va xaritadan joylashuvni belgilang</p>
                </div>

                <div class="flex h-[calc(95vh-200px)]">
                    <!-- Left Panel - Form -->
                    <div class="w-1/2 border-r border-gray-200 p-6 overflow-y-auto">
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
                                        <input type="text" name="geolocation" id="coordinatesInput" placeholder="41.2995, 69.2401" readonly
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
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
                                        <option value="">Xaritadan avtomatik aniqlanadi</option>
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
                    </div>

                    <!-- Right Panel - Improved Map -->
                    <div class="w-1/2 flex flex-col">
                        <div class="p-4 border-b border-gray-200 bg-gray-50">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                <i data-feather="map" class="w-5 h-5 inline mr-2 text-red-600"></i>
                                Zona aniqlash xaritasi
                            </h4>
                            <p class="text-sm text-gray-600">Xaritada obyekt joylashuvini belgilang va zona avtomatik aniqlanadi</p>
                        </div>
                        <div class="flex-1 p-4">
                            <div id="objectMap" class="w-full h-full rounded-lg border-2 border-gray-300 min-h-[400px]"></div>

                            <!-- Zone detection info -->
                            <div id="zoneInfo" class="mt-4 p-4 border-l-4 rounded-lg hidden bg-blue-50 border-blue-400">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900">Aniqlangan zona:</p>
                                        <p id="detectedZone" class="text-xl font-bold text-blue-600"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600">Koeffitsient:</p>
                                        <p id="zoneCoefficient" class="text-xl font-bold text-blue-600"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Map instructions -->
                            <div class="mt-4 text-center text-sm text-gray-500 bg-gray-100 p-3 rounded-lg">
                                <i data-feather="mouse-pointer" class="w-4 h-4 inline mr-1"></i>
                                Xaritada istalgan joyni bosib koordinata va zonani aniqlang
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

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
// =======================
// IMPROVED CONTRACT CREATION SYSTEM WITH FIXED MAP & ZONES
// =======================

// Real coefficient values
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
        'metro_radius_200m_outside': 0.6,  // Metro yaqinida
        'other_locations': 1.0             // Oddiy joylashuv
    }
};

// Zone data with colors
const zoneData = {
    '1': { name: '1-zona', coefficient: 1.40, color: '#dc2626', bgColor: '#fef2f2' },
    '2': { name: '2-zona', coefficient: 1.25, color: '#ea580c', bgColor: '#fff7ed' },
    '3': { name: '3-zona', coefficient: 1.00, color: '#ca8a04', bgColor: '#fefce8' },
    '4': { name: '4-zona', coefficient: 0.75, color: '#16a34a', bgColor: '#f0fdf4' },
    '5': { name: '5-zona', coefficient: 0.50, color: '#0891b2', bgColor: '#f0f9ff' }
};

// Map variables
let objectMap = null;
let mapMarker = null;
let currentZones = null;
let zoneBoundaries = {};
let kmlLoaded = false;

// =======================
// KML ZONE LOADING AND MAP INITIALIZATION
// =======================
async function loadZoneKML() {
    try {
        console.log('Loading zone KML file...');
        const response = await fetch('/zone.kml');
        if (!response.ok) {
            throw new Error(`KML file not found: ${response.status}`);
        }

        const kmlText = await response.text();
        const parser = new DOMParser();
        const kmlDoc = parser.parseFromString(kmlText, 'text/xml');

        // Check for parsing errors
        const parserError = kmlDoc.querySelector('parsererror');
        if (parserError) {
            throw new Error('Invalid KML format');
        }

        const placemarks = kmlDoc.querySelectorAll('Placemark');
        console.log(`Found ${placemarks.length} placemarks in KML`);

        placemarks.forEach((placemark, index) => {
            try {
                // Try different methods to extract zone information
                let zoneName = '';

                // Method 1: Check SchemaData
                const schemaData = placemark.querySelector('SchemaData');
                if (schemaData) {
                    const soniData = schemaData.querySelector('SimpleData[name="SONI"]') ||
                                    schemaData.querySelector('SimpleData[name="soni"]') ||
                                    schemaData.querySelector('SimpleData[name="Zone"]') ||
                                    schemaData.querySelector('SimpleData[name="zone"]');
                    if (soniData) {
                        zoneName = soniData.textContent.trim();
                    }
                }

                // Method 2: Check name element
                if (!zoneName) {
                    const nameElement = placemark.querySelector('name');
                    if (nameElement) {
                        zoneName = nameElement.textContent.trim();
                    }
                }

                // Method 3: Check description
                if (!zoneName) {
                    const descElement = placemark.querySelector('description');
                    if (descElement) {
                        zoneName = descElement.textContent.trim();
                    }
                }

                console.log(`Placemark ${index}: Zone name = "${zoneName}"`);

                // Extract zone number from the name
                const zoneMatch = zoneName.match(/(?:ZONA|ЗОНА|Zone|zone)[_\s-]*(\d+)/i);
                if (!zoneMatch) {
                    console.warn(`No zone number found in: "${zoneName}"`);
                    return;
                }

                const zoneId = zoneMatch[1];
                console.log(`Extracted zone ID: ${zoneId}`);

                // Extract coordinates from MultiGeometry or direct Polygon
                const multiGeometry = placemark.querySelector('MultiGeometry');
                const polygons = multiGeometry ?
                    multiGeometry.querySelectorAll('Polygon') :
                    placemark.querySelectorAll('Polygon');

                polygons.forEach((polygon, polyIndex) => {
                    const outerRing = polygon.querySelector('outerBoundaryIs LinearRing coordinates') ||
                                     polygon.querySelector('LinearRing coordinates') ||
                                     polygon.querySelector('coordinates');

                    if (!outerRing) {
                        console.warn(`No coordinates found for polygon ${polyIndex} in zone ${zoneId}`);
                        return;
                    }

                    const coordinatesText = outerRing.textContent.trim();
                    if (coordinatesText) {
                        const coords = parseKMLCoordinates(coordinatesText);
                        if (coords.length > 2) {
                            if (!zoneBoundaries[zoneId]) {
                                zoneBoundaries[zoneId] = [];
                            }
                            zoneBoundaries[zoneId].push(coords);
                            console.log(`Added polygon with ${coords.length} points to zone ${zoneId}`);
                        }
                    }
                });
            } catch (error) {
                console.error(`Error processing placemark ${index}:`, error);
            }
        });

        kmlLoaded = true;
        console.log('Zone boundaries loaded successfully:', Object.keys(zoneBoundaries));
        return true;
    } catch (error) {
        console.error('Error loading KML zones:', error);
        kmlLoaded = false;
        showNotification('Zona ma\'lumotlari yuklanmadi. Xarita cheklangan rejimda ishlaydi.', 'warning');
        return false;
    }
}

function parseKMLCoordinates(coordinatesText) {
    const coords = [];
    const coordPairs = coordinatesText.split(/[\s\n]+/).filter(pair => pair.trim());

    coordPairs.forEach(pair => {
        const parts = pair.split(',');
        if (parts.length >= 2) {
            const lng = parseFloat(parts[0]);
            const lat = parseFloat(parts[1]);

            // Validate coordinates are in Tashkent area
            if (!isNaN(lat) && !isNaN(lng) &&
                lat > 40.5 && lat < 42.0 &&
                lng > 68.5 && lng < 70.0) {
                coords.push([lat, lng]);
            }
        }
    });

    return coords;
}

function initializeMap() {
    if (typeof L === 'undefined') {
        console.error('Leaflet library not loaded');
        showNotification('Xarita kutubxonasi yuklanmagan', 'error');
        return;
    }

    try {
        if (!objectMap) {
            objectMap = L.map('objectMap').setView([41.2995, 69.2401], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 18
            }).addTo(objectMap);

            currentZones = L.layerGroup().addTo(objectMap);

            // Load and display zones from KML
            loadZoneKML().then((success) => {
                if (success && Object.keys(zoneBoundaries).length > 0) {
                    displayZonesOnMap();
                    showNotification('Zona ma\'lumotlari muvaffaqiyatli yuklandi', 'success');
                } else {
                    showNotification('Zona ma\'lumotlari topilmadi', 'warning');
                }
            });

            // Map click event for zone detection
            objectMap.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                // Remove existing marker
                if (mapMarker) {
                    objectMap.removeLayer(mapMarker);
                }

                // Add new marker
                mapMarker = L.marker([lat, lng], {
                    title: 'Obyekt joylashuvi',
                    draggable: true
                }).addTo(objectMap);

                // Make marker draggable
                mapMarker.on('dragend', function(e) {
                    const newPos = e.target.getLatLng();
                    updateLocationData(newPos.lat, newPos.lng);
                });

                updateLocationData(lat, lng);
            });

            console.log('Map initialized successfully');
        }
    } catch (error) {
        console.error('Error initializing map:', error);
        showNotification('Xarita yuklashda xatolik', 'error');
    }
}

function displayZonesOnMap() {
    if (!currentZones || Object.keys(zoneBoundaries).length === 0) return;

    Object.keys(zoneBoundaries).forEach(zoneId => {
        const zoneInfo = zoneData[zoneId];
        const polygons = zoneBoundaries[zoneId];

        if (zoneInfo && polygons) {
            polygons.forEach((coords) => {
                try {
                    const polygon = L.polygon(coords, {
                        color: zoneInfo.color,
                        fillColor: zoneInfo.color,
                        fillOpacity: 0.2,
                        weight: 2,
                        opacity: 0.8
                    }).bindPopup(`
                        <div class="text-center">
                            <strong>${zoneInfo.name}</strong><br>
                            Koeffitsient: <strong>${zoneInfo.coefficient}</strong>
                        </div>
                    `);

                    currentZones.addLayer(polygon);
                } catch (error) {
                    console.error(`Error creating polygon for zone ${zoneId}:`, error);
                }
            });
        }
    });

    // Fit map to show all zones
    if (currentZones.getLayers().length > 0) {
        try {
            const group = new L.featureGroup(currentZones.getLayers());
            objectMap.fitBounds(group.getBounds().pad(0.1));
        } catch (error) {
            console.log('Could not fit bounds, using default view');
        }
    }
}

function updateLocationData(lat, lng) {
    // Update coordinates input
    const coordsInput = document.getElementById('coordinatesInput');
    if (coordsInput) {
        coordsInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }

    // Detect zone using KML data
    const detectedZone = detectZoneByCoordinates(lat, lng);
    if (detectedZone) {
        showZoneInfo(detectedZone);
        updateModalZoneSelect(detectedZone);
    } else {
        hideZoneInfo();
        showNotification('Ushbu koordinata uchun zona aniqlanmadi', 'warning');
    }
}

// =======================
// DEBUG AND TEST FUNCTIONS
// =======================
function testZoneDetection() {
    console.log('=== Testing zone detection ===');

    // Test coordinates in central Tashkent (should be in Zone 1)
    const testLat = 41.2995;
    const testLng = 69.2401;

    console.log(`Testing coordinates: ${testLat}, ${testLng}`);
    console.log('Available zones:', Object.keys(zoneBoundaries));
    console.log('Zone data:', zoneData);

    if (objectMap) {
        objectMap.setView([testLat, testLng], 15);

        // Remove existing marker
        if (mapMarker) {
            objectMap.removeLayer(mapMarker);
        }

        // Add test marker
        mapMarker = L.marker([testLat, testLng], {
            title: 'Test Location - Toshkent Markazi',
            draggable: true
        }).addTo(objectMap);

        // Make marker draggable
        mapMarker.on('dragend', function(e) {
            const newPos = e.target.getLatLng();
            updateLocationData(newPos.lat, newPos.lng);
        });
    }

    updateLocationData(testLat, testLng);

    // Additional manual test
    console.log('Manual zone detection test:');
    const detectedZone = detectZoneByCoordinates(testLat, testLng);
    console.log('Result:', detectedZone);
}

function showZoneStats() {
    console.log('=== Zone Statistics ===');
    console.log('KML Loaded:', kmlLoaded);
    console.log('Available zones:', Object.keys(zoneBoundaries));

    Object.entries(zoneBoundaries).forEach(([zoneId, polygons]) => {
        console.log(`Zone ${zoneId}: ${polygons.length} polygons`);
        polygons.forEach((polygon, index) => {
            console.log(`  Polygon ${index}: ${polygon.length} points`);
        });
    });

    // Show notification with stats
    const zoneCount = Object.keys(zoneBoundaries).length;
    const totalPolygons = Object.values(zoneBoundaries).reduce((sum, polygons) => sum + polygons.length, 0);

    showNotification(`Yuklangan: ${zoneCount} zona, ${totalPolygons} polygon`, 'info');
}

function detectZoneByCoordinates(lat, lng) {
    if (!kmlLoaded || Object.keys(zoneBoundaries).length === 0) {
        console.warn('KML zones not loaded, cannot detect zone');
        return null;
    }

    // Check each zone's polygons
    for (const [zoneId, polygons] of Object.entries(zoneBoundaries)) {
        for (const polygon of polygons) {
            if (isPointInPolygon([lat, lng], polygon)) {
                console.log(`Point (${lat}, ${lng}) found in zone ${zoneId}`);
                return zoneId;
            }
        }
    }

    console.log(`Point (${lat}, ${lng}) not found in any zone`);
    return null;
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

function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    const R = 6371; // Earth radius in km
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function deg2rad(deg) {
    return deg * (Math.PI/180);
}

function showZoneInfo(zoneId) {
    const zoneInfo = document.getElementById('zoneInfo');
    const detectedZoneEl = document.getElementById('detectedZone');
    const zoneCoefficientEl = document.getElementById('zoneCoefficient');

    if (zoneInfo && detectedZoneEl && zoneCoefficientEl) {
        const zone = zoneData[zoneId];
        if (zone) {
            zoneInfo.style.borderColor = zone.color;
            zoneInfo.style.backgroundColor = zone.bgColor;
            zoneInfo.classList.remove('hidden');

            detectedZoneEl.textContent = zone.name;
            detectedZoneEl.style.color = zone.color;
            zoneCoefficientEl.textContent = zone.coefficient;
            zoneCoefficientEl.style.color = zone.color;
        }
    }
}

function hideZoneInfo() {
    const zoneInfo = document.getElementById('zoneInfo');
    if (zoneInfo) {
        zoneInfo.classList.add('hidden');
    }
}

function updateModalZoneSelect(zoneId) {
    const zoneSelect = document.getElementById('modalTerritorialZone');
    if (zoneSelect) {
        zoneSelect.value = zoneId;
        console.log(`Zone select updated to: ${zoneId}`);
    }
}

function setCoordinatesFromInput() {
    const coordsInput = document.getElementById('coordinatesInput');
    if (!coordsInput || !coordsInput.value.trim()) {
        showNotification('Koordinatalarni kiriting', 'warning');
        return;
    }

    const coordsText = coordsInput.value.trim();
    const coordMatch = coordsText.match(/(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/);

    if (!coordMatch) {
        showNotification('Koordinatalar formati noto\'g\'ri. Masalan: 41.2995, 69.2401', 'error');
        return;
    }

    const lat = parseFloat(coordMatch[1]);
    const lng = parseFloat(coordMatch[2]);

    // Validate coordinates are in reasonable range for Tashkent
    if (lat < 40.5 || lat > 42.0 || lng < 68.5 || lng > 70.0) {
        showNotification('Koordinatalar Toshkent hududidan tashqarida', 'warning');
        return;
    }

    // Update map view and marker
    if (objectMap) {
        objectMap.setView([lat, lng], 15);

        // Remove existing marker
        if (mapMarker) {
            objectMap.removeLayer(mapMarker);
        }

        // Add new marker
        mapMarker = L.marker([lat, lng], {
            title: 'Obyekt joylashuvi',
            draggable: true
        }).addTo(objectMap);

        // Make marker draggable
        mapMarker.on('dragend', function(e) {
            const newPos = e.target.getLatLng();
            updateLocationData(newPos.lat, newPos.lng);
        });
    }

    // Detect zone
    const detectedZone = detectZoneByCoordinates(lat, lng);
    if (detectedZone) {
        showZoneInfo(detectedZone);
        updateModalZoneSelect(detectedZone);
        showNotification(`Zona aniqlandi: ${zoneData[detectedZone].name}`, 'success');
    } else {
        hideZoneInfo();
        showNotification('Ushbu koordinata uchun zona aniqlanmadi', 'warning');
    }
}

// =======================
// CALCULATION FUNCTIONS
// =======================
function updateObjectVolume() {
    const objectSelect = document.querySelector('select[name="object_id"]');
    if (!objectSelect?.value) {
        resetCalculationDisplays();
        return;
    }

    const selectedOption = objectSelect.options[objectSelect.selectedIndex];

    try {
        // Get volume data with proper attribute names
        const volumes = {
            hb: parseFloat(selectedOption.dataset.volume) || 0,
            hyu: parseFloat(selectedOption.dataset.abovepermit) || 0,
            ha: parseFloat(selectedOption.dataset.parking) || 0,
            ht: parseFloat(selectedOption.dataset.technical) || 0,
            hu: parseFloat(selectedOption.dataset.common) || 0
        };

        // Update volume displays
        updateVolumeDisplays(volumes);

        // Calculate contract volume
        const contractVolume = Math.max(0, (volumes.hb + volumes.hyu) - (volumes.ha + volumes.ht + volumes.hu));

        // Update calculated volume display
        const displayCalcVolume = document.getElementById('display_calculated_volume');
        if (displayCalcVolume) {
            displayCalcVolume.textContent = formatNumber(contractVolume) + ' m³';
        }

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
        'display_hb': volumes.hb,
        'display_hyu': volumes.hyu,
        'display_ha': volumes.ha,
        'display_ht': volumes.ht,
        'display_hu': volumes.hu
    };

    Object.entries(displayMap).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = formatNumber(value) + ' m³';
    });
}

function calculateAndDisplayCoefficients(selectedOption) {
    const types = {
        construction: selectedOption.dataset.constructiontype || '1',
        object: selectedOption.dataset.objecttype || '5',
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

    // Update coefficient displays
    const displayMap = {
        'display_kt': coefs.kt,
        'display_ko': coefs.ko,
        'display_kz': coefs.kz,
        'display_kj': coefs.kj,
        'display_total_coef': totalCoef
    };

    Object.entries(displayMap).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value.toFixed(2);
    });
}

function calculateTotal() {
    try {
        const baseAmountSelect = document.querySelector('select[name="base_amount_id"]');
        const volumeInput = document.querySelector('input[name="contract_volume"]');
        const calculatedBhInput = document.querySelector('input[name="calculated_bh"]');
        const totalDisplay = document.getElementById('total_amount_display');
        const formulaDisplay = document.getElementById('formula_display');
        const totalCoefElement = document.getElementById('display_total_coef');

        if (!baseAmountSelect || !volumeInput || !totalDisplay) return;

        const selectedOption = baseAmountSelect.options[baseAmountSelect.selectedIndex];
        const baseAmount = selectedOption?.dataset.amount ? parseFloat(selectedOption.dataset.amount) : 0;
        const volume = parseFloat(volumeInput.value) || 0;
        const totalCoef = totalCoefElement ? parseFloat(totalCoefElement.textContent) || 1 : 1;

        // Calculate: Bh × total_coefficient
        const calculatedBh = baseAmount * totalCoef;
        if (calculatedBhInput) calculatedBhInput.value = calculatedBh.toFixed(2);

        // Calculate total: calculated_bh × volume
        const totalAmount = calculatedBh * volume;
        totalDisplay.textContent = formatNumber(totalAmount) + ' so\'m';

        // Update formula display
        if (formulaDisplay && baseAmount > 0 && volume > 0) {
            formulaDisplay.textContent =
                `Ti = ${formatNumber(calculatedBh)} × ${formatNumber(volume)} m³ = ${formatNumber(totalAmount)} so'm`;
        }

    } catch (error) {
        console.error('Error in calculateTotal:', error);
        showNotification('Hisoblashda xatolik yuz berdi', 'error');
    }
}

function resetCalculationDisplays() {
    const displays = [
        'display_hb', 'display_hyu', 'display_ha', 'display_ht', 'display_hu',
        'display_kt', 'display_ko', 'display_kz', 'display_kj', 'display_total_coef',
        'display_calculated_volume'
    ];

    displays.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = id.includes('display_k') ? '1.00' : '0 m³';
        }
    });

    document.getElementById('total_amount_display').textContent = '0 so\'m';
    ['contract_volume', 'calculated_bh'].forEach(name => {
        const element = document.querySelector(`input[name="${name}"]`);
        if (element) element.value = '';
    });
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

        // Initialize map after modal is visible
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

    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    if (form) form.reset();

    hideZoneInfo();

    if (mapMarker && objectMap) {
        objectMap.removeLayer(mapMarker);
        mapMarker = null;
    }
}

function toggleEntityFields() {
    const legalEntityRadio = document.querySelector('input[name="is_legal_entity"]:checked');
    if (!legalEntityRadio) return;

    const isLegalEntity = legalEntityRadio.value === '1';

    // Update visual cards
    document.querySelectorAll('.entity-type-card').forEach(card => {
        const input = card.parentElement.querySelector('input[type="radio"]');
        card.className = 'entity-type-card p-3 border-2 rounded-lg cursor-pointer transition-all';

        if (input?.checked) {
            if (input.value === '1') {
                card.classList.add('border-blue-500', 'bg-blue-50');
            } else {
                card.classList.add('border-green-500', 'bg-green-50');
            }
        } else {
            card.classList.add('border-gray-200');
        }
    });

    // Toggle field visibility
    const legalFields = document.getElementById('legalEntityFields');
    const physicalFields = document.getElementById('physicalPersonFields');

    if (legalFields && physicalFields) {
        if (isLegalEntity) {
            legalFields.classList.remove('hidden');
            physicalFields.classList.add('hidden');
        } else {
            legalFields.classList.add('hidden');
            physicalFields.classList.remove('hidden');
        }
    }
}

// =======================
// PAYMENT AND DATE FUNCTIONS
// =======================
function togglePaymentFields() {
    const paymentTypeSelect = document.querySelector('select[name="payment_type"]');
    if (!paymentTypeSelect) return;

    const isFullPayment = paymentTypeSelect.value === 'full';
    const initialPaymentField = document.getElementById('initial_payment_field');
    const constructionPeriodField = document.getElementById('construction_period_field');
    const initialPercentInput = document.querySelector('input[name="initial_payment_percent"]');

    if (isFullPayment) {
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
}

function calculateCompletionDate() {
    const contractDateInput = document.querySelector('input[name="contract_date"]');
    const yearsInput = document.querySelector('input[name="construction_period_years"]');
    const completionDateInput = document.querySelector('input[name="completion_date"]');

    if (!contractDateInput?.value || !yearsInput?.value || !completionDateInput) return;

    try {
        const contractDate = new Date(contractDateInput.value);
        const years = parseInt(yearsInput.value) || 2;

        if (isNaN(contractDate.getTime())) return;

        const completionDate = new Date(contractDate);
        completionDate.setFullYear(completionDate.getFullYear() + years);

        completionDateInput.value = completionDate.toISOString().split('T')[0];
    } catch (error) {
        console.error('Error calculating completion date:', error);
    }
}

// =======================
// FORM SUBMISSION HANDLERS
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
        ['volume', 'abovepermit', 'parking', 'technical', 'common', 'constructiontype', 'objecttype', 'zone', 'location'].forEach(attr => {
            newOption.dataset[attr] = result.object[attr] || '';
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
    notification.className = 'notification-toast fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full opacity-0';

    const typeClasses = {
        success: 'bg-green-600 text-white',
        error: 'bg-red-600 text-white',
        warning: 'bg-yellow-600 text-white',
        info: 'bg-blue-600 text-white'
    };

    notification.className += ` ${typeClasses[type] || typeClasses.info}`;

    notification.innerHTML = `
        <div class="flex items-center">
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg font-bold opacity-75 hover:opacity-100">&times;</button>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => notification.classList.remove('translate-x-full', 'opacity-0'), 100);
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

        handleFormSubmissions();
        setupEventListeners();

        togglePaymentFields();
        toggleEntityFields();
        calculateTotal();
        calculateCompletionDate();

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

console.log('Improved contract creation system loaded successfully');
</script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
