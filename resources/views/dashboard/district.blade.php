@extends('layouts.app')

@section('title', $district->name_uz . ' - Туман маълумотлари')
@section('page-title', $district->name_uz . ' тумани')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700">
        <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
        Бошқарув панелига қайтиш
    </a>

    <!-- District Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Жами шартномалар</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_contracts'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Юридик: {{ $stats['legal_entities'] }} | Жисмоний: {{ $stats['individuals'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="file-text" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Умумий сумма</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_amount'] ) }} сўм</p>
                    {{-- <p class="text-xs text-gray-500 mt-1">{{ ucfirst(number_to_uzbek_text($stats['total_amount'])) }} сўм</p> --}}
 <p class="text-xm text-gray-500 mt-2 italic leading-relaxed">
                        {{-- {{ ucfirst(number_to_uzbek_text($stats['total_amount'])) }} сўм --}}
                        {{ ucfirst(app(\App\Services\NumberToTextService::class)->convert($stats['total_amount'])) }} сўм

                    </p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <i data-feather="dollar-sign" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Тўланган</p>
                    <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($stats['total_paid'] ) }} сўм</p>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($stats['payment_percent'], 1) }}% бажарилди</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="check-circle" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Қарз</p>
                    <p class="text-2xl font-bold text-red-600 mt-2">{{ number_format($stats['debt'] ) }} сўм</p>
                    <p class="text-xs text-gray-500 mt-1">тўланиши керак</p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">{{ $district->name_uz }} тўловлар тарихи</h3>
        <div class="h-80">
            <canvas id="districtChart"></canvas>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Шартномалар рўйхати</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">№</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Шартнома рақами</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Буюртмачи</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Сумма</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Тўланган</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Ҳолати</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Сана</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($contracts as $contract)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">{{ ($contracts->currentPage() - 1) * $contracts->perPage() + $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('contracts.show', $contract) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $contract->contract_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $contract->subject->company_name ?? 'Кўрсатилмаган' }}</td>
                            <td class="px-6 py-4 text-sm">{{ number_format($contract->total_amount ) }} сўм</td>
                            <td class="px-6 py-4 text-sm">{{ number_format($contract->total_paid ) }} сўм</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      style="background-color: {{ $contract->status->color ?? '#6b7280' }}20; color: {{ $contract->status->color ?? '#6b7280' }}">
                                    {{ $contract->status->name_uz ?? 'Кўрсатилмаган' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $contract->contract_date ? $contract->contract_date->format('d.m.Y') : 'Кўрсатилмаган' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                Шартномалар топилмади
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contracts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Кўрсатилган {{ $contracts->firstItem() }}-{{ $contracts->lastItem() }} / {{ $contracts->total() }}
                    </div>
                    <div class="flex space-x-1">
                        {{ $contracts->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartData = @json($chartData);
const ctx = document.getElementById('districtChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(item => item.label),
        datasets: [{
            label: 'Тўловлар (млн сўм)',
            data: chartData.map(item => item.actual / 1000000),
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return new Intl.NumberFormat('ru-RU').format(context.parsed.y) + ' млн сўм';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => value + 'М'
                }
            }
        }
    }
});
</script>
@endpush
@endsection
