@extends('layouts.app')

@section('title', 'Янги кенгаш хулосаси')
@section('page-title', 'Янги кенгаш хулосаси қўшиш')

@section('header-actions')
    <a href="{{ route('kengash-hulosa.index') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
        Орқага
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('kengash-hulosa.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Асосий маълумотлар</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kengash Hulosa Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Кенгаш хулоса рақами</label>
                    <input type="text"
                           name="kengash_hulosa_raqami"
                           value="{{ old('kengash_hulosa_raqami') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kengash_hulosa_raqami') border-red-300 @enderror">
                    @error('kengash_hulosa_raqami')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kengash Hulosa Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Кенгаш хулоса санаси</label>
                    <input type="date"
                           name="kengash_hulosa_sanasi"
                           value="{{ old('kengash_hulosa_sanasi') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kengash_hulosa_sanasi') border-red-300 @enderror">
                    @error('kengash_hulosa_sanasi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- APZ Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">АПЗ рақами</label>
                    <input type="text"
                           name="apz_raqami"
                           value="{{ old('apz_raqami') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('apz_raqami') border-red-300 @enderror">
                    @error('apz_raqami')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- APZ Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">АПЗ берилган санаси</label>
                    <input type="date"
                           name="apz_berilgan_sanasi"
                           value="{{ old('apz_berilgan_sanasi') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('apz_berilgan_sanasi') border-red-300 @enderror">
                    @error('apz_berilgan_sanasi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Буюртмачи маълумотлари</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Буюртмачи</label>
                    <input type="text"
                           name="buyurtmachi"
                           value="{{ old('buyurtmachi') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('buyurtmachi') border-red-300 @enderror">
                    @error('buyurtmachi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Customer STIR/PINFL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Буюртмачи СТИР/ПИНФЛ</label>
                    <input type="text"
                           name="buyurtmachi_stir_pinfl"
                           value="{{ old('buyurtmachi_stir_pinfl') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('buyurtmachi_stir_pinfl') border-red-300 @enderror">
                    @error('buyurtmachi_stir_pinfl')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Customer Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Буюртмачи телефон рақами</label>
                    <input type="text"
                           name="buyurtmachi_telefon"
                           value="{{ old('buyurtmachi_telefon') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('buyurtmachi_telefon') border-red-300 @enderror">
                    @error('buyurtmachi_telefon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Designer Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Лойихачи маълумотлари</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Designer -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Лойихачи</label>
                    <input type="text"
                           name="loyihachi"
                           value="{{ old('loyihachi') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('loyihachi') border-red-300 @enderror">
                    @error('loyihachi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Designer STIR/PINFL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Лойихачи СТИР/ПИНФЛ</label>
                    <input type="text"
                           name="loyihachi_stir_pinfl"
                           value="{{ old('loyihachi_stir_pinfl') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('loyihachi_stir_pinfl') border-red-300 @enderror">
                    @error('loyihachi_stir_pinfl')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Designer Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Лойихачи телефон рақами</label>
                    <input type="text"
                           name="loyihachi_telefon"
                           value="{{ old('loyihachi_telefon') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('loyihachi_telefon') border-red-300 @enderror">
                    @error('loyihachi_telefon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Project Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Лойиха маълумотлари</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Project Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Лойиха смета хужжатларининг номланиши</label>
                    <textarea name="loyiha_smeta_nomi"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('loyiha_smeta_nomi') border-red-300 @enderror">{{ old('loyiha_smeta_nomi') }}</textarea>
                    @error('loyiha_smeta_nomi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- District -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Туман</label>
                    <input type="text"
                           name="tuman"
                           value="{{ old('tuman') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tuman') border-red-300 @enderror">
                    @error('tuman')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Building Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Бино тури</label>
                    <select name="bino_turi"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('bino_turi') border-red-300 @enderror">
                        <option value="">Танланг</option>
                        <option value="турар" {{ old('bino_turi') == 'турар' ? 'selected' : '' }}>Турар</option>
                        <option value="нотурар" {{ old('bino_turi') == 'нотурар' ? 'selected' : '' }}>Нотурар</option>
                    </select>
                    @error('bino_turi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Problem Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Муаммо тури</label>
                    <input type="text"
                           name="muammo_turi"
                           value="{{ old('muammo_turi') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('muammo_turi') border-red-300 @enderror">
                    @error('muammo_turi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Construction Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Қурилиш тури</label>
                    <input type="text"
                           name="qurilish_turi"
                           value="{{ old('qurilish_turi') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qurilish_turi') border-red-300 @enderror">
                    @error('qurilish_turi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Манзил</label>
                    <textarea name="manzil"
                              rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('manzil') border-red-300 @enderror">{{ old('manzil') }}</textarea>
                    @error('manzil')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Тўлов статуси</h3>

            <div class="space-y-4">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio"
                                   name="status"
                                   value="Мажбурий тўлов"
                                   {{ old('status', 'Мажбурий тўлов') == 'Мажбурий тўлов' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Мажбурий тўлов</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio"
                                   name="status"
                                   value="Тўловдан озод этилган"
                                   {{ old('status') == 'Тўловдан озод этилган' ? 'checked' : '' }}
                                   id="status-ozod"
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Тўловдан озод этилган</span>
                        </label>
                    </div>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Exemption Reason -->
                <div id="ozod-sababi" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Озод этилиш сабаби</label>
                    <select name="ozod_sababi"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Танланг</option>
                        @foreach($exemptionReasons as $key => $reason)
                            <option value="{{ $key }}" {{ old('ozod_sababi') == $key ? 'selected' : '' }}>
                                {{ $reason }}
                            </option>
                        @endforeach
                    </select>
                    @error('ozod_sababi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Contract Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Шартнома маълумотлари</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Contract Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Шартнома рақами</label>
                    <input type="text"
                           name="shartnoma_raqami"
                           value="{{ old('shartnoma_raqami') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('shartnoma_raqami') border-red-300 @enderror">
                    @error('shartnoma_raqami')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contract Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Шартнома санаси</label>
                    <input type="date"
                           name="shartnoma_sanasi"
                           value="{{ old('shartnoma_sanasi') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('shartnoma_sanasi') border-red-300 @enderror">
                    @error('shartnoma_sanasi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contract Value -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Шартнома қиймати (сўм)</label>
                    <input type="number"
                           name="shartnoma_qiymati"
                           value="{{ old('shartnoma_qiymati') }}"
                           step="0.01"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('shartnoma_qiymati') border-red-300 @enderror">
                    @error('shartnoma_qiymati')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actual Payment -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Факт тўлов (сўм)</label>
                    <input type="number"
                           name="fakt_tulov"
                           value="{{ old('fakt_tulov', 0) }}"
                           step="0.01"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('fakt_tulov') border-red-300 @enderror">
                    @error('fakt_tulov')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Debt -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Қарздорлик (сўм)</label>
                    <input type="number"
                           name="qarzdarlik"
                           value="{{ old('qarzdarlik', 0) }}"
                           step="0.01"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qarzdarlik') border-red-300 @enderror">
                    @error('qarzdarlik')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- TIC APZ ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">TIC АПЗ ID</label>
                    <input type="text"
                           name="tic_apz_id"
                           value="{{ old('tic_apz_id') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tic_apz_id') border-red-300 @enderror">
                    @error('tic_apz_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Files -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Файллар</h3>

            <div id="files-container">
                <div class="file-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Файл</label>
                        <input type="file"
                               name="files[]"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Санаси</label>
                        <input type="date"
                               name="file_dates[]"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Изоҳ</label>
                        <input type="text"
                               name="file_comments[]"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="button"
                                onclick="removeFileRow(this)"
                                class="px-3 py-2 text-red-600 hover:text-red-800">
                            <i data-feather="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button"
                    onclick="addFileRow()"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                Файл қўшиш
            </button>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex justify-end space-x-3">
                <a href="{{ route('kengash-hulosa.index') }}"
                   class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition-colors">
                    Бекор қилиш
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Сақлаш
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Toggle exemption reason field
    document.addEventListener('DOMContentLoaded', function() {
        const statusOzod = document.getElementById('status-ozod');
        const ozodSababiDiv = document.getElementById('ozod-sababi');
        const statusRadios = document.querySelectorAll('input[name="status"]');

        statusRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Тўловдан озод этилган') {
                    ozodSababiDiv.classList.remove('hidden');
                } else {
                    ozodSababiDiv.classList.add('hidden');
                }
            });
        });

        // Check initial state
        if (statusOzod.checked) {
            ozodSababiDiv.classList.remove('hidden');
        }
    });

    // Add file row
    function addFileRow() {
        const container = document.getElementById('files-container');
        const newRow = container.querySelector('.file-row').cloneNode(true);

        // Clear values
        newRow.querySelectorAll('input').forEach(input => {
            input.value = '';
        });

        container.appendChild(newRow);
        feather.replace();
    }

    // Remove file row
    function removeFileRow(button) {
        const container = document.getElementById('files-container');
        const rows = container.querySelectorAll('.file-row');

        if (rows.length > 1) {
            button.closest('.file-row').remove();
        }
    }
</script>
@endpush
