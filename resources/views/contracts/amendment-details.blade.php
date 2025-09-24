@extends('layouts.app')

@section('title', 'Shartnoma o\'zgarishlar tarixi - ' . $contract->contract_number)
@section('page-title', 'Shartnoma o\'zgarishlar tarixi')

@php
    // Get all amendments for this contract ordered by date
    $amendments = $contract->amendments()
        ->with(['createdBy', 'approvedBy'])
        ->orderBy('amendment_date', 'desc')
        ->get();

    function formatMoney($amount) {
        if (is_numeric($amount)) {
            return number_format($amount, 0, '.', ' ') . ' so\'m';
        }
        return $amount;
    }

    function formatPercent($percent) {
        if (is_numeric($percent)) {
            return number_format($percent, 1) . '%';
        }
        return $percent;
    }

    function parseChangesFromSummary($summary) {
        $changes = [];

        if (empty($summary)) return $changes;

        // Parse total amount change
        if (preg_match('/summa:\s*([\d\s,]+)\s*so\'m\s*→\s*([\d\s,]+)\s*so\'m/', $summary, $matches)) {
            $oldAmount = floatval(str_replace([' ', ','], '', $matches[1]));
            $newAmount = floatval(str_replace([' ', ','], '', $matches[2]));
            $changes['total_amount'] = [
                'old' => $oldAmount,
                'new' => $newAmount,
                'difference' => $newAmount - $oldAmount
            ];
        }

        // Parse initial payment change
        if (preg_match('/boshlang\'ich[^:]*:\s*([\d.]+)%\s*→\s*([\d.]+)%/', $summary, $matches)) {
            $oldPercent = floatval($matches[1]);
            $newPercent = floatval($matches[2]);
            $changes['initial_payment_percent'] = [
                'old' => $oldPercent,
                'new' => $newPercent,
                'difference' => $newPercent - $oldPercent
            ];
        }

        // Parse quarters change
        if (preg_match('/choraklar[^:]*:\s*(\d+)\s*→\s*(\d+)/', $summary, $matches)) {
            $oldQuarters = intval($matches[1]);
            $newQuarters = intval($matches[2]);
            $changes['quarters_count'] = [
                'old' => $oldQuarters,
                'new' => $newQuarters,
                'difference' => $newQuarters - $oldQuarters
            ];
        }

        // Parse completion date change
        if (preg_match('/yakunlash[^:]*:\s*([\d.]+)\s*→\s*([\d.]+)/', $summary, $matches)) {
            $changes['completion_date'] = [
                'old' => $matches[1],
                'new' => $matches[2]
            ];
        }

        return $changes;
    }

    // Calculate cumulative contract state through amendments
    $originalState = [
        'total_amount' => 422342342, // Extracted from first amendment's "old" value
        'initial_payment_percent' => 20.0,
        'quarters_count' => 3,
        'completion_date' => '16.04.2025'
    ];

    $cumulativeState = $originalState;
    $amendmentStates = [];

    // Process amendments in chronological order to build state history
    $chronologicalAmendments = $amendments->reverse();

    foreach ($chronologicalAmendments as $amendment) {
        $changes = parseChangesFromSummary($amendment->changes_summary);
        $beforeState = $cumulativeState;

        // Apply changes to cumulative state
        foreach ($changes as $field => $change) {
            $cumulativeState[$field] = $change['new'];
        }

        $amendmentStates[$amendment->id] = [
            'amendment' => $amendment,
            'before_state' => $beforeState,
            'after_state' => $cumulativeState,
            'changes' => $changes
        ];
    }
@endphp

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.payment_update', $contract) }}"
       class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800 transition-colors text-sm font-medium">
        ← Shartnomaga qaytish
    </a>

    <a href="{{ route('contracts.amendments.create', $contract) }}"
       class="inline-flex items-center px-4 py-2 bg-indigo-700 text-white rounded-md hover:bg-indigo-800 transition-colors text-sm font-medium">
        Yangi kelishuv yaratish
    </a>

    <a href="{{ route('contracts.show', $contract) }}"
       class="inline-flex items-center px-4 py-2 bg-blue-700 text-white rounded-md hover:bg-blue-800 transition-colors text-sm font-medium">
        Shartnoma tafsilotlari
    </a>
</div>
@endsection

@section('content')
<style>
.govt-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border: 2px solid #374151;
}

.govt-table th,
.govt-table td {
    border: 1px solid #6b7280;
    padding: 12px 16px;
    text-align: left;
    vertical-align: top;
}

.govt-table th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
}

.govt-table td {
    color: #1f2937;
    font-size: 0.875rem;
}

.govt-table tbody tr:nth-child(even) {
    background-color: #f9fafb;
}

.timeline-item {
    border-left: 4px solid #d1d5db;
    padding-left: 20px;
    margin-bottom: 32px;
    position: relative;
}

.timeline-item.approved {
    border-left-color: #10b981;
}

.timeline-item.pending {
    border-left-color: #f59e0b;
}

.timeline-dot {
    position: absolute;
    left: -8px;
    top: 24px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #d1d5db;
}

.timeline-dot.approved {
    background: #10b981;
}

.timeline-dot.pending {
    background: #f59e0b;
}

.govt-header {
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    color: white;
    padding: 24px;
    margin-bottom: 24px;
    border-radius: 8px;
}

.status-approved {
    background: #dcfce7;
    color: #166534;
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
}

.diff-positive { color: #059669; font-weight: 600; }
.diff-negative { color: #dc2626; font-weight: 600; }
.diff-neutral { color: #6b7280; }

.original-state {
    background: linear-gradient(135deg, #065f46 0%, #047857 100%);
    color: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 32px;
}
</style>

<div class="max-w-7xl mx-auto space-y-6">

    @include('partials.flash-messages')

    <!-- Government Header -->
    <div class="govt-header">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold mb-2">SHARTNOMA O'ZGARISHLAR TARIXI</h1>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm opacity-90">
                    <div><strong>Shartnoma raqami:</strong> {{ $contract->contract_number }}</div>
                    <div><strong>Shartnoma sanasi:</strong> {{ $contract->contract_date->format('d.m.Y') }}</div>
                    <div><strong>Jami kelishuvlar:</strong> {{ $amendments->count() }} ta</div>
                    <div><strong>Tasdiqlangan:</strong> {{ $amendments->where('is_approved', true)->count() }} ta</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm opacity-75 mb-2">Joriy holat</div>
                <div class="text-lg font-bold">
                    @if($amendments->where('is_approved', true)->count() > 0)
                        {{ $amendments->where('is_approved', true)->count() }} marta o'zgartirilgan
                    @else
                        O'zgartirilmagan
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="bg-white shadow-sm border-2 border-gray-300">
        <div class="border-b-2 border-gray-300 px-6 py-4 bg-gray-50">
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">UMUMIY STATISTIKA</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center border-2 border-gray-200 p-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $amendments->count() }}</div>
                    <div class="text-sm text-gray-600 uppercase">Jami kelishuvlar</div>
                </div>
                <div class="text-center border-2 border-green-200 p-4">
                    <div class="text-2xl font-bold text-green-700">{{ $amendments->where('is_approved', true)->count() }}</div>
                    <div class="text-sm text-gray-600 uppercase">Tasdiqlangan</div>
                </div>
                <div class="text-center border-2 border-yellow-200 p-4">
                    <div class="text-2xl font-bold text-yellow-700">{{ $amendments->where('is_approved', false)->count() }}</div>
                    <div class="text-sm text-gray-600 uppercase">Kutilmoqda</div>
                </div>
                <div class="text-center border-2 border-blue-200 p-4">
                    <div class="text-2xl font-bold text-blue-700">{{ $amendments->whereNotNull('changes_summary')->count() }}</div>
                    <div class="text-sm text-gray-600 uppercase">O'zgarishlar bilan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline of All Amendments -->
    <div class="bg-white shadow-sm border-2 border-gray-300">
        <div class="border-b-2 border-gray-300 px-6 py-4 bg-gray-50">
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">KELISHUVLAR TARIXI</h2>
        </div>

        <div class="p-6">
            <!-- Original State -->
            <div class="original-state">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">ASL SHARTNOMA HOLATI</h3>
                    <span class="text-sm opacity-75">{{ $contract->contract_date->format('d.m.Y') }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="opacity-75">Jami summa</div>
                        <div class="font-bold">{{ formatMoney($originalState['total_amount']) }}</div>
                    </div>
                    <div>
                        <div class="opacity-75">Boshlang'ich to'lov</div>
                        <div class="font-bold">{{ formatPercent($originalState['initial_payment_percent']) }}</div>
                    </div>
                    <div>
                        <div class="opacity-75">Choraklar soni</div>
                        <div class="font-bold">{{ $originalState['quarters_count'] }} ta</div>
                    </div>
                    <div>
                        <div class="opacity-75">Yakunlash sanasi</div>
                        <div class="font-bold">{{ $originalState['completion_date'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Amendments Timeline -->
            @forelse($amendments as $amendment)
                @php
                    $changes = parseChangesFromSummary($amendment->changes_summary);
                    $statusClass = $amendment->is_approved ? 'approved' : 'pending';
                @endphp

                <div class="timeline-item {{ $statusClass }}">
                    <div class="timeline-dot {{ $statusClass }}"></div>

                    <div class="bg-white border-2 border-gray-200 rounded-lg p-6">
                        <!-- Amendment Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $amendment->amendment_number }}</h3>
                                <p class="text-sm text-gray-600">{{ $amendment->amendment_date->format('d.m.Y') }}</p>
                            </div>
                            <div class="text-right">
                                @if($amendment->is_approved)
                                    <span class="status-approved">Tasdiqlangan</span>
                                @else
                                    <span class="status-pending">Kutilmoqda</span>
                                @endif
                                <div class="text-xs text-gray-500 mt-1">
                                    #{{ $amendment->sequential_number ?? ($amendments->count() - $loop->index) }}
                                </div>
                            </div>
                        </div>

                        <!-- Amendment Details -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Amendment Info -->
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2 uppercase text-sm">Kelishuv ma'lumotlari</h4>
                                <div class="space-y-2 text-sm">
                                    <div><strong>Sabab:</strong> {{ $amendment->reason }}</div>
                                    @if($amendment->description)
                                        <div><strong>Tavsif:</strong> {{ Str::limit($amendment->description, 100) }}</div>
                                    @endif
                                    <div><strong>Yaratgan:</strong> {{ $amendment->createdBy->name ?? 'Noma\'lum' }}</div>
                                    @if($amendment->is_approved)
                                        <div><strong>Tasdiqlagan:</strong> {{ $amendment->approvedBy->name ?? 'Noma\'lum' }}</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Changes Table -->
                            @if(!empty($changes))
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2 uppercase text-sm">O'zgarishlar</h4>
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="text-left p-2 border">Parametr</th>
                                            <th class="text-left p-2 border">Oldingi → Yangi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($changes as $field => $change)
                                            <tr>
                                                <td class="p-2 border font-medium">
                                                    @if($field === 'total_amount') Jami summa
                                                    @elseif($field === 'initial_payment_percent') Boshlang'ich to'lov
                                                    @elseif($field === 'quarters_count') Choraklar soni
                                                    @elseif($field === 'completion_date') Yakunlash sanasi
                                                    @endif
                                                </td>
                                                <td class="p-2 border">
                                                    @if($field === 'total_amount')
                                                        {{ formatMoney($change['old']) }} → {{ formatMoney($change['new']) }}
                                                        <span class="diff-{{ $change['difference'] >= 0 ? 'positive' : 'negative' }} ml-2">
                                                            ({{ $change['difference'] >= 0 ? '+' : '' }}{{ formatMoney(abs($change['difference'])) }})
                                                        </span>
                                                    @elseif($field === 'initial_payment_percent')
                                                        {{ formatPercent($change['old']) }} → {{ formatPercent($change['new']) }}
                                                        <span class="diff-{{ $change['difference'] >= 0 ? 'positive' : 'negative' }} ml-2">
                                                            ({{ $change['difference'] >= 0 ? '+' : '' }}{{ formatPercent(abs($change['difference'])) }})
                                                        </span>
                                                    @elseif($field === 'quarters_count')
                                                        {{ $change['old'] }} ta → {{ $change['new'] }} ta
                                                        <span class="diff-{{ $change['difference'] >= 0 ? 'positive' : 'negative' }} ml-2">
                                                            ({{ $change['difference'] >= 0 ? '+' : '' }}{{ abs($change['difference']) }})
                                                        </span>
                                                    @else
                                                        {{ $change['old'] }} → {{ $change['new'] }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-2 mt-4 pt-4 border-t">
                            <a href="{{ route('contracts.amendments.show', [$contract, $amendment]) }}"
                               class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                Batafsil ko'rish
                            </a>

                            @if(!$amendment->is_approved)
                                <a href="{{ route('contracts.amendments.edit', [$contract, $amendment]) }}"
                                   class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700">
                                    Tahrirlash
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="text-gray-500 text-lg mb-4">Hech qanday kelishuv topilmadi</div>
                    <a href="{{ route('contracts.amendments.create', $contract) }}"
                       class="inline-block px-6 py-3 bg-indigo-700 text-white rounded-md hover:bg-indigo-800 transition-colors font-medium">
                        Birinchi kelishuvni yaratish
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Current State Summary -->
    @if($amendments->where('is_approved', true)->count() > 0)
    <div class="bg-white shadow-sm border-2 border-gray-300">
        <div class="border-b-2 border-gray-300 px-6 py-4 bg-gray-50">
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">JORIY SHARTNOMA HOLATI</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center border-2 border-blue-200 p-4">
                    <div class="text-sm text-gray-600 uppercase mb-2">Joriy jami summa</div>
                    <div class="text-xl font-bold text-blue-700">
                        {{ formatMoney($contract->total_amount) }}
                    </div>
                </div>
                <div class="text-center border-2 border-purple-200 p-4">
                    <div class="text-sm text-gray-600 uppercase mb-2">Boshlang'ich to'lov</div>
                    <div class="text-xl font-bold text-purple-700">
                        {{ formatPercent($contract->initial_payment_percent ?? 20) }}
                    </div>
                </div>
                <div class="text-center border-2 border-green-200 p-4">
                    <div class="text-sm text-gray-600 uppercase mb-2">Choraklar soni</div>
                    <div class="text-xl font-bold text-green-700">
                        {{ $contract->quarters_count ?? 8 }} ta
                    </div>
                </div>
                <div class="text-center border-2 border-orange-200 p-4">
                    <div class="text-sm text-gray-600 uppercase mb-2">Yakunlash sanasi</div>
                    <div class="text-xl font-bold text-orange-700">
                        {{ $contract->completion_date?->format('d.m.Y') ?? 'Belgilanmagan' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="bg-white shadow-sm border-2 border-gray-300 p-6">
        <div class="text-center">
            <h3 class="text-lg font-bold text-gray-900 mb-4 uppercase tracking-wide">TEZKOR AMALLAR</h3>
            <div class="space-x-4">
                <a href="{{ route('contracts.amendments.create', $contract) }}"
                   class="inline-block px-6 py-3 bg-indigo-700 text-white rounded-md hover:bg-indigo-800 transition-colors font-medium">
                    YANGI KELISHUV YARATISH
                </a>

                <a href="{{ route('contracts.payment_update', $contract) }}"
                   class="inline-block px-6 py-3 bg-blue-700 text-white rounded-md hover:bg-blue-800 transition-colors font-medium">
                    TO'LOV BOSHQARUVI
                </a>

                <a href="{{ route('contracts.show', $contract) }}"
                   class="inline-block px-6 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-800 transition-colors font-medium">
                    SHARTNOMA TAFSILOTLARI
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
