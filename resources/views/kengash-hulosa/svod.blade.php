@extends('layouts.app')

@section('title', 'Кенгаш хулосалари своди')
@section('page-title', 'Кенгаш хулосалари своди')

@section('header-actions')
    <div class="flex items-center space-x-3">
        <button onclick="printDocument()"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-feather="printer" class="w-4 h-4 mr-2"></i>
            Чоп этиш
        </button>
        <a href="{{ route('kengash-hulosa.index') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-feather="list" class="w-4 h-4 mr-2"></i>
            Рўйхатга қайтиш
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Records -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i data-feather="file-text" class="w-5 h-5 text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ number_format($stats['total']) }}</h3>
                    <p class="text-sm text-gray-600">Жами хужжатлар</p>
                </div>
            </div>
        </div>

        <!-- Exempted -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-green-600">{{ number_format($stats['ozod']) }}</h3>
                    <p class="text-sm text-gray-600">Тўловдан озод</p>
                    <p class="text-xs text-gray-500">
                        {{ $stats['total'] > 0 ? number_format(($stats['ozod'] / $stats['total']) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
        </div>

        <!-- Mandatory Payment -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i data-feather="dollar-sign" class="w-5 h-5 text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-red-600">{{ number_format($stats['majburiy']) }}</h3>
                    <p class="text-sm text-gray-600">Мажбурий тўлов</p>
                    <p class="text-xs text-gray-500">
                        {{ $stats['total'] > 0 ? number_format(($stats['majburiy'] / $stats['total']) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Value -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i data-feather="trending-up" class="w-5 h-5 text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-purple-600">
                        {{ number_format($stats['total_qiymat'] / 1000000, 1) }}М
                    </h3>
                    <p class="text-sm text-gray-600">Жами қиймат (сўм)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Молиявий кўрсаткичлар</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Жами шартнома қиймати:</span>
                    <span class="font-semibold text-gray-900">
                        {{ number_format($stats['total_qiymat'], 0, ',', ' ') }} сўм
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Жами факт тўлов:</span>
                    <span class="font-semibold text-green-600">
                        {{ number_format($stats['total_tulov'], 0, ',', ' ') }} сўм
                    </span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600">Жами қарздорлик:</span>
                    <span class="font-semibold text-red-600">
                        {{ number_format($stats['total_qarz'], 0, ',', ' ') }} сўм
                    </span>
                </div>
            </div>
        </div>

        <!-- Payment Status Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Статус тақсимоти</h3>
            <canvas id="statusChart" class="max-h-64"></canvas>
        </div>

        <!-- Building Types Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Бино турлари</h3>
            <canvas id="binoChart" class="max-h-64"></canvas>
        </div>
    </div>

    <!-- By District Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Туманлар бўйича тақсимот</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Туман</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Жами</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Озод</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Мажбурий</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Озод %</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Жами қиймат</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Тўлов</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Қарз</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($byDistrict as $district)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ number_format($status->count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $stats['total'] > 0 ? number_format(($status->count / $stats['total']) * 100, 1) : 0 }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($status->total_qiymat / 1000000, 1) }}М сўм
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                {{ number_format($status->total_tulov / 1000000, 1) }}М сўм
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                {{ number_format($status->total_qarz / 1000000, 1) }}М сўм
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($byStatus as $status)
                    '{{ $status->status === "Тўловдан озод этилган" ? "Озод" : "Мажбурий" }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($byStatus as $status)
                        {{ $status->count }},
                    @endforeach
                ],
                backgroundColor: [
                    @foreach($byStatus as $status)
                        '{{ $status->status === "Тўловдан озод этилган" ? "#10B981" : "#EF4444" }}',
                    @endforeach
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Building Types Chart
    const binoCtx = document.getElementById('binoChart').getContext('2d');
    new Chart(binoCtx, {
        type: 'pie',
        data: {
            labels: [
                @foreach($byBinoTuri as $bino)
                    '{{ ucfirst($bino->bino_turi) }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($byBinoTuri as $bino)
                        {{ $bino->count }},
                    @endforeach
                ],
                backgroundColor: [
                    '#3B82F6', '#8B5CF6', '#F59E0B', '#06B6D4'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($monthlyStats);

    // Process monthly data
    const months = monthlyData.map(item => {
        const monthNames = [
            'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
            'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'
        ];
        return monthNames[item.month - 1] + ' ' + item.year;
    }).reverse();

    const counts = monthlyData.map(item => item.count).reverse();

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Ойлик қўшилган хужжатлар',
                data: counts,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
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
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Ой'
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Хужжатлар сони'
                    },
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });

    // Initialize feather icons
    feather.replace();
</script>
@endpush
