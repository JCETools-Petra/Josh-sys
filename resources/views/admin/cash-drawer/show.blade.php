<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="'Cash Drawer - ' . $drawer->drawer_date->format('d M Y')"
            :subtitle="$drawer->property->name . ' - ' . ucfirst(str_replace('_', ' ', $drawer->shift_type))"
            :subtitleBelow="true"
        >
            <x-slot name="actions">
                @if($drawer->isOpen())
                    <a href="{{ route($routePrefix . '.cash-drawer.edit', $drawer->id) }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Tutup Drawer
                    </a>
                @endif
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

            {{-- Status Badge --}}
            <div class="flex justify-center">
                @if($drawer->isOpen())
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        DRAWER AKTIF
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        DRAWER DITUTUP
                    </span>
                @endif
            </div>

            {{-- Summary Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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
                >
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </x-slot>
                </x-stats-card>

                @if($drawer->isClosed())
                    <x-stats-card
                        title="Variance"
                        :value="($drawer->variance >= 0 ? '+' : '') . 'Rp ' . number_format($drawer->variance, 0, ',', '.')"
                        :iconColor="$drawer->variance == 0 ? 'gray' : ($drawer->variance > 0 ? 'green' : 'red')"
                        :description="$drawer->variance == 0 ? 'Balance' : ($drawer->variance > 0 ? 'Lebih' : 'Kurang')"
                    >
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </x-slot>
                    </x-stats-card>
                @endif
            </div>

            {{-- Drawer Info --}}
            <x-card>
                <x-card.header>
                    <x-card.title>Informasi Drawer</x-card.title>
                </x-card.header>
                <x-card.body>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuka Oleh</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $drawer->openedBy->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Waktu Buka</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $drawer->opened_at->format('d M Y H:i') }}</dd>
                        </div>
                        @if($drawer->isClosed())
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ditutup Oleh</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $drawer->closedBy->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Waktu Tutup</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $drawer->closed_at->format('d M Y H:i') }}</dd>
                            </div>
                        @endif
                        @if($drawer->opening_notes)
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan Pembukaan</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $drawer->opening_notes }}</dd>
                            </div>
                        @endif
                        @if($drawer->closing_notes)
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan Penutupan</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $drawer->closing_notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-card.body>
            </x-card>

            {{-- Transactions List --}}
            <x-card>
                <x-card.header>
                    <x-card.title>Daftar Transaksi ({{ $drawer->transactions->count() }})</x-card.title>
                </x-card.header>

                <x-card.body class="p-0">
                    <x-table>
                        <x-table.head>
                            <tr>
                                <x-table.header>Waktu</x-table.header>
                                <x-table.header>Kategori</x-table.header>
                                <x-table.header>Deskripsi</x-table.header>
                                <x-table.header>Reference</x-table.header>
                                <x-table.header align="right">Amount</x-table.header>
                                <x-table.header>User</x-table.header>
                            </tr>
                        </x-table.head>
                        <x-table.body>
                            @forelse($drawer->transactions as $transaction)
                                <x-table.row>
                                    <x-table.cell nowrap>{{ $transaction->created_at->format('H:i:s') }}</x-table.cell>
                                    <x-table.cell>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $transaction->type === 'in' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $transaction->category_label }}
                                        </span>
                                    </x-table.cell>
                                    <x-table.cell>{{ $transaction->description }}</x-table.cell>
                                    <x-table.cell>
                                        @if($transaction->reference_type && $transaction->reference_id)
                                            <span class="text-xs text-gray-500">
                                                {{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </x-table.cell>
                                    <x-table.cell align="right" nowrap>
                                        <span class="font-semibold {{ $transaction->type === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $transaction->type === 'in' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </span>
                                    </x-table.cell>
                                    <x-table.cell>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $transaction->createdBy->name }}
                                        </span>
                                    </x-table.cell>
                                </x-table.row>
                            @empty
                                <x-table.row>
                                    <x-table.cell colspan="6" align="center">
                                        <span class="text-gray-500 dark:text-gray-400">Belum ada transaksi</span>
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
