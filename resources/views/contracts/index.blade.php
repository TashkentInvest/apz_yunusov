@extends('layouts.app')

@section('title', 'Шартномалар - АПЗ Тизими')
@section('page-title', 'Шартномаларни бошқариш')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('contracts.create') }}"
       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i data-feather="plus" class="w-4 h-4 mr-2 inline"></i>
        Янги шартнома
    </a>
    <button onclick="exportContracts()"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <i data-feather="download" class="w-4 h-4 mr-2 inline"></i>
        Экспорт
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('contracts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Шартнома рақами</label>
                <input type="text"
                       name="contract_number"
                       value="{{ request('contract_number') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="АПЗ-">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Туман</label>
                <select name="district_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Барча туманлар</option>
                    @foreach($districts ?? [] as $district)
                        @if(preg_match('/^[А-Яа-яЎўҚқҒғҲҳ]/u', $district->name_uz))
                            <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>
                                {{ $district->name_uz }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ҳолати</label>
                <select name="status_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Барча ҳолатлар</option>
                    @foreach($statuses ?? [] as $status)
                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                            {{ $status->name_uz }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-feather="search" class="w-4 h-4 mr-2 inline"></i>
                    Қидириш
                </button>
                <a href="{{ route('contracts.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i data-feather="x" class="w-4 h-4 mr-2 inline"></i>
                    Тозалаш
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Шартномалар сони</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $contracts->total() }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i data-feather="file-text" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-2">Режа учун умумий сумма</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ number_format($totalAmount, 0, '.', ' ') }} сўм
                    </p>
                    <p class="text-xm text-gray-500 mt-2 italic leading-relaxed">
                        {{ ucfirst(app(\App\Services\NumberToTextService::class)->convert($totalAmount)) }} сўм
                    </p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0 ml-4">
                    <i data-feather="dollar-sign" class="w-5 h-5 text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Фаол шартномалар</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $activeCount }}</p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
                </div>
            </div>
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
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Шартнома рақами</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Буюртмачи</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Туман</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тўланган</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ҳолати</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сана</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ижрочи</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Амаллар</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($contracts as $index => $contract)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <!-- Row Number -->
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ ($contracts->currentPage() - 1) * $contracts->perPage() + $loop->iteration }}
                            </td>

                            <!-- Contract Number -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('contracts.show', $contract) }}"
                                       class="text-blue-600 hover:text-blue-800">
                                        {{ $contract->contract_number }}
                                    </a>
                                </div>
                                <div class="text-sm text-gray-500">
                                    ID: {{ $contract->id }}
                                </div>
                            </td>

                            <!-- Subject/Customer -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $contract->subject->company_name ?? 'Кўрсатилмаган' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    @if($contract->subject->is_legal_entity)
                                        СТИР: {{ $contract->subject->inn ?? 'Топилмади' }}
                                    @else
                                        @if($contract->subject->document_series)
                                            Паспорт: {{ $contract->subject->document_series ?? 'Топилмади' }} <br>  ЖШШИР: {{ $contract->subject->pinfl ?? 'Топилмади' }}
                                        @else
                                            Топилмади
                                        @endif
                                    @endif
                                </div>
                            </td>

                            <!-- District -->
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $contract->object->district->name_uz ?? 'Кўрсатилмаган' }}
                            </td>

                            <!-- Contract Amount -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ number_format($contract->total_amount, 0, '.', ' ') }} сўм
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($contract->total_amount / 1000000, 1) }} млн
                                </div>
                            </td>

                            <!-- Payment Progress -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ number_format($contract->total_paid, 0, '.', ' ') }} сўм
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                         style="width: {{ min(100, $contract->payment_percent) }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ number_format($contract->payment_percent, 1) }}%
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      style="background-color: {{ $contract->status->color ?? '#6b7280' }}20; color: {{ $contract->status->color ?? '#6b7280' }}">
                                    {{ $contract->status->name_uz ?? 'Кўрсатилмаган' }}
                                </span>
                            </td>

                            <!-- Contract Date -->
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $contract->contract_date ? $contract->contract_date->format('d.m.Y') : 'Кўрсатилмаган' }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $contract->updatedBy ? $contract->updatedBy->email : 'Кўрсатилмаган' }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('contracts.payment_update', $contract) }}"
                                       class="text-gray-400 hover:text-blue-600" title="Таҳрирлаш">
                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i data-feather="file-text" class="w-12 h-12 text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2">Шартномалар топилмади</p>
                                    <p class="text-gray-500 mb-4">Қидирув параметрларини ўзгартириб кўринг</p>
                                    <a href="{{ route('contracts.create') }}"
                                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        Янги шартнома яратиш
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($contracts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Кўрсатилган {{ $contracts->firstItem() }}-{{ $contracts->lastItem() }} / {{ $contracts->total() }} натижа
                    </div>
                    <div class="flex space-x-1">
                        @foreach ($contracts->links()->elements as $element)
                            @if (is_string($element))
                                <span class="px-3 py-1 text-gray-400">{{ $element }}</span>
                            @endif

                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $contracts->currentPage())
                                        <span class="px-3 py-1 bg-blue-600 text-white rounded-lg">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}"
                                           class="px-3 py-1 text-gray-700 hover:bg-gray-200 rounded-lg">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportContracts() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = `{{ route('contracts.index') }}?${params.toString()}`;
}
</script>
@endpush
