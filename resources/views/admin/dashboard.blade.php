<x-app-layout>
    @php
        $chartColors = ['#e6194B', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#808000', '#000075', '#a9a9a9'];

        $revenueTitle = 'Ringkasan Pendapatan';
        if ($period === 'today') {
            $revenueTitle = 'Daily Revenue';
        } elseif ($period === 'month') {
            $revenueTitle = 'Monthly Revenue';
        } elseif ($period === 'year') {
            $revenueTitle = 'Yearly Revenue';
        } elseif ($period === 'custom') {
            if ($startDate->isSameDay($endDate)) {
                $revenueTitle = 'Revenue ' . $startDate->isoFormat('D MMMM YYYY');
            } elseif ($startDate->day == 1 && $endDate->day == $endDate->daysInMonth) {
                $revenueTitle = $startDate->isoFormat('MMMM YYYY') . ' Revenue';
            } else {
                $revenueTitle = 'Periode Revenue (' . $startDate->isoFormat('D MMM YY') . ' - ' . $endDate->isoFormat('D MMM YY') . ')';
            }
        }

        // Calculate KPI metrics
        $totalRoomsSold = $properties->sum(fn($p) =>
            ($p->total_offline_rooms ?? 0) +
            ($p->total_online_rooms ?? 0) +
            ($p->total_ta_rooms ?? 0) +
            ($p->total_gov_rooms ?? 0) +
            ($p->total_corp_rooms ?? 0) +
            ($p->total_afiliasi_rooms ?? 0)
        );

        $totalRoomRevenue = $properties->sum(fn($p) =>
            ($p->total_offline_room_income ?? 0) +
            ($p->total_online_room_income ?? 0) +
            ($p->total_ta_income ?? 0) +
            ($p->total_gov_income ?? 0) +
            ($p->total_corp_income ?? 0) +
            ($p->total_afiliasi_room_income ?? 0)
        );

        $avgARR = $totalRoomsSold > 0 ? $totalRoomRevenue / $totalRoomsSold : 0;

        // Calculate total available rooms for the period
        $numberOfDays = $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1;
        $totalAvailableRooms = $properties->sum('total_rooms') * $numberOfDays;
        $occupancyRate = $totalAvailableRooms > 0 ? ($totalRoomsSold / $totalAvailableRooms) * 100 : 0;
    @endphp

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }} - {{ $revenueTitle }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium">{{ $startDate->isoFormat('D MMM YYYY') }}</span> - <span class="font-medium">{{ $endDate->isoFormat('D MMM YYYY') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- FILTER SECTION --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <form action="{{ route('admin.dashboard') }}" method="GET" id="filter-form">
                    <input type="hidden" name="property_id" id="property_id_hidden">
                    <input type="hidden" name="start_date" id="start_date_hidden" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                    <input type="hidden" name="end_date" id="end_date_hidden" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                    <input type="hidden" name="period" id="period_hidden">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        {{-- Property Filter --}}
                        <div>
                            <label for="property_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Properti
                            </label>
                            <select id="property_select" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Properti</option>
                                @foreach($allPropertiesForFilter as $property)
                                    <option value="{{ $property->id }}" @selected($propertyId == $property->id)>{{ $property->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Quick Period Filters --}}
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Periode
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" data-period="today" class="filter-button quick-filter-btn px-4 py-2 rounded-lg text-sm font-medium {{ $period == 'today' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                    Hari Ini
                                </button>
                                <button type="button" data-period="month" class="filter-button quick-filter-btn px-4 py-2 rounded-lg text-sm font-medium {{ $period == 'month' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                    Bulan Ini
                                </button>
                                <button type="button" data-period="year" class="filter-button quick-filter-btn px-4 py-2 rounded-lg text-sm font-medium {{ $period == 'year' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                    Tahun Ini
                                </button>
                                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reset
                                </a>
                                <button type="button" id="export-excel-btn" class="px-4 py-2 rounded-lg text-sm font-medium bg-green-600 text-white hover:bg-green-700">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Export Excel
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Custom Date Range (Collapsible) --}}
                    <div x-data="{ open: {{ $period == 'custom' ? 'true' : 'false' }} }" class="mt-4">
                        <button type="button" @click="open = !open" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                            <span x-show="!open">+ Pilih Periode Kustom</span>
                            <span x-show="open">- Tutup Periode Kustom</span>
                        </button>

                        <div x-show="open" x-collapse class="mt-3 pt-3 border-t dark:border-gray-700">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                                <div>
                                    <label for="year_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                                    <select id="year_select" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-lg shadow-sm">
                                        @for ($y = now()->year + 1; $y >= now()->year - 5; $y--)
                                            <option value="{{ $y }}" @selected($startDate->year == $y)>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label for="month_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bulan</label>
                                    <select id="month_select" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-lg shadow-sm">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" @selected($startDate->month == $m)>{{ \Carbon\Carbon::create(null, $m)->isoFormat('MMMM') }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label for="day_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                                    <select id="day_select" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-lg shadow-sm"></select>
                                </div>
                                <div class="flex items-center">
                                    <label for="full_month_checkbox" class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" id="full_month_checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm"
                                               @if($period === 'custom' && $startDate->day == 1 && $endDate->day == $endDate->daysInMonth) checked @endif>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sebulan Penuh</span>
                                    </label>
                                </div>
                                <div>
                                    <button type="button" id="apply_custom_filter" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm font-medium">
                                        Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- KPI CARDS SECTION --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Revenue Card --}}
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium mb-1">Total Pendapatan</p>
                            <p class="text-3xl font-bold">Rp {{ number_format($totalOverallRevenue ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white bg-opacity-30 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $properties->count() }} Properti</span>
                    </div>
                </div>

                {{-- Occupancy Rate Card --}}
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium mb-1">Tingkat Okupansi</p>
                            <p class="text-3xl font-bold">{{ number_format($occupancyRate, 1) }}%</p>
                        </div>
                        <div class="bg-white bg-opacity-30 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span>{{ number_format($totalRoomsSold) }} / {{ number_format($totalAvailableRooms) }} Kamar</span>
                    </div>
                </div>

                {{-- Average ARR Card --}}
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium mb-1">Average Room Rate</p>
                            <p class="text-3xl font-bold">Rp {{ number_format($avgARR, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white bg-opacity-30 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span>Per Kamar Terjual</span>
                    </div>
                </div>

                {{-- Total Rooms Sold Card --}}
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium mb-1">Kamar Terjual</p>
                            <p class="text-3xl font-bold">{{ number_format($totalRoomsSold) }}</p>
                        </div>
                        <div class="bg-white bg-opacity-30 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span>{{ $numberOfDays }} Hari Periode</span>
                    </div>
                </div>
            </div>

            {{-- CHARTS SECTION --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Revenue Distribution Pie Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Distribusi Sumber Pendapatan</h3>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                    </div>
                    <div id="pieChartContainer" class="flex flex-col md:flex-row items-center gap-4">
                        <div class="w-full md:w-1/2" style="height: 280px;">
                            <canvas id="overallSourcePieChart"></canvas>
                        </div>
                        <div class="w-full md:w-1/2 space-y-2" id="pieChartLegend"></div>
                    </div>
                </div>

                {{-- Revenue by Property Bar Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pendapatan per Properti</h3>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div id="barChartContainer" style="height: 280px;">
                        <canvas id="overallIncomeByPropertyBarChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- PROPERTY CARDS SECTION --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Detail Properti
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($properties as $property)
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/50 dark:to-gray-800/50 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
                            @include('admin.properties._property_card', ['property' => $property, 'incomeCategories' => $incomeCategories, 'revenueTitle' => $revenueTitle])
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400 text-lg">Tidak ada data properti untuk periode ini</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- MICE BOOKINGS SECTION --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Event MICE Terkini (Top 10)
                    </h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $recentMiceBookings->count() }} Event</span>
                </div>

                @if($recentMiceBookings->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400">Tidak ada booking MICE untuk periode ini</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Klien</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Properti</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($recentMiceBookings as $booking)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($booking->event_date)->isoFormat('DD MMM YYYY') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $booking->client_name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ $booking->property->name ?? '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                                {{ $booking->miceCategory->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-right text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filter-form');
            const yearSelect = document.getElementById('year_select');
            const monthSelect = document.getElementById('month_select');
            const daySelect = document.getElementById('day_select');
            const fullMonthCheckbox = document.getElementById('full_month_checkbox');
            const propertySelect = document.getElementById('property_select');

            const startDateHidden = document.getElementById('start_date_hidden');
            const endDateHidden = document.getElementById('end_date_hidden');
            const propertyIdHidden = document.getElementById('property_id_hidden');
            const periodHidden = document.getElementById('period_hidden');

            const populateDays = () => {
                const year = yearSelect.value;
                const month = monthSelect.value;
                const daysInMonth = new Date(year, month, 0).getDate();

                const currentDay = '{{ $period === "custom" ? $startDate->day : now()->day }}';
                let selectedDay = daySelect.value || currentDay;
                if (selectedDay > daysInMonth) {
                    selectedDay = daysInMonth;
                }

                daySelect.innerHTML = '';
                for (let i = 1; i <= daysInMonth; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.text = i;
                    if (i == selectedDay) {
                        option.selected = true;
                    }
                    daySelect.appendChild(option);
                }
            };

            const toggleDaySelect = () => {
                daySelect.disabled = fullMonthCheckbox.checked;
                daySelect.classList.toggle('bg-gray-200', fullMonthCheckbox.checked);
                daySelect.classList.toggle('dark:bg-gray-800', fullMonthCheckbox.checked);
            };

            const submitForm = () => {
                propertyIdHidden.value = propertySelect.value;
                form.submit();
            };

            // Quick filter buttons
            document.querySelectorAll('.quick-filter-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const period = this.dataset.period;
                    periodHidden.value = period;

                    const now = new Date();
                    const year = yearSelect.value || now.getFullYear();
                    const month = (now.getMonth() + 1).toString().padStart(2, '0');
                    const day = now.getDate().toString().padStart(2, '0');

                    if (period === 'today') {
                        startDateHidden.value = `${year}-${month}-${day}`;
                        endDateHidden.value = `${year}-${month}-${day}`;
                    } else if (period === 'month') {
                        startDateHidden.value = `${year}-${month}-01`;
                        const lastDay = new Date(year, month, 0).getDate();
                        endDateHidden.value = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
                    } else if (period === 'year') {
                        startDateHidden.value = `${year}-01-01`;
                        endDateHidden.value = `${year}-12-31`;
                    }

                    submitForm();
                });
            });

            document.getElementById('apply_custom_filter').addEventListener('click', function() {
                const year = yearSelect.value;
                const month = monthSelect.value.toString().padStart(2, '0');
                let startDateStr, endDateStr;

                if (fullMonthCheckbox.checked) {
                    startDateStr = `${year}-${month}-01`;
                    const lastDay = new Date(year, month, 0).getDate();
                    endDateStr = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
                } else {
                    const day = daySelect.value.toString().padStart(2, '0');
                    startDateStr = `${year}-${month}-${day}`;
                    endDateStr = startDateStr;
                }

                startDateHidden.value = startDateStr;
                endDateHidden.value = endDateStr;
                periodHidden.value = 'custom';

                submitForm();
            });

            yearSelect.addEventListener('change', populateDays);
            monthSelect.addEventListener('change', populateDays);
            fullMonthCheckbox.addEventListener('change', toggleDaySelect);

            populateDays();
            toggleDaySelect();

            // Charts
            const isDarkMode = document.documentElement.classList.contains('dark');
            Chart.defaults.color = isDarkMode ? '#e5e7eb' : '#6b7280';
            Chart.defaults.borderColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            const pieChartData = @json($pieChartDataSource);
            const pieChartCategories = @json($pieChartCategories);
            const overallIncomeByPropertyData = @json($overallIncomeByProperty);
            const chartColors = @json($chartColors);
            let myPieChart;
            const pieCanvas = document.getElementById('overallSourcePieChart');
            const pieLegendContainer = document.getElementById('pieChartLegend');
            const pieChartContainer = document.getElementById('pieChartContainer');
            if (pieCanvas && pieLegendContainer && pieChartContainer) {
                const pieLabels = Object.values(pieChartCategories);
                const pieDataValues = pieChartData ? Object.keys(pieChartCategories).map(key => pieChartData['total_' + key] || 0) : [];
                const hasPieData = pieDataValues.some(v => v > 0);
                if (hasPieData) {
                    myPieChart = new Chart(pieCanvas, {type: 'pie',data: { labels: pieLabels, datasets: [{ data: pieDataValues, backgroundColor: chartColors }] },options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }});
                    pieLegendContainer.innerHTML = '';
                    pieLabels.forEach((label, index) => {
                        const value = pieDataValues[index];
                        if (value > 0) {
                            const legendItem = document.createElement('div');
                            legendItem.classList.add('flex', 'items-center', 'p-2', 'rounded-lg', 'cursor-pointer', 'hover:bg-gray-100', 'dark:hover:bg-gray-700', 'transition-colors');
                            legendItem.dataset.index = index;
                            legendItem.innerHTML = `<span class="w-4 h-4 rounded-full mr-3 flex-shrink-0" style="background-color: ${chartColors[index % chartColors.length]};"></span><div class="flex justify-between items-center w-full text-sm"><span class="legend-label text-gray-700 dark:text-gray-300 mr-2 truncate font-medium" title="${label}">${label}</span><span class="font-bold text-gray-900 dark:text-gray-100 text-right whitespace-nowrap">Rp ${new Intl.NumberFormat('id-ID').format(value)}</span></div>`;
                            legendItem.addEventListener('click', (event) => {
                                const clickedIndex = parseInt(event.currentTarget.dataset.index);
                                myPieChart.toggleDataVisibility(clickedIndex);
                                myPieChart.update();
                                event.currentTarget.classList.toggle('opacity-50');
                                event.currentTarget.querySelector('.legend-label').classList.toggle('line-through');
                            });
                            pieLegendContainer.appendChild(legendItem);
                        }
                    });
                } else {
                    pieChartContainer.innerHTML = `<div class="flex items-center justify-center w-full h-full text-gray-500 dark:text-gray-400">Tidak ada data untuk filter ini.</div>`;
                }
            }
            const barCanvas = document.getElementById('overallIncomeByPropertyBarChart');
            if (barCanvas) {
                const hasBarData = overallIncomeByPropertyData && overallIncomeByPropertyData.some(p => p.total_revenue > 0);
                if (hasBarData) {
                    const propertyColors = overallIncomeByPropertyData.map(p => p.chart_color || '#36A2EB');
                    new Chart(barCanvas, {type: 'bar',data: {labels: overallIncomeByPropertyData.map(p => p.name),datasets: [{label: 'Total Pendapatan (Rp)',data: overallIncomeByPropertyData.map(p => p.total_revenue || 0),backgroundColor: propertyColors,}]},options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value); } } } }, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(context) { return 'Total: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y); } } } } }});
                } else {
                    const barContainer = document.getElementById('barChartContainer');
                    barContainer.innerHTML = `<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Tidak ada data pendapatan pada periode ini.</div>`;
                }
            }

            // Export Excel button
            const exportButton = document.getElementById('export-excel-btn');
            if (exportButton) {
                exportButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    const startDate = document.getElementById('start_date_hidden').value;
                    const endDate = document.getElementById('end_date_hidden').value;

                    let exportUrl = new URL("{{ route('admin.dashboard.exportExcel') }}", window.location.origin);

                    if (startDate) {
                        exportUrl.searchParams.append('start_date', startDate);
                    }
                    if (endDate) {
                        exportUrl.searchParams.append('end_date', endDate);
                    }

                    window.location.href = exportUrl.toString();
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
