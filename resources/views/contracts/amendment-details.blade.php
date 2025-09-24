@extends('layouts.app')

@section('title', 'Qo\'shimcha kelishuv tafsilotlari - ' . $amendment->amendment_number)
@section('page-title', 'Qo\'shimcha kelishuv tafsilotlari')

@php
    // Get historical data including all previous amendments
    $previousAmendments = $contract->amendments()
        ->where('is_approved', true)
        ->where('amendment_date', '<', $amendment->amendment_date)
        ->orderBy('amendment_date', 'asc')
        ->get();

    // Start with original contract values
    $originalValues = [
        'total_amount' => $contract->total_amount,
        'initial_payment_percent' => $contract->initial_payment_percent ?? 20,
        'quarters_count' => $contract->quarters_count ?? 8,
        'completion_date' => $contract->completion_date
    ];

    // Apply all previous amendments to get the state before current amendment
    $beforeValues = $originalValues;
    foreach ($previousAmendments as $prevAmendment) {
        if ($prevAmendment->new_total_amount !== null) {
            $beforeValues['total_amount'] = $prevAmendment->new_total_amount;
        }
        if ($prevAmendment->new_initial_payment_percent !== null) {
            $beforeValues['initial_payment_percent'] = $prevAmendment->new_initial_payment_percent;
        }
        if ($prevAmendment->new_quarters_count !== null) {
            $beforeValues['quarters_count'] = $prevAmendment->new_quarters_count;
        }
        if ($prevAmendment->new_completion_date !== null) {
            $beforeValues['completion_date'] = $prevAmendment->new_completion_date;
        }
    }

    // Calculate current vs previous values
    $currentTotal = (float) $beforeValues['total_amount'];
    $newTotal = (float) ($amendment->new_total_amount ?? $currentTotal);
    $totalDiff = $newTotal - $currentTotal;

    $currentInitialPercent = (float) $beforeValues['initial_payment_percent'];
    $newInitialPercent = (float) ($amendment->new_initial_payment_percent ?? $currentInitialPercent);

    $currentQuarters = (int) $beforeValues['quarters_count'];
    $newQuarters = (int) ($amendment->new_quarters_count ?? $currentQuarters);

    $currentCompletionDate = $beforeValues['completion_date'];
    $newCompletionDate = $amendment->new_completion_date ?? $currentCompletionDate;

    function formatMoney($amount) {
        return number_format($amount, 0, '.', ' ') . ' so\'m';
    }

    function formatPercent($percent) {
        return number_format($percent, 1) . '%';
    }

    // Get all amendments for timeline
    $allAmendments = $contract->amendments()
        ->orderBy('amendment_date', 'desc')
        ->get();

    $hasHistory = $previousAmendments->count() > 0;
@endphp

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.payment_update', $contract) }}"
       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
        Shartnomaga qaytish
    </a>

    @if(!$amendment->is_approved)
    <a href="{{ route('contracts.amendments.edit', [$contract, $amendment]) }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="edit-2" class="w-4 h-4 mr-2"></i>
        Tahrirlash
    </a>

    <form method="POST" action="{{ route('contracts.amendments.approve', [$contract, $amendment]) }}" class="inline">
        @csrf
        <button type="submit"
                onclick="return confirm('Bu qo\'shimcha kelishuvni tasdiqlaysizmi? Tasdiqlangandan keyin o\'zgartirib bo\'lmaydi.')"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <i data-feather="check" class="w-4 h-4 mr-2"></i>
            Tasdiqlash
        </button>
    </form>

    <form method="POST" action="{{ route('contracts.amendments.delete', [$contract, $amendment]) }}" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit"
                onclick="return confirm('Bu qo\'shimcha kelishuvni o\'chirasizmi? Bu amal qaytarilmaydi!')"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
            <i data-feather="trash-2" class="w-4 h-4 mr-2"></i>
            O'chirish
        </button>
    </form>
    @else
    <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg">
        <i data-feather="check-circle" class="w-4 h-4 mr-2"></i>
        Tasdiqlangan
    </div>

    <!-- Create new amendment button -->
    <a href="{{ route('contracts.amendments.create', $contract) }}"
       class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <i data-feather="file-plus" class="w-4 h-4 mr-2"></i>
        Yangi kelishuv
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    @include('partials.flash-messages')

    <!-- Amendment Header with Contract Context -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-200">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                    <h1 class="text-2xl font-bold text-purple-900">
                        Qo'shimcha kelishuv: {{ $amendment->amendment_number }}
                    </h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $amendment->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        @if($amendment->is_approved)
                            <i data-feather="check-circle" class="w-4 h-4 mr-1"></i>
                            Tasdiqlangan
                        @else
                            <i data-feather="clock" class="w-4 h-4 mr-1"></i>
                            Tasdiqlanmagan
                        @endif
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-purple-700">
                    <div>
                        <span class="font-medium">Shartnoma:</span>
                        <span class="font-bold">{{ $contract->contract_number }}</span>
                    </div>
                    <div>
                        <span class="font-medium">Kelishuv sanasi:</span>
                        <span>{{ $amendment->amendment_date->format('d.m.Y') }}</span>
                    </div>
                    <div>
                        <span class="font-medium">Yaratilgan:</span>
                        <span>{{ $amendment->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($amendment->is_approved)
                    <div>
                        <span class="font-medium">Tasdiqlangan:</span>
                        <span>{{ $amendment->approved_at?->format('d.m.Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Amendment Number Badge -->
            <div class="text-right">
                <div class="bg-white rounded-lg p-3 border border-purple-200">
                    <div class="text-xs text-purple-600 mb-1">Ketma-ket raqami</div>
                    <div class="text-2xl font-bold text-purple-900">
                        #{{ $allAmendments->search(function($item) use ($amendment) {
                            return $item->id === $amendment->id;
                        }) + 1 }}
                    </div>
                    <div class="text-xs text-purple-500">
                        {{ $allAmendments->count() }} dan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historical Context Section -->
    @if($hasHistory)
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-6 border border-amber-200">
        <div class="flex items-center mb-4">
            <i data-feather="history" class="w-5 h-5 mr-2 text-amber-600"></i>
            <h3 class="text-lg font-bold text-amber-900">Tarixiy kontekst</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Original Values -->
            <div class="bg-white rounded-lg p-4 border border-amber-100">
                <h4 class="font-semibold text-amber-900 mb-3 flex items-center">
                    <i data-feather="archive" class="w-4 h-4 mr-2"></i>
                    Dastlabki qiymatlar
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Summa:</span>
                        <span class="font-medium">{{ formatMoney($originalValues['total_amount']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Boshlang'ich:</span>
                        <span class="font-medium">{{ formatPercent($originalValues['initial_payment_percent']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Choraklar:</span>
                        <span class="font-medium">{{ $originalValues['quarters_count'] }} ta</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Muddat:</span>
                        <span class="font-medium text-xs">
                            {{ $originalValues['completion_date']?->format('d.m.Y') ?? 'Yo\'q' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Previous State -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                <h4 class="font-semibold text-blue-900 mb-3 flex items-center">
                    <i data-feather="rewind" class="w-4 h-4 mr-2"></i>
                    Oldingi holat
                    <span class="ml-2 text-xs bg-blue-200 text-blue-700 px-2 py-1 rounded">
                        {{ $previousAmendments->count() }} kelishuv
                    </span>
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Summa:</span>
                        <span class="font-medium">{{ formatMoney($beforeValues['total_amount']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Boshlang'ich:</span>
                        <span class="font-medium">{{ formatPercent($beforeValues['initial_payment_percent']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Choraklar:</span>
                        <span class="font-medium">{{ $beforeValues['quarters_count'] }} ta</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Muddat:</span>
                        <span class="font-medium text-xs">
                            {{ $beforeValues['completion_date']?->format('d.m.Y') ?? 'Yo\'q' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Changes Summary -->
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
                <h4 class="font-semibold text-purple-900 mb-3 flex items-center">
                    <i data-feather="trending-up" class="w-4 h-4 mr-2"></i>
                    O'zgarishlar
                </h4>
                <div class="space-y-2 text-sm">
                    @if($totalDiff != 0)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Summa:</span>
                        <span class="font-medium {{ $totalDiff > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $totalDiff > 0 ? '+' : '' }}{{ formatMoney(abs($totalDiff)) }}
                        </span>
                    </div>
                    @endif

                    @if($newInitialPercent != $currentInitialPercent)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Boshlang'ich:</span>
                        <span class="font-medium {{ $newInitialPercent > $currentInitialPercent ? 'text-green-600' : 'text-red-600' }}">
                            {{ formatPercent(abs($newInitialPercent - $currentInitialPercent)) }}
                        </span>
                    </div>
                    @endif

                    @if($newQuarters != $currentQuarters)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Choraklar:</span>
                        <span class="font-medium {{ $newQuarters > $currentQuarters ? 'text-green-600' : 'text-red-600' }}">
                            {{ $newQuarters > $currentQuarters ? '+' : '' }}{{ $newQuarters - $currentQuarters }}
                        </span>
                    </div>
                    @endif

                    @if((!$newCompletionDate && $currentCompletionDate) || ($newCompletionDate && !$currentCompletionDate) || ($newCompletionDate && $currentCompletionDate && $newCompletionDate->format('Y-m-d') !== $currentCompletionDate->format('Y-m-d')))
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Muddat:</span>
                        <span class="font-medium text-blue-600 text-xs">
                            O'zgartirildi
                        </span>
                    </div>
                    @endif

                    @if($totalDiff == 0 && $newInitialPercent == $currentInitialPercent && $newQuarters == $currentQuarters && ((!$newCompletionDate && !$currentCompletionDate) || ($newCompletionDate && $currentCompletionDate && $newCompletionDate->format('Y-m-d') === $currentCompletionDate->format('Y-m-d'))))
                    <div class="text-gray-500 italic">
                        Asosiy parametrlarda o'zgarish yo'q
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Changes Comparison -->
    <div class="bg-white rounded-2xl shadow-lg border">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="git-compare" class="w-6 h-6 mr-3 text-blue-600"></i>
                O'zgarishlar taqqoslashi
                @if($hasHistory)
                    <span class="ml-2 text-sm font-normal text-gray-600">(oldingi holatga nisbatan)</span>
                @endif
            </h2>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Before Values -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i data-feather="file-text" class="w-5 h-5 mr-2 text-gray-600"></i>
                        {{ $hasHistory ? 'Oldingi qiymatlar' : 'Joriy qiymatlar' }}
                    </h3>

                    <div class="space-y-4">
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Jami summa:</span>
                                <span class="font-bold text-gray-900 text-lg">{{ formatMoney($currentTotal) }}</span>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Boshlang'ich to'lov:</span>
                                <span class="font-bold text-gray-900">{{ formatPercent($currentInitialPercent) }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ formatMoney($currentTotal * ($currentInitialPercent / 100)) }}
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Choraklar soni:</span>
                                <span class="font-bold text-gray-900">{{ $currentQuarters }} ta</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Chorak to'lovi: {{ formatMoney($currentQuarters > 0 ? ($currentTotal * (100 - $currentInitialPercent) / 100) / $currentQuarters : 0) }}
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Yakunlash sanasi:</span>
                                <span class="font-bold text-gray-900">
                                    {{ $currentCompletionDate?->format('d.m.Y') ?? 'Belgilanmagan' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Values -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                    <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center">
                        <i data-feather="edit" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Yangi qiymatlar
                        @if($amendment->is_approved)
                            <span class="ml-2 bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Qo'llanilgan</span>
                        @endif
                    </h3>

                    <div class="space-y-4">
                        <div class="bg-white rounded-lg p-4 border {{ $totalDiff != 0 ? ($totalDiff > 0 ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50') : 'border-blue-200' }}">
                            <div class="flex justify-between items-center">
                                <span class="text-blue-700 font-medium">Jami summa:</span>
                                <div class="text-right">
                                    <span class="font-bold text-blue-900 text-lg">{{ formatMoney($newTotal) }}</span>
                                    @if($totalDiff != 0)
                                    <div class="text-sm {{ $totalDiff > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $totalDiff > 0 ? '+' : '' }}{{ formatMoney(abs($totalDiff)) }}
                                        <span class="text-xs">({{ $totalDiff > 0 ? '↗' : '↘' }} {{ round(abs($totalDiff) / $currentTotal * 100, 1) }}%)</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-4 border {{ $newInitialPercent != $currentInitialPercent ? ($newInitialPercent > $currentInitialPercent ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50') : 'border-blue-200' }}">
                            <div class="flex justify-between items-center">
                                <span class="text-blue-700 font-medium">Boshlang'ich to'lov:</span>
                                <div class="text-right">
                                    <span class="font-bold text-blue-900">{{ formatPercent($newInitialPercent) }}</span>
                                    @if($newInitialPercent != $currentInitialPercent)
                                    <div class="text-sm {{ $newInitialPercent > $currentInitialPercent ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $newInitialPercent > $currentInitialPercent ? '+' : '' }}{{ formatPercent($newInitialPercent - $currentInitialPercent) }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-xs text-blue-600 mt-1">
                                {{ formatMoney($newTotal * ($newInitialPercent / 100)) }}
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-4 border {{ $newQuarters != $currentQuarters ? ($newQuarters > $currentQuarters ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50') : 'border-blue-200' }}">
                            <div class="flex justify-between items-center">
                                <span class="text-blue-700 font-medium">Choraklar soni:</span>
                                <div class="text-right">
                                    <span class="font-bold text-blue-900">{{ $newQuarters }} ta</span>
                                    @if($newQuarters != $currentQuarters)
                                    <div class="text-sm {{ $newQuarters > $currentQuarters ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $newQuarters > $currentQuarters ? '+' : '' }}{{ $newQuarters - $currentQuarters }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-xs text-blue-600 mt-1">
                                Chorak to'lovi: {{ formatMoney($newQuarters > 0 ? ($newTotal * (100 - $newInitialPercent) / 100) / $newQuarters : 0) }}
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <div class="flex justify-between items-center">
                                <span class="text-blue-700 font-medium">Yakunlash sanasi:</span>
                                <span class="font-bold text-blue-900">
                                    {{ $newCompletionDate?->format('d.m.Y') ?? 'Belgilanmagan' }}
                                </span>
                            </div>
                            @if($newCompletionDate && $currentCompletionDate && $newCompletionDate->format('Y-m-d') !== $currentCompletionDate->format('Y-m-d'))
                            <div class="text-xs text-blue-600 mt-1">
                                Oldingi: {{ $currentCompletionDate->format('d.m.Y') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Amendment Details -->
    <div class="bg-white rounded-2xl shadow-lg border">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="file-plus" class="w-6 h-6 mr-3 text-purple-600"></i>
                Kelishuv tafsilotlari
            </h2>
        </div>

        <div class="p-8 space-y-6">
            <!-- Reason -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-semibold text-yellow-900 mb-2 flex items-center">
                    <i data-feather="help-circle" class="w-4 h-4 mr-2"></i>
                    O'zgartirish sababi
                </h4>
                <p class="text-yellow-800">{{ $amendment->reason }}</p>
            </div>

            <!-- Description -->
            @if($amendment->description)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                    <i data-feather="file-text" class="w-4 h-4 mr-2"></i>
                    Qo'shimcha ma'lumot
                </h4>
                <p class="text-gray-700">{{ $amendment->description }}</p>
            </div>
            @endif

            <!-- Payment Impact -->
            @if($amendment->is_approved)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-semibold text-green-900 mb-2 flex items-center">
                    <i data-feather="trending-up" class="w-4 h-4 mr-2"></i>
                    To'lov ta'siri
                </h4>
                <div class="text-green-800 space-y-2">
                    @if($totalDiff != 0)
                    <p class="flex items-center">
                        <i data-feather="{{ $totalDiff > 0 ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4 mr-2 {{ $totalDiff > 0 ? 'text-green-600' : 'text-red-600' }}"></i>
                        Jami summa {{ $totalDiff > 0 ? 'oshirildi' : 'kamaytirildi' }}: {{ formatMoney(abs($totalDiff)) }}
                    </p>
                    @endif

                    @if($newInitialPercent != $currentInitialPercent)
                    <p class="flex items-center">
                        <i data-feather="{{ $newInitialPercent > $currentInitialPercent ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4 mr-2 {{ $newInitialPercent > $currentInitialPercent ? 'text-green-600' : 'text-red-600' }}"></i>
                        Boshlang'ich to'lov foizi {{ $newInitialPercent > $currentInitialPercent ? 'oshirildi' : 'kamaytirildi' }}
                        ({{ formatPercent(abs($newInitialPercent - $currentInitialPercent)) }})
                    </p>
                    @endif

                    @if($newQuarters != $currentQuarters)
                    <p class="flex items-center">
                        <i data-feather="{{ $newQuarters > $currentQuarters ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4 mr-2 {{ $newQuarters > $currentQuarters ? 'text-green-600' : 'text-red-600' }}"></i>
                        Choraklar soni {{ $newQuarters > $currentQuarters ? 'oshirildi' : 'kamaytirildi' }}
                        ({{ abs($newQuarters - $currentQuarters) }} ta)
                    </p>
                    @endif

                    <div class="mt-4 pt-3 border-t border-green-300">
                        <p class="font-medium flex items-center">
                            <i data-feather="check-circle" class="w-4 h-4 mr-2 text-green-600"></i>
                            Bu o'zgarishlar joriy shartnomaga qo'llanilgan va yangi to'lov jadvali yaratish mumkin.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Author Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                    <i data-feather="user" class="w-4 h-4 mr-2"></i>
                    Yaratuvchi ma'lumotlari
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                    <div>
                        <span class="font-medium">Yaratgan:</span> {{ $amendment->createdBy->name ?? 'Noma\'lum' }}
                    </div>
                    <div>
                        <span class="font-medium">Yaratilgan vaqt:</span> {{ $amendment->created_at->format('d.m.Y H:i') }}
                    </div>
                    @if($amendment->is_approved)
                    <div>
                        <span class="font-medium">Tasdiqlagan:</span> {{ $amendment->approvedBy->name ?? 'Noma\'lum' }}
                    </div>
                    <div>
                        <span class="font-medium">Tasdiqlangan vaqt:</span> {{ $amendment->approved_at?->format('d.m.Y H:i') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Amendments Timeline -->
    <div class="bg-white rounded-2xl shadow-lg border">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="git-commit" class="w-6 h-6 mr-3 text-indigo-600"></i>
                Barcha o'zgarishlar tarixi
                <span class="ml-3 bg-indigo-100 text-indigo-800 text-sm px-3 py-1 rounded-full">
                    {{ $allAmendments->count() }} ta kelishuv
                </span>
            </h2>
        </div>

        <div class="p-8">
            <div class="space-y-6">
                @foreach($allAmendments as $index => $histAmendment)
                <div class="relative">
                    <!-- Timeline connector -->
                    @if(!$loop->last)
                    <div class="absolute left-6 top-16 w-0.5 h-16 bg-gray-200"></div>
                    @endif

                    <div class="flex items-start space-x-4 p-6 rounded-xl border-2 transition-all hover:shadow-md
                        {{ $histAmendment->id === $amendment->id ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-100' : 'bg-gray-50 border-gray-200' }}">

                        <!-- Timeline dot -->
                        <div class="flex-shrink-0 mt-1">
                            @if($histAmendment->id === $amendment->id)
                                <div class="w-4 h-4 bg-blue-600 rounded-full ring-4 ring-blue-200"></div>
                            @elseif($histAmendment->is_approved)
                                <div class="w-4 h-4 bg-green-500 rounded-full ring-4 ring-green-200"></div>
                            @else
                                <div class="w-4 h-4 bg-yellow-500 rounded-full ring-4 ring-yellow-200"></div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-grow">
                            <div class="flex items-start justify-between">
                                <div class="flex-grow">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h4 class="font-bold text-lg {{ $histAmendment->id === $amendment->id ? 'text-blue-900' : 'text-gray-900' }}">
                                            {{ $histAmendment->amendment_number }}
                                        </h4>

                                        @if($histAmendment->id === $amendment->id)
                                            <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-medium">
                                                <i data-feather="eye" class="w-3 h-3 inline mr-1"></i>
                                                Joriy
                                            </span>
                                        @endif

                                        <span class="text-sm {{ $histAmendment->id === $amendment->id ? 'text-blue-600' : 'text-gray-500' }}">
                                            {{ $histAmendment->amendment_date->format('d.m.Y') }}
                                        </span>

                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            {{ $histAmendment->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $histAmendment->is_approved ? 'Tasdiqlangan' : 'Kutilmoqda' }}
                                        </span>
                                    </div>

                                    <p class="text-sm {{ $histAmendment->id === $amendment->id ? 'text-blue-800' : 'text-gray-600' }} mb-3">
                                        {{ $histAmendment->reason }}
                                    </p>

                                    <!-- Changes summary -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                        @if($histAmendment->new_total_amount)
                                        <div class="bg-white rounded p-2 border">
                                            <span class="text-gray-500">Yangi summa:</span>
                                            <div class="font-medium text-green-600">{{ formatMoney($histAmendment->new_total_amount) }}</div>
                                        </div>
                                        @endif

                                        @if($histAmendment->new_initial_payment_percent)
                                        <div class="bg-white rounded p-2 border">
                                            <span class="text-gray-500">Boshlang'ich:</span>
                                            <div class="font-medium text-purple-600">{{ formatPercent($histAmendment->new_initial_payment_percent) }}</div>
                                        </div>
                                        @endif

                                        @if($histAmendment->new_quarters_count)
                                        <div class="bg-white rounded p-2 border">
                                            <span class="text-gray-500">Choraklar:</span>
                                            <div class="font-medium text-indigo-600">{{ $histAmendment->new_quarters_count }} ta</div>
                                        </div>
                                        @endif

                                        @if($histAmendment->new_completion_date)
                                        <div class="bg-white rounded p-2 border">
                                            <span class="text-gray-500">Muddat:</span>
                                            <div class="font-medium text-blue-600">{{ $histAmendment->new_completion_date->format('d.m.Y') }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-2 ml-4">
                                    @if($histAmendment->id !== $amendment->id)
                                        <a href="{{ route('contracts.amendments.show', [$contract, $histAmendment]) }}"
                                           class="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors"
                                           title="Ko'rish">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    @if(!$histAmendment->is_approved && $histAmendment->id !== $amendment->id)
                                        <a href="{{ route('contracts.amendments.edit', [$contract, $histAmendment]) }}"
                                           class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors"
                                           title="Tahrirlash">
                                            <i data-feather="edit-2" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    <div class="text-xs text-gray-400">
                                        #{{ $index + 1 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Original contract card -->
                <div class="relative">
                    <div class="flex items-start space-x-4 p-6 rounded-xl border-2 border-green-200 bg-green-50">
                        <div class="flex-shrink-0 mt-1">
                            <div class="w-4 h-4 bg-green-600 rounded-full ring-4 ring-green-200"></div>
                        </div>

                        <div class="flex-grow">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-bold text-lg text-green-900 mb-2">
                                        Asl shartnoma: {{ $contract->contract_number }}
                                    </h4>
                                    <p class="text-sm text-green-700 mb-3">
                                        {{ $contract->contract_date->format('d.m.Y') }}da tuzilgan
                                    </p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                        <div class="bg-white rounded p-2 border border-green-200">
                                            <span class="text-gray-500">Dastlabki summa:</span>
                                            <div class="font-medium text-green-600">{{ formatMoney($originalValues['total_amount']) }}</div>
                                        </div>
                                        <div class="bg-white rounded p-2 border border-green-200">
                                            <span class="text-gray-500">Boshlang'ich:</span>
                                            <div class="font-medium text-green-600">{{ formatPercent($originalValues['initial_payment_percent']) }}</div>
                                        </div>
                                        <div class="bg-white rounded p-2 border border-green-200">
                                            <span class="text-gray-500">Choraklar:</span>
                                            <div class="font-medium text-green-600">{{ $originalValues['quarters_count'] }} ta</div>
                                        </div>
                                        <div class="bg-white rounded p-2 border border-green-200">
                                            <span class="text-gray-500">Muddat:</span>
                                            <div class="font-medium text-green-600">
                                                {{ $originalValues['completion_date']?->format('d.m.Y') ?? 'Yo\'q' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-xs text-green-600 font-medium">
                                    Asl holat
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Schedule Actions -->
    @if($amendment->is_approved)
    <div class="bg-white rounded-2xl shadow-lg border">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="calendar" class="w-6 h-6 mr-3 text-indigo-600"></i>
                To'lov jadvali amaliyotlari
            </h2>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-200">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4 class="font-semibold text-indigo-900 mb-2">Yangi jadval yaratish</h4>
                            <p class="text-indigo-700 text-sm mb-4">
                                Tasdiqlangan o'zgarishlar asosida yangi to'lov jadvali yarating
                            </p>
                        </div>
                        <i data-feather="plus-circle" class="w-8 h-8 text-indigo-600"></i>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-indigo-600">Yangi jami summa:</span>
                            <span class="font-bold text-indigo-900">{{ formatMoney($newTotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-indigo-600">Yangi choraklar soni:</span>
                            <span class="font-bold text-indigo-900">{{ $newQuarters }} ta</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-indigo-600">Chorak to'lovi:</span>
                            <span class="font-bold text-indigo-900">
                                {{ $newQuarters > 0 ? formatMoney(($newTotal * (100 - $newInitialPercent) / 100) / $newQuarters) : '0 so\'m' }}
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('contracts.create-schedule', $contract) }}?amendment_id={{ $amendment->id }}"
                       class="inline-flex items-center justify-center w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                        <i data-feather="calendar-plus" class="w-5 h-5 mr-2"></i>
                        Jadval yaratish
                    </a>
                </div>

                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Joriy jadval</h4>
                            <p class="text-gray-700 text-sm mb-4">
                                Mavjud to'lov jadvalini ko'rish va boshqarish
                            </p>
                        </div>
                        <i data-feather="list" class="w-8 h-8 text-gray-600"></i>
                    </div>

                    <div class="space-y-3 mb-6">
                        @if(isset($paymentData['summary_cards']))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Jami plan:</span>
                            <span class="font-bold text-gray-900">{{ $paymentData['summary_cards']['total_plan_formatted'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">To'langan:</span>
                            <span class="font-bold text-green-700">{{ $paymentData['summary_cards']['total_paid_formatted'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Qolgan:</span>
                            <span class="font-bold text-yellow-700">{{ $paymentData['summary_cards']['current_debt_formatted'] }}</span>
                        </div>
                        @endif
                    </div>

                    <a href="{{ route('contracts.payment_update', $contract) }}"
                       class="inline-flex items-center justify-center w-full px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
                        <i data-feather="eye" class="w-5 h-5 mr-2"></i>
                        Jadvalni ko'rish
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Contract Summary -->
    @if(isset($paymentData['summary_cards']))
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
        <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center">
            <i data-feather="bar-chart-3" class="w-5 h-5 mr-2"></i>
            Joriy shartnoma holati
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-4 text-center border border-blue-100">
                <div class="text-sm text-blue-700 font-medium">Jami plan</div>
                <div class="font-bold text-blue-900 text-lg">{{ $paymentData['summary_cards']['total_plan_formatted'] }}</div>
                <div class="text-xs text-blue-600 mt-1">Shartnoma bo'yicha</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center border border-green-100">
                <div class="text-sm text-green-700 font-medium">To'langan</div>
                <div class="font-bold text-green-900 text-lg">{{ $paymentData['summary_cards']['total_paid_formatted'] }}</div>
                <div class="text-xs text-green-600 mt-1">Haqiqiy to'lovlar</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center border border-yellow-100">
                <div class="text-sm text-yellow-700 font-medium">Joriy qarz</div>
                <div class="font-bold text-yellow-900 text-lg">{{ $paymentData['summary_cards']['current_debt_formatted'] }}</div>
                <div class="text-xs text-yellow-600 mt-1">To'lanmagan</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center border border-indigo-100">
                <div class="text-sm text-indigo-700 font-medium">Bajarilish</div>
                <div class="font-bold text-indigo-900 text-lg">{{ $paymentData['summary_cards']['completion_percent'] }}%</div>
                <div class="text-xs text-indigo-600 mt-1">Umumiy holat</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl p-6 border border-gray-200">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tezkor amallar</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('contracts.amendments.create', $contract) }}"
               class="inline-flex items-center justify-center px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i data-feather="file-plus" class="w-5 h-5 mr-2"></i>
                Yangi kelishuv yaratish
            </a>

            <a href="{{ route('contracts.payment_update', $contract) }}"
               class="inline-flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-feather="credit-card" class="w-5 h-5 mr-2"></i>
                To'lov boshqaruvi
            </a>

            <a href="{{ route('contracts.show', $contract) }}"
               class="inline-flex items-center justify-center px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i data-feather="file-text" class="w-5 h-5 mr-2"></i>
                Shartnoma tafsilotlari
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
@endsection

@push('scripts')
<script src="https://unpkg.com/feather-icons"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
@endpush
