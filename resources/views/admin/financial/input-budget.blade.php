<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Input Budget Tahunan - ') }} {{ $property->name }}
            </h2>
            <nav class="flex flex-wrap items-center space-x-2 sm:space-x-3">
                <x-nav-link :href="route('admin.financial.select-property')" class="ml-3">
                    {{ __('← Pilih Properti Lain') }}
                </x-nav-link>
                <x-nav-link :href="route('admin.financial.report', $property->id)" class="ml-3">
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

                    @php
                        // Check if budget data already exists for this year
                        $existingBudgetCount = \App\Models\FinancialEntry::where('property_id', $property->id)
                            ->where('year', $year)
                            ->where('budget_value', '>', 0)
                            ->count();
                        $hasExistingBudget = $existingBudgetCount > 0;
                    @endphp

                    @if ($hasExistingBudget)
                        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                        ⚠️ Data Budget Sudah Ada untuk Tahun {{ $year }}
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p class="mb-2">
                                            Terdapat <strong>{{ number_format($existingBudgetCount) }} entries</strong> budget yang sudah tersimpan untuk tahun ini.
                                        </p>
                                        <p class="mb-2 font-semibold">PENTING: Hindari Double Input!</p>
                                        <ul class="list-disc list-inside space-y-1 ml-2">
                                            <li>Jika Anda sudah <strong>IMPORT EXCEL</strong>, <span class="font-bold text-red-600">JANGAN klik "Simpan Budget"</span> lagi di form manual!</li>
                                            <li>Jika ingin update budget, gunakan <strong>IMPORT EXCEL</strong> saja (akan otomatis replace data lama)</li>
                                            <li>Jika ingin input manual baru, hapus data lama dulu dengan command: <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">php artisan budget:clear {{ $property->id }} {{ $year }}</code></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Year Selection -->
                    <form method="GET" action="{{ route('admin.financial.input-budget', $property->id) }}" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow">
                        <div class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-4 md:space-y-0">
                            <div class="flex-1">
                                <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun Budget</label>
                                <select name="year" id="year" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    @for($y = date('Y'); $y <= date('Y') + 3; $y++)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Pilih Tahun</button>
                            </div>
                        </div>
                    </form>

                    <!-- Template Download/Upload Section -->
                    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-3">Template Excel Budget</h3>
                        <p class="text-sm text-green-700 dark:text-green-300 mb-4">
                            Download template Excel untuk mengisi budget per bulan (Januari - Desember), kemudian upload kembali untuk import otomatis.
                        </p>

                        @if (session('warning'))
                            <div class="mb-4 text-sm text-yellow-800 dark:text-yellow-200 bg-yellow-100 dark:bg-yellow-700 border border-yellow-400 dark:border-yellow-600 rounded-md p-3">
                                {!! session('warning') !!}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="mb-4 font-medium text-sm text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-700 border border-red-400 dark:border-red-600 rounded-md p-3">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-4 md:space-y-0">
                            <!-- Download Template Button -->
                            <div>
                                <a href="{{ route('admin.financial.budget-template.download', ['property' => $property->id, 'year' => $year]) }}"
                                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium shadow-sm">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download Template Excel
                                </a>
                            </div>

                            <!-- Upload Form -->
                            <div class="flex-1">
                                <form method="POST" action="{{ route('admin.financial.budget-template.import', $property->id) }}" enctype="multipart/form-data" class="flex items-center space-x-2">
                                    @csrf
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <input type="file"
                                           name="file"
                                           accept=".xlsx,.xls"
                                           required
                                           class="block w-full text-sm text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer bg-gray-50 dark:bg-gray-900 focus:outline-none">
                                    <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium shadow-sm whitespace-nowrap">
                                        Upload & Import
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Tahun:</strong> {{ $year }}
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">
                            Input budget tahunan untuk masing-masing kategori. Budget akan didistribusikan secara merata ke 12 bulan.
                        </p>
                    </div>

                    <!-- Input Form -->
                    <form method="POST" action="{{ route('admin.financial.input-budget.store', $property->id) }}" x-data="{ activeTab: 0 }">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">

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
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Budget Tahunan (Rp)
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
                                                            name="entries[{{ $loop->parent->index }}_{{ $loop->index }}][budget_value]"
                                                            step="0.01"
                                                            min="0"
                                                            value="{{ $existingEntries[$cat['id']] ?? 0 }}"
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
                            <a href="{{ route('admin.financial.select-property') }}" class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                Kembali
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Simpan Budget
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
