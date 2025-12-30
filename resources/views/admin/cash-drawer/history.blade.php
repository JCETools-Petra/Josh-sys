<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Cash Drawer History & Reports"
            subtitle="Riwayat dan laporan cash drawer"
            :subtitleBelow="true"
        >
            <x-slot name="actions">
                <a href="{{ route($routePrefix . '.cash-drawer.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Section --}}
            <x-card>
                <x-card.header>
                    <x-card.title>Filter Laporan</x-card.title>
                </x-card.header>
                <x-card.body>
                    <form action="{{ route($routePrefix . '.cash-drawer.history') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            {{-- Property Filter --}}
                            <div>
                                <x-input-label for="property_id" value="Properti" />
                                <select name="property_id" id="property_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Semua Properti</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ $propertyId == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Start Date --}}
                            <div>
                                <x-input-label for="start_date" value="Tanggal Mulai" />
                                <input type="date"
                                       name="start_date"
                                       id="start_date"
                                       value="{{ $startDate }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- End Date --}}
                            <div>
                                <x-input-label for="end_date" value="Tanggal Akhir" />
                                <input type="date"
                                       name="end_date"
                                       id="end_date"
                                       value="{{ $endDate }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- Submit Button --}}
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </x-card.body>
            </x-card>

            {{-- Summary Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-stats-card
                    title="Total Drawers"
                    :value="$drawers->count()"
                    iconColor="blue"
                    description="Periode yang dipilih"
                >
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </x-slot>
                </x-stats-card>

                <x-stats-card
                    title="Total Opening Balance"
                    :value="'Rp ' . number_format($totalOpening, 0, ',', '.')"
                    iconColor="green"
                >
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </x-slot>
                </x-stats-card>

                <x-stats-card
                    title="Total Closing Balance"
                    :value="'Rp ' . number_format($totalClosing, 0, ',', '.')"
                    iconColor="purple"
                >
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </x-slot>
                </x-stats-card>

                <x-stats-card
                    title="Total Variance"
                    :value="($totalVariance >= 0 ? '+' : '') . 'Rp ' . number_format($totalVariance, 0, ',', '.')"
                    :iconColor="$totalVariance == 0 ? 'gray' : ($totalVariance > 0 ? 'green' : 'red')"
                >
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </x-slot>
                </x-stats-card>
            </div>

            {{-- Drawers List --}}
            <x-card>
                <x-card.header>
                    <x-card.title>Daftar Cash Drawer ({{ $drawers->count() }})</x-card.title>
                </x-card.header>

                <x-card.body class="p-0">
                    <x-table>
                        <x-table.head>
                            <tr>
                                <x-table.header>Tanggal</x-table.header>
                                <x-table.header>Properti</x-table.header>
                                <x-table.header>Shift</x-table.header>
                                <x-table.header>Dibuka Oleh</x-table.header>
                                <x-table.header>Ditutup Oleh</x-table.header>
                                <x-table.header align="right">Opening</x-table.header>
                                <x-table.header align="right">Closing</x-table.header>
                                <x-table.header align="right">Variance</x-table.header>
                                <x-table.header align="center">Status</x-table.header>
                                <x-table.header align="center">Aksi</x-table.header>
                            </tr>
                        </x-table.head>
                        <x-table.body>
                            @forelse($drawers as $drawer)
                                <x-table.row>
                                    <x-table.cell nowrap>{{ $drawer->drawer_date->format('d M Y') }}</x-table.cell>
                                    <x-table.cell>{{ $drawer->property->name }}</x-table.cell>
                                    <x-table.cell>
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            {{ ucfirst(str_replace('_', ' ', $drawer->shift_type)) }}
                                        </span>
                                    </x-table.cell>
                                    <x-table.cell>{{ $drawer->openedBy->name }}</x-table.cell>
                                    <x-table.cell>{{ $drawer->closedBy ? $drawer->closedBy->name : '-' }}</x-table.cell>
                                    <x-table.cell align="right">Rp {{ number_format($drawer->opening_balance, 0, ',', '.') }}</x-table.cell>
                                    <x-table.cell align="right">
                                        {{ $drawer->closing_balance ? 'Rp ' . number_format($drawer->closing_balance, 0, ',', '.') : '-' }}
                                    </x-table.cell>
                                    <x-table.cell align="right">
                                        @if($drawer->isClosed())
                                            <span class="font-semibold {{ $drawer->variance == 0 ? 'text-gray-600' : ($drawer->variance > 0 ? 'text-green-600' : 'text-red-600') }}">
                                                {{ $drawer->variance > 0 ? '+' : '' }}Rp {{ number_format($drawer->variance, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </x-table.cell>
                                    <x-table.cell align="center">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $drawer->isOpen() ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                            {{ $drawer->isOpen() ? 'Open' : 'Closed' }}
                                        </span>
                                    </x-table.cell>
                                    <x-table.cell align="center">
                                        <a href="{{ route($routePrefix . '.cash-drawer.show', $drawer->id) }}" class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    </x-table.cell>
                                </x-table.row>
                            @empty
                                <x-table.row>
                                    <x-table.cell colspan="10" align="center">
                                        <div class="py-8">
                                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-gray-500 dark:text-gray-400">Tidak ada data drawer untuk periode yang dipilih</p>
                                        </div>
                                    </x-table.cell>
                                </x-table.row>
                            @endforelse
                        </x-table.body>
                    </x-table>
                </x-card.body>
            </x-card>

        </div>
    </div>
</x-app-layout>
