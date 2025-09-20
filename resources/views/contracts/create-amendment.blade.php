@extends('layouts.app')

@section('title', 'Qo\'shimcha kelishuv yaratish - ' . $contract->contract_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Qo'shimcha kelishuv yaratish</h1>
                <p class="text-gray-600">Shartnoma: {{ $contract->contract_number }}</p>
            </div>
            <a href="{{ route('contracts.payment_update', $contract) }}" class="btn btn-secondary">
                <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
                Ortga qaytish
            </a>
        </div>

        @include('partials.flash-messages')

        <form method="POST" action="{{ route('contracts.amendments.store', $contract) }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Kelishuv raqami *</label>
                    <input type="text" name="amendment_number" required
                           value="{{ old('amendment_number') }}"
                           placeholder="Masalan: 1/2024"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('amendment_number') border-red-300 @enderror">
                    @error('amendment_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Kelishuv sanasi *</label>
                    <input type="date" name="amendment_date" required
                           value="{{ old('amendment_date', date('Y-m-d')) }}"
                           min="{{ $contract->contract_date->format('Y-m-d') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('amendment_date') border-red-300 @enderror">
                    @error('amendment_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="bg-purple-50 rounded-xl p-6 border-l-4 border-purple-500">
                <h3 class="text-lg font-bold text-purple-900 mb-6">O'zgartirishlar (ixtiyoriy)</h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Yangi jami summa (so'm)</label>
                        <input type="number" name="new_total_amount" step="0.01" min="1"
                               value="{{ old('new_total_amount') }}"
                               placeholder="Joriy: {{ number_format($contract->total_amount, 0, '.', ' ') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Yangi yakunlash sanasi</label>
                        <input type="date" name="new_completion_date"
                               value="{{ old('new_completion_date') }}"
                               min="{{ $contract->contract_date->format('Y-m-d') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Yangi boshlang'ich to'lov (%)</label>
                        <input type="number" name="new_initial_payment_percent" min="0" max="100" step="1"
                               value="{{ old('new_initial_payment_percent') }}"
                               placeholder="Joriy: {{ $contract->initial_payment_percent }}%"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Yangi choraklar soni</label>
                        <input type="number" name="new_quarters_count" min="1" max="20" step="1"
                               value="{{ old('new_quarters_count') }}"
                               placeholder="Joriy: {{ $contract->quarters_count }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">O'zgartirish sababi *</label>
                <textarea name="reason" required rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('reason') border-red-300 @enderror"
                          placeholder="Qo'shimcha kelishuv yaratish sababi">{{ old('reason') }}</textarea>
                @error('reason')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Qo'shimcha izoh</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                          placeholder="Batafsil ma'lumot (ixtiyoriy)">{{ old('description') }}</textarea>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('contracts.payment_update', $contract) }}" class="btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Bekor qilish
                </a>
                <button type="submit" class="btn bg-purple-600 text-white hover:bg-purple-700">
                    <i data-feather="file-plus" class="w-4 h-4 mr-2"></i>
                    Kelishuvni yaratish
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/feather-icons"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') feather.replace();
});
</script>
@endsection
