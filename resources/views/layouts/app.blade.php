<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'АРТ Бошқарув тизими')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        .card-hover {
            transition: all 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .sidebar-transition {
            transition: all 0.3s ease;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .sidebar-nav, .top-navbar {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 1rem !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-sm border-r border-gray-200 no-print sidebar-nav">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 px-4 border-b border-gray-200">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i data-feather="building" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="text-lg font-bold text-gray-900">АРТ тизими</span>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-2">
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 group transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <i data-feather="home" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600 {{ request()->routeIs('dashboard') ? 'text-blue-600' : '' }}"></i>
                       <span class="font-medium">Бош саҳифа</span>
                    </a>

        <a href="{{ route('monitoring') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 group transition-colors {{ request()->routeIs('monitoring') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <i data-feather="activity" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600 {{ request()->routeIs('monitoring') ? 'text-blue-600' : '' }}"></i>
                       <span class="font-medium">Мониторинг</span>
                    </a>


                    <a href="{{ route('contracts.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 group transition-colors {{ request()->routeIs('contracts.index*') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <i data-feather="file-text" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600 {{ request()->routeIs('contracts.index*') ? 'text-blue-600' : '' }}"></i>
                        <span class="font-medium">Шартномалар</span>
                        <span class="ml-auto bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-semibold">
                            {{ \App\Models\Contract::where('is_active', true)->count() }}
                        </span>
                    </a>


                    <a href="{{ route('contracts.yangi_shartnoma') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 group transition-colors {{ request()->routeIs('contracts.yangi_shartnoma*') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <i data-feather="file-text" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600 {{ request()->routeIs('contracts.yangi_shartnoma*') ? 'text-blue-600' : '' }}"></i>
                        <span class="font-medium">АРТ(АПЗ) тўлови бўйича шартнома тузилиши лозим бўлган объектлар рўйхати</span>

                    </a>

                    {{-- <a href="{{ route('contracts.debtors') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 group transition-colors {{ request()->routeIs('contracts.debtors') ? 'bg-red-50 text-red-700' : '' }}">
                        <i data-feather="alert-triangle" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600 {{ request()->routeIs('contracts.debtors') ? 'text-red-600' : '' }}"></i>
                        <span class="font-medium">Должники</span>
                        <span class="ml-auto bg-red-100 text-red-600 px-2 py-1 rounded-full text-xs font-semibold">
                            {{ \App\Models\Contract::whereHas('paymentSchedules', function($q) {
                                $q->where('is_active', true)
                                  ->whereRaw('quarter_amount > (SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id AND year = payment_schedules.year AND quarter = payment_schedules.quarter)');
                            })->count() }}
                        </span>
                    </a> --}}

                    <a href="{{ route('contracts.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 group transition-colors {{ request()->routeIs('payments.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <i data-feather="credit-card" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600 {{ request()->routeIs('payments.*') ? 'text-blue-600' : '' }}"></i>
                      <span class="font-medium">Тўловлар</span>
                    </a>

                    <a href="{{ route('subjects.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 group transition-colors {{ request()->routeIs('subjects.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <i data-feather="users" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600 {{ request()->routeIs('subjects.*') ? 'text-blue-600' : '' }}"></i>
                       <span class="font-medium">Буюртмачилар</span>
                    </a>

                    <!-- <div class="pt-4 mt-4 border-t border-gray-200">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Отчеты</p>
                    </div>

                    <a href="#" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 group transition-colors">
                        <i data-feather="bar-chart-2" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600"></i>
                        <span class="font-medium">Аналитика</span>
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 group transition-colors">
                        <i data-feather="download" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600"></i>
                        <span class="font-medium">Экспорт</span>
                    </a> -->



                </nav>

                <!-- User Section -->
               <div class="px-4 py-4 border-t border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gray-300 rounded-lg flex items-center justify-center">
                            <i data-feather="user" class="w-4 h-4 text-gray-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ auth()->user()->name ?? 'Пользователь' }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ auth()->user()->email ?? 'user@example.com' }}
                            </p>
                        </div>

                        <!-- Fixed Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="text-gray-400 hover:text-red-600 transition-colors p-1 rounded"
                                    title="Чиқиш"
                                    onclick="return confirm('Тизимдан чиқишни истайсизми?')">
                                <i data-feather="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden main-content">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200 no-print top-navbar">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">@yield('page-title')</h1>
                            @if(isset($breadcrumbs))
                                <nav class="flex mt-1" aria-label="Breadcrumb">
                                    <ol class="flex items-center space-x-1 text-sm text-gray-500">
                                        @foreach($breadcrumbs as $breadcrumb)
                                            <li>
                                                @if(!$loop->last)
                                                    <a href="{{ $breadcrumb['url'] }}" class="hover:text-gray-700">{{ $breadcrumb['name'] }}</a>
                                                    <i data-feather="chevron-right" class="w-4 h-4 mx-1 inline"></i>
                                                @else
                                                    <span class="text-gray-900">{{ $breadcrumb['name'] }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                </nav>
                            @endif
                        </div>

                        <div class="flex items-center space-x-4">
                            @yield('header-actions')

                            <!-- Search -->
                            <div class="relative">
                                <i data-feather="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                                <input type="text"
                                       class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Поиск...">
                            </div>

                            <!-- Notifications -->
                            <button class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                <i data-feather="bell" class="w-5 h-5"></i>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                        <div class="flex items-center">
                            <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
                        <div class="flex items-center">
                            <i data-feather="alert-circle" class="w-5 h-5 mr-2"></i>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i data-feather="alert-circle" class="w-5 h-5 mr-2"></i>
                            <span class="font-medium">Исправьте следующие ошибки:</span>
                        </div>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Modals -->
    @stack('modals')

    <script>
        // Initialize Feather Icons
        feather.replace();

        // CSRF token for AJAX requests
        window.axios = {
            defaults: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }
        };

        // Add hover effects
        document.querySelectorAll('.card-hover').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-2px)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });

        // Print functionality
        function printDocument() {
            window.print();
        }

        // Format numbers
        function formatNumber(num) {
            return new Intl.NumberFormat('ru-RU').format(num);
        }

        // Format currency
        function formatCurrency(num) {
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'UZS',
                minimumFractionDigits: 0
            }).format(num);
        }

        // Date formatting
        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('ru-RU');
        }

        // Common AJAX error handler
        function handleAjaxError(xhr) {
            let message = 'Произошла ошибка';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            // Show error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'fixed top-4 right-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg shadow-lg z-50';
            errorDiv.innerHTML = `
                <div class="flex items-center">
                    <i data-feather="alert-circle" class="w-5 h-5 mr-2"></i>
                    ${message}
                </div>
            `;
            document.body.appendChild(errorDiv);
            feather.replace();

            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // Show success message
        function showSuccessMessage(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'fixed top-4 right-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg shadow-lg z-50';
            successDiv.innerHTML = `
                <div class="flex items-center">
                    <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
                    ${message}
                </div>
            `;
            document.body.appendChild(successDiv);
            feather.replace();

            setTimeout(() => {
                successDiv.remove();
            }, 5000);
        }

        // Toggle loading state
        function toggleLoading(element, loading = true) {
            if (loading) {
                element.disabled = true;
                element.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Загрузка...
                `;
            } else {
                element.disabled = false;
                element.innerHTML = element.getAttribute('data-original-text') || 'Сохранить';
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
