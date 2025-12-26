<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('KPI Performance Analytics Center') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Comprehensive Key Performance Indicators Dashboard</p>
            </div>
            @if(auth()->user()->role === 'owner')
                <span class="inline-flex items-center px-3 py-2 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-medium rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Only Mode
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            {{-- ENHANCED FILTER SECTION --}}
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-xl sm:rounded-2xl mb-8">
                <div class="p-8 text-white">
                    <div class="flex items-center mb-6">
                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        <h3 class="text-2xl font-bold">Analysis Filters</h3>
                    </div>

                    <form action="{{ route('admin.kpi.analysis') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="md:col-span-2">
                                <label for="property_id" class="block text-sm font-semibold mb-2 text-white">Select Property</label>
                                <select name="property_id" id="property_id" class="block w-full pl-4 pr-10 py-3 text-base border-0 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 sm:text-sm rounded-xl text-gray-900 shadow-lg">
                                    <option value="">-- All Properties --</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}" {{ $propertyId == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="start_date" class="block text-sm font-semibold mb-2 text-white">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-lg focus:ring-2 focus:ring-white focus:ring-opacity-50 sm:text-sm">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-semibold mb-2 text-white">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-lg focus:ring-2 focus:ring-white focus:ring-opacity-50 sm:text-sm">
                            </div>
                        </div>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-gray-50 transform transition hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Apply Filters
                            </button>
                            @if($kpiData)
                                @can('manage-data')
                                    <a href="#" id="export-excel-btn" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-xl font-semibold text-sm shadow-lg hover:bg-green-700 transform transition hover:scale-105">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Export Excel
                                    </a>
                                @endcan
                                <button onclick="window.print()" class="inline-flex items-center px-6 py-3 bg-yellow-500 text-white rounded-xl font-semibold text-sm shadow-lg hover:bg-yellow-600 transform transition hover:scale-105">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Export PDF
                                </button>
                            @endif
                        </div>
                    </form>

                    <script>
                        document.getElementById('export-excel-btn')?.addEventListener('click', function(e) {
                            e.preventDefault();
                            const form = this.closest('form');
                            const propertyId = form.querySelector('[name="property_id"]').value;
                            const startDate = form.querySelector('[name="start_date"]').value;
                            const endDate = form.querySelector('[name="end_date"]').value;

                            const exportUrl = new URL("{{ route('admin.kpi.analysis.export') }}");
                            exportUrl.searchParams.append('property_id', propertyId);
                            exportUrl.searchParams.append('start_date', startDate);
                            exportUrl.searchParams.append('end_date', endDate);

                            window.location.href = exportUrl.toString();
                        });
                    </script>
                </div>
            </div>

            @if ($kpiData)
                {{-- PERFORMANCE SUMMARY BADGE --}}
                <div class="mb-8">
                    @php
                        $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
                        $performanceScore = min(100, ($kpiData['avgOccupancy'] * 0.4) + (($kpiData['revPar'] / 500000) * 60));
                        $performanceLevel = $performanceScore >= 80 ? 'Excellent' : ($performanceScore >= 60 ? 'Good' : ($performanceScore >= 40 ? 'Average' : 'Needs Improvement'));
                        $performanceColor = $performanceScore >= 80 ? 'from-green-500 to-emerald-600' : ($performanceScore >= 60 ? 'from-blue-500 to-cyan-600' : ($performanceScore >= 40 ? 'from-yellow-500 to-orange-600' : 'from-red-500 to-pink-600'));
                    @endphp

                    <div class="bg-gradient-to-r {{ $performanceColor }} rounded-2xl shadow-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-white text-opacity-90">Performance Analysis Period</h3>
                                <p class="text-2xl font-bold mt-1">{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMM YYYY') }}</p>
                                <p class="text-sm text-white text-opacity-80 mt-1">{{ $days }} days | {{ $selectedProperty ? $selectedProperty->name : 'All Properties' }}</p>
                            </div>
                            <div class="text-right">
                                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-white bg-opacity-20 backdrop-blur-sm">
                                    <div class="text-center">
                                        <p class="text-3xl font-bold">{{ number_format($performanceScore, 0) }}</p>
                                        <p class="text-xs">Score</p>
                                    </div>
                                </div>
                                <p class="text-sm font-semibold mt-2">{{ $performanceLevel }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PRIMARY KPI CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    {{-- Total Revenue Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden group">
                        <div class="h-2 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Total Revenue</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        Rp {{ number_format($kpiData['totalRevenue'], 0, ',', '.') }}
                                    </p>
                                    @if(isset($kpiData['revenueGrowth']))
                                        <p class="text-xs mt-2 {{ $kpiData['revenueGrowth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $kpiData['revenueGrowth'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($kpiData['revenueGrowth']), 1) }}% vs last period
                                        </p>
                                    @endif
                                </div>
                                <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-xl">
                                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Average Occupancy Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden group">
                        <div class="h-2 bg-gradient-to-r from-green-500 to-emerald-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Avg Occupancy</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                                        {{ number_format($kpiData['avgOccupancy'], 1) }}%
                                    </p>
                                    <div class="mt-2 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $kpiData['avgOccupancy']) }}%"></div>
                                    </div>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900 p-4 rounded-xl">
                                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ARR Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden group">
                        <div class="h-2 bg-gradient-to-r from-purple-500 to-pink-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Avg Room Rate (ARR)</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                        Rp {{ number_format($kpiData['avgArr'], 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Per room sold</p>
                                </div>
                                <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-xl">
                                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RevPAR Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden group">
                        <div class="h-2 bg-gradient-to-r from-orange-500 to-red-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">RevPAR</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                        Rp {{ number_format($kpiData['revPar'], 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Revenue per available room</p>
                                </div>
                                <div class="bg-orange-100 dark:bg-orange-900 p-4 rounded-xl">
                                    <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECONDARY KPI CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">F&B Revenue/Room</p>
                            <svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($kpiData['restoRevenuePerRoom'], 0, ',', '.') }}</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Rooms Sold</p>
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($kpiData['totalRoomsSold']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Room nights</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Average Daily Rate</p>
                            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($kpiData['totalRevenue'] / max(1, $days), 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Per day</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Room Revenue Share</p>
                            <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                            </svg>
                        </div>
                        @php
                            $roomShare = $kpiData['grandTotalRevenue'] > 0 ? ($kpiData['totalRoomRevenue'] / $kpiData['grandTotalRevenue']) * 100 : 0;
                        @endphp
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($roomShare, 1) }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Of total revenue</p>
                    </div>
                </div>

                {{-- CHARTS SECTION --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {{-- Daily Performance Chart --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Daily Performance Trend</h4>
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-medium rounded-full">Line Chart</span>
                        </div>
                        <div class="relative h-80">
                            <canvas id="kpiChart"></canvas>
                        </div>
                    </div>

                    {{-- Revenue Distribution Chart --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue Distribution</h4>
                            <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 text-xs font-medium rounded-full">Doughnut Chart</span>
                        </div>
                        <div class="relative h-80">
                            <canvas id="revenueDistributionChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Additional Analysis Charts --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {{-- Room Type Performance --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue by Channel</h4>
                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs font-medium rounded-full">Bar Chart</span>
                        </div>
                        <div class="relative h-80">
                            <canvas id="channelRevenueChart"></canvas>
                        </div>
                    </div>

                    {{-- Occupancy vs Target --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Occupancy vs Average</h4>
                            <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 text-xs font-medium rounded-full">Area Chart</span>
                        </div>
                        <div class="relative h-80">
                            <canvas id="occupancyTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- REVENUE BREAKDOWN SECTION --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {{-- Room Revenue Breakdown --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Room Revenue Breakdown</h4>
                        <div class="space-y-4">
                            @foreach($kpiData['roomRevenueBreakdown'] as $source => $amount)
                                @php
                                    $percentage = $kpiData['totalRoomRevenue'] > 0 ? ($amount / $kpiData['totalRoomRevenue']) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $source }}</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ number_format($percentage, 1) }}% of room revenue</p>
                                </div>
                            @endforeach
                            <div class="pt-4 border-t-2 border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <span class="text-base font-bold text-gray-900 dark:text-gray-100">Total Room Revenue</span>
                                    <span class="text-base font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($kpiData['totalRoomRevenue'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Other Revenue & Rooms Sold --}}
                    <div class="space-y-6">
                        {{-- Other Revenue --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                            <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Other Revenue Sources</h4>
                            <div class="space-y-3">
                                @foreach($kpiData['fbRevenueBreakdown'] as $source => $amount)
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $source }}</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                                <div class="flex items-center justify-between py-2 font-bold">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Total F&B</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">Rp {{ number_format($kpiData['totalFbRevenue'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2 font-bold">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">MICE/Event</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">Rp {{ number_format($kpiData['miceRevenue'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2 font-bold">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Others</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">Rp {{ number_format($kpiData['totalOtherRevenue'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Rooms Sold Breakdown --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                            <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Rooms Sold by Channel</h4>
                            <div class="space-y-3">
                                @foreach($kpiData['roomsSoldBreakdown'] as $source => $qty)
                                    @php
                                        $percentage = $kpiData['totalRoomsSold'] > 0 ? ($qty / $kpiData['totalRoomsSold']) * 100 : 0;
                                    @endphp
                                    <div class="flex items-center justify-between py-2">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $source }}</span>
                                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($qty) }} rooms</span>
                                            </div>
                                            <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                                <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="pt-3 border-t-2 border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between font-bold">
                                        <span class="text-sm text-gray-900 dark:text-gray-100">Total Rooms Sold</span>
                                        <span class="text-sm text-green-600 dark:text-green-400">{{ number_format($kpiData['totalRoomsSold']) }} rooms</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GRAND TOTAL --}}
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl p-8 mb-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-lg font-semibold text-white text-opacity-90 mb-2">GRAND TOTAL REVENUE</p>
                            <p class="text-5xl font-bold">Rp {{ number_format($kpiData['grandTotalRevenue'], 0, ',', '.') }}</p>
                            <p class="text-sm text-white text-opacity-80 mt-2">All revenue streams combined</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-6 rounded-2xl backdrop-blur-sm">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- MICE/EVENT TABLE --}}
                @if($miceBookings->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl mb-8">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">MICE/Event Details</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $miceBookings->count() }} events in this period</p>
                                </div>
                                <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-xl">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Client Name</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Property</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Event Date</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($miceBookings as $booking)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-bold">
                                                            {{ strtoupper(substr($booking->client_name, 0, 2)) }}
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $booking->client_name }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $booking->property->name ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($booking->event_date)->isoFormat('D MMMM YYYY') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-100 dark:bg-gray-900">
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-gray-100 text-right">TOTAL MICE REVENUE:</td>
                                            <td class="px-6 py-4 text-sm font-bold text-right text-purple-600 dark:text-purple-400">Rp {{ number_format($miceBookings->sum('total_price'), 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- DAILY DATA TABLE --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Daily Performance Details</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Day-by-day breakdown of all metrics</p>
                            </div>
                            <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-xl">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Occupancy</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">ARR</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Rooms Sold</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($dailyData as $data)
                                        @php
                                            $occupancyLevel = $data['occupancy'] >= 80 ? 'high' : ($data['occupancy'] >= 60 ? 'medium' : 'low');
                                            $statusColor = $occupancyLevel === 'high' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($occupancyLevel === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200');
                                            $statusText = $occupancyLevel === 'high' ? 'Excellent' : ($occupancyLevel === 'medium' ? 'Good' : 'Low');
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($data['date'])->isoFormat('D MMM YYYY') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($data['date'])->isoFormat('dddd') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($data['revenue'], 0, ',', '.') }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center">
                                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($data['occupancy'], 1) }}%</span>
                                                </div>
                                                <div class="mt-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 w-20 mx-auto">
                                                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-1.5 rounded-full" style="width: {{ min(100, $data['occupancy']) }}%"></div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-700 dark:text-gray-300">Rp {{ number_format($data['arr'], 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                    {{ $data['rooms_sold'] }} rooms
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            @else
                {{-- EMPTY STATE --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl">
                     <div class="p-12 text-center">
                        <svg class="mx-auto h-24 w-24 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-gray-100">No Data Available</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            @if($propertyId)
                                No data found for the selected property and date range.
                            @else
                                Please select a property and date range to start your KPI analysis.
                            @endif
                        </p>
                        <div class="mt-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Select filters above and click "Apply Filters" to view analytics</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            @if($kpiData && $dailyData->isNotEmpty())
                const dailyData = @json($dailyData);
                const kpiData = @json($kpiData);

                const labels = dailyData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                });
                const revenueData = dailyData.map(item => item.revenue);
                const occupancyData = dailyData.map(item => item.occupancy);
                const arrData = dailyData.map(item => item.arr);

                // Common chart options
                const commonOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                padding: 15,
                                font: { size: 11, weight: 'bold' },
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            cornerRadius: 8
                        }
                    }
                };

                // 1. Daily Performance Chart (Dual Axis)
                const kpiCtx = document.getElementById('kpiChart').getContext('2d');
                new Chart(kpiCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Daily Revenue',
                                data: revenueData,
                                borderColor: 'rgba(59, 130, 246, 1)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                yAxisID: 'y',
                                tension: 0.4,
                                fill: true,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Daily Occupancy (%)',
                                data: occupancyData,
                                borderColor: 'rgba(16, 185, 129, 1)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                yAxisID: 'y1',
                                tension: 0.4,
                                fill: true,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                ticks: {
                                    callback: value => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value)
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: {
                                     callback: value => value + '%'
                                },
                                max: 100
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });

                // 2. Revenue Distribution Chart (Doughnut)
                const revenueDistCtx = document.getElementById('revenueDistributionChart').getContext('2d');
                new Chart(revenueDistCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Room Revenue', 'F&B Revenue', 'MICE Revenue', 'Other Revenue'],
                        datasets: [{
                            data: [
                                kpiData.totalRoomRevenue,
                                kpiData.totalFbRevenue,
                                kpiData.miceRevenue,
                                kpiData.totalOtherRevenue
                            ],
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(168, 85, 247, 0.8)',
                                'rgba(251, 146, 60, 0.8)'
                            ],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            tooltip: {
                                ...commonOptions.plugins.tooltip,
                                callbacks: {
                                    label: (context) => {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.raw / total) * 100).toFixed(1);
                                        return context.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw) + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });

                // 3. Channel Revenue Chart (Horizontal Bar)
                const channelCtx = document.getElementById('channelRevenueChart').getContext('2d');
                const roomBreakdown = @json($kpiData['roomRevenueBreakdown']);
                new Chart(channelCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(roomBreakdown),
                        datasets: [{
                            label: 'Revenue by Channel',
                            data: Object.values(roomBreakdown),
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(251, 146, 60, 0.8)',
                                'rgba(168, 85, 247, 0.8)',
                                'rgba(236, 72, 153, 0.8)',
                                'rgba(14, 165, 233, 0.8)'
                            ],
                            borderRadius: 8,
                            borderWidth: 2,
                            borderColor: 'rgba(255, 255, 255, 0.5)'
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y',
                        plugins: {
                            ...commonOptions.plugins,
                            legend: { display: false },
                            tooltip: {
                                ...commonOptions.plugins.tooltip,
                                callbacks: {
                                    label: (context) => 'Revenue: Rp ' + new Intl.NumberFormat('id-ID').format(context.raw)
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                ticks: {
                                    callback: value => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value)
                                }
                            },
                            y: {
                                grid: { display: false }
                            }
                        }
                    }
                });

                // 4. Occupancy Trend Chart (Area)
                const occupancyTrendCtx = document.getElementById('occupancyTrendChart').getContext('2d');
                const avgOccupancy = {{ $kpiData['avgOccupancy'] }};
                new Chart(occupancyTrendCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Daily Occupancy',
                                data: occupancyData,
                                borderColor: 'rgba(251, 146, 60, 1)',
                                backgroundColor: 'rgba(251, 146, 60, 0.2)',
                                tension: 0.4,
                                fill: true,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Average Occupancy',
                                data: Array(labels.length).fill(avgOccupancy),
                                borderColor: 'rgba(220, 38, 38, 1)',
                                borderWidth: 2,
                                borderDash: [10, 5],
                                pointRadius: 0,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                ticks: {
                                    callback: value => value + '%'
                                }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });

                // Print styles
                const style = document.createElement('style');
                style.textContent = `
                    @media print {
                        .no-print { display: none !important; }
                        body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                        .shadow-lg, .shadow-xl { box-shadow: none !important; }
                    }
                `;
                document.head.appendChild(style);
            @endif
        </script>
    @endpush

</x-admin-layout>
