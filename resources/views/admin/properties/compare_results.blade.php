<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Property Performance Comparison') }}
            </h2>
            <div class="flex items-center space-x-3">
                @if(auth()->user()->role === 'owner')
                    <span class="inline-flex items-center px-3 py-2 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-medium rounded-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Only Mode
                    </span>
                @endif
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            {{-- HEADER SECTION --}}
            <div class="mb-8 px-4 sm:px-0">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">Perbandingan Kinerja Properti</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Periode: <span class="font-semibold ml-1">{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }}</span>
                        </p>
                        @php
                            $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
                        @endphp
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $days }} hari analisis</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Properti</p>
                        <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ count($properties) }}</p>
                    </div>
                </div>
            </div>

            {{-- OVERALL SUMMARY CARDS --}}
            @php
                $totalRevenue = $results->sum('total_overall_revenue');
                $avgOccupancy = $results->avg('average_occupancy');
                $totalRoomsSold = $results->sum('total_rooms_sold');
                $avgARR = $results->avg('average_arr');

                // Find best performers
                $bestRevenue = $results->sortByDesc('total_overall_revenue')->first();
                $bestOccupancy = $results->sortByDesc('average_occupancy')->first();
                $bestARR = $results->sortByDesc('average_arr')->first();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Revenue</p>
                            <p class="text-3xl font-bold mt-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                            <p class="text-blue-100 text-xs mt-2">Dari semua properti</p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 p-3 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Avg Occupancy</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($avgOccupancy, 1) }}%</p>
                            <p class="text-green-100 text-xs mt-2">Rata-rata semua properti</p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 p-3 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Total Rooms Sold</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($totalRoomsSold) }}</p>
                            <p class="text-purple-100 text-xs mt-2">Room nights</p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 p-3 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Avg Room Rate</p>
                            <p class="text-3xl font-bold mt-2">Rp {{ number_format($avgARR, 0, ',', '.') }}</p>
                            <p class="text-orange-100 text-xs mt-2">Average ARR</p>
                        </div>
                        <div class="bg-orange-400 bg-opacity-30 p-3 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PROPERTY PERFORMANCE CARDS WITH RANKING --}}
            <div class="mb-8">
                <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Individual Property Performance</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(count($properties), 3) }} gap-6">
                    @foreach($properties->sortByDesc(fn($p) => $results->get($p->id)->total_overall_revenue ?? 0)->values() as $index => $property)
                        @php
                            $result = $results->get($property->id);
                            $revenueShare = $totalRevenue > 0 ? ($result->total_overall_revenue / $totalRevenue) * 100 : 0;

                            // Calculate ranking
                            $revenueRank = $index + 1;
                            $rankBadgeColor = $revenueRank == 1 ? 'bg-yellow-400 text-yellow-900' : ($revenueRank == 2 ? 'bg-gray-300 text-gray-800' : ($revenueRank == 3 ? 'bg-orange-400 text-orange-900' : 'bg-gray-200 text-gray-600'));

                            // Performance status based on occupancy
                            $occupancyStatus = $result->average_occupancy >= 75 ? 'excellent' : ($result->average_occupancy >= 60 ? 'good' : ($result->average_occupancy >= 40 ? 'fair' : 'poor'));
                            $statusColors = [
                                'excellent' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'good' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                'fair' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'poor' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            ];
                            $statusLabels = [
                                'excellent' => 'Excellent',
                                'good' => 'Good',
                                'fair' => 'Fair',
                                'poor' => 'Needs Attention',
                            ];
                        @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                            {{-- Header with color bar --}}
                            <div class="h-2" style="background-color: {{ $property->chart_color ?? '#6366f1' }};"></div>

                            <div class="p-6">
                                {{-- Property name and rank --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 rounded-xl" style="background-color: {{ $property->chart_color ?? '#6366f1' }}20;">
                                            <svg class="w-6 h-6" style="color: {{ $property->chart_color ?? '#6366f1' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $property->name }}</h3>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$occupancyStatus] }} mt-1">
                                                {{ $statusLabels[$occupancyStatus] }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full {{ $rankBadgeColor }} font-bold text-lg">
                                            #{{ $revenueRank }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Rank</span>
                                    </div>
                                </div>

                                {{-- Revenue --}}
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Revenue</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($result->total_overall_revenue ?? 0, 0, ',', '.') }}</p>
                                    <div class="flex items-center mt-1">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                            <div class="h-2 rounded-full transition-all duration-500" style="width: {{ $revenueShare }}%; background-color: {{ $property->chart_color ?? '#6366f1' }};"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ number_format($revenueShare, 1) }}%</span>
                                    </div>
                                </div>

                                {{-- Key metrics grid --}}
                                <div class="grid grid-cols-2 gap-4 pt-4 border-t dark:border-gray-700">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Occupancy</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($result->average_occupancy ?? 0, 1) }}%</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Avg Rate</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($result->average_arr ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Rooms Sold</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($result->total_rooms_sold ?? 0) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Room Revenue</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format(($result->total_room_revenue ?? 0) / 1000000, 1) }}M</p>
                                    </div>
                                </div>

                                {{-- Revenue breakdown --}}
                                <div class="mt-4 pt-4 border-t dark:border-gray-700">
                                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">Revenue Breakdown</p>
                                    <div class="space-y-2">
                                        @php
                                            $roomPercent = $result->total_overall_revenue > 0 ? ($result->total_room_revenue / $result->total_overall_revenue) * 100 : 0;
                                            $fbPercent = $result->total_overall_revenue > 0 ? ($result->total_fb_revenue / $result->total_overall_revenue) * 100 : 0;
                                            $micePercent = $result->total_overall_revenue > 0 ? ($result->total_mice_revenue / $result->total_overall_revenue) * 100 : 0;
                                        @endphp
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-gray-600 dark:text-gray-400">Room</span>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($roomPercent, 1) }}%</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-gray-600 dark:text-gray-400">F&B</span>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($fbPercent, 1) }}%</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-gray-600 dark:text-gray-400">MICE</span>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($micePercent, 1) }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- CHARTS SECTION - Enhanced with multiple visualizations --}}
            <div class="mb-8">
                <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Visual Analytics</h4>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    {{-- Revenue Comparison Bar Chart --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue Comparison</h5>
                            <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 text-xs font-medium rounded-full">Bar Chart</span>
                        </div>
                        <div class="relative h-80">
                            <canvas id="comparisonBarChart"></canvas>
                        </div>
                    </div>

                    {{-- Revenue Distribution Pie Chart --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue Distribution</h5>
                            <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 text-xs font-medium rounded-full">Pie Chart</span>
                        </div>
                        <div class="relative h-80">
                            <canvas id="comparisonPieChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Additional Charts Row --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Occupancy Comparison --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Occupancy Rate Comparison</h5>
                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs font-medium rounded-full">Radar Chart</span>
                        </div>
                        <div class="relative h-80">
                            <canvas id="occupancyRadarChart"></canvas>
                        </div>
                    </div>

                    {{-- Revenue Source Breakdown --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue by Source</h5>
                            <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 text-xs font-medium rounded-full">Stacked Bar</span>
                        </div>
                        <div class="relative h-80">
                            <canvas id="revenueSourceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>


            {{-- DETAILED COMPARISON TABLE --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold">Detailed Metrics Comparison</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Comprehensive breakdown of all revenue sources and metrics</p>
                        </div>
                        @if(auth()->user()->role === 'admin')
                            <button onclick="exportTableToCSV()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export CSV
                            </button>
                        @else
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Export restricted to Admin only
                            </div>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full" id="comparisonTable">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-900">
                                        Metrics
                                    </th>
                                    @foreach($properties as $property)
                                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center justify-center space-x-2">
                                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $property->chart_color ?? '#6366f1' }};"></div>
                                                <span>{{ $property->name }}</span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr class="bg-gray-50 dark:bg-gray-900/50">
                                    <td colspan="{{ count($properties) + 1 }}" class="px-6 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300">Pendapatan Kamar</td>
                                </tr>
                                @php
                                    $metrics = [
                                        'offline' => 'Offline', 'online' => 'Online', 'ta' => 'Travel Agent',
                                        'gov' => 'Government', 'corp' => 'Corporate', 'afiliasi' => 'Afiliasi',
                                    ];
                                @endphp
                                @foreach($metrics as $key => $label)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">{{ $label }}</td>
                                    @foreach($properties as $property)
                                        @php
                                            $result = $results->get($property->id);
                                            $revenue = $result ? $result->{$key.'_revenue'} : 0;
                                            $rooms = $result ? $result->{$key.'_rooms'} : 0;
                                        @endphp
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-center">
                                            <div class="font-semibold">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
                                            @if($rooms > 0)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">({{ $rooms }} kamar)</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                                <tr class="bg-gray-100 dark:bg-gray-700/50 font-bold">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Subtotal Pendapatan Kamar</td>
                                    @foreach($properties as $property)
                                        @php
                                            $result = $results->get($property->id);
                                            $revenue = $result ? $result->total_room_revenue : 0;
                                            $rooms = $result ? $result->total_rooms_sold : 0;
                                        @endphp
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                                            <div>Rp {{ number_format($revenue, 0, ',', '.') }}</div>
                                            <div class="text-xs font-normal mt-1">({{ $rooms }} kamar)</div>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-900/50">
                                    <td colspan="{{ count($properties) + 1 }}" class="px-6 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300">Pendapatan Lainnya</td>
                                </tr>
                                <tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">Food & Beverage</td>@foreach($properties as $property) <td class="px-6 py-4 text-center text-sm">Rp {{ number_format($results->get($property->id)->total_fb_revenue ?? 0, 0, ',', '.') }}</td> @endforeach </tr>
                                <tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">MICE/Event</td>@foreach($properties as $property) <td class="px-6 py-4 text-center text-sm">Rp {{ number_format($results->get($property->id)->total_mice_revenue ?? 0, 0, ',', '.') }}</td> @endforeach </tr>
                                <tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">Lain-lain</td>@foreach($properties as $property) <td class="px-6 py-4 text-center text-sm">Rp {{ number_format($results->get($property->id)->total_others_revenue ?? 0, 0, ',', '.') }}</td> @endforeach </tr>
                                <tr class="bg-indigo-100 dark:bg-indigo-900/30 font-extrabold text-indigo-800 dark:text-indigo-200"><td class="px-6 py-4 whitespace-nowrap text-sm">GRAND TOTAL PENDAPATAN</td>@foreach($properties as $property) <td class="px-6 py-4 text-center text-sm">Rp {{ number_format($results->get($property->id)->total_overall_revenue ?? 0, 0, ',', '.') }}</td> @endforeach </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('admin.properties.compare_page') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                            &larr; Kembali ke Halaman Pilihan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                try {
                    const chartData = @json($chartData);
                    const properties = @json($properties);
                    const results = @json($results);

                    if (chartData && Array.isArray(chartData) && chartData.length > 0) {
                        const labels = chartData.map(item => item.label);
                        const revenues = chartData.map(item => item.revenue);
                        const colors = chartData.map(item => item.color);

                        // Common chart options
                        const commonOptions = {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: { size: 11 },
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

                        // 1. Bar Chart - Revenue Comparison
                        const barCanvas = document.getElementById('comparisonBarChart');
                        if (barCanvas) {
                            new Chart(barCanvas.getContext('2d'), {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Total Revenue',
                                        data: revenues,
                                        backgroundColor: colors.map(c => c + 'CC'),
                                        borderColor: colors,
                                        borderWidth: 2,
                                        borderRadius: 8,
                                        borderSkipped: false
                                    }]
                                },
                                options: {
                                    ...commonOptions,
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
                                        y: {
                                            beginAtZero: true,
                                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                            ticks: {
                                                callback: value => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact', compactDisplay: 'short' }).format(value)
                                            }
                                        },
                                        x: {
                                            grid: { display: false }
                                        }
                                    }
                                }
                            });
                        }

                        // 2. Doughnut Chart - Revenue Distribution
                        const pieCanvas = document.getElementById('comparisonPieChart');
                        if (pieCanvas) {
                            new Chart(pieCanvas.getContext('2d'), {
                                type: 'doughnut',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Revenue Share',
                                        data: revenues,
                                        backgroundColor: colors,
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
                        }

                        // 3. Radar Chart - Occupancy Comparison
                        const radarCanvas = document.getElementById('occupancyRadarChart');
                        if (radarCanvas) {
                            const occupancyData = properties.map(prop => {
                                const result = results[prop.id];
                                return result ? result.average_occupancy : 0;
                            });

                            new Chart(radarCanvas.getContext('2d'), {
                                type: 'radar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Occupancy Rate (%)',
                                        data: occupancyData,
                                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                                        borderColor: 'rgb(34, 197, 94)',
                                        borderWidth: 2,
                                        pointBackgroundColor: colors,
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                        pointRadius: 6,
                                        pointHoverRadius: 8
                                    }]
                                },
                                options: {
                                    ...commonOptions,
                                    scales: {
                                        r: {
                                            beginAtZero: true,
                                            max: 100,
                                            ticks: {
                                                stepSize: 20,
                                                callback: value => value + '%'
                                            },
                                            grid: { color: 'rgba(0, 0, 0, 0.1)' }
                                        }
                                    },
                                    plugins: {
                                        ...commonOptions.plugins,
                                        legend: { display: false }
                                    }
                                }
                            });
                        }

                        // 4. Stacked Bar Chart - Revenue by Source
                        const sourceCanvas = document.getElementById('revenueSourceChart');
                        if (sourceCanvas) {
                            const roomRevenues = properties.map(prop => results[prop.id]?.total_room_revenue || 0);
                            const fbRevenues = properties.map(prop => results[prop.id]?.total_fb_revenue || 0);
                            const miceRevenues = properties.map(prop => results[prop.id]?.total_mice_revenue || 0);
                            const otherRevenues = properties.map(prop => results[prop.id]?.total_others_revenue || 0);

                            new Chart(sourceCanvas.getContext('2d'), {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [
                                        {
                                            label: 'Room Revenue',
                                            data: roomRevenues,
                                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                            borderRadius: 6
                                        },
                                        {
                                            label: 'F&B Revenue',
                                            data: fbRevenues,
                                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                            borderRadius: 6
                                        },
                                        {
                                            label: 'MICE Revenue',
                                            data: miceRevenues,
                                            backgroundColor: 'rgba(168, 85, 247, 0.8)',
                                            borderRadius: 6
                                        },
                                        {
                                            label: 'Other Revenue',
                                            data: otherRevenues,
                                            backgroundColor: 'rgba(249, 115, 22, 0.8)',
                                            borderRadius: 6
                                        }
                                    ]
                                },
                                options: {
                                    ...commonOptions,
                                    scales: {
                                        x: { stacked: true, grid: { display: false } },
                                        y: {
                                            stacked: true,
                                            beginAtZero: true,
                                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                            ticks: {
                                                callback: value => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value)
                                            }
                                        }
                                    },
                                    plugins: {
                                        ...commonOptions.plugins,
                                        tooltip: {
                                            ...commonOptions.plugins.tooltip,
                                            callbacks: {
                                                label: (context) => context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw)
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }
                } catch (e) {
                    console.error('Failed to create charts:', e);
                }
            });

            // Export to CSV function
            function exportTableToCSV() {
                const table = document.getElementById('comparisonTable');
                let csv = [];

                // Get headers
                const headers = Array.from(table.querySelectorAll('thead tr th')).map(th => th.innerText.trim());
                csv.push(headers.join(','));

                // Get data rows
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cols = Array.from(row.querySelectorAll('td')).map(td => {
                        let text = td.innerText.trim();
                        // Remove commas and clean up text
                        text = text.replace(/,/g, '');
                        return '"' + text + '"';
                    });
                    if (cols.length > 0) {
                        csv.push(cols.join(','));
                    }
                });

                // Download CSV
                const csvContent = csv.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'property_comparison_' + new Date().toISOString().slice(0, 10) + '.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // Print styles
            const style = document.createElement('style');
            style.textContent = `
                @media print {
                    .no-print { display: none !important; }
                    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                }
            `;
            document.head.appendChild(style);
        </script>
    @endpush
</x-admin-layout>