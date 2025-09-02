{{-- resources/views/contracts/modals.blade.php --}}

{{-- Contract Edit Modal --}}
<div id="contractEditModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-900">SHARTNOMA MA'LUMOTLARINI TAHRIRLASH</h3>
        </div>

        <form id="contractEditForm" class="p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SHARTNOMA RAQAMI</label>
                    <input type="text"
                           name="contract_number"
                           value="{{ $contract->contract_number }}"
                           class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SHARTNOMA SANASI</label>
                    <input type="date"
                           name="contract_date"
                           value="{{ $contract->contract_date->format('Y-m-d') }}"
                           class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">JAMI SUMMA</label>
                    <input type="number"
                           name="total_amount"
                           value="{{ $contract->total_amount }}"
                           step="0.01"
                           min="0"
                           class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">BOSHLANG'ICH TO'LOV (%)</label>
                    <input type="number"
                           name="initial_payment_percent"
                           value="{{ $contract->initial_payment_percent }}"
                           min="0"
                           max="100"
                           class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">QURILISH MUDDATI (YIL)</label>
                    <input type="number"
                           name="construction_period_years"
                           value="{{ $contract->construction_period_years }}"
                           min="1"
                           max="10"
                           class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">HOLAT</label>
                    <select name="status_id"
                            class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                        @foreach(\App\Models\ContractStatus::all() as $status)
                            <option value="{{ $status->id }}" {{ $contract->status_id == $status->id ? 'selected' : '' }}>
                                {{ $status->name_ru }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button"
                        onclick="closeModal('contractEditModal')"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                    BEKOR QILISH
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    SAQLASH
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Auto Schedule Modal --}}
<div id="autoScheduleModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-900">AVTOMATIK GRAFIK TUZISH</h3>
        </div>

        <form id="autoScheduleForm" class="p-6">
            @csrf
            <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-1"></i>
                    <div>
                        <strong>Formula:</strong>
                        {{ number_format($contract->total_amount / 1000000, 1) }}M - {{ $contract->initial_payment_percent }}% = {{ number_format($remainingAmount / 1000000, 1) }}M → Choraklar soniga bo'linadi
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">BOSHLANG'ICH YIL</label>
                    <select name="start_year"
                            class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                        @for($y = date('Y'); $y <= date('Y') + 5; $y++)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>
                                {{ $y }} yil
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">JAMI CHORAKLAR SONI</label>
                    <select name="total_quarters"
                            class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                            onchange="updateCalculation()">
                        @for($q = 1; $q <= 20; $q++)
                            <option value="{{ $q }}" {{ $q == 8 ? 'selected' : '' }}>
                                {{ $q }} chorak
                            </option>
                        @endfor
                    </select>
                    <small class="text-gray-500 text-xs mt-1">16 chorak = 4 yil</small>
                </div>
            </div>

            <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg mb-6">
                <h4 class="font-bold mb-3 text-gray-800">HISOBLASH NATIJASI:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Jami summa:</span>
                            <span class="font-bold" id="totalAmount">{{ number_format($contract->total_amount / 1000000, 1) }}M</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Boshlang'ich to'lov:</span>
                            <span class="font-bold" id="initialAmount">{{ number_format(($contract->total_amount * $contract->initial_payment_percent) / 100 / 1000000, 1) }}M</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Qolgan summa:</span>
                            <span class="font-bold" id="remainingAmount">{{ number_format($remainingAmount / 1000000, 1) }}M</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Chorak summasi:</span>
                            <span class="font-bold text-blue-600" id="quarterAmount">{{ number_format($remainingAmount / 8 / 1000000, 1) }}M</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-1"></i>
                    <div class="text-sm">
                        <strong>Eslatma:</strong> Avtomatik grafik tuzilganda, mavjud plan to'lovlar o'chirib tashlanadi va yangi grafik yaratiladi.
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button"
                        onclick="closeModal('autoScheduleModal')"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                    BEKOR QILISH
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    GRAFIK TUZISH
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Payment Add Modal --}}
<div id="paymentModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-lg w-full">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-900">TO'LOV QO'SHISH</h3>
        </div>

        <form id="paymentForm" class="p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">TO'LOV SANASI</label>
                    <input type="date"
                           name="payment_date"
                           value="{{ date('Y-m-d') }}"
                           class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">TO'LOV SUMMASI</label>
                    <input type="number"
                           name="amount"
                           step="0.01"
                           min="0"
                           placeholder="0.00"
                           class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">HUJJAT RAQAMI</label>
                <input type="text"
                       name="payment_number"
                       maxlength="50"
                       placeholder="Chek, spravka raqami"
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">IZOH</label>
                <textarea name="notes"
                          rows="3"
                          placeholder="Qo'shimcha ma'lumot"
                          class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>

            <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-1"></i>
                    <div class="text-sm text-gray-700">
                        <strong>Eslatma:</strong> To'lov sanasi bo'yicha avtomatik tarzda tegishli chorak aniqlanadi.
                        To'lov summasi shartnoma summasidan oshmasligi kerak.
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button"
                        onclick="closeModal('paymentModal')"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                    BEKOR QILISH
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                    TO'LOVNI QO'SHISH
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Quarter Plan Modal --}}
<div id="quarterPlanModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-lg w-full">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-900">CHORAK UCHUN PLAN</h3>
            <p class="text-gray-600" id="quarterModalTitle">3-chorak 2024 yil</p>
        </div>

        <form id="quarterPlanForm" class="p-6">
            @csrf
            <input type="hidden" name="year" id="quarterYear">
            <input type="hidden" name="quarter" id="quarterNumber">

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">TO'LOV SUMMASI</label>
                <input type="number"
                       name="amount"
                       step="0.01"
                       min="0"
                       placeholder="0.00"
                       class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
                <small class="text-gray-500 text-xs mt-1">Chorak uchun rejalashtirilgan to'lov summasi</small>
            </div>

            <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg mb-6">
                <div class="flex items-start">
                    <i class="fas fa-lightbulb text-blue-500 mr-2 mt-1"></i>
                    <div class="text-sm text-blue-700">
                        <strong>Maslahat:</strong> Qolgan summani choraklar soniga bo'lib teng taqsimlashingiz mumkin yoki
                        har bir chorak uchun alohida summa belgilashingiz mumkin.
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button"
                        onclick="closeModal('quarterPlanModal')"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                    BEKOR QILISH
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    SAQLASH
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Quarter Details Modal --}}
<div id="quarterDetailsModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-900">CHORAK TO'LOVLARI TAFSILOTI</h3>
            <p class="text-gray-600" id="quarterDetailsTitle">2-chorak 2024 yil</p>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">SANA</th>
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">SUMMA</th>
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">HUJJAT</th>
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">IZOH</th>
                            <th class="border border-gray-200 p-3 text-left font-medium text-gray-700">AMALLAR</th>
                        </tr>
                    </thead>
                    <tbody id="quarterDetailsList">
                        <tr>
                            <td class="border border-gray-200 p-3">15.06.2024</td>
                            <td class="border border-gray-200 p-3 font-bold text-green-600">2.7M</td>
                            <td class="border border-gray-200 p-3">CHEK-001234</td>
                            <td class="border border-gray-200 p-3">Asosiy to'lov</td>
                            <td class="border border-gray-200 p-3">
                                <button onclick="editPayment(1)" class="text-blue-600 hover:text-blue-800 mr-2" title="Tahrirlash">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deletePaymentConfirm(1)" class="text-red-600 hover:text-red-800" title="O'chirish">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-gray-200 p-3">28.05.2024</td>
                            <td class="border border-gray-200 p-3 font-bold text-green-600">1.4M</td>
                            <td class="border border-gray-200 p-3">CHEK-001223</td>
                            <td class="border border-gray-200 p-3">Qo'shimcha to'lov</td>
                            <td class="border border-gray-200 p-3">
                                <button onclick="editPayment(2)" class="text-blue-600 hover:text-blue-800 mr-2" title="Tahrirlash">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deletePaymentConfirm(2)" class="text-red-600 hover:text-red-800" title="O'chirish">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-6 border-t flex justify-between items-center">
            <div class="text-lg font-bold text-green-600" id="quarterDetailsTotal">
                Jami: 4.1M so'm (2 ta to'lov)
            </div>
            <button onclick="closeModal('quarterDetailsModal')"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                YOPISH
            </button>
        </div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div id="confirmModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-900" id="confirmTitle">TASDIQLASH</h3>
        </div>

        <div class="p-6">
            <div class="flex items-start mb-6">
                <i class="fas fa-question-circle text-yellow-500 mr-3 mt-1 text-xl"></i>
                <p class="text-gray-700" id="confirmMessage">Bu amalni bajarishga ishonchingiz komilmi?</p>
            </div>

            <div class="flex justify-end space-x-3">
                <button onclick="closeModal('confirmModal')"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                    BEKOR QILISH
                </button>
                <button id="confirmButton"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                    TASDIQLASH
                </button>
            </div>
        </div>
    </div>
</div>
