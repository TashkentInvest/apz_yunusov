@extends('layouts.app')

@section('title', 'Бош сахифа - АПЗ Тизими')
@section('page-title', 'Бошқарув панели')

@section('content')
    <div class="space-y-6">
    <!-- Statistics Cards - Clickable -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Total Contracts -->
    <a href="{{ route('dashboard.contracts.status', 'total') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Жами шартномалар</p>
                <p class="text-3xl font-bold text-red-900 mt-2">{{ number_format($stats['total_contracts']) }} <span
                        class=" text-blue-900 mt-2">та</span></p>

                <div class="flex-1">
                    <p class="text-2xl font-bold text-red-900 mt-2">
                        {{ number_format($stats['total_amount'] / 1000000000, 1) }} <span
                            class=" text-blue-900 mt-2">млрд сўм</span></p>
                </div>
                <p class="text-xs text-gray-500 mt-1">Юридик: {{ $stats['legal_entities'] }} | Жисмоний:
                    {{ $stats['individuals'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                <i data-feather="file-text" class="w-6 h-6 text-blue-600"></i>
            </div>
        </div>
    </a>

    <!-- Active Contracts -->
    <a href="{{ route('monitoring.status', 'active') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Амалдаги шартномалар</p>
                <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($stats['active_contracts']) }} <span
                        class="text-blue-900 mt-2">та</span></p>

                <div class="flex-1">
                    <p class="text-2xl font-bold text-green-900 mt-2">
                        {{ number_format($stats['active_amount'] / 1000000000, 1) }} <span
                            class="text-blue-900 mt-2">млрд сўм</span></p>
                </div>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
            </div>
        </div>
    </a>

    <!-- Paid Amount -->
    <a href="{{ route('contracts.index') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Тўланган сумма</p>
                <p class="text-3xl font-bold text-purple-900 mt-2">{{ number_format($stats['paid_contracts_count']) }} <span
                        class="text-blue-900 mt-2">та</span></p>

                <div class="flex-1">
                    <p class="text-2xl font-bold text-purple-900 mt-2">
                        {{ number_format($stats['total_paid'] / 1000000000, 1) }} <span
                            class="text-blue-900 mt-2">млрд сўм</span></p>
                </div>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                <i data-feather="credit-card" class="w-6 h-6 text-purple-600"></i>
            </div>
        </div>
    </a>

    <!-- Remaining Debt -->
    <a href="{{ route('dashboard.contracts.status', 'debtors') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Қолдиқ</p>
                <p class="text-3xl font-bold text-red-900 mt-2">{{ number_format($stats['debtors_count']) }} <span
                        class="text-blue-900 mt-2">та</span></p>

                <div class="flex-1">
                    <p class="text-2xl font-bold text-red-900 mt-2">
                        {{ number_format($stats['total_debt'] / 1000000000, 1) }} <span
                            class="text-blue-900 mt-2">млрд сўм</span></p>
                </div>
            </div>
            <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
            </div>
        </div>
    </a>
</div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Monthly Payments Chart -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Тўловлар динамикаси</h3>
                        <div class="flex space-x-2">
                            <button onclick="changePeriod('month')"
                                class="px-3 py-1 text-sm rounded-lg period-btn {{ $period === 'month' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-100' }}">
                                Ой
                            </button>
                            <button onclick="changePeriod('quarter')"
                                class="px-3 py-1 text-sm rounded-lg period-btn {{ $period === 'quarter' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-100' }}">
                                Чорак
                            </button>
                            <button onclick="changePeriod('year')"
                                class="px-3 py-1 text-sm rounded-lg period-btn {{ $period === 'year' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-100' }}">
                                Йил
                            </button>
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Districts Stats - Clickable -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Туманлар бўйича</h3>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($districtStats as $district)
                        <a href="{{ route('dashboard.district.contracts', $district->district_id) }}"
                            class="flex items-center justify-between p-3 rounded-lg transition-all hover:bg-blue-50 hover:shadow-sm {{ $district->contracts_count > 0 ? 'cursor-pointer' : 'opacity-60' }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $district->district_name }}</p>
                                <p class="text-xs text-gray-500">{{ $district->contracts_count }} шартнома</p>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ number_format($district->total_amount) }} сўм</p>
                                @if ($district->total_amount > 0)
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-blue-600 h-2 rounded-full"
                                            style="width: {{ min(100, $district->payment_percentage) }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ number_format($district->payment_percentage, 1) }}%</p>
                                @else
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mt-1"></div>
                                    <p class="text-xs text-gray-500 mt-1">0%</p>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-gray-500">
                            <p>Маълумот йўқ</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Contracts -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Сўнгги шартномалар</h3>
                        <a href="{{ route('contracts.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-700">Барчаси</a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach ($recentContracts as $contract)
                            <div class="flex items-center space-x-4 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors"
                                onclick="window.location.href='{{ route('contracts.show', $contract) }}'">
                                <div
                                    class="w-2 h-2 rounded-full {{ $contract->status->code === 'ACTIVE' ? 'bg-green-500' : 'bg-gray-400' }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $contract->contract_number }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $contract->subject->company_name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ number_format($contract->total_amount) }}сўм</p>
                                    <p class="text-xs text-gray-500">{{ $contract->created_at->format('d.m.Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Сўнгги тўловлар</h3>
                        <a href="{{ route('contracts.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-700">Барчаси</a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach ($recentPayments as $payment)
                            <div class="flex items-center space-x-4 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors"
                                onclick="window.location.href='{{ route('contracts.show', $payment->contract) }}'">
                                <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                                    <i data-feather="credit-card" class="w-4 h-4 text-green-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $payment->contract->contract_number }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $payment->contract->subject->company_name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-green-600">
                                        +{{ number_format($payment->amount) }}сўм</p>
                                    <p class="text-xs text-gray-500">{{ $payment->payment_date->format('d.m.Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Тезкор амаллар</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('contracts.create') }}"
                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-blue-300 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                            <i data-feather="plus" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <span class="font-medium text-gray-900">Янги шартнома</span>
                    </div>
                </a>

                <a href="{{ route('subjects.create') }}"
                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-green-300 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                            <i data-feather="user-plus" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <span class="font-medium text-gray-900">Янги буюртмачи</span>
                    </div>
                </a>

                <a href="{{ route('dashboard.contracts.status', 'debtors') }}"
                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-red-300 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                            <i data-feather="alert-triangle" class="w-5 h-5 text-red-600"></i>
                        </div>
                        <span class="font-medium text-gray-900">Қарзларни кўриш</span>
                    </div>
                </a>

                <button onclick="generateReport()"
                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-purple-300 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                            <i data-feather="download" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <span class="font-medium text-gray-900">Хисоботни юклаш</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let monthlyChart;

        function initChart() {
            console.log('🎯 Starting chart initialization...');

            try {
                const canvas = document.getElementById('monthlyChart');
                if (!canvas) {
                    console.error('❌ Canvas element not found!');
                    return;
                }
                console.log('✅ Canvas element found');

                const ctx = canvas.getContext('2d');
                const chartData = @json($chartData);

                console.log('📊 Chart Data:', chartData);
                console.log('📊 Data length:', chartData.length);

                if (!chartData || chartData.length === 0) {
                    console.error('❌ No chart data available');
                    return;
                }

                // Check if Chart.js is loaded
                if (typeof Chart === 'undefined') {
                    console.error('❌ Chart.js not loaded!');
                    return;
                }
                console.log('✅ Chart.js loaded');

                monthlyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.map(item => item.label),
                        datasets: [{
                            label: 'Амалдаги тўловлар',
                            data: chartData.map(item => item.actual / 1000000),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }, {
                            label: 'Режа бўйича тўловлар',
                            data: chartData.map(item => item.planned / 1000000),
                            borderColor: 'rgb(156, 163, 175)',
                            backgroundColor: 'rgba(156, 163, 175, 0.1)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' +
                                            new Intl.NumberFormat('ru-RU').format(context.parsed.y) +
                                            ' млн сўм';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('ru-RU').format(value) + 'М';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                console.log('✅ Chart created successfully!');
            } catch (error) {
                console.error('❌ Error creating chart:', error);
                console.error('Error details:', error.message);
                console.error('Stack:', error.stack);
            }
        }

        async function changePeriod(period) {
            console.log('🔄 Changing period to:', period);

            document.querySelectorAll('.period-btn').forEach(btn => {
                btn.classList.remove('bg-blue-100', 'text-blue-700');
                btn.classList.add('text-gray-500', 'hover:bg-gray-100');
            });

            event.target.classList.remove('text-gray-500', 'hover:bg-gray-100');
            event.target.classList.add('bg-blue-100', 'text-blue-700');

            try {
                const url = `{{ route('dashboard.chart-data') }}?period=${period}`;
                console.log('📡 Fetching:', url);

                const response = await fetch(url);
                console.log('📥 Response status:', response.status);

                const data = await response.json();
                console.log('📊 New data:', data);

                if (!monthlyChart) {
                    console.error('❌ Chart not initialized!');
                    return;
                }

                monthlyChart.data.labels = data.map(item => item.label);
                monthlyChart.data.datasets[0].data = data.map(item => item.actual / 1000000);
                monthlyChart.data.datasets[1].data = data.map(item => item.planned / 1000000);
                monthlyChart.update();

                console.log('✅ Chart updated successfully!');
            } catch (error) {
                console.error('❌ Error updating chart:', error);
                alert('График янгилашда хатолик юз берди: ' + error.message);
            }
        }

        function generateReport() {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = `
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                <svg class="animate-spin w-5 h-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <span class="font-medium text-gray-900">Юкланмоқда...</span>
        </div>
    `;

            setTimeout(() => {
                button.innerHTML = originalText;
                window.location.href = '{{ route('dashboard.export') }}';
            }, 1000);
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 DOM Content Loaded');
            initChart();
        });

        // Also try to initialize after a short delay in case Chart.js is still loading
        setTimeout(() => {
            if (!monthlyChart) {
                console.log('⏰ Retry: Initializing chart after delay');
                initChart();
            }
        }, 500);
    </script>
@endpush
