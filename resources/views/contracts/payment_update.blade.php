@extends('layouts.app')

@section('title', 'Shartnoma to\'lov boshqaruvi - ' . ($paymentData['contract']['contract_number'] ?? 'Yangi shartnoma'))

@section('header-actions')
<div class="flex flex-wrap gap-3">
    @if(isset($paymentData['contract']))
        <a href="{{ route('contracts.show', $paymentData['contract']['id']) }}" class="btn btn-secondary">
            <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
            Ortga qaytish
        </a>
        <a href="{{ route('contracts.export-report', $paymentData['contract']['id']) }}" class="btn btn-success">
            <i data-feather="download" class="w-4 h-4 mr-2"></i>
            Hisobot yuklab olish
        </a>
    @endif
</div>
@endsection

@section('content')
<style>
.govt-header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); }
.govt-card { border-left: 5px solid #1e40af; }
.success-gradient { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
.warning-gradient { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
.danger-gradient { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); }
.info-gradient { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
.purple-gradient { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }
.btn { @apply inline-flex items-center px-4 py-2 rounded-lg font-medium transition-colors; }
.btn-primary { @apply bg-blue-600 text-white hover:bg-blue-700; }
.btn-secondary { @apply bg-gray-600 text-white hover:bg-gray-700; }
.btn-success { @apply bg-green-600 text-white hover:bg-green-700; }
.btn-warning { @apply bg-yellow-600 text-white hover:bg-yellow-700; }
.btn-danger { @apply bg-red-600 text-white hover:bg-red-700; }
.btn-purple { @apply bg-purple-600 text-white hover:bg-purple-700; }
.quarter-item { @apply bg-white border-2 border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer; }
.quarter-item.completed { @apply border-green-400 bg-green-50; }
.quarter-item.partial { @apply border-yellow-400 bg-yellow-50; }
.quarter-item.overdue { @apply border-red-400 bg-red-50; }
.status-badge { @apply px-3 py-1 rounded-full text-xs font-semibold uppercase; }
.status-completed { @apply bg-green-100 text-green-800; }
.status-partial { @apply bg-yellow-100 text-yellow-800; }
.status-overdue { @apply bg-red-100 text-red-800; }
.status-pending { @apply bg-gray-100 text-gray-800; }
.payment-card { @apply bg-white border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer; }
.payment-card:hover { transform: translateY(-2px); }
</style>

<div class="space-y-8">
    <!-- Government Header -->
    <div class="govt-header rounded-2xl shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">TIC</h1>
                <p class="text-xl opacity-90">Shartnoma to'lov boshqaruv tizimi</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold">{{ date('d.m.Y') }}</p>
                <p class="opacity-90">{{ date('H:i') }}</p>
            </div>
        </div>
    </div>

    @include('partials.flash-messages')

    <!-- Contract Form -->
    <div class="bg-white rounded-2xl shadow-lg border govt-card">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="file-text" class="w-6 h-6 mr-3 text-blue-600"></i>
                Shartnoma ma'lumotlari
                @if(isset($paymentData['contract']) && $paymentData['contract']['has_amendments'])
                    <span class="ml-4 bg-purple-100 text-purple-800 text-sm px-3 py-1 rounded-full">
                        {{ $paymentData['contract']['amendment_count'] }} ta qo'shimcha kelishuv
                    </span>
                @endif
            </h2>
        </div>

        <form method="POST" action="{{ isset($paymentData['contract']) ? route('contracts.update', $paymentData['contract']['id']) : route('contracts.store') }}" class="p-8 space-y-8">
            @csrf
            @if(isset($paymentData['contract']))
                @method('PUT')
                <input type="hidden" name="from_payment_update" value="1">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Shartnoma raqami *</label>
                    <input type="text" name="contract_number" required
                           value="{{ old('contract_number', $paymentData['contract']['contract_number'] ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('contract_number') border-red-300 @enderror">
                    @error('contract_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Shartnoma sanasi *</label>
                    <input type="date" name="contract_date" required
                           value="{{ old('contract_date', $paymentData['contract']['contract_date'] ?? date('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('contract_date') border-red-300 @enderror">
                    @error('contract_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Yakunlash sanasi</label>
                    <input type="date" name="completion_date"
                           value="{{ old('completion_date', $paymentData['contract']['completion_date'] ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('completion_date') border-red-300 @enderror">
                    @error('completion_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
                <h3 class="text-xl font-bold text-blue-900 mb-6">Moliyaviy ma'lumotlar</h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Jami shartnoma summasi (so'm) *</label>
                        <input type="number" name="total_amount" required step="0.01" min="1"
                               value="{{ old('total_amount', $paymentData['contract']['total_amount'] ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-bold @error('total_amount') border-red-300 @enderror">
                        @error('total_amount')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">To'lov turi *</label>
                        <select name="payment_type" required onchange="togglePaymentType(this)"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('payment_type') border-red-300 @enderror">
                            <option value="">To'lov turini tanlang</option>
                            <option value="installment" {{ old('payment_type', $paymentData['contract']['payment_type'] ?? '') === 'installment' ? 'selected' : '' }}>Bo'lib to'lash</option>
                            <option value="full" {{ old('payment_type', $paymentData['contract']['payment_type'] ?? '') === 'full' ? 'selected' : '' }}>To'liq to'lash</option>
                        </select>
                        @error('payment_type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div id="installmentSettings" class="space-y-6 mt-6" style="{{ old('payment_type', $paymentData['contract']['payment_type'] ?? '') === 'full' ? 'display: none;' : '' }}">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Boshlang'ich to'lov (%)</label>
                            <input type="number" name="initial_payment_percent" min="0" max="100" step="1"
                                   value="{{ old('initial_payment_percent', $paymentData['contract']['initial_payment_percent'] ?? 20) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Qurulish muddati (yil)</label>
                            <input type="number" name="construction_period_years" min="1" max="10" step="1"
                                   value="{{ old('construction_period_years', $paymentData['contract']['construction_period_years'] ?? 2) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Choraklar soni</label>
                            <input type="number" name="quarters_count" min="1" max="20" step="1"
                                   value="{{ old('quarters_count', $paymentData['contract']['quarters_count'] ?? 8) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    @if(isset($paymentData['contract']))
                    <div class="bg-white rounded-xl p-6 border-2 border-blue-200">
                        <h4 class="text-lg font-bold text-blue-900 mb-4">To'lov hisob-kitobi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div class="success-gradient rounded-lg p-4">
                                <p class="text-sm font-medium text-green-800">Boshlang'ich to'lov</p>
                                <p class="text-2xl font-bold text-green-900">{{ $paymentData['contract']['initial_payment_formatted'] }}</p>
                            </div>
                            <div class="info-gradient rounded-lg p-4">
                                <p class="text-sm font-medium text-blue-800">Qolgan summa</p>
                                <p class="text-2xl font-bold text-blue-900">{{ $paymentData['contract']['remaining_amount_formatted'] }}</p>
                            </div>
                            <div class="warning-gradient rounded-lg p-4">
                                <p class="text-sm font-medium text-indigo-800">Chorak to'lovi</p>
                                <p class="text-2xl font-bold text-indigo-900">{{ $paymentData['contract']['quarterly_amount_formatted'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <button type="button" onclick="this.form.reset()" class="btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Tozalash
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ isset($paymentData['contract']) ? 'Yangilash' : 'Saqlash' }}
                </button>
            </div>
        </form>
    </div>

    @if(isset($paymentData['contract']))
    <!-- Payment Management -->
    <div class="bg-white rounded-2xl shadow-lg border govt-card">
        <div class="border-b border-gray-200 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="calendar" class="w-6 h-6 mr-3 text-blue-600"></i>
                To'lov boshqaruvi
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('contracts.create-schedule', $paymentData['contract']['id']) }}" class="btn btn-primary">
                    <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                    Jadval tuzish
                </a>
                <button onclick="showAddPaymentModal()" class="btn btn-success">
                    <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                    To'lov qo'shish
                </button>
                <button onclick="showAmendments()" class="btn btn-warning">
                    <i data-feather="file-plus" class="w-4 h-4 mr-2"></i>
                    Qo'shimcha kelishuvlar
                </button>
                <a href="{{ route('contracts.amendments.create', $paymentData['contract']['id']) }}" class="btn btn-purple">
                    <i data-feather="plus-circle" class="w-4 h-4 mr-2"></i>
                    Yangi kelishuv
                </a>
            </div>
        </div>

        <div class="p-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="info-gradient rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="target" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <p class="text-sm font-medium text-blue-800">JAMI PLAN</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $paymentData['summary_cards']['total_plan_formatted'] }}</p>
                    <div class="mt-2 text-xs text-blue-600">
                        Foiz: {{ $paymentData['summary_cards']['completion_percent'] }}%
                    </div>
                </div>

                <div class="success-gradient rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <p class="text-sm font-medium text-green-800">TO'LANGAN</p>
                    <p class="text-2xl font-bold text-green-900">{{ $paymentData['summary_cards']['total_paid_formatted'] }}</p>
                    <div class="mt-2 text-xs text-green-600">
                        {{ $paymentData['payment_history']['total_count'] ?? 0 }} ta to'lov
                    </div>
                </div>

                <div class="warning-gradient rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="clock" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                    <p class="text-sm font-medium text-yellow-800">JORIY QARZ</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ $paymentData['summary_cards']['current_debt_formatted'] }}</p>
                    <div class="mt-2 text-xs text-yellow-600">Kelajakdagi to'lovlar</div>
                </div>

                <div class="danger-gradient rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
                    </div>
                    <p class="text-sm font-medium text-red-800">MUDDATI O'TGAN</p>
                    <p class="text-2xl font-bold text-red-900">{{ $paymentData['summary_cards']['overdue_debt_formatted'] }}</p>
                    <div class="mt-2 text-xs text-red-600">Tezda to'lash kerak</div>
                </div>
            </div>

            <!-- Quarterly Breakdown -->
            @forelse($paymentData['quarterly_breakdown'] as $year => $yearData)
            <div class="mb-8 bg-gray-50 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">{{ $year }} yil</h3>
                    <div class="flex space-x-6 text-sm">
                        <div class="text-center">
                            <div class="text-gray-600">Plan</div>
                            <div class="font-bold text-blue-600">{{ $yearData['totals']['plan_formatted'] }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-600">To'langan</div>
                            <div class="font-bold text-green-600">{{ $yearData['totals']['paid_formatted'] }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-600">Foiz</div>
                            <div class="font-bold">{{ $yearData['totals']['percent'] }}%</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($yearData['quarters'] as $quarter => $quarterData)
                    <div class="quarter-item {{ $quarterData['status_class'] }}" onclick="openQuarterDetails({{ $year }}, {{ $quarter }})">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold">{{ $quarter }}-chorak</h4>
                            <div class="flex items-center space-x-2">
                                <span class="status-badge status-{{ $quarterData['status_class'] }}">
                                    {{ $quarterData['status'] }}
                                </span>
                                @if($quarterData['is_amendment_based'])
                                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded" title="Qo'shimcha kelishuv asosida">QK</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Plan:</span>
                                <span class="font-semibold">{{ $quarterData['plan_amount_formatted'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">To'langan:</span>
                                <span class="font-semibold text-green-600">{{ $quarterData['fact_total_formatted'] }}</span>
                            </div>
                            @if($quarterData['debt'] > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Qarz:</span>
                                <span class="font-semibold {{ $quarterData['is_overdue'] ? 'text-red-600' : 'text-yellow-600' }}">
                                    {{ $quarterData['debt_formatted'] }}
                                </span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Foiz:</span>
                                <span class="font-bold">{{ $quarterData['payment_percent'] }}%</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all {{ $quarterData['progress_color'] }}"
                                     style="width: {{ min(100, $quarterData['payment_percent']) }}%"></div>
                            </div>

                            <div class="mt-3 flex justify-between items-center">
                                <div class="text-xs text-gray-500">
                                    @if(count($quarterData['payments']) > 0)
                                        {{ count($quarterData['payments']) }} ta to'lov
                                    @else
                                        To'lov yo'q
                                    @endif
                                </div>
                                <div class="flex space-x-1">
                                    <button onclick="addQuarterPayment({{ $year }}, {{ $quarter }})"
                                           class="p-1 bg-green-100 text-green-600 rounded hover:bg-green-200"
                                           title="To'lov qo'shish">
                                        <i data-feather="plus" class="w-4 h-4"></i>
                                    </button>
                                    @if(count($quarterData['payments']) > 0)
                                    <button onclick="showQuarterPayments({{ $year }}, {{ $quarter }})"
                                           class="p-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200"
                                           title="To'lovlarni ko'rish">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <i data-feather="calendar" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">To'lov jadvali mavjud emas</h3>
                <p class="text-gray-600 mb-6">Choraklar bo'yicha to'lov jadvalini yaratish kerak</p>
                <a href="{{ route('contracts.create-schedule', $paymentData['contract']['id']) }}" class="btn btn-primary">
                    <i data-feather="calendar" class="w-5 h-5 mr-2"></i>
                    Jadval tuzish
                </a>
            </div>
            @endforelse

            <!-- Payment History -->
            @if(count($paymentData['payment_history']['payments']) > 0)
            <div class="mt-8 pt-8 border-t border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">To'lovlar tarixi</h3>
                    <div class="text-sm text-gray-500">
                        Jami: {{ $paymentData['payment_history']['total_count'] }} ta •
                        Summa: {{ $paymentData['payment_history']['total_amount_formatted'] }}
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach(array_slice($paymentData['payment_history']['payments'], 0, 9) as $payment)
                    <div class="payment-card" onclick="showPaymentDetails({{ $payment['id'] }})">
                        <div class="flex justify-between items-start mb-2">
                            <div class="font-medium text-lg">{{ $payment['amount_formatted'] }}</div>
                            <div class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                {{ $payment['quarter_info'] }}
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">
                            <i data-feather="calendar" class="w-4 h-4 inline mr-1"></i>
                            {{ $payment['payment_date'] }}
                        </div>
                        @if($payment['payment_number'])
                        <div class="text-sm text-gray-500 mb-2">
                            <i data-feather="hash" class="w-4 h-4 inline mr-1"></i>
                            {{ $payment['payment_number'] }}
                        </div>
                        @endif
                        @if($payment['notes'])
                        <div class="text-xs text-gray-400 truncate">
                            {{ $payment['notes'] }}
                        </div>
                        @endif
                        <div class="mt-2 flex justify-between items-center text-xs text-gray-500">
                            <span>{{ $payment['created_at_human'] }}</span>
                            @if(in_array('edit', $payment['actions']))
                            <div class="flex space-x-1">
                                <button onclick="editPayment({{ $payment['id'] }})" class="text-blue-600 hover:text-blue-800">
                                    <i data-feather="edit-2" class="w-3 h-3"></i>
                                </button>
                                <button onclick="deletePayment({{ $payment['id'] }})" class="text-red-600 hover:text-red-800">
                                    <i data-feather="trash-2" class="w-3 h-3"></i>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(count($paymentData['payment_history']['payments']) > 9)
                <div class="mt-4 text-center">
                    <button onclick="showAllPayments()" class="text-blue-600 hover:text-blue-800">
                        Barcha to'lovlarni ko'rish ({{ $paymentData['payment_history']['total_count'] }})
                    </button>
                </div>
                @endif
            </div>
            @endif

            <!-- Amendments Section -->
            @if(count($paymentData['amendments']) > 0)
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Qo'shimcha kelishuvlar</h3>
                <div class="space-y-4">
                    @foreach($paymentData['amendments'] as $amendment)
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-purple-900">
                                    Kelishuv #{{ $amendment['amendment_number'] }}
                                </h4>
                                <p class="text-sm text-purple-700 mt-1">
                                    {{ $amendment['amendment_date'] }} • {{ $amendment['reason'] }}
                                </p>
                                @if($amendment['new_total_amount'])
                                <p class="text-sm text-gray-600 mt-2">
                                    Yangi summa: {{ $amendment['new_total_amount_formatted'] }}
                                </p>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="status-badge status-{{ $amendment['status_class'] }}">
                                    {{ $amendment['status_text'] }}
                                </span>
                                <button onclick="showAmendmentDetails({{ $amendment['id'] }})"
                                       class="p-1 bg-purple-100 text-purple-600 rounded hover:bg-purple-200">
                                    <i data-feather="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Add Payment Modal -->
<div id="addPaymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Yangi to'lov qo'shish</h3>
                <button onclick="hideAddPaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="addPaymentForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov sanasi *</label>
                    <input type="date" id="modalPaymentDate" name="payment_date" required
                           min="{{ $paymentData['contract']['contract_date'] ?? date('Y-m-d') }}"
                           value="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov summasi (so'm) *</label>
                    <input type="number" id="modalPaymentAmount" name="payment_amount" step="0.01" min="0.01" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 text-lg font-medium">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hujjat raqami</label>
                        <input type="text" id="modalPaymentNumber" name="payment_number" maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chorak (avtomatik)</label>
                        <input type="text" id="modalQuarterInfo" readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                    <textarea id="modalPaymentNotes" name="payment_notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideAddPaymentModal()"
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Bekor qilish
                    </button>
                    <button type="submit"
                           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        To'lovni qo'shish
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Modal date change handler
    const modalDateInput = document.getElementById('modalPaymentDate');
    if (modalDateInput) {
        modalDateInput.addEventListener('change', function() {
            updateQuarterInfo(this.value);
        });
        // Initialize quarter info
        updateQuarterInfo(modalDateInput.value);
    }

    // Add payment form submission
    const addPaymentForm = document.getElementById('addPaymentForm');
    if (addPaymentForm) {
        addPaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAddPayment();
        });
    }
});

function togglePaymentType(select) {
    const installmentDiv = document.getElementById('installmentSettings');
    if (select.value === 'full') {
        installmentDiv.style.display = 'none';
    } else {
        installmentDiv.style.display = 'block';
    }
}

function showAddPaymentModal(targetYear = null, targetQuarter = null) {
    document.getElementById('addPaymentModal').classList.remove('hidden');

    if (targetYear && targetQuarter) {
        // Pre-fill date for specific quarter
        const middleMonth = (targetQuarter - 1) * 3 + 2;
        const suggestedDate = `${targetYear}-${String(middleMonth).padStart(2, '0')}-15`;
        document.getElementById('modalPaymentDate').value = suggestedDate;
        updateQuarterInfo(suggestedDate);
    }

    feather.replace();
}

function hideAddPaymentModal() {
    document.getElementById('addPaymentModal').classList.add('hidden');
    document.getElementById('addPaymentForm').reset();
}

function addQuarterPayment(year, quarter) {
    showAddPaymentModal(year, quarter);
}

function updateQuarterInfo(dateStr) {
    if (!dateStr) return;

    const date = new Date(dateStr);
    const year = date.getFullYear();
    const quarter = Math.ceil((date.getMonth() + 1) / 3);

    document.getElementById('modalQuarterInfo').value = `${quarter}-chorak ${year}`;
}

function submitAddPayment() {
    const form = document.getElementById('addPaymentForm');
    const formData = new FormData(form);

    // Add contract ID
    const contractId = {{ $paymentData['contract']['id'] ?? 0 }};

    fetch(`/contracts/${contractId}/store-payment`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddPaymentModal();
            location.reload(); // Refresh to show new payment
        } else {
            alert('Xatolik: ' + (data.message || 'Noma\'lum xatolik'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('To\'lov qo\'shishda xatolik yuz berdi');
    });
}

function openQuarterDetails(year, quarter) {
    const contractId = {{ $paymentData['contract']['id'] ?? 0 }};
    window.location.href = `/contracts/${contractId}/quarter-details/${year}/${quarter}`;
}

function showQuarterPayments(year, quarter) {
    // Show quarter payments in modal or navigate to detailed page
    openQuarterDetails(year, quarter);
}

function showPaymentDetails(paymentId) {
    // Show payment details modal
    console.log('Show payment details:', paymentId);
}

function editPayment(paymentId) {
    // Open edit payment modal
    console.log('Edit payment:', paymentId);
}

function deletePayment(paymentId) {
    if (confirm('Bu to\'lovni o\'chirishni tasdiqlaysizmi?')) {
        // Delete payment
        console.log('Delete payment:', paymentId);
    }
}

function showAllPayments() {
    // Show all payments modal or page
    console.log('Show all payments');
}

function showAmendments() {
    // Show amendments modal
    console.log('Show amendments');
}

function showAmendmentDetails(amendmentId) {
    const contractId = {{ $paymentData['contract']['id'] ?? 0 }};
    window.location.href = `/contracts/${contractId}/amendments/${amendmentId}`;
}
</script>
@endsection
