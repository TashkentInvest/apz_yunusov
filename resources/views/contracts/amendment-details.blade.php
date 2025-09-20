@extends('layouts.app')

@section('title', 'Qo\'shimcha kelishuv tafsilotlari - ' . $amendment->amendment_number)
@section('page-title', 'Qo\'shimcha kelishuv tafsilotlari')

@php
    // Calculate current vs new values
    $currentTotal = (float) $contract->total_amount;
    $newTotal = (float) ($amendment->new_total_amount ?? $currentTotal);
    $totalDiff = $newTotal - $currentTotal;

    $currentInitialPercent = (float) ($contract->initial_payment_percent ?? 20);
    $newInitialPercent = (float) ($amendment->new_initial_payment_percent ?? $currentInitialPercent);

    $currentQuarters = (int) ($contract->quarters_count ?? 8);
    $newQuarters = (int) ($amendment->new_quarters_count ?? $currentQuarters);

    function formatMoney($amount) {
        return number_format($amount, 0, '.', ' ') . ' so\'m';
    }

    function formatPercent($percent) {
        return number_format($percent, 1) . '%';
    }
@endphp

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.payment_update', $contract) }}"
       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
        Shartnomaga qaytish
    </a>

    @if(!$amendment->is_approved)
    <form method="POST" action="{{ route('contracts.amendments.approve', [$contract, $amendment]) }}" class="inline">
        @csrf
        <button type="submit"
                onclick="return confirm('Bu qo\'shimcha kelishuvni tasdiqlaysizmi? Tasdiqlangandan keyin o\'zgartirib bo\'lmaydi.')"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <i data-feather="check" class="w-4 h-4 mr-2"></i>
            Tasdiqlash
        </button>
    </form>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-8">

    @include('partials.flash-messages')

    <!-- Amendment Header -->
    <div class="bg-purple-50 rounded-xl p-6 border border-purple-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-purple-900">
                    Qo'shimcha kelishuv #{{ $amendment->amendment_number }}
                </h1>
                <p class="text-purple-700 mt-1">
                    Shartnoma: {{ $contract->contract_number }}
                </p>
            </div>
            <div class="text-right">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $amendment->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    @if($amendment->is_approved)
                        <i data-feather="check-circle" class="w-4 h-4 mr-1"></i>
                        Tasdiqlangan
                    @else
                        <i data-feather="clock" class="w-4 h-4 mr-1"></i>
                        Tasdiqlanmagan
                    @endif
                </div>
                <div class="text-sm text-purple-600 mt-1">
                    {{ $amendment->amendment_date->format('d.m.Y') }}
                </div>
            </div>
        </div>

        <!-- Amendment Info -->
        <div class="bg-white rounded-lg p-4 border border-purple-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Yaratilgan:</span>
                    <span class="font-medium">{{ $amendment->created_at->format('d.m.Y H:i') }}</span>
                </div>
                @if($amendment->is_approved)
                <div>
                    <span class="text-gray-600">Tasdiqlangan:</span>
                    <span class="font-medium">{{ $amendment->approved_at?->format('d.m.Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Changes Comparison -->
    <div class="bg-white rounded-2xl shadow-lg border">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-feather="git-compare" class="w-6 h-6 mr-3 text-blue-600"></i>
                O'zgarishlar taqqoslashi
            </h2>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Current Values -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i data-feather="file-text" class="w-5 h-5 mr-2 text-gray-600"></i>
                        Joriy qiymatlar
                    </h3>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-600">Jami summa:</span>
                            <span class="font-bold text-gray-900">{{ formatMoney($currentTotal) }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-600">Boshlang'ich to'lov:</span>
                            <span class="font-bold text-gray-900">{{ formatPercent($currentInitialPercent) }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-600">Choraklar soni:</span>
                            <span class="font-bold text-gray-900">{{ $currentQuarters }} ta</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-600">Yakunlash sanasi:</span>
                            <span class="font-bold text-gray-900">
                                {{ $contract->completion_date?->format('d.m.Y') ?? 'Belgilanmagan' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- New Values -->
                <div class="bg-blue-50 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center">
                        <i data-feather="edit" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Yangi qiymatlar
                    </h3>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-blue-200">
                            <span class="text-blue-700">Jami summa:</span>
                            <div class="text-right">
                                <span class="font-bold text-blue-900">{{ formatMoney($newTotal) }}</span>
                                @if($totalDiff != 0)
                                <div class="text-xs {{ $totalDiff > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $totalDiff > 0 ? '+' : '' }}{{ formatMoney(abs($totalDiff)) }}
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-blue-200">
                            <span class="text-blue-700">Boshlang'ich to'lov:</span>
                            <div class="text-right">
                                <span class="font-bold text-blue-900">{{ formatPercent($newInitialPercent) }}</span>
                                @if($newInitialPercent != $currentInitialPercent)
                                <div class="text-xs {{ $newInitialPercent > $currentInitialPercent ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $newInitialPercent > $currentInitialPercent ? '+' : '' }}{{ formatPercent($newInitialPercent - $currentInitialPercent) }}
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-blue-200">
                            <span class="text-blue-700">Choraklar soni:</span>
                            <div class="text-right">
                                <span class="font-bold text-blue-900">{{ $newQuarters }} ta</span>
                                @if($newQuarters != $currentQuarters)
                                <div class="text-xs {{ $newQuarters > $currentQuarters ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $newQuarters > $currentQuarters ? '+' : '' }}{{ $newQuarters - $currentQuarters }}
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-blue-200">
                            <span class="text-blue-700">Yakunlash sanasi:</span>
                            <span class="font-bold text-blue-900">
                                {{ $amendment->new_completion_date?->format('d.m.Y') ?? ($contract->completion_date?->format('d.m.Y') ?? 'Belgilanmagan') }}
                            </span>
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
                <h4 class="font-semibold text-yellow-900 mb-2">O'zgartirish sababi</h4>
                <p class="text-yellow-800">{{ $amendment->reason }}</p>
            </div>

            <!-- Description -->
            @if($amendment->description)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">Qo'shimcha ma'lumot</h4>
                <p class="text-gray-700">{{ $amendment->description }}</p>
            </div>
            @endif

            <!-- Payment Impact -->
            @if($amendment->is_approved)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-semibold text-green-900 mb-2">To'lov ta'siri</h4>
                <div class="text-green-800 space-y-2">
                    @if($totalDiff != 0)
                    <p>• Jami summa {{ $totalDiff > 0 ? 'oshirildi' : 'kamaytirildi' }}: {{ formatMoney(abs($totalDiff)) }}</p>
                    @endif

                    @if($newInitialPercent != $currentInitialPercent)
                    <p>• Boshlang'ich to'lov foizi {{ $newInitialPercent > $currentInitialPercent ? 'oshirildi' : 'kamaytirildi' }}</p>
                    @endif

                    @if($newQuarters != $currentQuarters)
                    <p>• Choraklar soni {{ $newQuarters > $currentQuarters ? 'oshirildi' : 'kamaytirildi' }}</p>
                    @endif

                    <p class="font-medium pt-2 border-t border-green-300">
                        Bu o'zgarishlar joriy shartnomaga qo'llanilgan va yangi to'lov jadvali yaratish mumkin.
                    </p>
                </div>
            </div>
            @endif
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
                <div class="bg-indigo-50 rounded-lg p-4">
                    <h4 class="font-semibold text-indigo-900 mb-3">Yangi jadval yaratish</h4>
                    <p class="text-indigo-700 text-sm mb-4">
                        Tasdiqlangan o'zgarishlar asosida yangi to'lov jadvali yarating
                    </p>
                    <a href="{{ route('contracts.create-schedule', $contract) }}?amendment_id={{ $amendment->id }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                        Jadval tuzish
                    </a>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Joriy jadval</h4>
                    <p class="text-gray-700 text-sm mb-4">
                        Mavjud to'lov jadvalini ko'rish va boshqarish
                    </p>
                    <a href="{{ route('contracts.payment_update', $contract) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <i data-feather="eye" class="w-4 h-4 mr-2"></i>
                        Jadvalni ko'rish
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Contract Summary -->
    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
        <h3 class="text-lg font-bold text-blue-900 mb-4">Joriy shartnoma holati</h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-sm text-blue-700">Jami plan</div>
                <div class="font-bold text-blue-900">{{ $paymentData['summary_cards']['total_plan_formatted'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-blue-700">To'langan</div>
                <div class="font-bold text-green-700">{{ $paymentData['summary_cards']['total_paid_formatted'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-blue-700">Joriy qarz</div>
                <div class="font-bold text-yellow-700">{{ $paymentData['summary_cards']['current_debt_formatted'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-blue-700">Bajarilish</div>
                <div class="font-bold text-indigo-700">{{ $paymentData['summary_cards']['completion_percent'] }}%</div>
            </div>
        </div>
    </div>
</div>
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
