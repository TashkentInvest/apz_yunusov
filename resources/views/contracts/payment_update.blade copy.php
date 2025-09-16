@extends('layouts.app')

@section('title', 'Shartnoma to\'lov boshqaruvi - ' . ($contract->contract_number ?? 'Yangi shartnoma'))
@section('page-title', 'Shartnoma to\'lov boshqaruvi')

@section('header-actions')
<div class="flex flex-wrap gap-3">
    @if(isset($contract))
    <a href="{{ route('contracts.show', $contract) }}"
       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
        Ortga qaytish
    </a>
    @endif

    @if(isset($contract))
    <button onclick="openHistoryModal()"
            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <i data-feather="clock" class="w-4 h-4 mr-2"></i>
        Tarix ko'rish
    </button>
    @endif

    <button onclick="exportReport()"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="download" class="w-4 h-4 mr-2"></i>
        Hisobot yuklab olish
    </button>
</div>
@endsection

<style>
/* Custom styles for professional government appearance */
.govt-header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); }
.govt-card { border-left: 5px solid #1e40af; }
.success-gradient { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
.warning-gradient { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
.danger-gradient { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); }
.info-gradient { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
.primary-gradient { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); }

/* Year section styling */
.year-section {
    margin-bottom: 2rem;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.year-header {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-left: 6px solid #3b82f6;
    padding: 1.5rem 2rem;
    margin-bottom: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.year-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
}

.year-stats {
    display: flex;
    gap: 2rem;
    font-size: 0.875rem;
}

.year-stat {
    text-align: center;
}

.year-stat-label {
    color: #64748b;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.year-stat-value {
    font-weight: 700;
    font-size: 1.1rem;
}

.quarters-container {
    background: white;
    padding: 2rem;
}

.quarters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
}

.quarter-item {
    background: #fafafa;
    border: 2px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.quarter-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #e2e8f0;
    transition: background 0.3s ease;
}

.quarter-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.15);
    border-color: #3b82f6;
}

.quarter-item:hover::before {
    background: #3b82f6;
}

.quarter-item.overdue {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
}

.quarter-item.overdue::before {
    background: #dc2626;
}

.quarter-item.completed {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.quarter-item.completed::before {
    background: #16a34a;
}

.quarter-item.partial {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}

.quarter-item.partial::before {
    background: #f59e0b;
}

.quarter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.quarter-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.quarter-status {
    padding: 0.25rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-overdue { background: #fee2e2; color: #991b1b; }
.status-completed { background: #dcfce7; color: #166534; }
.status-partial { background: #fef3c7; color: #92400e; }
.status-pending { background: #f3f4f6; color: #374151; }

.quarter-amounts {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.amount-box {
    text-align: center;
    padding: 1rem;
    border-radius: 0.5rem;
    background: rgba(255,255,255,0.8);
    border: 1px solid rgba(0,0,0,0.05);
}

.amount-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

.amount-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
}

.amount-plan { color: #2563eb; }
.amount-paid { color: #16a34a; }
.amount-debt { color: #dc2626; }
.amount-overpaid { color: #059669; }

.progress-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1rem;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.progress-circle {
    position: relative;
    width: 60px;
    height: 60px;
}

.progress-circle svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.progress-circle .progress-bg {
    fill: none;
    stroke: #e5e7eb;
    stroke-width: 4;
}

.progress-circle .progress-bar {
    fill: none;
    stroke-width: 4;
    stroke-linecap: round;
    transition: stroke-dasharray 0.5s ease;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.875rem;
    font-weight: 700;
    color: #374151;
}

.quarter-actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    padding: 0.5rem;
    border-radius: 0.375rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-btn:hover { transform: scale(1.05); }
.action-view { background: #dbeafe; color: #1d4ed8; }
.action-view:hover { background: #bfdbfe; }
.action-edit { background: #d1fae5; color: #059669; }
.action-edit:hover { background: #bbf7d0; }
.action-pay { background: #fde68a; color: #d97706; }
.action-pay:hover { background: #fcd34d; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    color: #d1d5db;
}
</style>


@section('content')
<div class="space-y-8">
    <!-- Government Header -->
    <div class="govt-header rounded-2xl shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">TIC</h1>
                <p class="text-xl opacity-90">Shartnoma to'lov boshqaruv tizimi</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold">Sana: {{ date('d.m.Y') }}</p>
                <p class="opacity-90">Vaqt: {{ date('H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Contract Information Form -->
    <div class="bg-white rounded-2xl shadow-lg border govt-card">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="file-text" class="w-6 h-6 mr-3 text-blue-600"></i>
                Shartnoma ma'lumotlari
            </h2>
        </div>

        <form id="contractForm" class="p-8 space-y-8">
            @csrf
            @if(isset($contract))
                @method('PUT')
                <input type="hidden" name="contract_id" value="{{ $contract->id }}">
            @endif

            <!-- Basic Contract Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-3">Shartnoma raqami *</label>
        <input type="text" name="contract_number" required
               value="{{ old('contract_number', $contract->contract_number ?? '') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-medium @error('contract_number') border-red-300 @enderror">
        @error('contract_number')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-3">Shartnoma sanasi *</label>
        <input type="date" name="contract_date" required
               value="{{ old('contract_date', isset($contract) ? $contract->contract_date->format('Y-m-d') : date('Y-m-d')) }}"
               max="{{ date('Y-m-d') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg @error('contract_date') border-red-300 @enderror">
        @error('contract_date')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-3">Yakunlash sanasi</label>
        <input type="date" name="completion_date"
               value="{{ old('completion_date', isset($contract) && $contract->completion_date ? $contract->completion_date->format('Y-m-d') : '') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg @error('completion_date') border-red-300 @enderror">
        @error('completion_date')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

            <!-- Financial Information -->
            <div class="bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
                <h3 class="text-xl font-bold text-blue-900 mb-6">Moliyaviy ma'lumotlar</h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-3">Jami shartnoma summasi (so'm) *</label>
        <input type="number" name="total_amount" required step="0.01" min="1"
               value="{{ old('total_amount', $contract->total_amount ?? '') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-bold @error('total_amount') border-red-300 @enderror"
               onchange="calculatePaymentBreakdown()"
               placeholder="Masalan: 1000000.00">
        @error('total_amount')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-3">To'lov turi *</label>
        <select name="payment_type" required onchange="togglePaymentSettings()"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg @error('payment_type') border-red-300 @enderror">
            <option value="">To'lov turini tanlang</option>
            <option value="installment" {{ old('payment_type', $contract->payment_type ?? '') === 'installment' ? 'selected' : '' }}>Bo'lib to'lash</option>
            <option value="full" {{ old('payment_type', $contract->payment_type ?? '') === 'full' ? 'selected' : '' }}>To'liq to'lash</option>
        </select>
        @error('payment_type')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

                <!-- Installment Settings -->
                <div id="installmentSettings" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Boshlang'ich to'lov (%) *</label>
                            <input type="number" name="initial_payment_percent" min="0" max="100" step="1"
                                   value="{{ $contract->initial_payment_percent ?? 20 }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                   onchange="calculatePaymentBreakdown()">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Qurulish muddati (yil) *</label>
                            <input type="number" name="construction_period_years" min="1" max="10" step="1"
                                   value="{{ $contract->construction_period_years ?? 2 }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                   onchange="calculatePaymentBreakdown()">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Jami choraklar soni</label>
                            <input type="number" name="quarters_count" min="1" max="20" step="1"
                                   value="{{ $contract->quarters_count ?? 8 }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-semibold"
                                   onchange="calculatePaymentBreakdown()">
                        </div>
                    </div>

                    <!-- Payment Calculation Preview -->
                    <div id="paymentPreview" class="bg-white rounded-xl p-6 border-2 border-blue-200">
                        <h4 class="text-lg font-bold text-blue-900 mb-4">To'lov hisob-kitobi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div class="success-gradient rounded-lg p-4">
                                <p class="text-sm font-medium text-green-800">Boshlang'ich to'lov</p>
                                <p class="text-2xl font-bold text-green-900" id="initialAmount">0 so'm</p>
                            </div>
                            <div class="info-gradient rounded-lg p-4">
                                <p class="text-sm font-medium text-blue-800">Qolgan summa</p>
                                <p class="text-2xl font-bold text-blue-900" id="remainingAmount">0 so'm</p>
                            </div>
                            <div class="primary-gradient rounded-lg p-4">
                                <p class="text-sm font-medium text-indigo-800">Chorak to'lovi</p>
                                <p class="text-2xl font-bold text-indigo-900" id="quarterlyAmount">0 so'm</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
           <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
    <button type="button" onclick="resetForm()"
            class="px-8 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
        Tozalash
    </button>
    <button type="submit" id="contractSubmitBtn"
            class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
        <span id="submitText">{{ isset($contract) ? 'Yangilash' : 'Saqlash' }}</span>
        <i data-feather="loader" class="w-4 h-4 ml-2 hidden animate-spin" id="submitLoader"></i>
    </button>
</div>
        </form>
    </div>

    <!-- Payment Schedule Management -->
    @if(isset($contract))
    <div class="bg-white rounded-2xl shadow-lg border govt-card">
        <div class="border-b border-gray-200 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="calendar" class="w-6 h-6 mr-3 text-blue-600"></i>
                To'lov jadvali boshqaruvi
            </h2>
            <div class="flex space-x-3">
                <button onclick="openPaymentScheduleModal()"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                    Jadval tuzish
                </button>
                <button onclick="openPaymentModal()"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                    To'lov qo'shish
                </button>
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
                    <p class="text-2xl font-bold text-blue-900" id="totalPlan">0</p>
                </div>

                <div class="success-gradient rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <p class="text-sm font-medium text-green-800">TO'LANGAN</p>
                    <p class="text-2xl font-bold text-green-900" id="totalPaid">0</p>
                </div>

                <div class="warning-gradient rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="clock" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                    <p class="text-sm font-medium text-yellow-800">JORIY QARZ</p>
                    <p class="text-2xl font-bold text-yellow-900" id="currentDebt">0</p>
                </div>

                <div class="danger-gradient rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
                    </div>
                    <p class="text-sm font-medium text-red-800">MUDDATI O'TGAN</p>
                    <p class="text-2xl font-bold text-red-900" id="overdueDebt">0</p>
                </div>
            </div>

            <!-- Quarterly Breakdown - New Year-based Layout -->
            <div id="quarterlyBreakdown" class="space-y-6">
                <!-- Dynamic content will be inserted here -->
            </div>
        </div>
    </div>
    @endif
</div>

<!-- History Modal -->
<div id="historyModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-6xl sm:w-full max-h-screen overflow-y-auto">
            <div class="px-8 py-6 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <i data-feather="clock" class="w-5 h-5 mr-2 text-purple-600"></i>
                    Shartnoma o'zgarishlar tarixi
                </h3>
                <button onclick="closeHistoryModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="px-8 py-6">
                <div class="text-center text-gray-500 py-8">
                    Tarix funksiyasi hozircha ishlab chiqilmoqda
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Schedule Modal -->
<div id="paymentScheduleModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-4xl sm:w-full max-h-screen overflow-y-auto">
            <form id="paymentScheduleForm">
                @csrf
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">To'lov jadvali tuzish</h3>
                </div>

                <div class="px-8 py-6 space-y-6">
                    <!-- Schedule Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Jadval turi</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="schedule_type" value="auto" checked class="text-blue-600 mr-3">
                                <div>
                                    <span class="font-medium">Avtomatik taqsimlash</span>
                                    <p class="text-sm text-gray-600">Barcha choraklar uchun teng miqdorda</p>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="schedule_type" value="custom" class="text-blue-600 mr-3">
                                <div>
                                    <span class="font-medium">Qo'lda belgilash</span>
                                    <p class="text-sm text-gray-600">Har bir chorak uchun alohida miqdor</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Year and Quarters Selection -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Yil</label>
        <select name="schedule_year" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            @if(isset($contract))
                @php
                    $contractDate = $contract->contract_date;
                    $contractYear = $contractDate->year;
                    $contractMonth = $contractDate->month;
                    $contractQuarter = ceil($contractMonth / 3);
                    $constructionYears = $contract->construction_period_years ?? 2;
                    $quartersCount = $contract->quarters_count ?? 8;

                    // Calculate how many years needed for all quarters
                    $remainingQuartersInContractYear = 5 - $contractQuarter; // Quarters left in contract year
                    $remainingQuarters = max(0, $quartersCount - $remainingQuartersInContractYear);
                    $additionalYears = ceil($remainingQuarters / 4);
                    $endYear = $contractYear + $additionalYears;
                @endphp

                {{-- Contract year (starting quarter) --}}
                <option value="{{ $contractYear }}" selected>
                    {{ $contractYear }} yil ({{ $contractQuarter }}-chorakdan boshlanadi)
                </option>

                {{-- Additional years if needed --}}
                @for($year = $contractYear + 1; $year <= $endYear; $year++)
                    <option value="{{ $year }}">{{ $year }} yil</option>
                @endfor
            @else
                {{-- Fallback when no contract --}}
                <option value="{{ date('Y') }}">{{ date('Y') }} yil</option>
                <option value="{{ date('Y') + 1 }}">{{ date('Y') + 1 }} yil</option>
            @endif
        </select>
        @if(isset($contract))
            <p class="text-xs text-blue-600 mt-1 font-medium">
                📅 Shartnoma: {{ $contract->contract_date->format('d.m.Y') }}
                ({{ $contractQuarter }}-chorak)
            </p>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Choraklar soni</label>
        <input type="number" name="quarters_count" min="1" max="20" step="1"
               value="{{ isset($contract) ? ($contract->quarters_count ?? 8) : 4 }}"
               onchange="updateSchedulePreview()"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
               placeholder="1-20 orasida">
        @if(isset($contract))
            <p class="text-xs text-gray-500 mt-1">
                Shartnomada {{ $contract->quarters_count ?? 8 }} ta chorak belgilangan
            </p>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Jami summa (so'm)</label>
        @if(isset($contract))
            @php
                $initialPayment = $contract->total_amount * (($contract->initial_payment_percent ?? 0) / 100);
                $remainingAmount = $contract->total_amount - $initialPayment;
            @endphp
            <input type="number" name="total_schedule_amount" step="0.01" min="0.01"
                   value="{{ $remainingAmount }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-green-600 mt-1 font-medium">
                💰 Qolgan summa: {{ number_format($remainingAmount, 0, '.', ' ') }} so'm
            </p>
            <p class="text-xs text-gray-500">
                (Jami: {{ number_format($contract->total_amount, 0, '.', ' ') }} so'm -
                Boshlang'ich: {{ number_format($initialPayment, 0, '.', ' ') }} so'm)
            </p>
        @else
            <input type="number" name="total_schedule_amount" step="0.01" min="0.01"
                   placeholder="Choraklar uchun taqsimlanadigan summa"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        @endif
    </div>
</div>


                    <!-- Custom Schedule Grid -->
                    <div id="customScheduleGrid" class="hidden">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Choraklar bo'yicha taqsimlash</h4>
                        <div class="grid grid-cols-2 gap-4" id="quarterInputs">
                            <!-- Dynamic quarter inputs will be added here -->
                        </div>
                    </div>

                    <!-- Schedule Preview -->
                    <div id="schedulePreview" class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Jadval ko'rinishi</h4>
                        <div class="grid grid-cols-2 gap-3" id="previewGrid">
                            <!-- Preview cards will be generated here -->
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
          <button type="button" onclick="closePaymentScheduleModal()"
            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
        Bekor qilish
    </button>
    <button type="submit" id="scheduleSubmitBtn"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
        <i data-feather="calendar" class="w-4 h-4 mr-2"></i>
        Jadvalni saqlash
    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    button[type="submit"] {
    transition: all 0.2s ease;
}

button[type="submit"]:disabled {
    pointer-events: none;
    opacity: 0.6;
    cursor: not-allowed;
}

button[type="submit"]:active {
    transform: scale(0.98);
}

/* Visual feedback for clicking */
.payment-form-submitting {
    opacity: 0.7;
    pointer-events: none;
}

/* Loading state styling */
.btn-loading {
    position: relative;
    color: transparent !important;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<!-- Add Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form id="paymentForm">
                @csrf
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i data-feather="credit-card" class="w-5 h-5 mr-2 text-green-600"></i>
                        Yangi to'lov qo'shish
                    </h3>
                </div>

                <div class="px-8 py-6 space-y-4">
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">To'lov sanasi *</label>
    <input type="date" name="payment_date" required
           value="{{ old('payment_date', date('Y-m-d')) }}"
           min="{{ isset($contract) ? $contract->contract_date->format('Y-m-d') : date('Y-m-d') }}"
           max="{{ date('Y-m-d') }}"
           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('payment_date') border-red-300 @enderror">
    @error('payment_date')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
    @if(isset($contract))
        <p class="text-xs text-gray-500 mt-1">
            Eng erta sana: {{ $contract->contract_date->format('d.m.Y') }}
            (Shartnoma sanasi)
        </p>
    @else
        <p class="text-xs text-gray-500 mt-1">To'lov sanasi shartnoma sanasidan oldin bo'lishi mumkin emas</p>
    @endif
</div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">To'lov summasi (so'm) *</label>
        <input type="number" name="payment_amount" step="0.01" min="0.01" required
               value="{{ old('payment_amount') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-lg font-medium @error('payment_amount') border-red-300 @enderror"
               placeholder="0.00">
        @error('payment_amount')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Hujjat raqami</label>
        <input type="text" name="payment_number" maxlength="50"
               value="{{ old('payment_number') }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('payment_number') border-red-300 @enderror"
               placeholder="Chek, spravka raqami">
        @error('payment_number')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
        <textarea name="payment_notes" rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('payment_notes') border-red-300 @enderror"
                  placeholder="Qo'shimcha ma'lumot">{{ old('payment_notes') }}</textarea>
        @error('payment_notes')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>
                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
          <button type="button" onclick="closePaymentModal()"
            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
        Bekor qilish
    </button>
    <button type="submit" id="paymentSubmitBtn"
            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
        <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
        To'lovni qo'shish
    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quarter Details Modal -->
<div id="quarterDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-4xl sm:w-full max-h-screen overflow-y-auto">
            <div class="px-8 py-6 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900" id="quarterDetailsTitle">Chorak ma'lumotlari</h3>
                <button onclick="closeQuarterDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="px-8 py-6" id="quarterDetailsContent">
                <!-- Dynamic content will be inserted here -->
            </div>
        </div>
    </div>
</div>


{{-- 1. Add this after the Payment Schedule Management section --}}
@if(isset($contract))
<!-- Payment History Section -->
<div class="bg-white rounded-2xl shadow-lg border govt-card">
    <div class="border-b border-gray-200 p-6">
        <h2 class="text-2xl font-bold text-gray-900 flex items-center">
            <i data-feather="clock" class="w-6 h-6 mr-3 text-purple-600"></i>
            To'lov tarixi
        </h2>
    </div>
    <div class="p-8">
        <div id="paymentHistoryContainer">
            <div class="text-center py-8 text-gray-500">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-3"></div>
                <p>Tarix yuklanmoqda...</p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 2. Add Edit Payment Modal after the existing modals --}}
<!-- Edit Payment Modal -->
<div id="editPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form id="editPaymentForm">
                @csrf
                <input type="hidden" name="payment_id" value="">

                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i data-feather="edit-2" class="w-5 h-5 mr-2 text-blue-600"></i>
                        To'lovni tahrirlash
                    </h3>
                </div>

                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To'lov sanasi *</label>
                        <input type="date" name="payment_date" required
                               min="{{ isset($contract) ? $contract->contract_date->format('Y-m-d') : date('Y-m-d') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('payment_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To'lov summasi (so'm) *</label>
                        <input type="number" name="payment_amount" step="0.01" min="0" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg font-medium"
                               placeholder="0.00">
                        @error('payment_amount')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hujjat raqami</label>
                        <input type="text" name="payment_number" maxlength="50"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="Chek, spravka raqami">
                        @error('payment_number')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                        <textarea name="payment_notes" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Qo'shimcha ma'lumot"></textarea>
                        @error('payment_notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closeEditPaymentModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Bekor qilish
                    </button>
                    <button type="submit" id="editPaymentSubmitBtn"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="editPaymentSubmitText">Yangilash</span>
                        <i data-feather="loader" class="w-4 h-4 ml-2 hidden animate-spin" id="editPaymentLoader"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. Enhanced meta tags and CSRF protection --}}
@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="contract-start-date" content="{{ isset($contract) ? $contract->contract_date->format('Y-m-d') : date('Y-m-d') }}">
@endpush



@if ($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-feather="alert-triangle" class="w-5 h-5 text-red-400"></i>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Quyidagi xatoliklarni tuzating:</h3>
            <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif


{{-- 5. Enhanced success/error flash messages --}}
@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6" id="successMessage">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-feather="check-circle" class="w-5 h-5 text-green-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
        <div class="ml-auto pl-3">
            <button onclick="document.getElementById('successMessage').remove()" class="text-green-400 hover:text-green-600">
                <i data-feather="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6" id="errorMessage">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-feather="alert-triangle" class="w-5 h-5 text-red-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
        <div class="ml-auto pl-3">
            <button onclick="document.getElementById('errorMessage').remove()" class="text-red-400 hover:text-red-600">
                <i data-feather="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
@endif



@endsection
@push('scripts')
<script src="https://unpkg.com/feather-icons"></script>
<script>
// COMPLETE WORKING JAVASCRIPT - ALL ERRORS FIXED

// Global variables
const contractData = @json($contract ?? null);
let quarterlyData = {};
let currentQuarterData = null;

// Prevent multiple submissions
let isSubmittingPayment = false;
let isSubmittingSchedule = false;
let isSubmittingContract = false;

// Safe feather replace function
function safeFeatherReplace() {
    try {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    } catch (error) {
        console.warn('Feather icons replace failed:', error);
    }
}

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    disableEnterKeySubmissions();
    setupFormValidation();
    removeDateRestrictions(); // NEW: Remove date restrictions

    setTimeout(() => {
        const successMsg = document.getElementById('successMessage');
        const errorMsg = document.getElementById('errorMessage');
        if (successMsg) successMsg.remove();
        if (errorMsg) errorMsg.remove();
    }, 5000);

    if (document.querySelector('select[name="schedule_year"]')) {
        document.querySelector('select[name="schedule_year"]').addEventListener('change', updateSchedulePreview);
    }

    if (document.querySelector('input[name="quarters_count"]')) {
        document.querySelector('input[name="quarters_count"]').addEventListener('change', function() {
            if (document.querySelector('input[name="schedule_type"]:checked') &&
                document.querySelector('input[name="schedule_type"]:checked').value === 'custom') {
                generateQuarterInputs();
            }
            updateSchedulePreview();
        });
    }

    const totalAmountInput = document.querySelector('input[name="total_amount"]');
    if (totalAmountInput) {
        totalAmountInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value && value < 1) {
                this.setCustomValidity('Shartnoma summasi 1 so\'mdan kam bo\'lishi mumkin emas');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    const contractDateInput = document.querySelector('input[name="contract_date"]');
    if (contractDateInput) {
        contractDateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate > today) {
                this.setCustomValidity('Shartnoma sanasi bugundan kech bo\'lishi mumkin emas');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    safeFeatherReplace();

    if (contractData) {
        togglePaymentSettings();
        calculatePaymentBreakdown();
        loadQuarterlyData();
    } else {
        calculatePaymentBreakdown();
    }

    setupEventListeners();
});

// NEW: Remove restrictive date validations
function removeDateRestrictions() {
    const paymentDateInputs = document.querySelectorAll('input[name="payment_date"]');
    paymentDateInputs.forEach(input => {
        // Remove max attribute that restricts future dates
        input.removeAttribute('max');

        // Update validation to only check contract date minimum
        input.addEventListener('change', function() {
            if (!contractData) return;

            const selectedDate = new Date(this.value);
            const contractDate = new Date(contractData.contract_date);

            // Only validate against contract start date
            if (selectedDate < contractDate) {
                this.setCustomValidity('To\'lov sanasi shartnoma sanasidan oldin bo\'lishi mumkin emas');
            } else {
                this.setCustomValidity(''); // Clear any validation errors
            }
        });
    });
}

function disableEnterKeySubmissions() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            if (e.target.tagName === 'SELECT') {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            showNotification('Form submit qilish uchun tugmadan foydalaning, Enter tugmasi ishlamaydi', 'warning');
            return false;
        }
    });
}

function setupFormValidation() {
    document.addEventListener('click', function(e) {
        if (e.target.type === 'submit' || e.target.closest('button[type="submit"]')) {
            const button = e.target.type === 'submit' ? e.target : e.target.closest('button[type="submit"]');

            if (button.disabled || button.classList.contains('processing')) {
                e.preventDefault();
                e.stopPropagation();
                showNotification('Amal bajarilmoqda, iltimos kuting...', 'warning');
                return false;
            }

            button.classList.add('processing');
            setTimeout(() => {
                button.classList.remove('processing');
            }, 3000);
        }
    });
}

function calculateQuartersFromContractDate() {
    if (!contractData || !contractData.contract_date) {
        return {
            years: [new Date().getFullYear()],
            startQuarter: 1,
            contractYear: new Date().getFullYear(),
            contractQuarter: 1
        };
    }

    const contractDate = new Date(contractData.contract_date);
    const contractYear = contractDate.getFullYear();
    const contractMonth = contractDate.getMonth() + 1;
    const contractQuarter = Math.ceil(contractMonth / 3);

    const constructionYears = contractData.construction_period_years || 2;
    const totalQuarters = contractData.quarters_count || 8;

    const years = [];
    const startingQuartersInYear = 5 - contractQuarter;

    years.push(contractYear);

    let remainingQuarters = totalQuarters - startingQuartersInYear;
    let currentYear = contractYear + 1;

    while (remainingQuarters > 0) {
        years.push(currentYear);
        remainingQuarters -= 4;
        currentYear++;
    }

    return {
        years: years,
        startQuarter: contractQuarter,
        contractYear: contractYear,
        contractQuarter: contractQuarter,
        contractMonth: contractMonth
    };
}

function populateYearOptions() {
    const yearSelect = document.querySelector('select[name="schedule_year"]');
    if (!yearSelect || !contractData) return;

    const scheduleInfo = calculateQuartersFromContractDate();
    yearSelect.innerHTML = '';

    scheduleInfo.years.forEach((year, index) => {
        const option = document.createElement('option');
        option.value = year;

        if (year === scheduleInfo.contractYear) {
            option.textContent = `${year} yil (Shartnoma yili - ${scheduleInfo.contractQuarter}-chorakdan)`;
            option.selected = true;
        } else {
            option.textContent = `${year} yil`;
        }

        yearSelect.appendChild(option);
    });
}

function setupEventListeners() {
    const paymentTypeSelect = document.querySelector('select[name="payment_type"]');
    if (paymentTypeSelect) {
        paymentTypeSelect.addEventListener('change', togglePaymentSettings);
    }

    const totalAmountInput = document.querySelector('input[name="total_amount"]');
    if (totalAmountInput) {
        totalAmountInput.addEventListener('input', debounce(calculatePaymentBreakdown, 500));
    }

    const initialPaymentInput = document.querySelector('input[name="initial_payment_percent"]');
    if (initialPaymentInput) {
        initialPaymentInput.addEventListener('input', debounce(calculatePaymentBreakdown, 500));
    }

    const quartersCountInput = document.querySelector('input[name="quarters_count"]');
    if (quartersCountInput) {
        quartersCountInput.addEventListener('input', debounce(calculatePaymentBreakdown, 500));
    }

    document.querySelectorAll('input[name="schedule_type"]').forEach(radio => {
        radio.addEventListener('change', toggleCustomScheduleGrid);
    });

    const contractForm = document.getElementById('contractForm');
    if (contractForm) {
        contractForm.addEventListener('submit', handleContractSubmit);
    }

    const paymentScheduleForm = document.getElementById('paymentScheduleForm');
    if (paymentScheduleForm) {
        paymentScheduleForm.addEventListener('submit', handleScheduleSubmit);
    }

    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', handlePaymentSubmit);
    }
}

function togglePaymentSettings() {
    const paymentType = document.querySelector('select[name="payment_type"]').value;
    const installmentDiv = document.getElementById('installmentSettings');

    if (paymentType === 'full') {
        installmentDiv.style.display = 'none';
        document.querySelector('input[name="initial_payment_percent"]').value = 100;
        document.querySelector('input[name="quarters_count"]').value = 0;
    } else {
        installmentDiv.style.display = 'block';
        if (document.querySelector('input[name="initial_payment_percent"]').value == 100) {
            document.querySelector('input[name="initial_payment_percent"]').value = 20;
        }
    }
    calculatePaymentBreakdown();
}

function calculatePaymentBreakdown() {
    const totalAmount = parseFloat(document.querySelector('input[name="total_amount"]').value) || 0;
    const initialPercent = parseFloat(document.querySelector('input[name="initial_payment_percent"]').value) || 0;
    const quartersCount = parseInt(document.querySelector('input[name="quarters_count"]').value) || 0;
    const paymentType = document.querySelector('select[name="payment_type"]').value;

    const initialAmount = totalAmount * (initialPercent / 100);
    const remainingAmount = totalAmount - initialAmount;
    const quarterlyAmount = quartersCount > 0 ? remainingAmount / quartersCount : 0;

    const initialAmountEl = document.getElementById('initialAmount');
    const remainingAmountEl = document.getElementById('remainingAmount');
    const quarterlyAmountEl = document.getElementById('quarterlyAmount');

    if (initialAmountEl) initialAmountEl.textContent = formatFullCurrency(initialAmount);
    if (remainingAmountEl) remainingAmountEl.textContent = formatFullCurrency(remainingAmount);
    if (quarterlyAmountEl) quarterlyAmountEl.textContent = formatFullCurrency(quarterlyAmount);

    const previewDiv = document.getElementById('paymentPreview');
    if (previewDiv) {
        if (paymentType === 'full') {
            previewDiv.style.display = 'none';
        } else {
            previewDiv.style.display = 'block';
        }
    }
}

function loadQuarterlyData() {
    if (!contractData) return;

    fetch(`/contracts/${contractData.id}/quarterly-breakdown`)
        .then(response => response.json())
        .then(data => {
            quarterlyData = data || {};
            renderQuarterlyBreakdown();
            updateSummaryCards();
        })
        .catch(error => {
            console.error('Error loading quarterly data:', error);
            quarterlyData = {};
            renderEmptyQuarterlyBreakdown();
        });
}

function calculateYearTotals(quarterEntries) {
    let yearPlanTotal = 0, yearPaidTotal = 0, yearDebtTotal = 0;

    quarterEntries.forEach(([_, quarter]) => {
        const planAmount = parseFloat(quarter.plan_amount) || 0;
        const factTotal = parseFloat(quarter.fact_total) || 0;
        const debt = parseFloat(quarter.debt) || 0;

        yearPlanTotal += planAmount;
        yearPaidTotal += factTotal;
        yearDebtTotal += Math.max(0, debt);
    });

    return {
        yearPlanTotal: isNaN(yearPlanTotal) ? 0 : yearPlanTotal,
        yearPaidTotal: isNaN(yearPaidTotal) ? 0 : yearPaidTotal,
        yearDebtTotal: isNaN(yearDebtTotal) ? 0 : yearDebtTotal,
        yearPercent: yearPlanTotal > 0 ? (yearPaidTotal / yearPlanTotal) * 100 : 0
    };
}

function renderQuarterlyBreakdown() {
    const container = document.getElementById('quarterlyBreakdown');

    if (!quarterlyData || Object.keys(quarterlyData).length === 0) {
        renderEmptyQuarterlyBreakdown();
        return;
    }

    let html = '';
    const sortedYears = Object.keys(quarterlyData).sort((a, b) => parseInt(a) - parseInt(b));

    sortedYears.forEach(year => {
        const quarters = quarterlyData[year];

        if (!quarters || typeof quarters !== 'object') {
            return;
        }

        const quarterEntries = Object.entries(quarters)
            .filter(([quarter, data]) => {
                const planAmount = parseFloat(data.plan_amount) || 0;
                const factTotal = parseFloat(data.fact_total) || 0;
                return planAmount > 0 || factTotal > 0;
            })
            .sort((a, b) => parseInt(a[0]) - parseInt(b[0]));

        if (quarterEntries.length === 0) return;

        const totals = calculateYearTotals(quarterEntries);

        html += `
        <div class="year-section">
            <div class="year-header">
                <div class="year-title">
                    <i data-feather="calendar" class="w-6 h-6 mr-3 text-blue-600"></i>
                    ${year} yil to'lov jadvali
                </div>
                <div class="year-stats">
                    <div class="year-stat">
                        <div class="year-stat-label">Plan</div>
                        <div class="year-stat-value amount-plan">${formatFullCurrency(totals.yearPlanTotal)}</div>
                    </div>
                    <div class="year-stat">
                        <div class="year-stat-label">To'langan</div>
                        <div class="year-stat-value amount-paid">${formatFullCurrency(totals.yearPaidTotal)}</div>
                    </div>
                    <div class="year-stat">
                        <div class="year-stat-label">Qarz</div>
                        <div class="year-stat-value amount-debt">${formatFullCurrency(totals.yearDebtTotal)}</div>
                    </div>
                    <div class="year-stat">
                        <div class="year-stat-label">Foiz</div>
                        <div class="year-stat-value">${totals.yearPercent.toFixed(1)}%</div>
                    </div>
                </div>
            </div>

            <div class="quarters-container">
                <div class="quarters-grid">`;

        quarterEntries.forEach(([quarter, quarterData]) => {
            const planAmount = parseFloat(quarterData.plan_amount) || 0;
            const factTotal = parseFloat(quarterData.fact_total) || 0;
            const debt = parseFloat(quarterData.debt) || 0;
            const paymentPercent = parseFloat(quarterData.payment_percent) || 0;
            const isOverdue = quarterData.is_overdue || false;

            const statusClass = getQuarterStatusClass(quarterData);
            const statusText = getQuarterStatusText(quarterData);

            html += `
                    <div class="quarter-item ${statusClass}" onclick="openQuarterDetails(${year}, ${quarter})">
                        <div class="quarter-header">
                            <div class="quarter-title">
                                ${quarter}-chorak
                                ${isOverdue ? '<i data-feather="alert-triangle" class="w-4 h-4 text-red-500"></i>' : ''}
                            </div>
                            <div class="quarter-status ${getStatusColorClass(quarterData)}">${statusText}</div>
                        </div>

                        <div class="quarter-amounts">
                            <div class="amount-box">
                                <div class="amount-label">Plan</div>
                                <div class="amount-value amount-plan">${formatFullCurrency(planAmount)}</div>
                            </div>
                            <div class="amount-box">
                                <div class="amount-label">To'langan</div>
                                <div class="amount-value amount-paid">${formatFullCurrency(factTotal)}</div>
                            </div>
                        </div>

                        <div class="quarter-amounts">
                            <div class="amount-box">
                                <div class="amount-label">${debt >= 0 ? 'Qarz' : 'Ortiqcha'}</div>
                                <div class="amount-value ${debt >= 0 ? 'amount-debt' : 'amount-overpaid'}">${formatFullCurrency(Math.abs(debt))}</div>
                            </div>
                            <div class="amount-box">
                                <div class="amount-label">Foiz</div>
                                <div class="amount-value">${Math.round(paymentPercent)}%</div>
                            </div>
                        </div>

                        <div class="progress-section">
                            <div class="progress-circle">
                                <svg viewBox="0 0 36 36">
                                    <path class="progress-bg" d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                    <path class="progress-bar ${getProgressColor(paymentPercent)}" stroke-dasharray="${Math.round(paymentPercent)}, 100" d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                </svg>
                                <div class="progress-text">${Math.round(paymentPercent)}%</div>
                            </div>

                            <div class="quarter-actions">
                                <button onclick="event.stopPropagation(); openQuarterDetails(${year}, ${quarter})"
                                        class="action-btn action-view" title="Batafsil">
                                    <i data-feather="eye" class="w-4 h-4"></i>
                                </button>
                                <button onclick="event.stopPropagation(); editQuarterPlan(${year}, ${quarter})"
                                        class="action-btn action-edit" title="Tahrirlash">
                                    <i data-feather="edit-2" class="w-4 h-4"></i>
                                </button>
                                <button onclick="event.stopPropagation(); addQuarterPayment(${year}, ${quarter})"
                                        class="action-btn action-pay" title="To'lov qo'shish">
                                    <i data-feather="plus-circle" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>`;
        });

        html += `
                </div>
            </div>
        </div>`;
    });

    container.innerHTML = html;
    safeFeatherReplace();
}

function renderEmptyQuarterlyBreakdown() {
    const container = document.getElementById('quarterlyBreakdown');
    if (!container) return;

    container.innerHTML = `
    <div class="empty-state">
        <div class="empty-icon">
            <i data-feather="calendar" class="w-16 h-16"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-3">To'lov jadvali mavjud emas</h3>
        <p class="text-gray-600 mb-6">Choraklar bo'yicha to'lov jadvalini tuzish uchun "Jadval tuzish" tugmasini bosing</p>
        <button onclick="openPaymentScheduleModal()"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i data-feather="calendar" class="w-5 h-5 mr-2"></i>
            Jadval tuzish
        </button>
    </div>
    `;
    safeFeatherReplace();
}

function updateSummaryCards() {
    let totalPlan = 0, totalPaid = 0, currentDebt = 0, overdueDebt = 0;

    if (quarterlyData && typeof quarterlyData === 'object') {
        Object.values(quarterlyData).forEach(quarters => {
            if (quarters && typeof quarters === 'object') {
                Object.values(quarters).forEach(quarter => {
                    const planAmount = parseFloat(quarter.plan_amount) || 0;
                    const factTotal = parseFloat(quarter.fact_total) || 0;
                    const debt = parseFloat(quarter.debt) || 0;
                    const isOverdue = quarter.is_overdue || false;

                    totalPlan += planAmount;
                    totalPaid += factTotal;

                    if (debt > 0) {
                        if (isOverdue) {
                            overdueDebt += debt;
                        } else {
                            currentDebt += debt;
                        }
                    }
                });
            }
        });
    }

    totalPlan = isNaN(totalPlan) ? 0 : totalPlan;
    totalPaid = isNaN(totalPaid) ? 0 : totalPaid;
    currentDebt = isNaN(currentDebt) ? 0 : currentDebt;
    overdueDebt = isNaN(overdueDebt) ? 0 : overdueDebt;

    const totalPlanElement = document.getElementById('totalPlan');
    const totalPaidElement = document.getElementById('totalPaid');
    const currentDebtElement = document.getElementById('currentDebt');
    const overdueDebtElement = document.getElementById('overdueDebt');

    if (totalPlanElement) totalPlanElement.textContent = formatFullCurrency(totalPlan);
    if (totalPaidElement) totalPaidElement.textContent = formatFullCurrency(totalPaid);
    if (currentDebtElement) currentDebtElement.textContent = formatFullCurrency(currentDebt);
    if (overdueDebtElement) overdueDebtElement.textContent = formatFullCurrency(overdueDebt);
}

function getQuarterStatusClass(quarterData) {
    if (quarterData.is_overdue && quarterData.debt > 0) return 'overdue';
    if (quarterData.payment_percent >= 100) return 'completed';
    if (quarterData.payment_percent > 0) return 'partial';
    return '';
}

function getQuarterStatusText(quarterData) {
    if (quarterData.is_overdue && quarterData.debt > 0) return 'Muddati o\'tgan';
    if (quarterData.payment_percent >= 100) return 'To\'liq to\'langan';
    if (quarterData.payment_percent > 0) return 'Qisman to\'langan';
    return 'To\'lanmagan';
}

function getStatusColorClass(quarterData) {
    if (quarterData.is_overdue && quarterData.debt > 0) return 'status-overdue';
    if (quarterData.payment_percent >= 100) return 'status-completed';
    if (quarterData.payment_percent > 0) return 'status-partial';
    return 'status-pending';
}

function getProgressColor(percent) {
    if (percent >= 100) return 'stroke-green-500';
    if (percent >= 50) return 'stroke-yellow-500';
    if (percent > 0) return 'stroke-blue-500';
    return 'stroke-gray-300';
}

function openPaymentScheduleModal() {
    document.getElementById('paymentScheduleModal').classList.remove('hidden');
    populateYearOptions();

    if (contractData) {
        const totalScheduleAmount = document.querySelector('input[name="total_schedule_amount"]');
        if (totalScheduleAmount) {
            const initialPayment = contractData.total_amount * ((contractData.initial_payment_percent || 0) / 100);
            const remainingAmount = contractData.total_amount - initialPayment;
            totalScheduleAmount.value = remainingAmount;
        }

        const quartersInput = document.querySelector('input[name="quarters_count"]');
        if (quartersInput) {
            quartersInput.value = contractData.quarters_count || 8;
        }
    }

    updateSchedulePreview();
}

function closePaymentScheduleModal() {
    document.getElementById('paymentScheduleModal').classList.add('hidden');
    document.getElementById('paymentScheduleForm').reset();
}

function openPaymentModal() {
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentForm').reset();

    const existingNote = document.querySelector('#quarterPaymentNote');
    if (existingNote) {
        existingNote.remove();
    }
}

function openHistoryModal() {
    document.getElementById('historyModal').classList.remove('hidden');
}

function closeHistoryModal() {
    document.getElementById('historyModal').classList.add('hidden');
}

function openQuarterDetails(year, quarter) {
    const quarterData = quarterlyData[year] && quarterlyData[year][quarter];
    if (!quarterData) return;

    currentQuarterData = { year, quarter, data: quarterData };

    document.getElementById('quarterDetailsTitle').textContent = `${quarter}-chorak ${year} yil ma'lumotlari`;

    const content = `
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="info-gradient rounded-lg p-4 text-center">
                <p class="text-sm font-medium text-blue-800">PLAN SUMMASI</p>
                <p class="text-2xl font-bold text-blue-900">${formatFullCurrency(quarterData.plan_amount)}</p>
            </div>
            <div class="success-gradient rounded-lg p-4 text-center">
                <p class="text-sm font-medium text-green-800">TO'LANGAN</p>
                <p class="text-2xl font-bold text-green-900">${formatFullCurrency(quarterData.fact_total)}</p>
            </div>
            <div class="${quarterData.debt > 0 ? 'danger-gradient' : 'success-gradient'} rounded-lg p-4 text-center">
                <p class="text-sm font-medium ${quarterData.debt > 0 ? 'text-red-800' : 'text-green-800'}">${quarterData.debt > 0 ? 'QARZ' : 'ORTIQCHA'}</p>
                <p class="text-2xl font-bold ${quarterData.debt > 0 ? 'text-red-900' : 'text-green-900'}">${formatFullCurrency(Math.abs(quarterData.debt))}</p>
            </div>
        </div>

        <div class="flex justify-center space-x-4">
            <button onclick="editQuarterPlan(${year}, ${quarter})"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-feather="edit" class="w-4 h-4 mr-2"></i>
                Planni tahrirlash
            </button>
            <button onclick="addQuarterPayment(${year}, ${quarter})"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                To'lov qo'shish
            </button>
        </div>

        <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-lg font-bold text-gray-900 mb-4">To'lovlar tarixi</h4>
            <div id="quarterPaymentsList" class="space-y-3">
                ${quarterData.payments ? renderPaymentsList(quarterData.payments) : '<p class="text-gray-500 text-center py-8">Hali to\'lovlar mavjud emas</p>'}
            </div>
        </div>
    </div>
    `;

    document.getElementById('quarterDetailsContent').innerHTML = content;
    document.getElementById('quarterDetailsModal').classList.remove('hidden');
    safeFeatherReplace();
}

function closeQuarterDetailsModal() {
    document.getElementById('quarterDetailsModal').classList.add('hidden');
    currentQuarterData = null;
}

function toggleCustomScheduleGrid() {
    const scheduleType = document.querySelector('input[name="schedule_type"]:checked');
    if (!scheduleType) return;

    const customGrid = document.getElementById('customScheduleGrid');

    if (scheduleType.value === 'custom') {
        customGrid.classList.remove('hidden');
        generateQuarterInputs();
    } else {
        customGrid.classList.add('hidden');
    }

    updateSchedulePreview();
}

function generateQuarterInputs() {
    const quartersCount = parseInt(document.querySelector('input[name="quarters_count"]').value) || 4;
    const container = document.getElementById('quarterInputs');

    if (!contractData) {
        generateStandardQuarterInputs(quartersCount, container);
        return;
    }

    const contractDate = new Date(contractData.contract_date);
    const contractYear = contractDate.getFullYear();
    const contractMonth = contractDate.getMonth() + 1;
    const contractQuarter = Math.ceil(contractMonth / 3);

    let html = '';
    const equalPercent = (100 / quartersCount).toFixed(2);

    const gridClass = quartersCount <= 4 ? 'grid-cols-2' :
                     quartersCount <= 8 ? 'grid-cols-4' : 'grid-cols-5';
    container.className = `grid ${gridClass} gap-4`;

    let currentYear = contractYear;
    let currentQuarter = contractQuarter;

    for (let i = 1; i <= quartersCount; i++) {
        const isFirst = i === 1;
        const quarterInfo = isFirst ? ' (Boshlash)' : '';
        const quarterLabel = `${currentQuarter}-chorak ${currentYear}${quarterInfo}`;

        html += `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">${quarterLabel} (%)</label>
            <input type="number" name="quarter_${i}_percent" min="0" max="100" step="0.01" value="${equalPercent}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 ${isFirst ? 'ring-2 ring-green-400' : ''}"
                   onchange="updateSchedulePreview()">
        </div>
        `;

        currentQuarter++;
        if (currentQuarter > 4) {
            currentQuarter = 1;
            currentYear++;
        }
    }

    container.innerHTML = html;
}

function generateStandardQuarterInputs(quartersCount, container) {
    let html = '';
    const equalPercent = (100 / quartersCount).toFixed(2);

    const gridClass = quartersCount <= 4 ? 'grid-cols-2' :
                     quartersCount <= 8 ? 'grid-cols-4' : 'grid-cols-5';
    container.className = `grid ${gridClass} gap-4`;

    for (let i = 1; i <= quartersCount; i++) {
        html += `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">${i}-chorak (%)</label>
            <input type="number" name="quarter_${i}_percent" min="0" max="100" step="0.01" value="${equalPercent}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                   onchange="updateSchedulePreview()">
        </div>
        `;
    }

    container.innerHTML = html;
}

function updateSchedulePreview() {
    const quartersCount = parseInt(document.querySelector('input[name="quarters_count"]').value) || 4;
    const totalAmount = parseFloat(document.querySelector('input[name="total_schedule_amount"]').value) || 0;
    const scheduleType = document.querySelector('input[name="schedule_type"]:checked');
    const previewGrid = document.getElementById('previewGrid');

    if (!scheduleType || !previewGrid) return;

    if (!contractData) {
        generateStandardPreview(quartersCount, totalAmount, scheduleType.value, previewGrid);
        return;
    }

    const contractDate = new Date(contractData.contract_date);
    const contractYear = contractDate.getFullYear();
    const contractMonth = contractDate.getMonth() + 1;
    const contractQuarter = Math.ceil(contractMonth / 3);

    let html = '';
    let totalPercent = 0;

    const gridClass = quartersCount <= 4 ? 'grid-cols-2' :
                     quartersCount <= 8 ? 'grid-cols-4' :
                     quartersCount <= 12 ? 'grid-cols-6' : 'grid-cols-8';
    previewGrid.className = `grid ${gridClass} gap-3`;

    let currentYear = contractYear;
    let currentQuarter = contractQuarter;

    for (let i = 0; i < quartersCount; i++) {
        let percent, amount;

        if (scheduleType.value === 'auto') {
            percent = 100 / quartersCount;
            amount = totalAmount / quartersCount;
        } else {
            const input = document.querySelector(`input[name="quarter_${i + 1}_percent"]`);
            percent = parseFloat(input ? input.value : 0) || 0;
            amount = totalAmount * (percent / 100);
        }

        totalPercent += percent;

        const isFirst = i === 0;
        const quarterLabel = `${currentQuarter}-chorak ${currentYear}`;

        html += `
        <div class="bg-white border-2 border-blue-200 rounded-lg p-3 text-center ${isFirst ? 'ring-2 ring-green-400 bg-green-50' : ''}">
            <div class="text-sm font-medium text-blue-600 mb-1">${quarterLabel}</div>
            <div class="text-lg font-bold text-blue-900">${formatFullCurrency(amount)}</div>
            <div class="text-xs text-gray-500">${percent.toFixed(1)}%</div>
            ${isFirst ? '<div class="text-xs text-green-600 font-bold mt-1">BOSHLASH</div>' : ''}
        </div>
        `;

        currentQuarter++;
        if (currentQuarter > 4) {
            currentQuarter = 1;
            currentYear++;
        }
    }

    if (scheduleType.value === 'custom') {
        const isValidTotal = Math.abs(totalPercent - 100) < 0.1;
        html += `
        <div class="col-span-full mt-4 p-4 rounded-lg ${isValidTotal ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
            <div class="text-center">
                <div class="font-bold">Jami: ${totalPercent.toFixed(1)}%</div>
                <div class="text-sm">${isValidTotal ? 'To\'g\'ri' : '100% bo\'lishi kerak'}</div>
            </div>
        </div>
        `;
    } else {
        html += `
        <div class="col-span-full mt-4 p-4 rounded-lg bg-blue-100 text-blue-800">
            <div class="text-center">
                <div class="font-bold">Jami: 100%</div>
                <div class="text-sm">Har bir chorak: ${(100/quartersCount).toFixed(1)}%</div>
                <div class="text-xs text-gray-600 mt-1">
                    <strong>Boshlanadi:</strong> ${contractQuarter}-chorak ${contractYear}
                    (${contractDate.toLocaleDateString('uz-UZ')})
                </div>
            </div>
        </div>
        `;
    }

    previewGrid.innerHTML = html;
}

function generateStandardPreview(quartersCount, totalAmount, scheduleType, previewGrid) {
    let html = '';
    let totalPercent = 0;

    const gridClass = quartersCount <= 4 ? 'grid-cols-2' :
                     quartersCount <= 8 ? 'grid-cols-4' :
                     quartersCount <= 12 ? 'grid-cols-6' : 'grid-cols-8';
    previewGrid.className = `grid ${gridClass} gap-3`;

    for (let i = 1; i <= quartersCount; i++) {
        let percent, amount;

        if (scheduleType === 'auto') {
            percent = 100 / quartersCount;
            amount = totalAmount / quartersCount;
        } else {
            const input = document.querySelector(`input[name="quarter_${i}_percent"]`);
            percent = parseFloat(input ? input.value : 0) || 0;
            amount = totalAmount * (percent / 100);
        }

        totalPercent += percent;

        html += `
        <div class="bg-white border-2 border-blue-200 rounded-lg p-3 text-center">
            <div class="text-sm font-medium text-blue-600 mb-1">${i}-chorak</div>
            <div class="text-lg font-bold text-blue-900">${formatFullCurrency(amount)}</div>
            <div class="text-xs text-gray-500">${percent.toFixed(1)}%</div>
        </div>
        `;
    }

    previewGrid.innerHTML = html;
}

async function handleContractSubmit(e) {
    e.preventDefault();

    if (isSubmittingContract) {
        showNotification('Shartnoma saqlanmoqda, iltimos kuting...', 'warning');
        return false;
    }

    isSubmittingContract = true;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    toggleSubmitState(submitBtn, submitText, submitLoader, true);

    try {
        const formData = new FormData(e.target);

        const contractNumber = formData.get('contract_number');
        const totalAmount = parseFloat(formData.get('total_amount'));
        const contractDate = formData.get('contract_date');

        if (!contractNumber || contractNumber.trim().length < 3) {
            throw new Error('Shartnoma raqami kamida 3 ta belgidan iborat bo\'lishi kerak');
        }

        if (!totalAmount || totalAmount < 1) {
            throw new Error('Shartnoma summasi 1 so\'mdan kam bo\'lishi mumkin emas');
        }

        if (!contractDate) {
            throw new Error('Shartnoma sanasini kiriting');
        }

        const selectedDate = new Date(contractDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate > today) {
            throw new Error('Shartnoma sanasi bugundan kech bo\'lishi mumkin emas');
        }

        const url = contractData ? `/contracts/${contractData.id}` : '/contracts/store';
        const method = contractData ? 'PUT' : 'POST';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value;

        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-HTTP-Method-Override': method,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            showNotification(result.message || 'Muvaffaqiyatli saqlandi', 'success');
            if (!contractData && result.contract) {
                setTimeout(() => {
                    window.location.href = `/contracts/${result.contract.id}/payment-update`;
                }, 1500);
            } else {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } else {
            throw new Error(result.message || 'Xatolik yuz berdi');
        }
    } catch (error) {
        console.error('Contract submission error:', error);
        showNotification(error.message, 'error');
    } finally {
        isSubmittingContract = false;
        toggleSubmitState(submitBtn, submitText, submitLoader, false);
    }
}

async function handleScheduleSubmit(e) {
    e.preventDefault();

    if (isSubmittingSchedule) {
        showNotification('Jadval yaratilmoqda, iltimos kuting...', 'warning');
        return false;
    }

    if (!contractData) {
        showNotification('Avval shartnomani saqlang', 'error');
        return false;
    }

    isSubmittingSchedule = true;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <i data-feather="loader" class="w-4 h-4 mr-2 animate-spin"></i>
        Jadval yaratilmoqda...
    `;

    try {
        const formData = new FormData(e.target);
        const scheduleType = formData.get('schedule_type');
        const quartersCount = parseInt(formData.get('quarters_count'));
        const totalAmount = parseFloat(formData.get('total_schedule_amount'));

        if (!quartersCount || quartersCount < 1 || quartersCount > 20) {
            throw new Error('Choraklar soni 1-20 orasida bo\'lishi kerak');
        }

        if (!totalAmount || totalAmount <= 0) {
            throw new Error('Jadval summasi 0 dan katta bo\'lishi kerak');
        }

        const contractDate = new Date(contractData.contract_date);
        const contractYear = contractDate.getFullYear();
        const contractMonth = contractDate.getMonth() + 1;
        const contractQuarter = Math.ceil(contractMonth / 3);

        const quarterlySchedule = [];
        let currentYear = contractYear;
        let currentQuarter = contractQuarter;

        for (let i = 0; i < quartersCount; i++) {
            let quarterAmount;

            if (scheduleType === 'auto') {
                quarterAmount = totalAmount / quartersCount;
            } else {
                const percent = parseFloat(formData.get(`quarter_${i + 1}_percent`) || 0);
                quarterAmount = totalAmount * (percent / 100);
            }

            quarterlySchedule.push({
                year: currentYear,
                quarter: currentQuarter,
                quarter_amount: quarterAmount,
                sequence: i + 1
            });

            currentQuarter++;
            if (currentQuarter > 4) {
                currentQuarter = 1;
                currentYear++;
            }
        }

        if (scheduleType === 'custom') {
            let totalPercent = 0;
            for (let i = 1; i <= quartersCount; i++) {
                totalPercent += parseFloat(formData.get(`quarter_${i}_percent`) || 0);
            }

            if (Math.abs(totalPercent - 100) > 0.1) {
                throw new Error('Foizlar yig\'indisi 100% bo\'lishi kerak');
            }
        }

        const scheduleData = new FormData();
        scheduleData.append('_token', formData.get('_token'));
        scheduleData.append('schedule_type', scheduleType);
        scheduleData.append('quarters_count', quartersCount);
        scheduleData.append('total_schedule_amount', totalAmount);
        scheduleData.append('contract_start_date', contractData.contract_date);
        scheduleData.append('quarterly_schedule', JSON.stringify(quarterlySchedule));

        const response = await fetch(`/contracts/${contractData.id}/create-quarterly-schedule`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: scheduleData
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            closePaymentScheduleModal();
            showNotification(result.message, 'success');
            loadQuarterlyData();
        } else {
            throw new Error(result.message || 'Jadval yaratishda xatolik');
        }
    } catch (error) {
        console.error('Schedule submission error:', error);
        showNotification(error.message, 'error');
    } finally {
        isSubmittingSchedule = false;
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        safeFeatherReplace();
    }
}

// ENHANCED: Payment submission with debug and quarter detection
async function handlePaymentSubmit(e) {
    e.preventDefault();

    if (isSubmittingPayment) {
        showNotification('To\'lov qo\'shilmoqda, iltimos kuting...', 'warning');
        return false;
    }

    if (!contractData) {
        showNotification('Avval shartnomani saqlang', 'error');
        return false;
    }

    isSubmittingPayment = true;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <i data-feather="loader" class="w-4 h-4 mr-2 animate-spin"></i>
        To'lov qo'shilmoqda...
    `;

    try {
        const formData = new FormData(e.target);
        const paymentDate = formData.get('payment_date');
        const paymentAmount = parseFloat(formData.get('payment_amount'));

        if (!paymentDate) {
            throw new Error('To\'lov sanasini kiriting');
        }

        if (!paymentAmount || paymentAmount <= 0) {
            throw new Error('To\'lov summasini to\'g\'ri kiriting');
        }

        console.log('=== PAYMENT QUARTER CALCULATION DEBUG ===');
        console.log('Payment Date:', paymentDate);
        console.log('Contract Data:', contractData);
        console.log('Quarterly Data:', quarterlyData);

        const paymentDateObj = new Date(paymentDate);
        const paymentYear = paymentDateObj.getFullYear();
        const paymentMonth = paymentDateObj.getMonth() + 1;
        const calculatedQuarter = Math.ceil(paymentMonth / 3);

        console.log('Date Analysis:');
        console.log('- Payment Year:', paymentYear);
        console.log('- Payment Month:', paymentMonth);
        console.log('- Calculated Quarter:', calculatedQuarter);

        console.log('Available Quarters in Schedule:');
        if (quarterlyData && typeof quarterlyData === 'object') {
            Object.keys(quarterlyData).forEach(year => {
                if (quarterlyData[year] && typeof quarterlyData[year] === 'object') {
                    Object.keys(quarterlyData[year]).forEach(quarter => {
                        const planAmount = parseFloat(quarterlyData[year][quarter].plan_amount) || 0;
                        if (planAmount > 0) {
                            console.log(`- ${quarter}-chorak ${year}: Plan ${planAmount} so'm`);
                        }
                    });
                }
            });
        }

        let targetQuarter;
        if (quarterlyData &&
            quarterlyData[paymentYear] &&
            quarterlyData[paymentYear][calculatedQuarter] &&
            parseFloat(quarterlyData[paymentYear][calculatedQuarter].plan_amount) > 0) {

            targetQuarter = {
                year: paymentYear,
                quarter: calculatedQuarter,
                date: paymentDateObj,
                isValid: true,
                source: 'direct_calculation'
            };

            console.log('✅ Direct quarter found:', targetQuarter);
        } else {
            console.log('❌ Direct quarter not found, searching for alternatives...');

            const availableQuarters = [];
            if (quarterlyData && typeof quarterlyData === 'object') {
                Object.keys(quarterlyData).forEach(year => {
                    if (quarterlyData[year] && typeof quarterlyData[year] === 'object') {
                        Object.keys(quarterlyData[year]).forEach(quarter => {
                            const planAmount = parseFloat(quarterlyData[year][quarter].plan_amount) || 0;
                            if (planAmount > 0) {
                                availableQuarters.push({
                                    year: parseInt(year),
                                    quarter: parseInt(quarter),
                                    planAmount: planAmount
                                });
                            }
                        });
                    }
                });
            }

            console.log('Available quarters for fallback:', availableQuarters);

            if (availableQuarters.length > 0) {
                let closest = availableQuarters[0];
                let minDiff = Math.abs(getQuarterMiddleDate(closest.year, closest.quarter) - paymentDateObj);

                availableQuarters.forEach(quarter => {
                    const quarterMiddle = getQuarterMiddleDate(quarter.year, quarter.quarter);
                    const diff = Math.abs(quarterMiddle - paymentDateObj);

                    if (diff < minDiff) {
                        minDiff = diff;
                        closest = quarter;
                    }
                });

                targetQuarter = {
                    year: closest.year,
                    quarter: closest.quarter,
                    date: paymentDateObj,
                    isValid: true,
                    source: 'closest_available',
                    originalCalculation: {
                        year: paymentYear,
                        quarter: calculatedQuarter
                    }
                };

                console.log('✅ Closest quarter found:', targetQuarter);
                showNotification(`To'lov ${calculatedQuarter}-chorak ${paymentYear} o'rniga ${closest.quarter}-chorak ${closest.year} ga tayinlanadi`, 'warning');
            } else {
                throw new Error('Hech qanday to\'lov jadvali mavjud emas');
            }
        }

        console.log('=== FINAL QUARTER ASSIGNMENT ===');
        console.log('Target Quarter:', targetQuarter);

        formData.append('target_year', targetQuarter.year);
        formData.append('target_quarter', targetQuarter.quarter);
        formData.append('quarter_validation', 'true');
        formData.append('calculation_debug', JSON.stringify({
            paymentDate: paymentDate,
            calculatedYear: paymentYear,
            calculatedQuarter: calculatedQuarter,
            assignedYear: targetQuarter.year,
            assignedQuarter: targetQuarter.quarter,
            source: targetQuarter.source
        }));

        console.log('Form data being sent:');
        console.log('- target_year:', targetQuarter.year);
        console.log('- target_quarter:', targetQuarter.quarter);

        const response = await fetch(`/contracts/${contractData.id}/store-fact-payment`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        console.log('Backend response status:', response.status);

        if (!response.ok) {
            const errorData = await response.json();
            console.log('Backend error response:', errorData);
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }

        const result = await response.json();
        console.log('Backend success response:', result);

        if (result.success) {
            closePaymentModal();
            showNotification(
                `To'lov muvaffaqiyatli qo'shildi: ${targetQuarter.quarter}-chorak ${targetQuarter.year}`,
                'success'
            );
            loadQuarterlyData();

            if (typeof loadPaymentHistory === 'function') {
                loadPaymentHistory();
            }
        } else {
            throw new Error(result.message || 'To\'lov qo\'shishda xatolik');
        }
    } catch (error) {
        console.error('Payment submission error:', error);
        showNotification(error.message, 'error');
    } finally {
        isSubmittingPayment = false;
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        safeFeatherReplace();
    }
}

// Helper function for quarter middle date calculation
function getQuarterMiddleDate(year, quarter) {
    const quarterStartMonth = (quarter - 1) * 3 + 1;
    const quarterMiddleMonth = quarterStartMonth + 1;
    return new Date(year, quarterMiddleMonth - 1, 15);
}

// FIXED: Add payment for specific quarter with proper date suggestion
function addQuarterPayment(year, quarter) {
    console.log('=== ADD QUARTER PAYMENT DEBUG ===');
    console.log('Requested year:', year);
    console.log('Requested quarter:', quarter);

    if (!contractData) {
        showNotification('Shartnoma ma\'lumotlari topilmadi', 'error');
        return;
    }

    if (!quarterlyData || !quarterlyData[year] || !quarterlyData[year][quarter]) {
        showNotification(`${quarter}-chorak ${year} yil uchun jadval mavjud emas`, 'error');
        return;
    }

    openPaymentModal();

    // FIXED: Calculate proper date for the requested quarter
    const quarterStartMonth = (quarter - 1) * 3 + 1; // 1, 4, 7, 10
    const quarterMiddleMonth = quarterStartMonth + 1; // 2, 5, 8, 11
    const quarterEndMonth = quarter * 3; // 3, 6, 9, 12

    console.log('Quarter month calculation:', {
        quarter: quarter,
        startMonth: quarterStartMonth,
        middleMonth: quarterMiddleMonth,
        endMonth: quarterEndMonth
    });

    // FIXED: Use proper date for the quarter without restrictive validations
    let suggestedDate = new Date(year, quarterMiddleMonth - 1, 15); // Middle of quarter

    // ONLY check contract date constraint (not future date constraint)
    const contractStartDate = new Date(contractData.contract_date);
    if (suggestedDate < contractStartDate) {
        const quarterStartDate = new Date(year, quarterStartMonth - 1, 1);
        if (contractStartDate <= new Date(year, quarterEndMonth, 0)) {
            suggestedDate = contractStartDate;
        } else {
            suggestedDate = quarterStartDate;
        }
    }

    console.log('Final suggested date:', suggestedDate.toISOString().split('T')[0]);
    console.log('Date verification:', {
        suggestedDate: suggestedDate.toISOString().split('T')[0],
        suggestedMonth: suggestedDate.getMonth() + 1,
        calculatedQuarter: Math.ceil((suggestedDate.getMonth() + 1) / 3),
        requestedQuarter: quarter,
        matches: Math.ceil((suggestedDate.getMonth() + 1) / 3) === quarter
    });

    const dateInput = document.querySelector('input[name="payment_date"]');
    if (dateInput) {
        dateInput.value = suggestedDate.toISOString().split('T')[0];
        dateInput.removeAttribute('max');

        const existingNote = document.querySelector('#quarterPaymentNote');
        if (existingNote) {
            existingNote.remove();
        }

        const noteDiv = document.createElement('div');
        noteDiv.id = 'quarterPaymentNote';
        noteDiv.className = 'mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg';

        const verificationQuarter = Math.ceil((suggestedDate.getMonth() + 1) / 3);
        const isCorrectQuarter = verificationQuarter === quarter;

        noteDiv.innerHTML = `
            <div class="flex items-center">
                <i data-feather="info" class="w-4 h-4 mr-2 text-blue-600"></i>
                <span class="text-sm font-medium text-blue-800">
                    Bu to'lov ${quarter}-chorak ${year} yil uchun qo'shiladi
                    ${!isCorrectQuarter ? `(Diqqat: Tanlangan sana ${verificationQuarter}-chorakka mos keladi)` : ''}
                </span>
            </div>
        `;

        dateInput.parentNode.appendChild(noteDiv);
        safeFeatherReplace();
    }

    showNotification(`${quarter}-chorak ${year} yil uchun to'lov qo'shish`, 'info');
}

function toggleSubmitState(button, textElement, loaderElement, isLoading) {
    if (isLoading) {
        button.disabled = true;
        if (textElement) textElement.classList.add('hidden');
        if (loaderElement) loaderElement.classList.remove('hidden');
    } else {
        button.disabled = false;
        if (textElement) textElement.classList.remove('hidden');
        if (loaderElement) loaderElement.classList.add('hidden');
    }
}

function formatFullCurrency(amount) {
    if (isNaN(amount) || amount === null || amount === undefined) {
        amount = 0;
    }

    if (typeof amount === 'string') {
        amount = parseFloat(amount) || 0;
    }

    return new Intl.NumberFormat('uz-UZ', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' so\'m';
}

function showNotification(message, type) {
    const bgColor = type === 'success' ? 'bg-green-500' :
                   type === 'warning' ? 'bg-yellow-500' :
                   type === 'info' ? 'bg-blue-500' :
                   'bg-red-500';
    const icon = type === 'success' ? 'check-circle' :
                type === 'warning' ? 'alert-triangle' :
                type === 'info' ? 'info' :
                'alert-triangle';

    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i data-feather="${icon}" class="w-5 h-5 mr-2"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);
    safeFeatherReplace();

    setTimeout(() => notification.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, type === 'success' ? 3000 : 5000);
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

function resetForm() {
    if (confirm('Barcha ma\'lumotlarni tozalashni xohlaysizmi? Bu amal bekor qilinmaydi.')) {
        const contractForm = document.getElementById('contractForm');
        if (contractForm) {
            contractForm.reset();
        }

        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.setCustomValidity('');
        });

        calculatePaymentBreakdown();
        showNotification('Forma tozalandi', 'info');
    }
}

function exportReport() {
    if (!contractData) {
        showNotification('Avval shartnomani saqlang', 'error');
        return;
    }

    showNotification('Hisobot tayyorlanmoqda...', 'info');

    setTimeout(() => {
        const reportData = generateReportData();
        downloadReport(reportData, `shartnoma_${contractData.contract_number}_hisobot.json`);
        showNotification('Hisobot muvaffaqiyatli yuklab olindi', 'success');
    }, 2000);
}

function generateReportData() {
    return {
        contract_info: {
            contract_number: contractData.contract_number,
            contract_date: contractData.contract_date,
            total_amount: contractData.total_amount
        },
        payment_summary: quarterlyData,
        generated_at: new Date().toISOString(),
        report_type: 'payment_breakdown'
    };
}

function downloadReport(data, filename) {
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function renderPaymentsList(payments) {
    if (!payments || payments.length === 0) {
        return '<p class="text-gray-500 text-center py-8">Hali to\'lovlar mavjud emas</p>';
    }

    return payments.map(payment => `
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-medium">${payment.payment_number || 'To\'lov #' + payment.id}</div>
                    <div class="text-sm text-gray-600">${new Date(payment.payment_date).toLocaleDateString('uz-UZ')}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-green-600">${formatFullCurrency(payment.amount)}</div>
                    <div class="text-sm text-gray-500">${payment.notes || ''}</div>
                </div>
            </div>
        </div>
    `).join('');
}

function editQuarterPlan(year, quarter) {
    showNotification(`${quarter}-chorak ${year} yil planini tahrirlash funksiyasi ishlab chiqilmoqda`, 'info');
}

// Close modals on background click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
        const modals = ['paymentScheduleModal', 'paymentModal', 'quarterDetailsModal', 'historyModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        });
    }
});

// Optional loadPaymentHistory function
function loadPaymentHistory() {
    const historyContainer = document.getElementById('paymentHistoryContainer');
    if (!historyContainer || !contractData) return;

    // Show loading state
    historyContainer.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-3"></div>
            <p class="text-gray-500">Tarix yuklanmoqda...</p>
        </div>
    `;

    fetch(`/contracts/${contractData.id}/payment-history`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.history && data.history.length > 0) {
                historyContainer.innerHTML = renderPaymentHistoryDetail(data.history, data.contract_info);
            } else {
                historyContainer.innerHTML = renderEmptyHistory();
            }
        })
        .catch(error => {
            console.error('Error loading payment history:', error);
            historyContainer.innerHTML = renderHistoryError();
        });
}

function renderPaymentHistoryDetail(history, contractInfo) {
    let html = `
        <div class="space-y-4">
            <!-- History Header -->
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i data-feather="clock" class="w-5 h-5 text-purple-600 mr-2"></i>
                        <h4 class="font-bold text-purple-900">To'lovlar tarixi</h4>
                    </div>
                    <div class="text-sm text-purple-700">
                        Jami: ${history.length} ta faoliyat
                    </div>
                </div>
                <div class="text-xs text-purple-600 mt-1">
                    Shartnoma: ${contractInfo.contract_number} (${contractInfo.contract_date})
                </div>
            </div>

            <!-- History Timeline -->
            <div class="relative">
                <!-- Timeline line -->
                <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                <div class="space-y-4">
    `;

    history.forEach((item, index) => {
        const isLast = index === history.length - 1;
        html += renderHistoryItem(item, isLast);
    });

    html += `
                </div>
            </div>
        </div>
    `;

    safeFeatherReplace();
    return html;
}

function renderHistoryItem(item, isLast) {
    const colorClasses = {
        green: 'bg-green-100 text-green-800 border-green-200',
        blue: 'bg-blue-100 text-blue-800 border-blue-200',
        red: 'bg-red-100 text-red-800 border-red-200',
        gray: 'bg-gray-100 text-gray-800 border-gray-200'
    };

    const iconColors = {
        green: 'text-green-600 bg-green-100',
        blue: 'text-blue-600 bg-blue-100',
        red: 'text-red-600 bg-red-100',
        gray: 'text-gray-600 bg-gray-100'
    };

    const colorClass = colorClasses[item.color] || colorClasses.gray;
    const iconColor = iconColors[item.color] || iconColors.gray;

    return `
        <div class="relative flex items-start space-x-4">
            <!-- Timeline dot -->
            <div class="flex-shrink-0 w-12 h-12 ${iconColor} rounded-full flex items-center justify-center z-10 border-2 border-white shadow-sm">
                <i data-feather="${item.icon}" class="w-5 h-5"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0 pb-4">
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full ${colorClass}">
                                ${item.action_text}
                            </span>
                            <span class="text-sm font-medium text-gray-900">
                                ${item.table_text}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">
                            ${item.created_at_human}
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="text-sm text-gray-700 mb-2">
                        ${item.description || item.formatted_description}
                    </div>

                    <!-- Changes details -->
                    ${item.changes && item.changes.length > 0 ? renderChangesDetail(item.changes) : ''}

                    <!-- Footer -->
                    <div class="flex items-center justify-between text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100">
                        <div class="flex items-center">
                            <i data-feather="user" class="w-3 h-3 mr-1"></i>
                            ${item.user ? item.user.name : 'Tizim'}
                        </div>
                        <div class="flex items-center">
                            <i data-feather="calendar" class="w-3 h-3 mr-1"></i>
                            ${item.created_at_formatted}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderChangesDetail(changes) {
    if (!changes || changes.length === 0) return '';

    let html = `
        <div class="bg-gray-50 rounded-md p-3 mt-2">
            <div class="text-xs font-medium text-gray-700 mb-2">O'zgarishlar:</div>
            <div class="space-y-1">
    `;

    changes.forEach(change => {
        html += `
            <div class="flex items-center justify-between text-xs">
                <span class="font-medium text-gray-600">${change.field}:</span>
                <div class="flex items-center space-x-2">
                    <span class="text-red-600 line-through">${change.old}</span>
                    <span class="text-gray-400">→</span>
                    <span class="text-green-600 font-medium">${change.new}</span>
                </div>
            </div>
        `;
    });

    html += `
            </div>
        </div>
    `;

    return html;
}

// Render empty history state
function renderEmptyHistory() {
    return `
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-feather="clock" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h4 class="text-lg font-medium text-gray-900 mb-2">Tarix mavjud emas</h4>
            <p class="text-gray-500 max-w-sm mx-auto">
                Hali hech qanday to'lov yoki o'zgarish tarixi mavjud emas.
                To'lov qo'shganingizdan so'ng bu yerda ko'rinadi.
            </p>
        </div>
    `;
}

// Render history error state
function renderHistoryError() {
    return `
        <div class="text-center py-8">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-feather="alert-triangle" class="w-6 h-6 text-red-500"></i>
            </div>
            <h4 class="text-lg font-medium text-gray-900 mb-2">Tarixni yuklashda xatolik</h4>
            <p class="text-gray-500 mb-4">Ma'lumotlarni yuklashda muammo yuz berdi</p>
            <button onclick="loadPaymentHistory()"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i data-feather="refresh-cw" class="w-4 h-4 mr-2"></i>
                Qaytadan urinish
            </button>
        </div>
    `;
}

// Load payment history stats (optional)
function loadPaymentHistoryStats() {
    if (!contractData) return;

    fetch(`/contracts/${contractData.id}/payment-history-stats`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateHistoryStats(data.stats);
            }
        })
        .catch(error => {
            console.error('Error loading history stats:', error);
        });
}

// Update history statistics display
function updateHistoryStats(stats) {
    const statsContainer = document.getElementById('historyStatsContainer');
    if (!statsContainer) return;

    statsContainer.innerHTML = `
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 border border-gray-200 text-center">
                <div class="text-2xl font-bold text-purple-600">${stats.total_actions || 0}</div>
                <div class="text-xs text-gray-500">Jami faoliyat</div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200 text-center">
                <div class="text-2xl font-bold text-green-600">${stats.payment_actions || 0}</div>
                <div class="text-xs text-gray-500">To'lov amallar</div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200 text-center">
                <div class="text-2xl font-bold text-blue-600">${stats.schedule_actions || 0}</div>
                <div class="text-xs text-gray-500">Jadval amallar</div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200 text-center">
                <div class="text-2xl font-bold text-orange-600">${stats.recent_activity || 0}</div>
                <div class="text-xs text-gray-500">Haftalik faoliyat</div>
            </div>
        </div>
    `;
}

// Export payment history
function exportPaymentHistory() {
    if (!contractData) {
        showNotification('Shartnoma ma\'lumotlari topilmadi', 'error');
        return;
    }

    showNotification('Tarix eksport qilinmoqda...', 'info');

    fetch(`/contracts/${contractData.id}/export-payment-history`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        }
        throw new Error('Export failed');
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `shartnoma_${contractData.contract_number}_tarix.xlsx`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        showNotification('Tarix muvaffaqiyatli yuklab olindi', 'success');
    })
    .catch(error => {
        console.error('Export error:', error);
        showNotification('Eksportda xatolik yuz berdi', 'error');
    });
}

// Filter payment history
function filterPaymentHistory(filterType) {
    const historyContainer = document.getElementById('paymentHistoryContainer');
    if (!historyContainer || !contractData) return;

    let url = `/contracts/${contractData.id}/payment-history`;
    if (filterType && filterType !== 'all') {
        url += `?filter=${filterType}`;
    }

    historyContainer.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-3"></div>
            <p class="text-gray-500">Filterlash...</p>
        </div>
    `;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.history && data.history.length > 0) {
                historyContainer.innerHTML = renderPaymentHistoryDetail(data.history, data.contract_info);
            } else {
                historyContainer.innerHTML = renderEmptyHistory();
            }
        })
        .catch(error => {
            console.error('Error filtering history:', error);
            historyContainer.innerHTML = renderHistoryError();
        });
}

// Initialize payment history when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Auto-load payment history if container exists
    setTimeout(() => {
        if (document.getElementById('paymentHistoryContainer') && contractData) {
            loadPaymentHistory();
            loadPaymentHistoryStats();
        }
    }, 1000); // Load after other components
});
</script>
@endpush
