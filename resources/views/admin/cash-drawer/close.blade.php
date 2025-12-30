<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Tutup Cash Drawer"
            :subtitle="'Rekonsiliasi kas - ' . $drawer->property->name"
            :subtitleBelow="true"
        />
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Summary Before Closing --}}
            <x-card>
                <x-card.header>
                    <x-card.title>Ringkasan Transaksi Hari Ini</x-card.title>
                </x-card.header>
                <x-card.body>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Saldo Awal</p>
                            <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">
                                Rp {{ number_format($summary['opening_balance'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                            <p class="text-sm text-green-600 dark:text-green-400 font-medium">Total Cash IN</p>
                            <p class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1">
                                Rp {{ number_format($summary['total_cash_in'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                            <p class="text-sm text-red-600 dark:text-red-400 font-medium">Total Cash OUT</p>
                            <p class="text-2xl font-bold text-red-900 dark:text-red-100 mt-1">
                                Rp {{ number_format($summary['total_cash_out'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Expected Balance Calculation --}}
                    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-6">
                        <h4 class="text-sm font-semibold text-purple-900 dark:text-purple-100 mb-4">Kalkulasi Expected Balance:</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-purple-700 dark:text-purple-300">Saldo Awal:</span>
                                <span class="font-semibold text-purple-900 dark:text-purple-100">Rp {{ number_format($summary['opening_balance'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-purple-700 dark:text-purple-300">+ Total Cash IN:</span>
                                <span class="font-semibold text-green-600 dark:text-green-400">+ Rp {{ number_format($summary['total_cash_in'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-purple-700 dark:text-purple-300">- Total Cash OUT:</span>
                                <span class="font-semibold text-red-600 dark:text-red-400">- Rp {{ number_format($summary['total_cash_out'], 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-purple-300 dark:border-purple-700 pt-2 mt-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-purple-900 dark:text-purple-100">Expected Balance:</span>
                                    <span class="text-2xl font-bold text-purple-900 dark:text-purple-100">Rp {{ number_format($summary['expected_balance'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Transaction Breakdown --}}
                    <div class="mt-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Breakdown per Kategori:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($summary['transactions_by_category'] as $category => $data)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $data['label'] }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $data['count'] }} transaksi</p>
                                        </div>
                                        <div class="text-right">
                                            @if($data['in'] > 0)
                                                <p class="text-sm font-semibold text-green-600 dark:text-green-400">+ Rp {{ number_format($data['in'], 0, ',', '.') }}</p>
                                            @endif
                                            @if($data['out'] > 0)
                                                <p class="text-sm font-semibold text-red-600 dark:text-red-400">- Rp {{ number_format($data['out'], 0, ',', '.') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card.body>
            </x-card>

            {{-- Closing Form --}}
            <x-card>
                <form action="{{ route($routePrefix . '.cash-drawer.close', $drawer->id) }}" method="POST">
                    @csrf

                    <x-card.header>
                        <x-card.title>Hitung Uang Fisik & Tutup Drawer</x-card.title>
                    </x-card.header>

                    <x-card.body class="space-y-6">
                        {{-- Actual Closing Balance --}}
                        <div>
                            <x-input-label for="closing_balance" value="Saldo Akhir (Hasil Hitung Fisik)" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                                </div>
                                <input type="number"
                                       name="closing_balance"
                                       id="closing_balance"
                                       step="0.01"
                                       min="0"
                                       value="{{ old('closing_balance') }}"
                                       class="block w-full pl-10 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-lg font-semibold"
                                       placeholder="{{ number_format($summary['expected_balance'], 0, ',', '.') }}"
                                       required
                                       oninput="calculateVariance()">
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Hitung uang fisik di drawer, masukkan jumlah total
                            </p>
                            <x-input-error :messages="$errors->get('closing_balance')" class="mt-2" />
                        </div>

                        {{-- Variance Display (Dynamic) --}}
                        <div id="variance-display" class="hidden">
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Selisih Detected</h4>
                                        <div class="mt-2 text-sm">
                                            <p class="text-yellow-700 dark:text-yellow-400">
                                                <span class="font-semibold">Variance: <span id="variance-amount"></span></span>
                                            </p>
                                            <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-500">
                                                <span id="variance-message"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Closing Notes --}}
                        <div>
                            <x-input-label for="closing_notes" value="Catatan Penutupan" />
                            <textarea name="closing_notes"
                                      id="closing_notes"
                                      rows="3"
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Jelaskan jika ada selisih, atau catatan khusus lainnya...">{{ old('closing_notes') }}</textarea>
                            <x-input-error :messages="$errors->get('closing_notes')" class="mt-2" />
                        </div>

                        {{-- Warning Box --}}
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Perhatian!</h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Pastikan menghitung uang fisik dengan teliti</li>
                                            <li>Drawer yang sudah ditutup tidak bisa dibuka lagi</li>
                                            <li>Jika ada selisih, akan tercatat sebagai adjustment</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-card.body>

                    <x-card.footer>
                        <div class="flex items-center justify-between">
                            <a href="{{ route($routePrefix . '.cash-drawer.show', $drawer->id) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Tutup Drawer
                            </button>
                        </div>
                    </x-card.footer>
                </form>
            </x-card>

        </div>
    </div>

    @push('scripts')
    <script>
        const expectedBalance = {{ $summary['expected_balance'] }};

        function calculateVariance() {
            const closingBalance = parseFloat(document.getElementById('closing_balance').value) || 0;
            const variance = closingBalance - expectedBalance;
            const varianceDisplay = document.getElementById('variance-display');
            const varianceAmount = document.getElementById('variance-amount');
            const varianceMessage = document.getElementById('variance-message');

            if (closingBalance > 0 && variance !== 0) {
                varianceDisplay.classList.remove('hidden');
                const absVariance = Math.abs(variance);
                varianceAmount.textContent = (variance >= 0 ? '+' : '-') + ' Rp ' + new Intl.NumberFormat('id-ID').format(absVariance);

                if (variance > 0) {
                    varianceMessage.textContent = 'Uang fisik LEBIH Rp ' + new Intl.NumberFormat('id-ID').format(absVariance) + ' dari yang seharusnya';
                } else {
                    varianceMessage.textContent = 'Uang fisik KURANG Rp ' + new Intl.NumberFormat('id-ID').format(absVariance) + ' dari yang seharusnya';
                }
            } else {
                varianceDisplay.classList.add('hidden');
            }
        }
    </script>
    @endpush
</x-app-layout>
