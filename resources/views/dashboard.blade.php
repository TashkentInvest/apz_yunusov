@extends('layouts.app')

@section('title', 'Главная - АПЗ Система')
@section('page-title', 'Панель управления')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Contracts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Всего договоров</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_contracts']) }}</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i data-feather="trending-up" class="w-4 h-4 inline mr-1"></i>
                        +{{ $stats['active_contracts'] }} активных
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="file-text" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Общая сумма</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_amount'], 0, '.', ' ') }}</p>
                    <p class="text-sm text-gray-500 mt-1">сум</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <i data-feather="dollar-sign" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Оплачено</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_paid'], 0, '.', ' ') }}</p>
                    <p class="text-sm text-blue-600 mt-1">
                        {{ $stats['total_amount'] > 0 ? number_format(($stats['total_paid'] / $stats['total_amount']) * 100, 1) : 0 }}% выполнено
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="check-circle" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Debtors -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Должники</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['debtors_count']) }}</p>
                    <p class="text-sm text-red-600 mt-1">
                        <i data-feather="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                        {{ number_format($stats['total_debt'], 0, '.', ' ') }} сум
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Payments Chart -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Динамика платежей</h3>
                    <div class="flex space-x-2">
                        <button onclick="changePeriod('month')"
                                class="px-3 py-1 text-sm rounded-lg period-btn {{ $period === 'month' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-100' }}">
                            Месяц
                        </button>
                        <button onclick="changePeriod('quarter')"
                                class="px-3 py-1 text-sm rounded-lg period-btn {{ $period === 'quarter' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-100' }}">
                            Квартал
                        </button>
                        <button onclick="changePeriod('year')"
                                class="px-3 py-1 text-sm rounded-lg period-btn {{ $period === 'year' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-100' }}">
                            Год
                        </button>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Districts Stats -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">По районам</h3>
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @forelse($districtStats as $district)
                <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors {{ $district->contracts_count > 0 ? '' : 'opacity-60' }}"
                     onclick="showDistrictDetails({{ $district->district_id }}, '{{ $district->district_name }}')">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $district->district_name }}</p>
                        <p class="text-xs text-gray-500">{{ $district->contracts_count }} договоров</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">{{ number_format($district->total_amount, 0, '.', ' ') }}</p>
                        @if($district->total_amount > 0)
                            <div class="w-16 bg-gray-200 rounded-full h-2 mt-1">
                                <div class="bg-blue-600 h-2 rounded-full"
                                     style="width: {{ min(100, $district->payment_percentage) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ number_format($district->payment_percentage, 1) }}%</p>
                        @else
                            <div class="w-16 bg-gray-200 rounded-full h-2 mt-1"></div>
                            <p class="text-xs text-gray-500 mt-1">0%</p>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500">
                    <p>Нет данных по районам</p>
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
                    <h3 class="text-lg font-semibold text-gray-900">Последние договоры</h3>
                    <a href="{{ route('contracts.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Все договоры</a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($recentContracts as $contract)
                    <div class="flex items-center space-x-4 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors"
                         onclick="window.location.href='{{ route('contracts.show', $contract) }}'">
                        <div class="w-2 h-2 rounded-full {{ $contract->status->code === 'ACTIVE' ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $contract->contract_number }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $contract->subject->display_name ?? $contract->subject->company_name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ number_format($contract->total_amount, 0, '.', ' ') }}</p>
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
                    <h3 class="text-lg font-semibold text-gray-900">Последние платежи</h3>
                    <a href="" class="text-sm text-blue-600 hover:text-blue-700">Все платежи</a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($recentPayments as $payment)
                    <div class="flex items-center space-x-4 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors"
                         onclick="window.location.href='{{ route('contracts.show', $payment->contract) }}'">
                        <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                            <i data-feather="credit-card" class="w-4 h-4 text-green-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $payment->contract->contract_number }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $payment->contract->subject->display_name ?? $payment->contract->subject->company_name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-green-600">+{{ number_format($payment->amount, 0, '.', ' ') }}</p>
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
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Быстрые действия</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('contracts.create') }}"
               class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                        <i data-feather="plus" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <span class="font-medium text-gray-900">Новый договор</span>
                </div>
            </a>

            <a href="{{ route('subjects.create') }}"
               class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                        <i data-feather="user-plus" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <span class="font-medium text-gray-900">Новый заказчик</span>
                </div>
            </a>

            <a href="{{ route('contracts.index', ['overdue' => 1]) }}"
               class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                        <i data-feather="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <span class="font-medium text-gray-900">Просмотр долгов</span>
                </div>
            </a>

            <button onclick="generateReport()"
                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                        <i data-feather="download" class="w-5 h-5 text-purple-600"></i>
                    </div>
                    <span class="font-medium text-gray-900">Скачать отчет</span>
                </div>
            </button>
        </div>
    </div>
</div>

<!-- District Details Modal -->
<div id="districtModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900" id="districtModalTitle">Детали района</h3>
            </div>
            <div class="px-6 py-4" id="districtModalContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button onclick="closeDistrictModal()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let monthlyChart;

// Initialize chart
function initChart() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');

    const chartData = @json($chartData);

    monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.label),
            datasets: [{
                label: 'Фактические платежи',
                data: chartData.map(item => item.actual / 1000000), // Convert to millions
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }, {
                label: 'Плановые платежи',
                data: chartData.map(item => item.planned / 1000000), // Convert to millions
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
                                   new Intl.NumberFormat('ru-RU').format(context.parsed.y) + ' млн сум';
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
}

// Change period function
async function changePeriod(period) {
    // Update button states
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.classList.remove('bg-blue-100', 'text-blue-700');
        btn.classList.add('text-gray-500', 'hover:bg-gray-100');
    });

    event.target.classList.remove('text-gray-500', 'hover:bg-gray-100');
    event.target.classList.add('bg-blue-100', 'text-blue-700');

    try {
        const response = await fetch(`{{ route('dashboard.chart-data') }}?period=${period}`);
        const data = await response.json();

        // Update chart
        monthlyChart.data.labels = data.map(item => item.label);
        monthlyChart.data.datasets[0].data = data.map(item => item.actual / 1000000);
        monthlyChart.data.datasets[1].data = data.map(item => item.planned / 1000000);
        monthlyChart.update();

    } catch (error) {
        console.error('Error updating chart:', error);
        alert('Ошибка обновления графика');
    }
}

function showDistrictDetails(districtId, districtName) {
    document.getElementById('districtModalTitle').textContent = `Детали района: ${districtName}`;
    document.getElementById('districtModalContent').innerHTML = `
        <div class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-gray-500 mt-2">Загрузка...</p>
        </div>
    `;
    document.getElementById('districtModal').classList.remove('hidden');

    // Load district details
    fetch(`/dashboard/district/${districtId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('districtModalContent').innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-blue-600">Всего договоров</p>
                            <p class="text-2xl font-bold text-blue-900">${data.stats.total_contracts}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-green-600">Общая сумма</p>
                            <p class="text-2xl font-bold text-green-900">${new Intl.NumberFormat('ru-RU').format(data.stats.total_amount)} сум</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm text-yellow-600">Оплачено</p>
                            <p class="text-2xl font-bold text-yellow-900">${new Intl.NumberFormat('ru-RU').format(data.stats.total_paid)} сум</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <p class="text-sm text-red-600">Прогресс</p>
                            <p class="text-2xl font-bold text-red-900">${data.stats.progress.toFixed(1)}%</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Последние договоры:</h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            ${data.contracts.map(contract => `
                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                    <div>
                                        <p class="text-sm font-medium">${contract.contract_number}</p>
                                        <p class="text-xs text-gray-500">${contract.subject ? (contract.subject.display_name || contract.subject.company_name) : 'Не указан'}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm">${new Intl.NumberFormat('ru-RU').format(contract.total_amount)} сум</p>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error loading district details:', error);
            document.getElementById('districtModalContent').innerHTML = `
                <div class="text-center py-4">
                    <p class="text-red-500">Ошибка загрузки данных</p>
                </div>
            `;
        });
}

function closeDistrictModal() {
    document.getElementById('districtModal').classList.add('hidden');
}

function generateReport() {
    // Show loading
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
            <span class="font-medium text-gray-900">Генерация...</span>
        </div>
    `;

    // Generate report (implement this functionality)
    setTimeout(() => {
        button.innerHTML = originalText;
        // Download report or show success message
        window.location.href = '{{ route("dashboard.export") }}';
    }, 2000);
}

// Initialize chart when page loads
document.addEventListener('DOMContentLoaded', function() {
    initChart();
});

// Auto-refresh data every 5 minutes
setInterval(() => {
    // You can implement auto-refresh here if needed
    console.log('Auto-refresh would happen here...');
}, 300000);
</script>
@endpush
