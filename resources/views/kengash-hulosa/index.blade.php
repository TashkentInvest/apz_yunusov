@extends('layouts.app')

@section('title', 'Кенгаш хулосалари')
@section('page-title', 'Кенгаш хулосалари')

@section('header-actions')
    <div class="flex items-center space-x-3">
        <!-- Import Button -->
        <button onclick="document.getElementById('import-modal').classList.remove('hidden')"
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-feather="upload" class="w-4 h-4 mr-2"></i>
            Импорт
        </button>

        <!-- Export Button -->
        <a href="{{ route('kengash-hulosa.export') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-feather="download" class="w-4 h-4 mr-2"></i>
            Экспорт
        </a>

        <!-- Svod Button -->
        <a href="{{ route('kengash-hulosasi.svod') }}"
           class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-feather="bar-chart-2" class="w-4 h-4 mr-2"></i>
            Свод
        </a>

        <!-- Add New Button -->
        <a href="{{ route('kengash-hulosa.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-feather="plus" class="w-4 h-4 mr-2"></i>
            Янги қўшиш
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('kengash-hulosa.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Қидириш</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Рақам, буюртмачи, лойихачи..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Барча статуслар</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- District Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Туман</label>
                <select name="tuman"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Барча туманлар</option>
                    @foreach($districts as $district)
                        <option value="{{ $district }}" {{ request('tuman') == $district ? 'selected' : '' }}>
                            {{ $district }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Building Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Бино тури</label>
                <select name="bino_turi"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Барча турлар</option>
                    @foreach($binoTurlari as $tur)
                        <option value="{{ $tur }}" {{ request('bino_turi') == $tur ? 'selected' : '' }}>
                            {{ ucfirst($tur) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end space-x-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i data-feather="search" class="w-4 h-4 mr-1 inline"></i>
                    Қидириш
                </button>
                <a href="{{ route('kengash-hulosa.index') }}"
                   class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    <i data-feather="x" class="w-4 h-4 mr-1 inline"></i>
                    Тозалаш
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Жами <span class="font-semibold">{{ $kengash_hulosalari->total() }}</span> та натижа топилди
            </p>
            <div class="text-sm text-gray-500">
                {{ $kengash_hulosalari->firstItem() ?? 0 }}-{{ $kengash_hulosalari->lastItem() ?? 0 }} кўрсатилмоқда
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Кенгаш хулоса</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">АПЗ рақами</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Буюртмачи</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Лойихачи</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Туман</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Файллар</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Амаллар</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($kengash_hulosalari as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $loop->iteration + ($kengash_hulosalari->currentPage() - 1) * $kengash_hulosalari->perPage() }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item->kengash_hulosa_raqami }}</div>
                                <div class="text-sm text-gray-500">{{ $item->kengash_hulosa_sanasi?->format('d.m.Y') }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item->apz_raqami }}</div>
                                <div class="text-sm text-gray-500">{{ $item->apz_berilgan_sanasi?->format('d.m.Y') }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="{{ $item->buyurtmachi }}">
                                    {{ Str::limit($item->buyurtmachi, 30) }}
                                </div>
                                @if($item->buyurtmachi_stir_pinfl)
                                    <div class="text-sm text-gray-500">{{ $item->buyurtmachi_stir_pinfl }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="{{ $item->loyihachi }}">
                                    {{ Str::limit($item->loyihachi, 30) }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->tuman }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($item->isTulovdanOzod())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i data-feather="check-circle" class="w-3 h-3 mr-1"></i>
                                        Тўловдан озод
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i data-feather="dollar-sign" class="w-3 h-3 mr-1"></i>
                                        Мажбурий тўлов
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($item->files->count() > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i data-feather="file" class="w-3 h-3 mr-1"></i>
                                        {{ $item->files->count() }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('kengash-hulosa.show', $item) }}"
                                       class="text-blue-600 hover:text-blue-900" title="Кўриш">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('kengash-hulosa.edit', $item) }}"
                                       class="text-indigo-600 hover:text-indigo-900" title="Таҳрирлаш">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('kengash-hulosa.destroy', $item) }}"
                                          onsubmit="return confirm('Ушбу маълумотни ўчиришни хохлайсизми?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Ўчириш">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i data-feather="inbox" class="w-12 h-12 text-gray-300 mb-2"></i>
                                    <p class="text-lg font-medium">Маълумот топилмади</p>
                                    <p class="text-sm">Янги кенгаш хулосаси қўшиш учун "Янги қўшиш" тугмасини босинг</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($kengash_hulosalari->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $kengash_hulosalari->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Import Modal -->
<div id="import-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Excel файлдан импорт қилиш</h3>
                <button onclick="document.getElementById('import-modal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i data-feather="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('kengash-hulosa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Excel файлни танланг</label>
                    <input type="file"
                           name="excel_file"
                           accept=".xlsx,.xls"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Фақат .xlsx ва .xls форматдаги файллар қабул қилинади</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="document.getElementById('import-modal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg">
                        Бекор қилиш
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                        Импорт қилиш
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Close modal when clicking outside
    document.getElementById('import-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
</script>
@endpush
