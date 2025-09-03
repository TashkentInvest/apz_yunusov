{{-- Shartnoma to'lov boshqaruvi - Production Ready --}}
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

    <button onclick="exportReport()"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="download" class="w-4 h-4 mr-2"></i>
        Hisobot yuklab olish
    </button>
</div>
@endsection

@push('styles')
<style>
/* Custom styles for professional government appearance */
.govt-header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); }
.govt-card { border-left: 5px solid #1e40af; }
.success-gradient { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
.warning-gradient { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
.danger-gradient { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); }
.info-gradient { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
.primary-gradient { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); }

.quarter-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
}
.quarter-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.15);
    border-color: #3b82f6;
}

.debt-overdue { border-color: #dc2626; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); }
.debt-current { border-color: #f59e0b; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
.paid-complete { border-color: #16a34a; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
.paid-partial { border-color: #2563eb; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }

.progress-ring { width: 60px; height: 60px; }
.progress-ring circle { transition: stroke-dasharray 0.5s ease; }

.animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Government Header -->
    <div class="govt-header rounded-2xl shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">O'ZBEKISTON RESPUBLIKASI</h1>
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
                           value="{{ $contract->contract_number ?? '' }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-medium">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Shartnoma sanasi *</label>
                    <input type="date" name="contract_date" required
                           value="{{ isset($contract) ? $contract->contract_date->format('Y-m-d') : date('Y-m-d') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Yakunlash sanasi</label>
                    <input type="date" name="completion_date"
                           value="{{ isset($contract) && $contract->completion_date ? $contract->completion_date->format('Y-m-d') : '' }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg">
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
                <h3 class="text-xl font-bold text-blue-900 mb-6">Moliyaviy ma'lumotlar</h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Jami shartnoma summasi (so'm) *</label>
                        <input type="number" name="total_amount" required step="0.01" min="0"
                               value="{{ $contract->total_amount ?? '' }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-bold"
                               onchange="calculatePaymentBreakdown()">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">To'lov turi *</label>
                        <select name="payment_type" required onchange="togglePaymentSettings()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg">
                            <option value="installment" {{ (isset($contract) && $contract->payment_type === 'installment') ? 'selected' : 'selected' }}>Bo'lib to'lash</option>
                            <option value="full" {{ (isset($contract) && $contract->payment_type === 'full') ? 'selected' : '' }}>To'liq to'lash</option>
                        </select>
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
                            <input type="number" name="quarters_count" min="1" max="20" step="1" readonly
                                   value="{{ $contract->quarters_count ?? 8 }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-lg font-semibold">
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
                <button type="submit"
                        class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
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
                    <p class="text-2xl font-bold text-blue-900" id="totalPlan">{{ number_format($contract->paymentSummary['plan_total'] ?? 0, 0, '.', ' ') }}</p>
                </div>

                <div class="success-gradient rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <p class="text-sm font-medium text-green-800">TO'LANGAN</p>
                    <p class="text-2xl font-bold text-green-900" id="totalPaid">{{ number_format($contract->paymentSummary['fact_total'] ?? 0, 0, '.', ' ') }}</p>
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

            <!-- Quarterly Breakdown -->
            <div id="quarterlyBreakdown" class="space-y-6">
                <!-- Dynamic content will be inserted here -->
            </div>
        </div>
    </div>
    @endif
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
                                @for($y = date('Y'); $y <= date('Y') + 5; $y++)
                                    <option value="{{ $y }}">{{ $y }} yil</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Choraklar soni</label>
                            <select name="quarters_count" onchange="updateSchedulePreview()" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="1">1 chorak</option>
                                <option value="2">2 chorak</option>
                                <option value="3">3 chorak</option>
                                <option value="4" selected>4 chorak</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jami summa</label>
                            <input type="number" name="total_schedule_amount" step="0.01"
                                   value="{{ isset($contract) ? $contract->remaining_amount : 0 }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Custom Schedule Grid -->
                    <div id="customScheduleGrid" class="hidden">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Choraklar bo'yicha taqsimlash</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="quarterInputs">
                            <!-- Dynamic quarter inputs will be added here -->
                        </div>
                    </div>

                    <!-- Schedule Preview -->
                    <div id="schedulePreview" class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Jadval ko'rinishi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3" id="previewGrid">
                            <!-- Preview cards will be generated here -->
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closePaymentScheduleModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Bekor qilish
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Jadvalni saqlash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                        <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To'lov summasi (so'm) *</label>
                        <input type="number" name="payment_amount" step="0.01" min="0" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-lg font-medium"
                               placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hujjat raqami</label>
                        <input type="text" name="payment_number" maxlength="50"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                               placeholder="Chek, spravka raqami">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                        <textarea name="payment_notes" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                  placeholder="Qo'shimcha ma'lumot"></textarea>
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closePaymentModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Bekor qilish
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
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
@endsection

@push('scripts')
<script src="https://unpkg.com/feather-icons"></script>
<script>
// Global variables
const contractData = @json($contract ?? null);
let quarterlyData = {};
let currentQuarterData = null;

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();

    // Initialize payment settings based on contract data
    if (contractData) {
        togglePaymentSettings();
        calculatePaymentBreakdown();
        loadQuarterlyData();
    } else {
        calculatePaymentBreakdown();
    }

    setupEventListeners();
});

function setupEventListeners() {
    // Form change listeners
    document.querySelector('select[name="payment_type"]').addEventListener('change', togglePaymentSettings);
    document.querySelector('input[name="total_amount"]').addEventListener('input', debounce(calculatePaymentBreakdown, 500));
    document.querySelector('input[name="initial_payment_percent"]').addEventListener('input', debounce(calculatePaymentBreakdown, 500));
    document.querySelector('input[name="construction_period_years"]').addEventListener('input', debounce(calculatePaymentBreakdown, 500));

    // Schedule form listeners
    document.querySelectorAll('input[name="schedule_type"]').forEach(radio => {
        radio.addEventListener('change', toggleCustomScheduleGrid);
    });

    // Form submissions
    document.getElementById('contractForm').addEventListener('submit', handleContractSubmit);
    document.getElementById('paymentScheduleForm').addEventListener('submit', handleScheduleSubmit);
    document.getElementById('paymentForm').addEventListener('submit', handlePaymentSubmit);
}

// Toggle payment settings based on payment type
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

// Calculate and display payment breakdown
function calculatePaymentBreakdown() {
    const totalAmount = parseFloat(document.querySelector('input[name="total_amount"]').value) || 0;
    const initialPercent = parseFloat(document.querySelector('input[name="initial_payment_percent"]').value) || 0;
    const constructionYears = parseInt(document.querySelector('input[name="construction_period_years"]').value) || 2;
    const paymentType = document.querySelector('select[name="payment_type"]').value;

    const initialAmount = totalAmount * (initialPercent / 100);
    const remainingAmount = totalAmount - initialAmount;
    const quartersCount = paymentType === 'full' ? 0 : constructionYears * 4;
    const quarterlyAmount = quartersCount > 0 ? remainingAmount / quartersCount : 0;

    // Update quarters count
    document.querySelector('input[name="quarters_count"]').value = quartersCount;

    // Update display
    document.getElementById('initialAmount').textContent = formatCurrency(initialAmount);
    document.getElementById('remainingAmount').textContent = formatCurrency(remainingAmount);
    document.getElementById('quarterlyAmount').textContent = formatCurrency(quarterlyAmount);

    // Update preview visibility
    const previewDiv = document.getElementById('paymentPreview');
    if (paymentType === 'full') {
        previewDiv.style.display = 'none';
    } else {
        previewDiv.style.display = 'block';
    }
}

// Load quarterly data for existing contract
function loadQuarterlyData() {
    if (!contractData) return;

    // Simulate AJAX call to load quarterly data
    fetch(`/contracts/${contractData.id}/quarterly-breakdown`)
        .then(response => response.json())
        .then(data => {
            quarterlyData = data;
            renderQuarterlyBreakdown();
            updateSummaryCards();
        })
        .catch(error => {
            console.error('Error loading quarterly data:', error);
            renderEmptyQuarterlyBreakdown();
        });
}

// Render quarterly breakdown
function renderQuarterlyBreakdown() {
    const container = document.getElementById('quarterlyBreakdown');

    if (Object.keys(quarterlyData).length === 0) {
        renderEmptyQuarterlyBreakdown();
        return;
    }

    let html = '';

    Object.entries(quarterlyData).forEach(([year, quarters]) => {
        html += `
        <div class="bg-gray-50 rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">${year} yil</h3>
                <button onclick="editYearSchedule(${year})"
                        class="px-4 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors">
                    <i data-feather="edit" class="w-4 h-4 mr-1"></i>
                    Tahrirlash
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        `;

        for (let quarter = 1; quarter <= 4; quarter++) {
            const quarterData = quarters[quarter] || {
                plan_amount: 0,
                fact_total: 0,
                debt: 0,
                payment_percent: 0,
                is_overdue: false
            };

            const cardClass = getQuarterCardClass(quarterData);
            const progressColor = getProgressColor(quarterData.payment_percent);

            html += `
            <div class="quarter-card ${cardClass} rounded-xl p-5" onclick="openQuarterDetails(${year}, ${quarter})">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-bold text-gray-800">${quarter}-chorak</h4>
                    <div class="relative">
                        <svg class="progress-ring transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-gray-300" stroke="currentColor" stroke-width="3" fill="none"
                                  d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="${progressColor}" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"
                                  stroke-dasharray="${quarterData.payment_percent}, 100"
                                  d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold">${Math.round(quarterData.payment_percent)}%</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Plan:</span>
                        <span class="font-bold text-blue-600">${formatCurrencyShort(quarterData.plan_amount)}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Fakt:</span>
                        <span class="font-bold text-green-600">${formatCurrencyShort(quarterData.fact_total)}</span>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-sm font-medium ${quarterData.debt > 0 ? 'text-red-600' : 'text-green-600'}">
                            ${quarterData.debt > 0 ? 'Qarz:' : 'Ortiqcha:'}
                        </span>
                        <span class="font-bold ${quarterData.debt > 0 ? 'text-red-600' : 'text-green-600'}">
                            ${formatCurrencyShort(Math.abs(quarterData.debt))}
                        </span>
                    </div>

                    ${quarterData.is_overdue ? `
                    <div class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-medium text-center animate-pulse-slow">
                        MUDDATI O'TGAN
                    </div>
                    ` : ''}
                </div>
            </div>
            `;
        }

        html += `
            </div>
        </div>
        `;
    });

    container.innerHTML = html;
    feather.replace();
}

// Render empty quarterly breakdown
function renderEmptyQuarterlyBreakdown() {
    const container = document.getElementById('quarterlyBreakdown');
    container.innerHTML = `
    <div class="text-center py-16">
        <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-feather="calendar-plus" class="w-12 h-12 text-blue-600"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-3">To'lov jadvali mavjud emas</h3>
        <p class="text-gray-600 mb-6">Choraklar bo'yicha to'lov jadvalini tuzish uchun "Jadval tuzish" tugmasini bosing</p>
        <button onclick="openPaymentScheduleModal()"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i data-feather="calendar-plus" class="w-5 h-5 mr-2"></i>
            Jadval tuzish
        </button>
    </div>
    `;
    feather.replace();
}

// Get quarter card CSS class based on status
function getQuarterCardClass(quarterData) {
    if (quarterData.is_overdue && quarterData.debt > 0) return 'debt-overdue';
    if (quarterData.debt > 0) return 'debt-current';
    if (quarterData.payment_percent >= 100) return 'paid-complete';
    if (quarterData.payment_percent > 0) return 'paid-partial';
    return 'bg-gray-50 border-gray-200';
}

// Get progress color based on completion percentage
function getProgressColor(percent) {
    if (percent >= 100) return 'text-green-500';
    if (percent >= 50) return 'text-yellow-500';
    if (percent > 0) return 'text-blue-500';
    return 'text-gray-300';
}

// Update summary cards
function updateSummaryCards() {
    let totalPlan = 0, totalPaid = 0, currentDebt = 0, overdueDebt = 0;

    Object.values(quarterlyData).forEach(quarters => {
        Object.values(quarters).forEach(quarter => {
            totalPlan += quarter.plan_amount || 0;
            totalPaid += quarter.fact_total || 0;

            if (quarter.debt > 0) {
                if (quarter.is_overdue) {
                    overdueDebt += quarter.debt;
                } else {
                    currentDebt += quarter.debt;
                }
            }
        });
    });

    document.getElementById('totalPlan').textContent = formatCurrency(totalPlan);
    document.getElementById('totalPaid').textContent = formatCurrency(totalPaid);
    document.getElementById('currentDebt').textContent = formatCurrency(currentDebt);
    document.getElementById('overdueDebt').textContent = formatCurrency(overdueDebt);
}

// Modal management functions
function openPaymentScheduleModal() {
    document.getElementById('paymentScheduleModal').classList.remove('hidden');
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
                <p class="text-2xl font-bold text-blue-900">${formatCurrency(quarterData.plan_amount)}</p>
            </div>
            <div class="success-gradient rounded-lg p-4 text-center">
                <p class="text-sm font-medium text-green-800">TO'LANGAN</p>
                <p class="text-2xl font-bold text-green-900">${formatCurrency(quarterData.fact_total)}</p>
            </div>
            <div class="${quarterData.debt > 0 ? 'danger-gradient' : 'success-gradient'} rounded-lg p-4 text-center">
                <p class="text-sm font-medium ${quarterData.debt > 0 ? 'text-red-800' : 'text-green-800'}">${quarterData.debt > 0 ? 'QARZ' : 'ORTIQCHA'}</p>
                <p class="text-2xl font-bold ${quarterData.debt > 0 ? 'text-red-900' : 'text-green-900'}">${formatCurrency(Math.abs(quarterData.debt))}</p>
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

        <!-- Payment History -->
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
    feather.replace();
}

function closeQuarterDetailsModal() {
    document.getElementById('quarterDetailsModal').classList.add('hidden');
    currentQuarterData = null;
}

// Toggle custom schedule grid
function toggleCustomScheduleGrid() {
    const scheduleType = document.querySelector('input[name="schedule_type"]:checked').value;
    const customGrid = document.getElementById('customScheduleGrid');

    if (scheduleType === 'custom') {
        customGrid.classList.remove('hidden');
        generateQuarterInputs();
    } else {
        customGrid.classList.add('hidden');
    }

    updateSchedulePreview();
}

// Generate quarter inputs based on selected count
function generateQuarterInputs() {
    const quartersCount = parseInt(document.querySelector('select[name="quarters_count"]').value);
    const container = document.getElementById('quarterInputs');

    let html = '';
    for (let i = 1; i <= quartersCount; i++) {
        html += `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">${i}-chorak (%)</label>
            <input type="number" name="quarter_${i}_percent" min="0" max="100" step="0.1" value="${100/quartersCount}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                   onchange="updateSchedulePreview()">
        </div>
        `;
    }

    container.innerHTML = html;
}

// Update schedule preview
function updateSchedulePreview() {
    const quartersCount = parseInt(document.querySelector('select[name="quarters_count"]').value);
    const totalAmount = parseFloat(document.querySelector('input[name="total_schedule_amount"]').value) || 0;
    const scheduleType = document.querySelector('input[name="schedule_type"]:checked').value;
    const previewGrid = document.getElementById('previewGrid');

    let html = '';
    let totalPercent = 0;

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
        <div class="bg-white border-2 border-blue-200 rounded-lg p-4 text-center">
            <div class="text-sm font-medium text-blue-600 mb-2">${i}-chorak</div>
            <div class="text-lg font-bold text-blue-900">${formatCurrencyShort(amount)}</div>
            <div class="text-xs text-gray-500">${percent.toFixed(1)}%</div>
        </div>
        `;
    }

    // Add total validation indicator
    if (scheduleType === 'custom') {
        const isValidTotal = Math.abs(totalPercent - 100) < 0.1;
        html += `
        <div class="col-span-full mt-4 p-4 rounded-lg ${isValidTotal ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
            <div class="text-center">
                <div class="font-bold">Jami: ${totalPercent.toFixed(1)}%</div>
                <div class="text-sm">${isValidTotal ? 'To\'g\'ri' : '100% bo\'lishi kerak'}</div>
            </div>
        </div>
        `;
    }

    previewGrid.innerHTML = html;
}

// Form submission handlers
async function handleContractSubmit(e) {
    e.preventDefault();

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    toggleSubmitState(submitBtn, submitText, submitLoader, true);

    try {
        const formData = new FormData(e.target);
        const url = contractData ? `/contracts/${contractData.id}` : '/contracts';
        const method = contractData ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-HTTP-Method-Override': method
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');
            if (!contractData) {
                setTimeout(() => {
                    window.location.href = `/contracts/${result.contract.id}/payment-update`;
                }, 1500);
            }
        } else {
            throw new Error(result.message || 'Xatolik yuz berdi');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    } finally {
        toggleSubmitState(submitBtn, submitText, submitLoader, false);
    }
}

async function handleScheduleSubmit(e) {
    e.preventDefault();

    if (!contractData) {
        showNotification('Avval shartnomani saqlang', 'error');
        return;
    }

    const formData = new FormData(e.target);
    const scheduleType = formData.get('schedule_type');
    const quartersCount = parseInt(formData.get('quarters_count'));

    // Validate custom percentages
    if (scheduleType === 'custom') {
        let totalPercent = 0;
        for (let i = 1; i <= quartersCount; i++) {
            totalPercent += parseFloat(formData.get(`quarter_${i}_percent`) || 0);
        }

        if (Math.abs(totalPercent - 100) > 0.1) {
            showNotification('Foizlar yig\'indisi 100% bo\'lishi kerak', 'error');
            return;
        }
    }

    try {
        const response = await fetch(`/contracts/${contractData.id}/create-quarterly-schedule`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closePaymentScheduleModal();
            showNotification(result.message, 'success');
            loadQuarterlyData(); // Reload data
        } else {
            throw new Error(result.message || 'Jadval yaratishda xatolik');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

async function handlePaymentSubmit(e) {
    e.preventDefault();

    if (!contractData) {
        showNotification('Avval shartnomani saqlang', 'error');
        return;
    }

    const formData = new FormData(e.target);

    try {
        const response = await fetch(`/contracts/${contractData.id}/store-fact-payment`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closePaymentModal();
            showNotification(result.message, 'success');
            loadQuarterlyData(); // Reload data
        } else {
            throw new Error(result.message || 'To\'lov qo\'shishda xatolik');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Utility functions
function toggleSubmitState(button, textElement, loaderElement, isLoading) {
    if (isLoading) {
        button.disabled = true;
        textElement.classList.add('hidden');
        loaderElement.classList.remove('hidden');
    } else {
        button.disabled = false;
        textElement.classList.remove('hidden');
        loaderElement.classList.add('hidden');
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('uz-UZ', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' so\'m';
}

function formatCurrencyShort(amount) {
    if (amount >= 1000000000) {
        return (amount / 1000000000).toFixed(1) + 'Mlrd';
    } else if (amount >= 1000000) {
        return (amount / 1000000).toFixed(1) + 'Mln';
    } else if (amount >= 1000) {
        return (amount / 1000).toFixed(1) + 'K';
    }
    return Math.round(amount).toLocaleString();
}

function showNotification(message, type) {
    const bgColor = type === 'success' ? 'bg-green-500' :
                   type === 'warning' ? 'bg-yellow-500' :
                   'bg-red-500';
    const icon = type === 'success' ? 'check-circle' :
                type === 'warning' ? 'alert-triangle' :
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
    if (confirm('Barcha ma\'lumotlarni tozalashni xohlaysizmi?')) {
        document.getElementById('contractForm').reset();
        calculatePaymentBreakdown();
    }
}

function exportReport() {
    if (!contractData) {
        showNotification('Avval shartnomani saqlang', 'error');
        return;
    }

    // Mock export for demonstration
    showNotification('Hisobot tayyorlanmoqda...', 'info');

    // Generate and download mock report
    setTimeout(() => {
        const reportData = generateReportData();
        downloadReport(reportData, `shartnoma_${contractData.contract_number}_hisobot.json`);
        showNotification('Hisobot muvaffaqiyatli yuklab olindi', 'success');
    }, 2000);

    /* // Actual export when backend is ready
    window.open(`/contracts/${contractData.id}/payment-report`, '_blank');
    */
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
                    <div class="font-bold text-green-600">${formatCurrency(payment.amount)}</div>
                    <div class="text-sm text-gray-500">${payment.notes || ''}</div>
                </div>
            </div>
        </div>
    `).join('');
}

// Additional helper functions for specific actions
function editYearSchedule(year) {
    openPaymentScheduleModal();
    document.querySelector('select[name="schedule_year"]').value = year;
}

function editQuarterPlan(year, quarter) {
    // Implementation for editing specific quarter plan
    showNotification(`${quarter}-chorak ${year} yil planini tahrirlash`, 'info');
}

function addQuarterPayment(year, quarter) {
    openPaymentModal();
    // Set payment date to middle of the quarter
    const month = (quarter - 1) * 3 + 2; // Middle month of quarter
    const date = new Date(year, month - 1, 15);
    document.querySelector('input[name="payment_date"]').value = date.toISOString().split('T')[0];
}

// Close modals on background click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
        const modals = ['paymentScheduleModal', 'paymentModal', 'quarterDetailsModal'];
        modals.forEach(modalId => {
            if (e.target.closest(`#${modalId}`)) {
                document.getElementById(modalId).classList.add('hidden');
            }
        });
    }
});
</script>
@endpush
