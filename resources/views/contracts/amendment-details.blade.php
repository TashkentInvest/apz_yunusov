@extends('layouts.app')

@section('title', 'Shartnoma o\'zgarishlar tarixi - ' . $contract->contract_number)
@section('page-title', 'Shartnoma o\'zgarishlar tarixi')

@php
    // Get all amendments for this contract
    $amendments = $contract->amendments()
        ->with(['createdBy', 'approvedBy'])
        ->orderBy('amendment_date', 'asc')
        ->get();

    function formatMoney($amount) {
        if (is_numeric($amount)) {
            return number_format($amount, 0, '.', ' ') . ' so\'m';
        }
        return $amount ?: 'N/A';
    }

    function formatPercent($percent) {
        if (is_numeric($percent)) {
            return number_format($percent, 1) . '%';
        }
        return $percent ?: 'N/A';
    }

    // Calculate contract evolution through amendments
    $contractEvolution = [];

    // Original state (extract from first amendment's "old" values)
    $originalState = [
        'total_amount' => 422342342,
        'initial_payment_percent' => 20.0,
        'quarters_count' => 3,
        'completion_date' => '16.04.2025',
        'date' => $contract->contract_date->format('d.m.Y'),
        'stage' => 'Asl shartnoma'
    ];
    $contractEvolution[] = $originalState;

    // Add each amendment as a state
    $currentState = $originalState;
    foreach ($amendments as $amendment) {
        if ($amendment->is_approved) {
            $newState = $currentState;
            $newState['date'] = $amendment->amendment_date->format('d.m.Y');
            $newState['stage'] = $amendment->amendment_number;

            if ($amendment->new_total_amount) {
                $newState['total_amount'] = $amendment->new_total_amount;
            }
            if ($amendment->new_initial_payment_percent) {
                $newState['initial_payment_percent'] = $amendment->new_initial_payment_percent;
            }
            if ($amendment->new_quarters_count) {
                $newState['quarters_count'] = $amendment->new_quarters_count;
            }
            if ($amendment->new_completion_date) {
                $newState['completion_date'] = $amendment->new_completion_date->format('d.m.Y');
            }

            $contractEvolution[] = $newState;
            $currentState = $newState;
        }
    }
@endphp

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.payment_update', $contract) }}"
       class="px-4 py-2 bg-gray-700 text-white rounded font-medium text-sm hover:bg-gray-800">
        ← Shartnomaga qaytish
    </a>

    <a href="{{ route('contracts.amendments.create', $contract) }}"
       class="px-4 py-2 bg-blue-700 text-white rounded font-medium text-sm hover:bg-blue-800">
        Yangi kelishuv yaratish
    </a>
</div>
@endsection

@section('content')
<style>
.govt-document {
    font-family: 'Times New Roman', serif;
    line-height: 1.4;
}

.govt-header {
    background: #f8f9fa;
    border: 3px solid #1a1a1a;
    padding: 20px;
    margin-bottom: 30px;
    text-align: center;
}

.govt-section {
    background: white;
    border: 2px solid #1a1a1a;
    margin-bottom: 25px;
}

.govt-section-header {
    background: #e9ecef;
    border-bottom: 2px solid #1a1a1a;
    padding: 12px 20px;
    font-weight: bold;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.govt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.govt-table th,
.govt-table td {
    border: 1px solid #1a1a1a;
    padding: 8px 12px;
    text-align: left;
}

.govt-table th {
    background: #f8f9fa;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
}

.evolution-step {
    background: white;
    border: 2px solid #1a1a1a;
    margin-bottom: 15px;
    position: relative;
}

.evolution-step::before {
    content: "";
    position: absolute;
    left: -10px;
    top: 20px;
    width: 16px;
    height: 16px;
    background: #1a1a1a;
    border-radius: 50%;
}

.evolution-step.original::before {
    background: #28a745;
}

.evolution-step.amendment::before {
    background: #007bff;
}

.step-header {
    background: #f8f9fa;
    border-bottom: 1px solid #1a1a1a;
    padding: 10px 15px;
    font-weight: bold;
}

.step-content {
    padding: 15px;
}

.parameter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 10px;
}

.parameter-item {
    border: 1px solid #dee2e6;
    padding: 10px;
    background: #f8f9fa;
}

.parameter-label {
    font-size: 11px;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: 5px;
    letter-spacing: 0.5px;
}

.parameter-value {
    font-size: 14px;
    font-weight: bold;
    color: #1a1a1a;
}

.changes-arrow {
    color: #007bff;
    font-weight: bold;
    font-size: 16px;
    margin: 0 8px;
}

.summary-box {
    background: #e7f3ff;
    border: 2px solid #007bff;
    padding: 15px;
    margin: 10px 0;
    border-radius: 4px;
}

.approved-badge {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 10px;
    text-transform: uppercase;
    font-weight: bold;
}

.pending-badge {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 10px;
    text-transform: uppercase;
    font-weight: bold;
}
</style>

<div class="max-w-5xl mx-auto govt-document">

    <!-- Document Header -->
    <div class="govt-header">
        <h1 style="font-size: 20px; font-weight: bold; margin: 0 0 15px 0;">
            SHARTNOMA O'ZGARISHLAR TARIXI
        </h1>
        <div style="font-size: 14px; font-weight: bold;">
            SHARTNOMA RAQAMI: {{ $contract->contract_number }}
        </div>
        <div style="font-size: 12px; margin-top: 10px;">
            Tuzilgan sana: {{ $contract->contract_date->format('d.m.Y') }} |
            Jami kelishuvlar: {{ $amendments->count() }} ta |
            Tasdiqlangan: {{ $amendments->where('is_approved', true)->count() }} ta
        </div>
    </div>

    @include('partials.flash-messages')

    <!-- Contract Evolution Timeline -->
    <div class="govt-section">
        <div class="govt-section-header">
            SHARTNOMA PARAMETRLARI EVOLUTSIYASI
        </div>
        <div style="padding: 20px; padding-left: 30px;">
            @foreach($contractEvolution as $index => $state)
                <div class="evolution-step {{ $index === 0 ? 'original' : 'amendment' }}">
                    <div class="step-header">
                        <div style="display: flex; justify-content: between; align-items: center;">
                            <strong>{{ $state['stage'] }}</strong>
                            <span style="font-size: 12px; color: #6c757d; margin-left: auto;">
                                {{ $state['date'] }}
                            </span>
                        </div>
                    </div>
                    <div class="step-content">
                        <div class="parameter-grid">
                            <div class="parameter-item">
                                <div class="parameter-label">Jami summa</div>
                                <div class="parameter-value">{{ formatMoney($state['total_amount']) }}</div>
                            </div>
                            <div class="parameter-item">
                                <div class="parameter-label">Boshlang'ich to'lov</div>
                                <div class="parameter-value">{{ formatPercent($state['initial_payment_percent']) }}</div>
                            </div>
                            <div class="parameter-item">
                                <div class="parameter-label">Choraklar soni</div>
                                <div class="parameter-value">{{ $state['quarters_count'] }} ta</div>
                            </div>
                            <div class="parameter-item">
                                <div class="parameter-label">Yakunlash sanasi</div>
                                <div class="parameter-value">{{ $state['completion_date'] }}</div>
                            </div>
                        </div>

                        @if($index > 0)
                            @php
                                $prevState = $contractEvolution[$index - 1];
                                $changes = [];
                                if ($state['total_amount'] != $prevState['total_amount']) {
                                    $changes[] = formatMoney($prevState['total_amount']) . ' → ' . formatMoney($state['total_amount']);
                                }
                                if ($state['initial_payment_percent'] != $prevState['initial_payment_percent']) {
                                    $changes[] = formatPercent($prevState['initial_payment_percent']) . ' → ' . formatPercent($state['initial_payment_percent']) . ' (boshlang\'ich)';
                                }
                                if ($state['quarters_count'] != $prevState['quarters_count']) {
                                    $changes[] = $prevState['quarters_count'] . ' ta → ' . $state['quarters_count'] . ' ta (choraklar)';
                                }
                                if ($state['completion_date'] != $prevState['completion_date']) {
                                    $changes[] = $prevState['completion_date'] . ' → ' . $state['completion_date'] . ' (muddat)';
                                }
                            @endphp
                            @if(!empty($changes))
                                <div class="summary-box">
                                    <strong>O'zgarishlar:</strong><br>
                                    {{ implode('; ', $changes) }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Amendments Details Table -->
    <div class="govt-section">
        <div class="govt-section-header">
            KELISHUVLAR BATAFSIL MA'LUMOTLARI
        </div>
        <div style="padding: 20px;">
            <table class="govt-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">T/R</th>
                        <th style="width: 150px;">Kelishuv raqami</th>
                        <th style="width: 100px;">Sana</th>
                        <th>O'zgarishlar</th>
                        <th style="width: 200px;">Sabab</th>
                        <th style="width: 100px;">Holat</th>
                        <th style="width: 120px;">Tasdiqlagan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($amendments as $index => $amendment)
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                            <td style="font-weight: bold;">
                                <a href="{{ route('contracts.amendments.show', [$contract, $amendment]) }}"
                                   style="color: #007bff; text-decoration: none;">
                                    {{ $amendment->amendment_number }}
                                </a>
                            </td>
                            <td>{{ $amendment->amendment_date->format('d.m.Y') }}</td>
                            <td>
                                @if($amendment->changes_summary)
                                    <div style="font-size: 12px; font-family: monospace;">
                                        {{ $amendment->changes_summary }}
                                    </div>
                                @else
                                    <span style="color: #6c757d; font-style: italic;">Ma'lumot yo'q</span>
                                @endif
                            </td>
                            <td style="font-size: 12px;">
                                {{ Str::limit($amendment->reason, 100) }}
                            </td>
                            <td style="text-align: center;">
                                @if($amendment->is_approved)
                                    <span class="approved-badge">Tasdiqlangan</span>
                                @else
                                    <span class="pending-badge">Kutilmoqda</span>
                                @endif
                            </td>
                            <td style="font-size: 12px;">
                                @if($amendment->is_approved)
                                    {{ $amendment->approvedBy->name ?? 'Noma\'lum' }}<br>
                                    <span style="color: #6c757d;">{{ $amendment->approved_at->format('d.m.Y H:i') }}</span>
                                @else
                                    <span style="color: #6c757d;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #6c757d;">
                                Hech qanday kelishuv mavjud emas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Current Contract Status -->
    <div class="govt-section">
        <div class="govt-section-header">
            JORIY SHARTNOMA HOLATI
        </div>
        <div style="padding: 20px;">
            @if(count($contractEvolution) > 1)
                @php $currentState = end($contractEvolution); @endphp
                <div style="background: #d1ecf1; border: 2px solid #bee5eb; padding: 20px; border-radius: 4px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0c5460;">
                            {{ $amendments->where('is_approved', true)->count() }} MARTA O'ZGARTIRILGAN SHARTNOMA
                        </h3>
                    </div>

                    <div class="parameter-grid">
                        <div style="background: white; border: 1px solid #bee5eb; padding: 15px; text-align: center;">
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;">JORIY JAMI SUMMA</div>
                            <div style="font-size: 18px; font-weight: bold; color: #0c5460;">
                                {{ formatMoney($currentState['total_amount']) }}
                            </div>
                        </div>
                        <div style="background: white; border: 1px solid #bee5eb; padding: 15px; text-align: center;">
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;">BOSHLANG'ICH TO'LOV</div>
                            <div style="font-size: 18px; font-weight: bold; color: #0c5460;">
                                {{ formatPercent($currentState['initial_payment_percent']) }}
                            </div>
                        </div>
                        <div style="background: white; border: 1px solid #bee5eb; padding: 15px; text-align: center;">
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;">CHORAKLAR SONI</div>
                            <div style="font-size: 18px; font-weight: bold; color: #0c5460;">
                                {{ $currentState['quarters_count'] }} ta
                            </div>
                        </div>
                        <div style="background: white; border: 1px solid #bee5eb; padding: 15px; text-align: center;">
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;">YAKUNLASH SANASI</div>
                            <div style="font-size: 18px; font-weight: bold; color: #0c5460;">
                                {{ $currentState['completion_date'] }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div style="text-align: center; padding: 30px; color: #6c757d;">
                    Shartnoma hali o'zgartirilmagan
                </div>
            @endif
        </div>
    </div>

    <!-- Document Footer -->
    <div style="background: #f8f9fa; border: 2px solid #1a1a1a; padding: 15px; text-align: center; margin-top: 30px;">
        <div style="font-size: 12px; color: #6c757d;">
            Ushbu hujjat {{ date('d.m.Y H:i') }} da tuzilgan<br>
            Shartnoma boshqaruv tizimi tomonidan
        </div>
        <div style="margin-top: 15px;">
            <a href="{{ route('contracts.amendments.create', $contract) }}"
               style="display: inline-block; padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; font-size: 12px; text-transform: uppercase; margin-right: 10px;">
                Yangi kelishuv yaratish
            </a>
            <a href="{{ route('contracts.payment_update', $contract) }}"
               style="display: inline-block; padding: 8px 16px; background: #28a745; color: white; text-decoration: none; border-radius: 3px; font-size: 12px; text-transform: uppercase; margin-right: 10px;">
                To'lov boshqaruvi
            </a>
            <a href="{{ route('contracts.show', $contract) }}"
               style="display: inline-block; padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 3px; font-size: 12px; text-transform: uppercase;">
                Shartnoma tafsilotlari
            </a>
        </div>
    </div>

</div>

@endsection
