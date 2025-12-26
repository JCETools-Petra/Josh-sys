<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight">
                    Laporan Keuangan P&L
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $property->name }} • {{ \Carbon\Carbon::create(2000, $month, 1)->format('F') }} {{ $year }}
                </p>
            </div>
            <nav class="flex flex-wrap items-center gap-2">
                <x-nav-link :href="route('admin.financial.select-property')"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Pilih Properti
                </x-nav-link>
                <x-nav-link :href="route('admin.financial.input-actual', $property->id)"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Input Data
                </x-nav-link>
            </nav>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Period Selector & Export Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.financial.report', $property->id) }}" class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                        <div class="flex flex-col sm:flex-row gap-4 flex-1">
                            <div class="flex-1 min-w-[140px]">
                                <label for="year" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Tahun
                                </label>
                                <select name="year" id="year"
                                    class="block w-full px-4 py-2.5 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1 min-w-[140px]">
                                <label for="month" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Bulan
                                </label>
                                <select name="month" id="month"
                                    class="block w-full px-4 py-2.5 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    @foreach($months as $m)
                                        <option value="{{ $m['value'] }}" {{ $month == $m['value'] ? 'selected' : '' }}>
                                            {{ $m['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                    class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 transition-all duration-200 shadow-md hover:shadow-lg">
                                    Tampilkan
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.financial.export-excel', ['property' => $property->id, 'year' => $year, 'month' => $month]) }}"
                               class="inline-flex items-center px-4 py-2.5 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm hover:shadow">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Excel
                            </a>
                            <a href="{{ route('admin.financial.export-pdf', ['property' => $property->id, 'year' => $year, 'month' => $month]) }}"
                               class="inline-flex items-center px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors shadow-sm hover:shadow">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                PDF
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Executive Summary - KPI Cards --}}
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Executive Summary</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    {{-- GOP % Card --}}
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl shadow-sm border border-emerald-200 dark:border-emerald-800 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">GOP %</div>
                            <div class="w-10 h-10 bg-emerald-500 dark:bg-emerald-600 rounded-lg flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-black {{ $kpis['gop_percentage'] > 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($kpis['gop_percentage'], 1) }}%
                        </div>
                        <div class="mt-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">Gross Operating Profit</div>
                    </div>

                    {{-- Labor Cost % Card --}}
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl shadow-sm border border-blue-200 dark:border-blue-800 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Labor Cost</div>
                            <div class="w-10 h-10 bg-blue-500 dark:bg-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-black text-blue-700 dark:text-blue-300">
                            {{ number_format($kpis['labor_cost_percentage'], 1) }}%
                        </div>
                        <div class="mt-2 text-xs text-blue-600 dark:text-blue-400 font-medium">of Total Revenue</div>
                    </div>

                    {{-- F&B Cost % Card --}}
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl shadow-sm border border-purple-200 dark:border-purple-800 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider">F&B Cost</div>
                            <div class="w-10 h-10 bg-purple-500 dark:bg-purple-600 rounded-lg flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-black text-purple-700 dark:text-purple-300">
                            {{ number_format($kpis['fnb_cost_percentage'], 1) }}%
                        </div>
                        <div class="mt-2 text-xs text-purple-600 dark:text-purple-400 font-medium">of F&B Revenue</div>
                    </div>

                    {{-- RevPAR Card --}}
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 rounded-xl shadow-sm border border-indigo-200 dark:border-indigo-800 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">RevPAR</div>
                            <div class="w-10 h-10 bg-indigo-500 dark:bg-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl lg:text-3xl font-black text-indigo-700 dark:text-indigo-300">
                            Rp {{ number_format($kpis['revenue_per_available_room'], 0) }}
                        </div>
                        <div class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 font-medium">Revenue per Available Room</div>
                    </div>

                    {{-- CPOR Card --}}
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-xl shadow-sm border border-orange-200 dark:border-orange-800 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xs font-semibold text-orange-600 dark:text-orange-400 uppercase tracking-wider">CPOR</div>
                            <div class="w-10 h-10 bg-orange-500 dark:bg-orange-600 rounded-lg flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl lg:text-3xl font-black text-orange-700 dark:text-orange-300">
                            Rp {{ number_format($kpis['cost_per_occupied_room'], 0) }}
                        </div>
                        <div class="mt-2 text-xs text-orange-600 dark:text-orange-400 font-medium">
                            Cost per Occupied Room <span class="font-semibold">({{ number_format($kpis['total_occupied_rooms']) }})</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Budget Alerts --}}
            @if(count($alerts) > 0)
            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border-l-4 border-yellow-400 rounded-xl shadow-sm p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-400 dark:bg-yellow-500 rounded-xl flex items-center justify-center shadow-md">
                            <svg class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-base font-bold text-yellow-900 dark:text-yellow-200 mb-1">
                            Budget Alert
                        </h4>
                        <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-3">
                            {{ count($alerts) }} kategori melebihi budget yang ditetapkan
                        </p>
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800">
                            <ul class="space-y-2">
                                @foreach(array_slice($alerts, 0, 3) as $alert)
                                <li class="flex items-start gap-2 text-sm">
                                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-900 dark:text-gray-100">
                                        <strong class="font-semibold">{{ $alert['category_name'] }}:</strong>
                                        <span class="text-red-600 dark:text-red-400 font-medium">Overbudget Rp {{ number_format($alert['variance'], 0) }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">({{ number_format($alert['variance_percentage'], 1) }}%)</span>
                                    </span>
                                </li>
                                @endforeach
                                @if(count($alerts) > 3)
                                <li class="text-xs text-gray-600 dark:text-gray-400 italic ml-6">
                                    ... dan {{ count($alerts) - 3 }} kategori lainnya
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Comparative Analysis --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Comparative Analysis</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Performance comparison across periods</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Current Period --}}
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/50 dark:to-gray-800/50 rounded-lg p-5 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Current Period</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $comparative['current']['period'] }}</div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Revenue</span>
                                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($comparative['current']['revenue'], 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1 pl-4">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">↳ MICE</span>
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Rp {{ number_format($comparative['current']['mice'], 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Expense</span>
                                    <span class="text-sm font-bold text-red-600 dark:text-red-400">Rp {{ number_format($comparative['current']['expense'], 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg px-3 -mx-1">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">GOP</span>
                                    <span class="text-sm font-black text-blue-600 dark:text-blue-400">Rp {{ number_format($comparative['current']['gop'], 0) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Month over Month --}}
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 rounded-lg p-5 border border-indigo-200 dark:border-indigo-800">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">Month over Month</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">vs {{ $comparative['mom']['period'] }}</div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Revenue</span>
                                    <span class="text-sm font-bold px-2.5 py-1 rounded-md {{ $comparative['mom']['revenue_change'] >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' }}">
                                        {{ $comparative['mom']['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['revenue_change'], 1) }}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-1 pl-4">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">↳ MICE</span>
                                    <span class="text-xs font-semibold {{ $comparative['mom']['mice_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $comparative['mom']['mice_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['mice_change'], 1) }}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Expense</span>
                                    <span class="text-sm font-bold px-2.5 py-1 rounded-md {{ $comparative['mom']['expense_change'] <= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' }}">
                                        {{ $comparative['mom']['expense_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['expense_change'], 1) }}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2 bg-white dark:bg-gray-800 rounded-lg px-3 -mx-1 shadow-sm">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">GOP</span>
                                    <span class="text-sm font-black px-2.5 py-1 rounded-md {{ $comparative['mom']['gop_change'] >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' }}">
                                        {{ $comparative['mom']['gop_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['gop_change'], 1) }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Year over Year --}}
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-5 border border-purple-200 dark:border-purple-800">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wide">Year over Year</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">vs {{ $comparative['yoy']['period'] }}</div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Revenue</span>
                                    <span class="text-sm font-bold px-2.5 py-1 rounded-md {{ $comparative['yoy']['revenue_change'] >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' }}">
                                        {{ $comparative['yoy']['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['revenue_change'], 1) }}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-1 pl-4">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">↳ MICE</span>
                                    <span class="text-xs font-semibold {{ $comparative['yoy']['mice_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $comparative['yoy']['mice_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['mice_change'], 1) }}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Expense</span>
                                    <span class="text-sm font-bold px-2.5 py-1 rounded-md {{ $comparative['yoy']['expense_change'] <= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' }}">
                                        {{ $comparative['yoy']['expense_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['expense_change'], 1) }}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2 bg-white dark:bg-gray-800 rounded-lg px-3 -mx-1 shadow-sm">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">GOP</span>
                                    <span class="text-sm font-black px-2.5 py-1 rounded-md {{ $comparative['yoy']['gop_change'] >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' }}">
                                        {{ $comparative['yoy']['gop_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['gop_change'], 1) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Visualizations --}}
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg flex items-center justify-center shadow-sm">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Data Visualization</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Visual insights from financial data</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-bold text-gray-900 dark:text-gray-100">Revenue & Expense Trend</h4>
                            <span class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 px-2 py-1 rounded-full font-medium">12 Months</span>
                        </div>
                        <div class="relative h-72 w-full">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-bold text-gray-900 dark:text-gray-100">Revenue Breakdown</h4>
                            <span class="text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 px-2 py-1 rounded-full font-medium">Current Month</span>
                        </div>
                        <div class="relative h-72 w-full">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-bold text-gray-900 dark:text-gray-100">Expense by Department</h4>
                            <span class="text-xs bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 px-2 py-1 rounded-full font-medium">Current Month</span>
                        </div>
                        <div class="relative h-72 w-full">
                            <canvas id="expenseChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-bold text-gray-900 dark:text-gray-100">Budget vs Actual</h4>
                            <span class="text-xs bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 px-2 py-1 rounded-full font-medium">Comparison</span>
                        </div>
                        <div class="relative h-72 w-full">
                            <canvas id="budgetChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Forecast Section --}}
            @if(count($forecast) > 0)
            <div class="bg-gradient-to-br from-slate-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Financial Forecast</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Projected performance for the next 3 months</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Month</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Revenue Forecast</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-red-700 dark:text-red-300 uppercase tracking-wider">Expense Forecast</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">GOP Forecast</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @foreach($forecast as $f)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $f['month'] }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($f['revenue_forecast'], 0) }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($f['expense_forecast'], 0) }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($f['gop_forecast'], 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">
                            Forecast based on historical trend analysis and seasonal patterns. Actual results may vary.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- P&L Statement Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-lg flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Profit & Loss Statement</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Detailed financial breakdown</p>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-4 py-2">
                            <p class="text-sm font-bold text-blue-900 dark:text-blue-100">
                                {{ \Carbon\Carbon::create(2000, $month, 1)->format('F') }} {{ $year }}
                            </p>
                        </div>
                    </div>

                    {{-- Breakfast Redistribution Info --}}
                    @if(isset($pnlData['breakfast_info']) && $pnlData['breakfast_info'])
                        @php
                            $info = $pnlData['breakfast_info'];
                        @endphp

                        @if($info['type'] === 'recipient')
                            {{-- Sunnyday Inn receives breakfast from other properties --}}
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 border-l-4 border-blue-500 rounded-lg p-6 shadow-sm">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-blue-500 dark:bg-blue-600 rounded-lg flex items-center justify-center shadow-md">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-2">
                                            {{ $info['message'] }}
                                        </h4>
                                        <p class="text-sm text-blue-800 dark:text-blue-200 mb-4">
                                            {{ $property->name }} menerima breakfast revenue dari properti berikut karena {{ $property->name }} yang menyediakan layanan breakfast untuk tamu mereka:
                                        </p>
                                        <div class="space-y-3">
                                            @foreach($info['details'] as $detail)
                                                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-blue-200 dark:border-blue-700 shadow-sm">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <h5 class="font-semibold text-gray-900 dark:text-white">{{ $detail['property_name'] }}</h5>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">Source Property</p>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                                                Rp {{ number_format($detail['current'], 0, ',', '.') }}
                                                            </div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400">Current Month</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-blue-900 dark:text-blue-100">Total Breakfast Revenue dari Properti Lain:</span>
                                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($info['total_current'], 0, ',', '.') }}</span>
                                            </div>
                                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-2">
                                                Revenue ini sudah termasuk dalam total F&B Revenue dan Breakfast Revenue di laporan ini.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @elseif($info['type'] === 'source')
                            {{-- Source properties (Akat, Ermasu, Bell) send breakfast to Sunnyday Inn --}}
                            <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 border-l-4 border-amber-500 rounded-lg p-6 shadow-sm">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-amber-500 dark:bg-amber-600 rounded-lg flex items-center justify-center shadow-md">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-lg font-bold text-amber-900 dark:text-amber-100 mb-2">
                                            {{ $info['message'] }}
                                        </h4>
                                        <p class="text-sm text-amber-800 dark:text-amber-200 mb-4">
                                            Breakfast untuk tamu {{ $property->name }} disediakan oleh {{ $info['recipient'] }}. Oleh karena itu, breakfast revenue dialihkan ke {{ $info['recipient'] }}.
                                        </p>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-amber-200 dark:border-amber-700 shadow-sm">
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900 rounded-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h5 class="font-semibold text-gray-900 dark:text-white">Breakfast Revenue Dialihkan</h5>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Current Month</p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-lg font-bold text-amber-600 dark:text-amber-400">
                                                        Rp {{ number_format($info['current'], 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-3 border-t border-amber-200 dark:border-amber-700">
                                                <p class="text-xs text-amber-700 dark:text-amber-300">
                                                    <strong>Catatan:</strong> Revenue ini TIDAK termasuk dalam total revenue {{ $property->name }} di laporan ini karena sudah dialihkan ke {{ $info['recipient'] }}.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex items-center gap-2 text-xs text-amber-700 dark:text-amber-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Sistem telah otomatis mengurangi breakfast revenue dari total F&B revenue {{ $property->name }}.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gradient-to-r from-gray-800 to-gray-900 dark:from-gray-900 dark:to-black">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider sticky left-0 bg-gray-800 dark:bg-gray-900 z-10">
                                        Description
                                    </th>
                                    <th colspan="3" class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider border-l-2 border-blue-400">
                                        <div class="flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Current Month
                                        </div>
                                    </th>
                                    <th colspan="3" class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider border-l-2 border-purple-400">
                                        <div class="flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            Year to Date (YTD)
                                        </div>
                                    </th>
                                </tr>
                                <tr class="bg-gray-700 dark:bg-gray-800">
                                    <th class="px-6 py-3 sticky left-0 bg-gray-700 dark:bg-gray-800 z-10"></th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-emerald-300 uppercase tracking-wide">Actual</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-blue-300 uppercase tracking-wide">Budget</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-yellow-300 uppercase tracking-wide border-r-2 border-gray-600">Variance</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-emerald-300 uppercase tracking-wide">Actual</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-blue-300 uppercase tracking-wide">Budget</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-yellow-300 uppercase tracking-wide">Variance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr class="bg-gradient-to-r from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border-l-4 border-emerald-600">
                                    <td colspan="7" class="px-6 py-4 text-sm font-bold text-emerald-900 dark:text-emerald-100 uppercase tracking-wide flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        REVENUE (PENDAPATAN)
                                    </td>
                                </tr>

                                @foreach($pnlData['categories'] as $category)
                                    @if($category['type'] === 'revenue')
                                        @include('financial.partials.category-row', ['category' => $category])
                                    @endif
                                @endforeach

                                <tr class="bg-gradient-to-r from-emerald-200 to-green-200 dark:from-emerald-800 dark:to-green-800 border-t-4 border-emerald-700 shadow-sm">
                                    <td class="px-6 py-4 text-base font-black text-emerald-900 dark:text-emerald-100 uppercase">
                                        TOTAL REVENUE
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black text-emerald-900 dark:text-emerald-100">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['actual_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black text-emerald-900 dark:text-emerald-100">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['budget_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black border-r-2 border-gray-400 {{ $pnlData['totals']['total_revenue']['variance_current'] >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['variance_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black text-emerald-900 dark:text-emerald-100">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['actual_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black text-emerald-900 dark:text-emerald-100">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['budget_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black {{ $pnlData['totals']['total_revenue']['variance_ytd'] >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['variance_ytd'], 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr class="bg-gradient-to-r from-red-100 to-rose-100 dark:from-red-900 dark:to-rose-900 border-l-4 border-red-600">
                                    <td colspan="7" class="px-6 py-4 text-sm font-bold text-red-900 dark:text-red-100 uppercase tracking-wide flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        EXPENSES (PENGELUARAN)
                                    </td>
                                </tr>

                                @foreach($pnlData['categories'] as $category)
                                    @if($category['type'] === 'expense')
                                        @include('financial.partials.category-row', ['category' => $category])
                                    @endif
                                @endforeach

                                <tr class="bg-gradient-to-r from-red-200 to-rose-200 dark:from-red-800 dark:to-rose-800 border-t-4 border-red-700 shadow-sm">
                                    <td class="px-6 py-4 text-base font-black text-red-900 dark:text-red-100 uppercase">
                                        TOTAL EXPENSES
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black text-red-900 dark:text-red-100">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['actual_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black text-red-900 dark:text-red-100">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['budget_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black border-r-2 border-gray-400 {{ $pnlData['totals']['total_expenses']['variance_current'] <= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['variance_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black text-red-900 dark:text-red-100">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['actual_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black text-red-900 dark:text-red-100">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['budget_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-black {{ $pnlData['totals']['total_expenses']['variance_ytd'] <= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['variance_ytd'], 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr class="bg-gradient-to-r from-blue-200 to-indigo-200 dark:from-blue-800 dark:to-indigo-800 border-t-4 border-l-4 border-blue-700 shadow-lg">
                                    <td class="px-6 py-5 text-lg font-black text-blue-900 dark:text-blue-50 uppercase flex items-center gap-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                        </svg>
                                        GROSS OPERATING PROFIT (GOP)
                                    </td>
                                    <td class="px-6 py-5 text-right text-lg font-black text-blue-900 dark:text-blue-50">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['actual_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-5 text-right text-lg font-black text-blue-900 dark:text-blue-50">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['budget_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-5 text-right text-lg font-black border-r-2 border-gray-500 {{ $pnlData['totals']['gross_operating_profit']['variance_current'] >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['variance_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-5 text-right text-lg font-black text-blue-900 dark:text-blue-50">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['actual_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-5 text-right text-lg font-black text-blue-900 dark:text-blue-50">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['budget_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-5 text-right text-lg font-black {{ $pnlData['totals']['gross_operating_profit']['variance_ytd'] >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['variance_ytd'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Chart.js data from backend
        const chartData = @json($chartData);

        // Validate chartData structure
        if (!chartData) {
            console.error('Chart data is missing');
        }

        // Ensure arrays exist
        if (!chartData.monthly_trend) chartData.monthly_trend = [];
        if (!chartData.revenue_breakdown) chartData.revenue_breakdown = [];
        if (!chartData.expense_breakdown) chartData.expense_breakdown = [];

        // Trend Chart (Revenue & Expense over 12 months)
        if (chartData.monthly_trend && chartData.monthly_trend.length > 0) {
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: chartData.monthly_trend.map(d => d.month),
                    datasets: [
                        {
                            label: 'Revenue (Actual)',
                            data: chartData.monthly_trend.map(d => d.revenue_actual),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.3
                        },
                        {
                            label: 'Revenue (Budget)',
                            data: chartData.monthly_trend.map(d => d.revenue_budget),
                            borderColor: 'rgb(134, 239, 172)',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            tension: 0.3
                        },
                        {
                            label: 'Expense (Actual)',
                            data: chartData.monthly_trend.map(d => d.expense_actual),
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.3
                        },
                        {
                            label: 'Expense (Budget)',
                            data: chartData.monthly_trend.map(d => d.expense_budget),
                            borderColor: 'rgb(252, 165, 165)',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('trendChart').parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">No trend data available for this period</p>';
        }

        // Revenue Breakdown Pie Chart
        if (chartData.revenue_breakdown && chartData.revenue_breakdown.length > 0) {
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'pie',
                data: {
                    labels: chartData.revenue_breakdown.map(d => d.name),
                    datasets: [{
                        data: chartData.revenue_breakdown.map(d => d.value),
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(59, 130, 246)',
                            'rgb(168, 85, 247)',
                            'rgb(251, 146, 60)',
                            'rgb(236, 72, 153)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('revenueChart').parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">No revenue data available for this period</p>';
        }

        // Expense Breakdown Doughnut Chart
        if (chartData.expense_breakdown && chartData.expense_breakdown.length > 0) {
            const expenseCtx = document.getElementById('expenseChart').getContext('2d');
            new Chart(expenseCtx, {
                type: 'doughnut',
                data: {
                    labels: chartData.expense_breakdown.map(d => d.name),
                    datasets: [{
                        data: chartData.expense_breakdown.map(d => d.value),
                        backgroundColor: [
                            'rgb(239, 68, 68)',
                            'rgb(249, 115, 22)',
                            'rgb(234, 179, 8)',
                            'rgb(168, 85, 247)',
                            'rgb(236, 72, 153)',
                            'rgb(14, 165, 233)',
                            'rgb(34, 197, 94)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('expenseChart').parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">No expense data available for this period</p>';
        }

        // Budget vs Actual Bar Chart (Current Month)
        if (chartData.monthly_trend && chartData.monthly_trend.length > 0) {
            const budgetCtx = document.getElementById('budgetChart').getContext('2d');
            const currentMonthData = chartData.monthly_trend[chartData.monthly_trend.length - 1];
            new Chart(budgetCtx, {
                type: 'bar',
                data: {
                    labels: ['Revenue', 'Expense', 'GOP'],
                    datasets: [
                        {
                            label: 'Actual',
                            data: [
                                currentMonthData.revenue_actual,
                                currentMonthData.expense_actual,
                                currentMonthData.gop_actual
                            ],
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(59, 130, 246, 0.8)'
                            ]
                        },
                        {
                            label: 'Budget',
                            data: [
                                currentMonthData.revenue_budget,
                                currentMonthData.expense_budget,
                                currentMonthData.gop_budget
                            ],
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.4)',
                                'rgba(239, 68, 68, 0.4)',
                                'rgba(59, 130, 246, 0.4)'
                            ]
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('budgetChart').parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">No budget data available for this period</p>';
        }
    </script>
    @endpush
</x-app-layout>