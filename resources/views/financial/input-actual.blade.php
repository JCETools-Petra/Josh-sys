<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Input Data Aktual - ') }} {{ $property->name }}
            </h2>
            <nav class="flex flex-wrap items-center space-x-2 sm:space-x-3">
                <x-nav-link :href="route('property.financial.report')" class="ml-3">
                    {{ __('Lihat Laporan P&L') }}
                </x-nav-link>
            </nav>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-700 border border-green-400 dark:border-green-600 rounded-md p-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Period Selection -->
                    <form method="GET" action="{{ route('property.financial.input-actual') }}" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow">
                        <div class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-4 md:space-y-0">
                            <div class="flex-1">
                                <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
                                <select name="year" id="year" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="flex-1">
                                <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bulan</label>
                                <select name="month" id="month" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(2000, $m, 1)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Pilih Periode</button>
                            </div>
                        </div>
                    </form>

                    <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    <strong>Periode:</strong> {{ \Carbon\Carbon::create(2000, $month, 1)->format('F') }} {{ $year }}
                                </p>
                                <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">
                                    Input data realisasi (Actual) untuk bulan ini. Data pendapatan (Revenue) akan diambil otomatis dari pencatatan harian.
                                </p>
                            </div>
                            <!-- Copy from Previous Month Button -->
                            <form method="POST" action="{{ route('property.financial.copy-previous-month') }}" class="inline-block" onsubmit="return confirm('Salin semua data dari bulan sebelumnya? Data yang ada akan ditimpa.');">
                                @csrf
                                <input type="hidden" name="year" value="{{ $year }}">
                                <input type="hidden" name="month" value="{{ $month }}">
                                <button type="submit" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-md hover:bg-purple-700 whitespace-nowrap">
                                    📋 Salin dari Bulan Lalu
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Input Form -->
                    <form method="POST" action="{{ route('property.financial.input-actual.store') }}" x-data="{ activeTab: 0 }">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">

                        <!-- Department Tabs -->
                        <div class="mb-6">
                            <div class="border-b border-gray-200 dark:border-gray-700">
                                <nav class="flex -mb-px space-x-4 overflow-x-auto">
                                    @foreach($departments as $index => $dept)
                                        <button
                                            type="button"
                                            @click="activeTab = {{ $index }}"
                                            :class="activeTab === {{ $index }} ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                                        >
                                            {{ $dept['department'] }}
                                        </button>
                                    @endforeach
                                </nav>
                            </div>
                        </div>

                        <!-- Department Content -->
                        @foreach($departments as $index => $dept)
                            <div x-show="activeTab === {{ $index }}" class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ $dept['department'] }}</h3>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-2/3">
                                                    Kategori
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">
                                                    Actual (Rp)
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($dept['categories'] as $cat)
                                                <tr class="{{ $cat['is_payroll'] ? 'bg-yellow-50 dark:bg-yellow-900' : '' }}">
                                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                        <div class="font-medium">{{ $cat['name'] }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $cat['full_path'] }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input
                                                            type="number"
                                                            name="entries[{{ $loop->parent->index }}_{{ $loop->index }}][actual_value]"
                                                            step="0.01"
                                                            min="0"
                                                            value="{{ $existingEntries[$cat['id']]->actual_value ?? 0 }}"
                                                            class="w-full text-right border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                                        >
                                                        <input type="hidden" name="entries[{{ $loop->parent->index }}_{{ $loop->index }}][category_id]" value="{{ $cat['id'] }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach

                        <!-- Submit Button -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="{{ route('property.financial.report') }}" class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
