<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Cash Drawer Management"
            subtitle="Kelola kas harian front office"
            :subtitleBelow="true"
        >
            <x-slot name="actions">
                @if(!$activeDrawer)
                    <a href="{{ route($routePrefix . '.cash-drawer.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Buka Drawer
                    </a>
                @endif
                <a href="{{ route($routePrefix . '.cash-drawer.history') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    History & Reports
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Active Drawer Status --}}
            @if($activeDrawer)
                <x-card>
                    <x-card.header>
                        <div class="flex items-center space-x-3">
                            <div class="bg-green-100 dark:bg-green-900 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <x-card.title>Drawer Aktif</x-card.title>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Dibuka oleh {{ $activeDrawer->openedBy->name }} - {{ $activeDrawer->opened_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route($routePrefix . '.cash-drawer.show', $activeDrawer->id) }}" class="inline-flex items-center px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Detail
                            </a>
                            <a href="{{ route($routePrefix . '.cash-drawer.edit', $activeDrawer->id) }}" class="inline-flex items-center px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Tutup Drawer
                            </a>
                        </div>
                    </x-card.header>

                    <x-card.body>
                        {{-- Cash Summary Stats --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <x-stats-card
                                title="Saldo Awal"
                                :value="'Rp ' . number_format($summary['opening_balance'], 0, ',', '.')"
                                iconColor="blue"
                            >
                                <x-slot name="icon">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </x-slot>
                            </x-stats-card>

                            <x-stats-card
                                title="Total Cash IN"
                                :value="'Rp ' . number_format($summary['total_cash_in'], 0, ',', '.')"
                                iconColor="green"
                                trend="+{{ count(array_filter($activeDrawer->transactions->toArray(), fn($t) => $t['type'] === 'in')) }} transaksi"
                                trendDirection="up"
                            >
                                <x-slot name="icon">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </x-slot>
                            </x-stats-card>

                            <x-stats-card
                                title="Total Cash OUT"
                                :value="'Rp ' . number_format($summary['total_cash_out'], 0, ',', '.')"
                                iconColor="red"
                                trend="-{{ count(array_filter($activeDrawer->transactions->toArray(), fn($t) => $t['type'] === 'out')) }} transaksi"
                                trendDirection="down"
                            >
                                <x-slot name="icon">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                    </svg>
                                </x-slot>
                            </x-stats-card>

                            <x-stats-card
                                title="Expected Balance"
                                :value="'Rp ' . number_format($summary['expected_balance'], 0, ',', '.')"
                                iconColor="purple"
                                description="Saldo yang seharusnya"
                            >
                                <x-slot name="icon">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </x-slot>
                            </x-stats-card>
                        </div>

                        {{-- Recent Transactions --}}
                        <div class="mt-6">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Transaksi Terakhir (5 terbaru)</h4>
                            <x-table>
                                <x-table.head>
                                    <tr>
                                        <x-table.header>Waktu</x-table.header>
                                        <x-table.header>Kategori</x-table.header>
                                        <x-table.header>Deskripsi</x-table.header>
                                        <x-table.header align="right">Amount</x-table.header>
                                    </tr>
                                </x-table.head>
                                <x-table.body>
                                    @forelse($activeDrawer->transactions->take(5) as $transaction)
                                        <x-table.row>
                                            <x-table.cell nowrap>{{ $transaction->created_at->format('H:i') }}</x-table.cell>
                                            <x-table.cell>
                                                <span class="px-2 py-1 text-xs rounded-full {{ $transaction->type === 'in' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                    {{ $transaction->category_label }}
                                                </span>
                                            </x-table.cell>
                                            <x-table.cell>{{ $transaction->description }}</x-table.cell>
                                            <x-table.cell align="right" nowrap>
                                                <span class="font-semibold {{ $transaction->type === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ $transaction->type === 'in' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                                </span>
                                            </x-table.cell>
                                        </x-table.row>
                                    @empty
                                        <x-table.row>
                                            <x-table.cell colspan="4" align="center">
                                                <span class="text-gray-500 dark:text-gray-400">Belum ada transaksi</span>
                                            </x-table.cell>
                                        </x-table.row>
                                    @endforelse
                                </x-table.body>
                            </x-table>
                        </div>
                    </x-card.body>
                </x-card>
            @else
                {{-- No Active Drawer --}}
                <x-card>
                    <x-card.body class="text-center py-12">
                        <div class="bg-gray-100 dark:bg-gray-700 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak Ada Drawer Aktif</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">Buka drawer baru untuk mulai mencatat transaksi kas harian</p>
                        <a href="{{ route($routePrefix . '.cash-drawer.create') }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Buka Drawer Baru
                        </a>
                    </x-card.body>
                </x-card>
            @endif

            {{-- Recent Closed Drawers --}}
            @if($recentDrawers->count() > 0)
                <x-card>
                    <x-card.header>
                        <x-card.title>Riwayat Drawer Terakhir</x-card.title>
                        <a href="{{ route($routePrefix . '.cash-drawer.history') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
                    </x-card.header>

                    <x-card.body class="p-0">
                        <x-table>
                            <x-table.head>
                                <tr>
                                    <x-table.header>Tanggal</x-table.header>
                                    <x-table.header>Shift</x-table.header>
                                    <x-table.header>Dibuka Oleh</x-table.header>
                                    <x-table.header align="right">Opening</x-table.header>
                                    <x-table.header align="right">Closing</x-table.header>
                                    <x-table.header align="right">Variance</x-table.header>
                                    <x-table.header align="center">Aksi</x-table.header>
                                </tr>
                            </x-table.head>
                            <x-table.body>
                                @foreach($recentDrawers as $drawer)
                                    <x-table.row>
                                        <x-table.cell nowrap>{{ $drawer->drawer_date->format('d M Y') }}</x-table.cell>
                                        <x-table.cell>
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ ucfirst(str_replace('_', ' ', $drawer->shift_type)) }}
                                            </span>
                                        </x-table.cell>
                                        <x-table.cell>{{ $drawer->openedBy->name }}</x-table.cell>
                                        <x-table.cell align="right">Rp {{ number_format($drawer->opening_balance, 0, ',', '.') }}</x-table.cell>
                                        <x-table.cell align="right">Rp {{ number_format($drawer->closing_balance, 0, ',', '.') }}</x-table.cell>
                                        <x-table.cell align="right">
                                            <span class="font-semibold {{ $drawer->variance == 0 ? 'text-gray-600' : ($drawer->variance > 0 ? 'text-green-600' : 'text-red-600') }}">
                                                {{ $drawer->variance > 0 ? '+' : '' }}Rp {{ number_format($drawer->variance, 0, ',', '.') }}
                                            </span>
                                        </x-table.cell>
                                        <x-table.cell align="center">
                                            <a href="{{ route($routePrefix . '.cash-drawer.show', $drawer->id) }}" class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                        </x-table.cell>
                                    </x-table.row>
                                @endforeach
                            </x-table.body>
                        </x-table>
                    </x-card.body>
                </x-card>
            @endif

        </div>
    </div>
</x-app-layout>
