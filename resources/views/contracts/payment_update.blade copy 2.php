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
/* Custom styles */
.govt-header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); }
.govt-card { border-left: 5px solid #1e40af; }
.success-gradient { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
.warning-gradient { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
.danger-gradient { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); }
.info-gradient { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
.primary-gradient { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); }

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
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.quarter-item {
    background: #fafafa;
    border: 2px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    cursor: pointer;
}

.quarter-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.15);
}

.quarter-item.overdue { border-color: #dc2626; background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); }
.quarter-item.completed { border-color: #16a34a; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); }
.quarter-item.partial { border-color: #f59e0b; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); }

.notification {
    max-width: 400px;
    backdrop-filter: blur(8px);
}

.notification-success { background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9)); }
.notification-error { background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.9)); }
.notification-warning { background: linear-gradient(135deg, rgba(245, 158, 11, 0.9), rgba(217, 119, 6, 0.9)); }
.notification-info { background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(37, 99, 235, 0.9)); }

.btn-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

@media (max-width: 768px) {
    .quarters-grid { grid-template-columns: 1fr; }
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
                           value="{{ $contract->contract_number ?? '' }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg font-medium">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Shartnoma sanasi *</label>
                    <input type="date" name="contract_date" required
                           value="{{ isset($contract) ? $contract->contract_date->format('Y-m-d') : date('Y-m-d') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Yakunlash sanasi</label>
                    <input type="date" name="completion_date"
                           value="{{ isset($contract) && $contract->completion_date ? $contract->completion_date->format('Y-m-d') : '' }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg">
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
                <h3 class="text-xl font-bold text-blue-900 mb-6">Moliyaviy ma'lumotlar</h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Jami shartnoma summasi (so'm) *</label>
                        <input type="text" name="total_amount" required data-format="currency"
                               value="{{ isset($contract) ? number_format($contract->total_amount, 0, '.', ' ') : '' }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg font-bold"
                               placeholder="0">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">To'lov turi *</label>
                        <select name="payment_type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg">
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
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Qurulish muddati (yil) *</label>
                            <input type="number" name="construction_period_years" min="1" max="10" step="1"
                                   value="{{ $contract->construction_period_years ?? 2 }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Jami choraklar soni</label>
                            <input type="number" name="quarters_count" min="1" max="20" step="1"
                                   value="{{ $contract->quarters_count ?? 8 }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg font-semibold">
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
                <button onclick="openPaymentScheduleModal()" id="scheduleBtn"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                    Jadval tuzish
                </button>
                {{-- <button onclick="openPaymentModal()" id="paymentBtn"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                    To'lov qo'shish
                </button> --}}
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
                                @for($y = date('Y') - 1; $y <= date('Y') + 5; $y++)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }} yil</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Choraklar soni</label>
                            <input type="number" name="quarters_count" min="1" max="20" step="1" value="{{$contract->quarters_count ?? 8}}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jami summa</label>
                            <input type="text" name="total_schedule_amount" data-format="currency"
                                   value="{{ isset($contract) ? number_format($contract->remaining_amount ?? 0, 0, '.', ' ') : '0' }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
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
                        <input type="text" name="payment_amount" required data-format="currency"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-lg font-medium"
                               placeholder="0">
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
                    <button type="submit" id="paymentSubmitBtn"
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        To'lovni qo'shish
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div id="editPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form id="editPaymentForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="payment_id" value="">

                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i data-feather="edit" class="w-5 h-5 mr-2 text-blue-600"></i>
                        To'lovni tahrirlash
                    </h3>
                </div>

                <div class="px-8 py-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To'lov sanasi *</label>
                        <input type="date" name="payment_date" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To'lov summasi (so'm) *</label>
                        <input type="text" name="payment_amount" required data-format="currency"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg font-medium"
                               placeholder="0">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hujjat raqami</label>
                        <input type="text" name="payment_number" maxlength="50"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:

<input type="text" name="payment_number" maxlength="50"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="Chek, spravka raqami">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                        <textarea name="payment_notes" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Qo'shimcha ma'lumot"></textarea>
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-gray-200 flex justify-end space-x-4">
                    <button type="button" onclick="closeEditPaymentModal()"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Bekor qilish
                    </button>
                    <button type="submit" id="editPaymentSubmitBtn"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Saqlash
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

<!-- History Modal -->
<div id="historyModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-4xl sm:w-full max-h-screen overflow-y-auto">
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
                <div id="paymentHistoryTimeline" class="max-h-96 overflow-y-auto">
                    <div class="flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                        <span class="ml-3 text-gray-600">Tarix yuklanmoqda...</span>
                    </div>
                </div>
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
let isSubmitting = false;

// Currency formatting utilities
function formatCurrencyInput(value) {
    const numericValue = value.replace(/\D/g, '');
    return numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function parseCurrencyValue(formattedValue) {
    return parseFloat(formattedValue.replace(/\s/g, '')) || 0;
}

function formatDisplayCurrency(amount) {
    if (isNaN(amount) || amount === null || amount === undefined) {
        amount = 0;
    }
    return new Intl.NumberFormat('uz-UZ', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' so\'m';
}

// Setup currency input formatters
function setupCurrencyInputs() {
    const currencyInputs = document.querySelectorAll('input[data-format="currency"]');

    currencyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            const cursorPosition = e.target.selectionStart;
            const oldLength = e.target.value.length;

            e.target.value = formatCurrencyInput(e.target.value);

            const newLength = e.target.value.length;
            const newPosition = cursorPosition + (newLength - oldLength);
            e.target.setSelectionRange(newPosition, newPosition);
        });

        input.addEventListener('paste', function(e) {
            setTimeout(() => {
                e.target.value = formatCurrencyInput(e.target.value);
            }, 0);
        });

        if (input.value && input.value !== '0') {
            input.value = formatCurrencyInput(input.value);
        }
    });
}

// Safe feather replace
function safeFeatherReplace() {
    try {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    } catch (error) {
        console.warn('Feather icons replace failed:', error);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    safeFeatherReplace();
    setupCurrencyInputs();

    if (contractData) {
        togglePaymentSettings();
        calculatePaymentBreakdown();
        loadQuarterlyData();
        loadPaymentHistory();
    } else {
        calculatePaymentBreakdown();
    }

    setupEventListeners();
});

function setupEventListeners() {
    document.querySelector('select[name="payment_type"]').addEventListener('change', togglePaymentSettings);

    const amountInputs = ['total_amount', 'initial_payment_percent', 'quarters_count'];
    amountInputs.forEach(inputName => {
        const input = document.querySelector(`input[name="${inputName}"]`);
        if (input) {
            input.addEventListener('input', debounce(() => {
                if (inputName === 'total_amount') {
                    setupCurrencyInputs();
                }
                calculatePaymentBreakdown();
            }, 500));
        }
    });

    document.querySelectorAll('input[name="schedule_type"]').forEach(radio => {
        radio.addEventListener('change', toggleCustomScheduleGrid);
    });

    // Form submissions with duplicate prevention
    document.getElementById('contractForm').addEventListener('submit', handleContractSubmit);
    document.getElementById('paymentScheduleForm').addEventListener('submit', handleScheduleSubmit);
    document.getElementById('paymentForm').addEventListener('submit', handlePaymentSubmit);

    const editForm = document.getElementById('editPaymentForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditPaymentSubmit);
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
    const totalAmountInput = document.querySelector('input[name="total_amount"]');
    const totalAmount = totalAmountInput ? parseCurrencyValue(totalAmountInput.value || '0') : 0;
    const initialPercent = parseFloat(document.querySelector('input[name="initial_payment_percent"]').value) || 0;
    const quartersCount = parseInt(document.querySelector('input[name="quarters_count"]').value) || 0;
    const paymentType = document.querySelector('select[name="payment_type"]').value;

    const initialAmount = totalAmount * (initialPercent / 100);
    const remainingAmount = totalAmount - initialAmount;
    const quarterlyAmount = quartersCount > 0 ? remainingAmount / quartersCount : 0;

    document.getElementById('initialAmount').textContent = formatDisplayCurrency(initialAmount);
    document.getElementById('remainingAmount').textContent = formatDisplayCurrency(remainingAmount);
    document.getElementById('quarterlyAmount').textContent = formatDisplayCurrency(quarterlyAmount);

    const previewDiv = document.getElementById('paymentPreview');
    if (paymentType === 'full') {
        previewDiv.style.display = 'none';
    } else {
        previewDiv.style.display = 'block';
    }
}

// Load quarterly data with quarters starting from contract date
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
        if (!quarters || typeof quarters !== 'object') return;

        const quarterEntries = Object.entries(quarters)
            .filter(([quarter, data]) => {
                const planAmount = parseFloat(data.plan_amount) || 0;
                const factTotal = parseFloat(data.fact_total) || 0;
                return planAmount > 0 || factTotal > 0;
            })
            .sort((a, b) => parseInt(a[0]) - parseInt(b[0]));

        if (quarterEntries.length === 0) return;

        html += `
        <div class="year-section">
            <div class="year-header">
                <div class="text-xl font-bold text-gray-800 flex items-center">
                    <i data-feather="calendar" class="w-6 h-6 mr-3 text-blue-600"></i>
                    ${year} yil to'lov jadvali
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">`;

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
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-800">${quarter}-chorak</h4>
                        <span class="px-2 py-1 text-xs font-semibold rounded ${getStatusBadgeClass(quarterData)}">${statusText}</span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Plan:</span>
                            <span class="font-medium text-blue-600">${formatDisplayCurrency(planAmount)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">To'langan:</span>
                            <span class="font-medium text-green-600">${formatDisplayCurrency(factTotal)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">${debt >= 0 ? 'Qarz:' : 'Ortiqcha:'}</span>
                            <span class="font-medium ${debt >= 0 ? 'text-red-600' : 'text-green-600'}">${formatDisplayCurrency(Math.abs(debt))}</span>
                        </div>
                        <div class="flex justify-between border-t pt-3">
                            <span class="text-sm text-gray-600">To'lov foizi:</span>
                            <span class="font-bold text-gray-800">${Math.round(paymentPercent)}%</span>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <div class="flex space-x-2">
                            <button onclick="event.stopPropagation(); editPayment(${year}, ${quarter})"
                                    class="p-2 text-blue-600 hover:bg-blue-100 rounded transition-colors" title="Tahrirlash">
                                <i data-feather="edit-2" class="w-4 h-4"></i>
                            </button>
                            <button onclick="event.stopPropagation(); addQuarterPayment(${year}, ${quarter})"
                                    class="p-2 text-green-600 hover:bg-green-100 rounded transition-colors" title="To'lov qo'shish">
                                <i data-feather="plus" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="text-right">
                            <div class="w-12 h-12 relative">
                                <svg class="w-12 h-12 transform -rotate-90">
                                    <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" class="text-gray-200"/>
                                    <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent"
                                            stroke-dasharray="${Math.round(paymentPercent * 1.25)}, 125"
                                            class="${getProgressColor(paymentPercent)}"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-xs font-bold">${Math.round(paymentPercent)}%</span>
                                </div>
                            </div>
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
    container.innerHTML = `
    <div class="text-center py-16">
        <i data-feather="calendar" class="w-16 h-16 mx-auto text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 mb-3">To'lov jadvali mavjud emas</h3>
        <p class="text-gray-600 mb-6">Choraklar bo'yicha to'lov jadvalini tuzish uchun "Jadval tuzish" tugmasini bosing</p>
        <button onclick="openPaymentScheduleModal()"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i data-feather="plus" class="w-5 h-5 mr-2"></i>
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

    document.getElementById('totalPlan').textContent = formatDisplayCurrency(totalPlan);
    document.getElementById('totalPaid').textContent = formatDisplayCurrency(totalPaid);
    document.getElementById('currentDebt').textContent = formatDisplayCurrency(currentDebt);
    document.getElementById('overdueDebt').textContent = formatDisplayCurrency(overdueDebt);
}

// Helper functions for quarter styling
function getQuarterStatusClass(quarterData) {
    if (quarterData.is_overdue && quarterData.debt > 0) return 'overdue border-red-200 bg-red-50';
    if (quarterData.payment_percent >= 100) return 'completed border-green-200 bg-green-50';
    if (quarterData.payment_percent > 0) return 'partial border-yellow-200 bg-yellow-50';
    return 'border-gray-200 bg-gray-50';
}

function getQuarterStatusText(quarterData) {
    if (quarterData.is_overdue && quarterData.debt > 0) return 'Muddati o\'tgan';
    if (quarterData.payment_percent >= 100) return 'To\'liq';
    if (quarterData.payment_percent > 0) return 'Qisman';
    return 'To\'lanmagan';
}

function getStatusBadgeClass(quarterData) {
    if (quarterData.is_overdue && quarterData.debt > 0) return 'bg-red-100 text-red-800';
    if (quarterData.payment_percent >= 100) return 'bg-green-100 text-green-800';
    if (quarterData.payment_percent > 0) return 'bg-yellow-100 text-yellow-800';
    return 'bg-gray-100 text-gray-800';
}

function getProgressColor(percent) {
    if (percent >= 100) return 'text-green-500';
    if (percent >= 50) return 'text-yellow-500';
    if (percent > 0) return 'text-blue-500';
    return 'text-gray-300';
}

// Modal functions
function openPaymentScheduleModal() {
    document.getElementById('paymentScheduleModal').classList.remove('hidden');
    setupCurrencyInputs();
}

function closePaymentScheduleModal() {
    document.getElementById('paymentScheduleModal').classList.add('hidden');
    document.getElementById('paymentScheduleForm').reset();
}

function openPaymentModal() {
    document.getElementById('paymentModal').classList.remove('hidden');
    setupCurrencyInputs();
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentForm').reset();
}

function openEditPaymentModal() {
    document.getElementById('editPaymentModal').classList.remove('hidden');
    setupCurrencyInputs();
}

function closeEditPaymentModal() {
    document.getElementById('editPaymentModal').classList.add('hidden');
    document.getElementById('editPaymentForm').reset();
}

function openQuarterDetails(year, quarter) {
    const quarterData = quarterlyData[year] && quarterlyData[year][quarter];
    if (!quarterData) return;

    document.getElementById('quarterDetailsTitle').textContent = `${quarter}-chorak ${year} yil ma'lumotlari`;

    const content = `
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="info-gradient rounded-lg p-4 text-center">
                <p class="text-sm font-medium text-blue-800">PLAN</p>
                <p class="text-2xl font-bold text-blue-900">${formatDisplayCurrency(quarterData.plan_amount)}</p>
            </div>
            <div class="success-gradient rounded-lg p-4 text-center">
                <p class="text-sm font-medium text-green-800">TO'LANGAN</p>
                <p class="text-2xl font-bold text-green-900">${formatDisplayCurrency(quarterData.fact_total)}</p>
            </div>
            <div class="${quarterData.debt > 0 ? 'danger-gradient' : 'success-gradient'} rounded-lg p-4 text-center">
                <p class="text-sm font-medium ${quarterData.debt > 0 ? 'text-red-800' : 'text-green-800'}">${quarterData.debt > 0 ? 'QARZ' : 'ORTIQCHA'}</p>
                <p class="text-2xl font-bold ${quarterData.debt > 0 ? 'text-red-900' : 'text-green-900'}">${formatDisplayCurrency(Math.abs(quarterData.debt))}</p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-lg font-bold text-gray-900 mb-4">To'lovlar ro'yxati</h4>
            <div id="quarterPaymentsList" class="space-y-3">
                ${renderPaymentsList(quarterData.payments)}
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
}

function renderPaymentsList(payments) {
    if (!payments || payments.length === 0) {
        return '<p class="text-gray-500 text-center py-8">Hali to\'lovlar mavjud emas</p>';
    }

    return payments.map(payment => `
        <div class="bg-white rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-center">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-medium text-gray-900">${payment.payment_number || 'To\'lov #' + payment.id}</div>
                        <div class="flex space-x-2">
                            <button onclick="editSpecificPayment(${payment.id})"
                                    class="text-blue-600 hover:text-blue-800 p-1 rounded transition-colors"
                                    title="Tahrirlash">
                                <i data-feather="edit-2" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deletePayment(${payment.id})"
                                    class="text-red-600 hover:text-red-800 p-1 rounded transition-colors"
                                    title="O'chirish">
                                <i data-feather="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 mb-1">${new Date(payment.payment_date).toLocaleDateString('uz-UZ')}</div>
                    ${payment.notes ? `<div class="text-sm text-gray-500">${payment.notes}</div>` : ''}
                </div>
                <div class="text-right ml-4">
                    <div class="font-bold text-green-600 text-lg">${formatDisplayCurrency(payment.amount)}</div>
                </div>
            </div>
        </div>
    `).join('');
}

function toggleCustomScheduleGrid() {
    const scheduleType = document.querySelector('input[name="schedule_type"]:checked').value;
    const customGrid = document.getElementById('customScheduleGrid');

    if (scheduleType === 'custom') {
        customGrid.classList.remove('hidden');
    } else {
        customGrid.classList.add('hidden');
    }
}

// Form submission handlers with one-time submission prevention
async function handleContractSubmit(e) {
    e.preventDefault();

    if (isSubmitting) {
        showNotification('Iltimos kuting, so\'rov yuborilmoqda...', 'warning');
        return false;
    }

    isSubmitting = true;
    const submitBtn = document.getElementById('contractSubmitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    // Disable button and show loading
    submitBtn.classList.add('btn-disabled');
    submitText.classList.add('hidden');
    submitLoader.classList.remove('hidden');

    try {
        const formData = new FormData(e.target);

        // Parse currency values
        const totalAmountInput = document.querySelector('input[name="total_amount"]');
        if (totalAmountInput) {
            const totalAmount = parseCurrencyValue(totalAmountInput.value || '0');
            formData.set('total_amount', totalAmount);
        }

        const url = contractData ? `/contracts/${contractData.id}` : '/contracts/store';
        const method = contractData ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-HTTP-Method-Override': method,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message || 'Muvaffaqiyatli saqlandi', 'success');
            if (!contractData && result.contract) {
                setTimeout(() => {
                    window.location.href = `/contracts/${result.contract.id}/payment-update`;
                }, 1500);
            } else {
                setTimeout(() => window.location.reload(), 1500);
            }
        } else {
            throw new Error(result.message || 'Xatolik yuz berdi');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    } finally {
        submitBtn.classList.remove('btn-disabled');
        submitBtn.textContent = 'Jadvalni saqlash';
    }
}

async function handlePaymentSubmit(e) {
    e.preventDefault();

    if (!contractData) {
        showNotification('Avval shartnomani saqlang', 'error');
        return;
    }

    const submitBtn = document.getElementById('paymentSubmitBtn');
    if (submitBtn.classList.contains('btn-disabled')) {
        showNotification('Iltimos kuting, to\'lov qo\'shilmoqda...', 'warning');
        return;
    }

    submitBtn.classList.add('btn-disabled');
    submitBtn.textContent = 'Qo\'shilmoqda...';

    try {
        const formData = new FormData(e.target);

        // Parse currency amount
        const paymentAmountInput = document.querySelector('#paymentForm input[name="payment_amount"]');
        if (paymentAmountInput) {
            const paymentAmount = parseCurrencyValue(paymentAmountInput.value || '0');
            formData.set('payment_amount', paymentAmount);
        }

        const response = await fetch(`/contracts/${contractData.id}/store-fact-payment`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closePaymentModal();
            showNotification(result.message, 'success');
            loadQuarterlyData();
            loadPaymentHistory();
        } else {
            throw new Error(result.message || 'To\'lov qo\'shishda xatolik');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    } finally {
        submitBtn.classList.remove('btn-disabled');
        submitBtn.textContent = 'To\'lovni qo\'shish';
    }
}

async function handleEditPaymentSubmit(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('editPaymentSubmitBtn');
    if (submitBtn.classList.contains('btn-disabled')) {
        showNotification('Iltimos kuting, tahrirlash davom etmoqda...', 'warning');
        return;
    }

    submitBtn.classList.add('btn-disabled');
    submitBtn.textContent = 'Saqlanmoqda...';

    try {
        const formData = new FormData(e.target);
        const paymentId = formData.get('payment_id');

        // Parse currency amount
        const paymentAmountInput = document.querySelector('#editPaymentForm input[name="payment_amount"]');
        if (paymentAmountInput) {
            const paymentAmount = parseCurrencyValue(paymentAmountInput.value || '0');
            formData.set('payment_amount', paymentAmount);
        }

        const response = await fetch(`/contracts/${contractData.id}/fact-payment/${paymentId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeEditPaymentModal();
            showNotification(result.message, 'success');
            loadQuarterlyData();
            loadPaymentHistory();
            closeQuarterDetailsModal();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showNotification('To\'lov tahrirlashda xatolik: ' + error.message, 'error');
    } finally {
        submitBtn.classList.remove('btn-disabled');
        submitBtn.textContent = 'Saqlash';
    }
}

// Payment edit and delete functions
async function editSpecificPayment(paymentId) {
    try {
        const response = await fetch(`/contracts/${contractData.id}/fact-payment/${paymentId}`);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        const payment = result.payment;

        // Populate edit modal
        document.querySelector('#editPaymentModal input[name="payment_date"]').value = payment.payment_date;
        document.querySelector('#editPaymentModal input[name="payment_amount"]').value = formatCurrencyInput(payment.amount.toString());
        document.querySelector('#editPaymentModal input[name="payment_number"]').value = payment.payment_number || '';
        document.querySelector('#editPaymentModal textarea[name="payment_notes"]').value = payment.notes || '';
        document.querySelector('#editPaymentModal input[name="payment_id"]').value = paymentId;

        openEditPaymentModal();

    } catch (error) {
        showNotification('To\'lov ma\'lumotlarini yuklashda xatolik: ' + error.message, 'error');
    }
}

async function deletePayment(paymentId) {
    if (!confirm('Ushbu to\'lovni o\'chirishni tasdiqlaysizmi?')) {
        return;
    }

    try {
        const response = await fetch(`/contracts/${contractData.id}/fact-payment/${paymentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');
            loadQuarterlyData();
            loadPaymentHistory();
            closeQuarterDetailsModal();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showNotification('To\'lov o\'chirishda xatolik: ' + error.message, 'error');
    }
}

// Helper functions
function addQuarterPayment(year, quarter) {
    openPaymentModal();
    // Set payment date to middle of quarter based on contract start date
    const contractStartDate = new Date(contractData.contract_date);
    const quarterStartMonth = contractStartDate.getMonth() + ((quarter - 1) * 3);
    const paymentYear = contractStartDate.getFullYear() + Math.floor(quarterStartMonth / 12);
    const paymentMonth = (quarterStartMonth % 12) + 1;

    const date = new Date(paymentYear, paymentMonth - 1, 15);
    document.querySelector('input[name="payment_date"]').value = date.toISOString().split('T')[0];
}

function editPayment(year, quarter) {
    showNotification(`${quarter}-chorak ${year} yil planini tahrirlash funksiyasi ishlab chiqilmoqda`, 'info');
}

// Payment history functions
async function loadPaymentHistory() {
    if (!contractData) return;

    try {
        const response = await fetch(`/contracts/${contractData.id}/payment-history`);
        const result = await response.json();

        if (result.success) {
            renderPaymentHistoryTimeline(result.histories);
        }
    } catch (error) {
        console.error('Error loading payment history:', error);
    }
}

function renderPaymentHistoryTimeline(histories) {
    const timelineContainer = document.getElementById('paymentHistoryTimeline');
    if (!timelineContainer) return;

    if (!histories || histories.length === 0) {
        timelineContainer.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <i data-feather="clock" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                <p>Hali tarix ma'lumotlari mavjud emas</p>
            </div>
        `;
        safeFeatherReplace();
        return;
    }

    const timelineHTML = histories.map((history, index) => {
        const isLast = index === histories.length - 1;
        const actionIcon = getActionIcon(history.action);
        const actionColor = getActionColor(history.action);

        return `
            <div class="relative ${!isLast ? 'pb-8' : ''}">
                ${!isLast ? '<span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200"></span>' : ''}
                <div class="relative flex items-start space-x-3">
                    <div class="relative">
                        <div class="h-10 w-10 rounded-full ${actionColor} flex items-center justify-center ring-8 ring-white">
                            <i data-feather="${actionIcon}" class="w-5 h-5 text-white"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm">
                            <div class="font-medium text-gray-900">${history.formatted_description || history.description}</div>
                            <div class="mt-0.5 text-gray-500">
                                ${history.user_name} • ${history.created_at}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    timelineContainer.innerHTML = `
        <div class="flow-root">
            <ul class="-mb-8">${timelineHTML}</ul>
        </div>
    `;

    safeFeatherReplace();
}

function getActionIcon(action) {
    switch (action) {
        case 'created': return 'plus-circle';
        case 'updated': return 'edit';
        case 'deleted': return 'trash-2';
        default: return 'activity';
    }
}

function getActionColor(action) {
    switch (action) {
        case 'created': return 'bg-green-500';
        case 'updated': return 'bg-blue-500';
        case 'deleted': return 'bg-red-500';
        default: return 'bg-gray-500';
    }
}

function openHistoryModal() {
    document.getElementById('historyModal').classList.remove('hidden');
    loadPaymentHistory();
}

function closeHistoryModal() {
    document.getElementById('historyModal').classList.add('hidden');
}

// Utility functions
function showNotification(message, type = 'info', duration = 4000) {
    const bgColors = {
        success: 'notification-success',
        error: 'notification-error',
        warning: 'notification-warning',
        info: 'notification-info'
    };

    const icons = {
        success: 'check-circle',
        error: 'alert-triangle',
        warning: 'alert-triangle',
        info: 'info'
    };

    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 ${bgColors[type]} text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;

    notification.innerHTML = `
        <div class="flex items-center">
            <i data-feather="${icons[type]}" class="w-5 h-5 mr-3 flex-shrink-0"></i>
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i data-feather="x" class="w-4 h-4"></i>
            </button>
        </div>
    `;

    document.body.appendChild(notification);
    safeFeatherReplace();

    setTimeout(() => notification.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        if (document.body.contains(notification)) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }
    }, duration);
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
        setupCurrencyInputs();
    }
}

function exportReport() {
    if (!contractData) {
        showNotification('Avval shartnomani saqlang', 'error');
        return;
    }
    showNotification('Hisobot funksiyasi ishlab chiqilmoqda', 'info');
}

// Close modals on background click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
        const modals = ['paymentScheduleModal', 'paymentModal', 'quarterDetailsModal', 'historyModal', 'editPaymentModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        });
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = ['paymentScheduleModal', 'paymentModal', 'quarterDetailsModal', 'historyModal', 'editPaymentModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        });
    }
});
</script>
@endpush
