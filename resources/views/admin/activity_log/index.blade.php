<x-admin-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Log Aktivitas Pengguna')"
            :badge="'Total: ' . $logs->total() . ' log'"
        />
    </x-slot>

    <div x-data="{ open: false, log: {} }" class="py-12">
        {{-- Modal untuk menampilkan detail --}}
        <div x-show="open" @keydown.window.escape="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>
            <div @click.away="open = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-700 dark:to-blue-900 px-6 py-4 rounded-t-lg">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Detail Log Aktivitas
                    </h3>
                </div>

                <div class="p-6">
                    {{-- Informasi Utama --}}
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pengguna</label>
                                <p class="text-sm text-gray-900 dark:text-gray-100 font-medium" x-text="log.user ? log.user.name : 'Sistem'"></p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Properti</label>
                                <p class="text-sm text-gray-900 dark:text-gray-100 font-medium" x-text="log.property ? log.property.name : '-'"></p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</label>
                                <p class="text-sm">
                                    <span x-show="log.action === 'create'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        Buat Baru
                                    </span>
                                    <span x-show="log.action === 'update'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        Perbarui
                                    </span>
                                    <span x-show="log.action === 'delete'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                        Hapus
                                    </span>
                                    <span x-show="log.action === 'restore'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                        Pulihkan
                                    </span>
                                    <span x-show="log.action === 'force_delete'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-200 text-red-900 dark:bg-red-900 dark:text-red-100">
                                        Hapus Permanen
                                    </span>
                                    <span x-show="log.action === 'view'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100">
                                        Lihat
                                    </span>
                                    <span x-show="log.action === 'export'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-800 dark:text-teal-100">
                                        Ekspor
                                    </span>
                                    <span x-show="log.action === 'import'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800 dark:bg-pink-800 dark:text-pink-100">
                                        Impor
                                    </span>
                                    <span x-show="log.action === 'login'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100">
                                        Login
                                    </span>
                                    <span x-show="log.action === 'logout'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        Logout
                                    </span>
                                    <span x-show="!['create', 'update', 'delete', 'restore', 'force_delete', 'view', 'export', 'import', 'login', 'logout'].includes(log.action)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100" x-text="log.action">
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Waktu</label>
                                <p class="text-sm text-gray-900 dark:text-gray-100 font-medium" x-text="log.created_at"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-4">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2 block">Deskripsi</label>
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                            <p class="text-sm text-gray-900 dark:text-gray-100" x-text="log.description || 'Tidak ada deskripsi'"></p>
                        </div>
                    </div>

                    {{-- Rincian Perubahan --}}
                    <div x-show="log.changes && Object.keys(log.changes).length > 0">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2 block">Rincian Perubahan</label>
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Field</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Nilai Lama</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Nilai Baru</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="(change, attribute) in log.changes" :key="attribute">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 capitalize" x-text="attribute"></td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-sm bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200 font-mono" x-text="change.old || '-'"></span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-sm bg-green-50 dark:bg-green-900 text-green-700 dark:text-green-200 font-mono" x-text="change.new || '-'"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Teknis --}}
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2 block">Informasi Teknis</label>
                        <div class="grid grid-cols-1 gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                <span><strong>IP Address:</strong> <span x-text="log.ip_address || 'N/A'"></span></span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="break-all"><strong>User Agent:</strong> <span x-text="log.user_agent || 'N/A'"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-700 px-6 py-4 rounded-b-lg flex justify-end">
                    <button @click="open = false" class="px-6 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors font-medium">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Konten utama halaman --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                {{-- Total Aktivitas --}}
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 dark:from-blue-600 dark:to-blue-800 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Aktivitas</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['total']) }}</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Hari Ini --}}
                <div class="bg-gradient-to-br from-green-500 to-green-700 dark:from-green-600 dark:to-green-800 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Hari Ini</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['today']) }}</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Minggu Ini --}}
                <div class="bg-gradient-to-br from-purple-500 to-purple-700 dark:from-purple-600 dark:to-purple-800 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Minggu Ini</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['this_week']) }}</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Tipe Aksi Terbanyak --}}
                <div class="bg-gradient-to-br from-orange-500 to-orange-700 dark:from-orange-600 dark:to-orange-800 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Aksi Terbanyak</p>
                            @php
                                $topAction = collect($stats['by_action'])->sortDesc()->keys()->first();
                                $topActionLabel = $actions[$topAction] ?? ucfirst($topAction ?? '-');
                            @endphp
                            <p class="text-2xl font-bold mt-2">{{ $topActionLabel }}</p>
                            <p class="text-orange-100 text-xs mt-1">{{ number_format($stats['by_action'][$topAction] ?? 0) }} kali</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Filter --}}
            <div class="mb-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filter Log Aktivitas
                    </h3>
                    <div class="flex gap-2">
                        <button type="button" onclick="window.location.reload()" class="inline-flex items-center px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                        <a href="{{ route('admin.activity_log.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="inline-flex items-center px-3 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Excel
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.activity_log.index') }}" method="GET" class="p-6" x-data="{
                    setDateRange(range) {
                        const today = new Date();
                        let startDate, endDate;

                        if (range === 'today') {
                            startDate = endDate = today.toISOString().split('T')[0];
                        } else if (range === 'last7days') {
                            endDate = today.toISOString().split('T')[0];
                            startDate = new Date(today.setDate(today.getDate() - 7)).toISOString().split('T')[0];
                        } else if (range === 'last30days') {
                            endDate = new Date().toISOString().split('T')[0];
                            startDate = new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0];
                        } else if (range === 'thismonth') {
                            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                            startDate = firstDay.toISOString().split('T')[0];
                            endDate = lastDay.toISOString().split('T')[0];
                        }

                        document.getElementById('start_date').value = startDate;
                        document.getElementById('end_date').value = endDate;
                        this.$el.submit();
                    }
                }">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- Search --}}
                        <div class="lg:col-span-3">
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Cari
                            </label>
                            <input type="text" name="search" id="search" placeholder="Cari deskripsi, pengguna, atau properti..."
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   value="{{ request('search') }}">
                        </div>

                        {{-- Date Range Quick Presets --}}
                        <div class="lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Rentang Waktu Cepat
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="setDateRange('today')" class="px-4 py-2 text-sm bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-200 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors border border-blue-200 dark:border-blue-700">
                                    Hari Ini
                                </button>
                                <button type="button" @click="setDateRange('last7days')" class="px-4 py-2 text-sm bg-purple-50 dark:bg-purple-900 text-purple-700 dark:text-purple-200 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-800 transition-colors border border-purple-200 dark:border-purple-700">
                                    7 Hari Terakhir
                                </button>
                                <button type="button" @click="setDateRange('last30days')" class="px-4 py-2 text-sm bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors border border-indigo-200 dark:border-indigo-700">
                                    30 Hari Terakhir
                                </button>
                                <button type="button" @click="setDateRange('thismonth')" class="px-4 py-2 text-sm bg-green-50 dark:bg-green-900 text-green-700 dark:text-green-200 rounded-lg hover:bg-green-100 dark:hover:bg-green-800 transition-colors border border-green-200 dark:border-green-700">
                                    Bulan Ini
                                </button>
                            </div>
                        </div>

                        {{-- Start Date --}}
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Dari Tanggal
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   value="{{ request('start_date') }}">
                        </div>

                        {{-- End Date --}}
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Sampai Tanggal
                            </label>
                            <input type="date" name="end_date" id="end_date"
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   value="{{ request('end_date') }}">
                        </div>

                        {{-- Action Filter --}}
                        <div>
                            <label for="action" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Tipe Aksi
                            </label>
                            <select name="action" id="action" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Aksi</option>
                                @foreach($actions as $key => $label)
                                    <option value="{{ $key }}" {{ request('action') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Property Filter --}}
                        <div>
                            <label for="property_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Properti
                            </label>
                            <select name="property_id" id="property_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Properti</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>{{ $property->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- User Filter --}}
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Pengguna
                            </label>
                            <select name="user_id" id="user_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Pengguna</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                        <a href="{{ route('admin.activity_log.index') }}" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors font-medium text-center">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Filter
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- Active Filters Indicator --}}
            @if(request()->hasAny(['search', 'start_date', 'end_date', 'action', 'property_id', 'user_id']))
                <div class="mb-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Filter Aktif
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                @if(request('search'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white dark:bg-blue-800 text-blue-800 dark:text-blue-100 border border-blue-200 dark:border-blue-600">
                                        Pencarian: "{{ request('search') }}"
                                    </span>
                                @endif
                                @if(request('start_date'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white dark:bg-blue-800 text-blue-800 dark:text-blue-100 border border-blue-200 dark:border-blue-600">
                                        Dari: {{ \Carbon\Carbon::parse(request('start_date'))->format('d M Y') }}
                                    </span>
                                @endif
                                @if(request('end_date'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white dark:bg-blue-800 text-blue-800 dark:text-blue-100 border border-blue-200 dark:border-blue-600">
                                        Sampai: {{ \Carbon\Carbon::parse(request('end_date'))->format('d M Y') }}
                                    </span>
                                @endif
                                @if(request('action'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white dark:bg-blue-800 text-blue-800 dark:text-blue-100 border border-blue-200 dark:border-blue-600">
                                        Aksi: {{ $actions[request('action')] ?? request('action') }}
                                    </span>
                                @endif
                                @if(request('property_id'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white dark:bg-blue-800 text-blue-800 dark:text-blue-100 border border-blue-200 dark:border-blue-600">
                                        Properti: {{ $properties->find(request('property_id'))->name ?? request('property_id') }}
                                    </span>
                                @endif
                                @if(request('user_id'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white dark:bg-blue-800 text-blue-800 dark:text-blue-100 border border-blue-200 dark:border-blue-600">
                                        Pengguna: {{ $users->find(request('user_id'))->name ?? request('user_id') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.activity_log.index') }}" class="ml-4 text-sm text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100 font-medium">
                            Hapus Filter
                        </a>
                    </div>
                </div>
            @endif

            {{-- Tabel Log --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Daftar Aktivitas
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $logs->total() }} log)</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider w-8">Aksi</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Waktu</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Pengguna</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider hidden lg:table-cell">Properti</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($logs as $log)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-3 py-3 whitespace-nowrap w-8">
                                            <button @click="open = true; log = {{ json_encode($log) }}" class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 hover:bg-blue-50 dark:hover:bg-blue-900 rounded-lg transition-colors" title="Lihat Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $log->created_at->format('d/m') }}</span>
                                                <span class="text-xs text-gray-400">{{ $log->created_at->format('H:i') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-7 w-7 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                                    {{ substr($log->user->name ?? 'S', 0, 1) }}
                                                </div>
                                                <div class="ml-2 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $log->user->name ?? 'Sistem' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden lg:table-cell">
                                            @if($log->property)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                                    {{ $log->property->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            @if($log->action === 'create')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Buat Baru
                                                </span>
                                            @elseif($log->action === 'update')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                                    </svg>
                                                    Perbarui
                                                </span>
                                            @elseif($log->action === 'delete')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Hapus
                                                </span>
                                            @elseif($log->action === 'restore')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Pulihkan
                                                </span>
                                            @elseif($log->action === 'force_delete')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-200 text-red-900 dark:bg-red-900 dark:text-red-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Hapus Permanen
                                                </span>
                                            @elseif($log->action === 'view')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Lihat
                                                </span>
                                            @elseif($log->action === 'export')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-800 dark:text-teal-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Ekspor
                                                </span>
                                            @elseif($log->action === 'import')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800 dark:bg-pink-800 dark:text-pink-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Impor
                                                </span>
                                            @elseif($log->action === 'login')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Login
                                                </span>
                                            @elseif($log->action === 'logout')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Logout
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ $actions[$log->action] ?? ucfirst($log->action) }}
                                                </span>
                                            @endif
                                            <div class="mt-2 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->description }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 lg:hidden">
                                                @if($log->property)
                                                    <span class="inline-flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                        {{ $log->property->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">
                                                    @if(request()->hasAny(['search', 'start_date', 'end_date', 'action', 'property_id', 'user_id']))
                                                        Tidak ada aktivitas yang cocok dengan filter Anda
                                                    @else
                                                        Belum ada aktivitas yang tercatat
                                                    @endif
                                                </p>
                                                <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">
                                                    @if(request()->hasAny(['search', 'start_date', 'end_date', 'action', 'property_id', 'user_id']))
                                                        Coba ubah atau reset filter Anda
                                                    @else
                                                        Log aktivitas akan muncul di sini
                                                    @endif
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($logs->hasPages())
                        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
