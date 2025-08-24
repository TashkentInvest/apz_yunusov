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
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_amount'] / 1000000000, 1) }}Б</p>
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
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_paid'] / 1000000000, 1) }}Б</p>
                    <p class="text-sm text-blue-600 mt-1">
                        {{ $stats['total_amount'] > 0 ? round(($stats['total_paid'] / $stats['total_amount']) * 100, 1) : 0 }}% выполнено
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
                        {{ number_format($stats['total_debt'] / 1000000, 1) }}М долг
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
                        <button class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-lg">Месяц</button>
                        <button class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Квартал</button>
                        <button class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Год</button>
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
            <div class="space-y-4">
                @foreach($districtStats as $district)
                @dump($district)
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $district->district_name }}</p>
                        <p class="text-xs text-gray-500">{{ $district->contracts_count }} договоров</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">{{ number_format($district->total_amount / 1000000, 1) }}М</p>
                        <div class="w-16 bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-blue-600 h-2 rounded-full"
                                 style="width: {{ $district->total_amount > 0 ? ($district->paid_amount / $district->total_amount) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
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
                    @php
                        $recentContracts = \App\Models\Contract::with(['subject', 'status'])
                            ->where('is_active', true)
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    @foreach($recentContracts as $contract)
                    <div class="flex items-center space-x-4">
                        <div class="w-2 h-2 rounded-full {{ $contract->status->code === 'ACTIVE' ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $contract->contract_number }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $contract->subject->display_name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ number_format($contract->total_amount / 1000000, 1) }}М</p>
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
                    <a href="{{ route('payments.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Все платежи</a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @php
                        $recentPayments = \App\Models\ActualPayment::with(['contract.subject'])
                            ->orderBy('payment_date', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    @foreach($recentPayments as $payment)
                    <div class="flex items-center space-x-4">
                        <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                            <i data-feather="credit-card" class="w-4 h-4 text-green-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $payment->contract->contract_number }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $payment->contract->subject->display_name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-green-600">+{{ number_format($payment->amount / 1000000, 1) }}М</p>
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

            <a href="{{ route('contracts.debtors') }}"
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
@endsection

@push('scripts')
<script>
// Monthly Payments Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: [
            @foreach($monthlyStats->reverse() as $stat)
                '{{ $stat->month }}/{{ substr($stat->year, 2) }}',
            @endforeach
        ],
        datasets: [{
            label: 'Платежи (млн сум)',
            data: [
                @foreach($monthlyStats->reverse() as $stat)
                    {{ $stat->total_amount / 1000000 }},
                @endforeach
            ],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
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

    // Simulate report generation
    setTimeout(() => {
        button.innerHTML = originalText;
        showSuccessMessage('Отчет успешно сгенерирован');
    }, 2000);
}

// Auto-refresh data every 5 minutes
setInterval(() => {
    // In a real application, you would fetch fresh data here
    console.log('Refreshing dashboard data...');
}, 300000);
</script>
@endpush
