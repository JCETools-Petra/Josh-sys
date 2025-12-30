<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Buka Cash Drawer Baru"
            subtitle="Mulai shift baru dengan saldo awal"
            :subtitleBelow="true"
        />
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <x-card>
                <form action="{{ route($routePrefix . '.cash-drawer.store') }}" method="POST">
                    @csrf

                    <x-card.body class="space-y-6">
                        {{-- Property Selection --}}
                        <div>
                            <x-input-label for="property_id" value="Properti" />
                            <select name="property_id" id="property_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Pilih Properti</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ old('property_id', auth()->user()->property_id) == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('property_id')" class="mt-2" />
                        </div>

                        {{-- Shift Type --}}
                        <div>
                            <x-input-label for="shift_type" value="Tipe Shift" />
                            <select name="shift_type" id="shift_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="full_day" {{ old('shift_type') == 'full_day' ? 'selected' : '' }}>Full Day (Seharian)</option>
                                <option value="morning" {{ old('shift_type') == 'morning' ? 'selected' : '' }}>Morning (Pagi)</option>
                                <option value="afternoon" {{ old('shift_type') == 'afternoon' ? 'selected' : '' }}>Afternoon (Siang)</option>
                                <option value="night" {{ old('shift_type') == 'night' ? 'selected' : '' }}>Night (Malam)</option>
                            </select>
                            <x-input-error :messages="$errors->get('shift_type')" class="mt-2" />
                        </div>

                        {{-- Opening Balance --}}
                        <div>
                            <x-input-label for="opening_balance" value="Saldo Awal" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                                </div>
                                <input type="number"
                                       name="opening_balance"
                                       id="opening_balance"
                                       step="0.01"
                                       min="0"
                                       value="{{ old('opening_balance', 1000000) }}"
                                       class="block w-full pl-10 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="1000000"
                                       required>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jumlah uang tunai awal yang tersedia untuk kembalian</p>
                            <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                        </div>

                        {{-- Opening Notes --}}
                        <div>
                            <x-input-label for="opening_notes" value="Catatan (Opsional)" />
                            <textarea name="opening_notes"
                                      id="opening_notes"
                                      rows="3"
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Catatan khusus untuk pembukaan drawer ini...">{{ old('opening_notes') }}</textarea>
                            <x-input-error :messages="$errors->get('opening_notes')" class="mt-2" />
                        </div>

                        {{-- Info Box --}}
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Informasi Penting</h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Pastikan menghitung uang fisik sebelum input saldo awal</li>
                                            <li>Saldo awal akan digunakan sebagai patokan rekonsiliasi</li>
                                            <li>Semua transaksi cash akan tercatat otomatis dari sistem</li>
                                            <li>Drawer hanya bisa dibuka satu untuk satu properti</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-card.body>

                    <x-card.footer>
                        <div class="flex items-center justify-between">
                            <a href="{{ route($routePrefix . '.cash-drawer.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Kembali
                            </a>
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Buka Drawer
                            </button>
                        </div>
                    </x-card.footer>
                </form>
            </x-card>

        </div>
    </div>
</x-app-layout>
