@extends('layouts.app')

@section('title', 'Кенгаш хулосаси')
@section('page-title', 'Кенгаш хулосаси')

@section('header-actions')
    <div class="flex items-center space-x-3">
        <button onclick="printDocument()"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-feather="printer" class="w-4 h-4 mr-2"></i>
            Чоп этиш
        </button>
        <a href="{{ route('kengash-hulosa.edit', $kengashHulosa) }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-feather="edit" class="w-4 h-4 mr-2"></i>
            Таҳрирлаш
        </a>
        <a href="{{ route('kengash-hulosa.index') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
            Орқага
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Basic Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Асосий маълумотлар</h3>
            @if($kengashHulosa->isTulovdanOzod())
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <i data-feather="check-circle" class="w-4 h-4 mr-1"></i>
                    Тўловдан озод этилган
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <i data-feather="dollar-sign" class="w-4 h-4 mr-1"></i>
                    Мажбурий тўлов
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500">Кенгаш хулоса рақами</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->kengash_hulosa_raqami ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Кенгаш хулоса санаси</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->kengash_hulosa_sanasi?->format('d.m.Y') ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">АПЗ рақами</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $kengashHulosa->apz_raqami ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">АПЗ берилган санаси</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->apz_berilgan_sanasi?->format('d.m.Y') ?: '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Буюртмачи маълумотлари</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500">Буюртмачи</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->buyurtmachi ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">СТИР/ПИНФЛ</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $kengashHulosa->buyurtmachi_stir_pinfl ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Телефон рақами</label>
                <p class="mt-1 text-sm text-gray-900">
                    @if($kengashHulosa->buyurtmachi_telefon)
                        <a href="tel:{{ $kengashHulosa->buyurtmachi_telefon }}" class="text-blue-600 hover:text-blue-800">
                            {{ $kengashHulosa->buyurtmachi_telefon }}
                        </a>
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Designer Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Лойихачи маълумотлари</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500">Лойихачи</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->loyihachi ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">СТИР/ПИНФЛ</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $kengashHulosa->loyihachi_stir_pinfl ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Телефон рақами</label>
                <p class="mt-1 text-sm text-gray-900">
                    @if($kengashHulosa->loyihachi_telefon)
                        <a href="tel:{{ $kengashHulosa->loyihachi_telefon }}" class="text-blue-600 hover:text-blue-800">
                            {{ $kengashHulosa->loyihachi_telefon }}
                        </a>
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Project Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Лойиха маълумотлари</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500">Лойиха смета хужжатларининг номланиши</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->loyiha_smeta_nomi ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Туман</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->tuman ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Бино тури</label>
                <p class="mt-1 text-sm text-gray-900">
                    @if($kengashHulosa->bino_turi)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $kengashHulosa->bino_turi == 'турар' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ ucfirst($kengashHulosa->bino_turi) }}
                        </span>
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Муаммо тури</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->muammo_turi ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Қурилиш тури</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->qurilish_turi ?: '—' }}</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500">Манзил</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->manzil ?: '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Payment Status -->
    @if($kengashHulosa->isTulovdanOzod() && $kengashHulosa->ozod_sababi)
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-green-900 mb-4">Тўловдан озод этилиш сабаби</h3>
        <p class="text-sm text-green-800">
            {{ $exemptionReasons[$kengashHulosa->ozod_sababi] ?? $kengashHulosa->ozod_sababi }}
        </p>
    </div>
    @endif

    <!-- Contract Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Шартнома маълумотлари</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500">Шартнома рақами</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $kengashHulosa->shartnoma_raqami ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Шартнома санаси</label>
                <p class="mt-1 text-sm text-gray-900">{{ $kengashHulosa->shartnoma_sanasi?->format('d.m.Y') ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Шартнома қиймати</label>
                <p class="mt-1 text-sm text-gray-900 font-semibold">
                    @if($kengashHulosa->shartnoma_qiymati)
                        {{ number_format($kengashHulosa->shartnoma_qiymati, 0, ',', ' ') }} сўм
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Факт тўлов</label>
                <p class="mt-1 text-sm font-semibold {{ $kengashHulosa->fakt_tulov > 0 ? 'text-green-600' : 'text-gray-900' }}">
                    {{ number_format($kengashHulosa->fakt_tulov, 0, ',', ' ') }} сўм
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Қарздорлик</label>
                <p class="mt-1 text-sm font-semibold {{ $kengashHulosa->qarzdarlik > 0 ? 'text-red-600' : 'text-gray-900' }}">
                    {{ number_format($kengashHulosa->qarzdarlik, 0, ',', ' ') }} сўм
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">TIC АПЗ ID</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $kengashHulosa->tic_apz_id ?: '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Files -->
    @if($kengashHulosa->files->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Файллар ({{ $kengashHulosa->files->count() }})</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
         @foreach($kengashHulosa->files as $file)
    <a href="{{ asset('storage/' . $file->file_path) }}"
       target="_blank"
       class="block border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">

        <div class="flex items-start justify-between mb-2">
            <div class="flex items-center space-x-2">
                <i data-feather="file" class="w-5 h-5 {{ $file->icon_class }}"></i>
                <span class="text-xs font-medium {{ $file->icon_class }}">{{ $file->extension }}</span>
            </div>
        </div>

        <h4 class="text-sm font-medium text-gray-900 truncate" title="{{ $file->original_name }}">
            {{ $file->original_name }}
        </h4>

        <div class="mt-2 space-y-1">
            <p class="text-xs text-gray-500">Ҳажми: {{ $file->formatted_file_size }}</p>
            @if($file->file_date)
                <p class="text-xs text-gray-500">Санаси: {{ \Carbon\Carbon::parse($file->file_date)->format('d.m.Y') }}</p>
            @endif
            @if($file->comment)
                <p class="text-xs text-gray-600 italic">{{ $file->comment }}</p>
            @endif
            <p class="text-xs text-gray-400">
                Юклаган: {{ $file->uploader->name ?? 'Номаълум' }}
                <br>
                {{ $file->created_at->format('d.m.Y H:i') }}
            </p>
        </div>
    </a>
@endforeach

        </div>
    </div>
    @else
    <div class="bg-gray-50 rounded-lg border-2 border-dashed border-gray-200 p-8 text-center">
        <i data-feather="upload" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
        <p class="text-gray-500">Ҳеч қандай файл юкланмаган</p>
        <p class="text-sm text-gray-400 mt-1">Файл юклаш учун маълумотни таҳрирланг</p>
    </div>
    @endif

    <!-- System Information -->
    <div class="bg-gray-50 rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Тизим маълумотлари</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <label class="block text-sm font-medium text-gray-500">Яратган фойдаланувчи</label>
                <p class="mt-1 text-gray-900">{{ $kengashHulosa->creator->name ?? 'Номаълум' }}</p>
                <p class="text-xs text-gray-500">{{ $kengashHulosa->created_at->format('d.m.Y H:i') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Сўнгги ўзгартирган</label>
                <p class="mt-1 text-gray-900">{{ $kengashHulosa->updater->name ?? 'Номаълум' }}</p>
                <p class="text-xs text-gray-500">{{ $kengashHulosa->updated_at->format('d.m.Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize feather icons
    feather.replace();
</script>
@endpush
