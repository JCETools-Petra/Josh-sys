<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Laporan P&L (Profit & Loss) - ') }} {{ $property->name }}
            </h2>
            <nav class="flex flex-wrap items-center space-x-2 sm:space-x-3">
                <x-nav-link :href="route('property.financial.input-actual')" class="ml-3">
                    {{ __('Input Data Aktual') }}
                </x-nav-link>
            </nav>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Period Selection & Export Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" action="{{ route('property.financial.report') }}" class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow">
                        <div class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-4 md:space-y-0">
                            <div class="flex-1">
                                <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
                                <select name="year" id="year" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1">
                                <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bulan</label>
                                <select name="month" id="month" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    @foreach($months as $m)
                                        <option value="{{ $m['value'] }}" {{ $month == $m['value'] ? 'selected' : '' }}>
                                            {{ $m['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Tampilkan</button>
                            </div>
                        </div>
                    </form>

                    <!-- Export Buttons -->
                    <div class="flex flex-wrap gap-3 justify-end">
                        <a href="{{ route('property.financial.export-excel', ['year' => $year, 'month' => $month]) }}"
                           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                            📊 Export Excel
                        </a>
                        <a href="{{ route('property.financial.export-pdf', ['year' => $year, 'month' => $month]) }}"
                           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                            📄 Export PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- GOP Percentage -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">GOP %</div>
                    <div class="mt-2 text-3xl font-bold {{ $kpis['gop_percentage'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($kpis['gop_percentage'], 1) }}%
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Gross Operating Profit</div>
                </div>

                <!-- Labor Cost % -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Labor Cost %</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">
                        {{ number_format($kpis['labor_cost_percentage'], 1) }}%
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">of Revenue</div>
                </div>

                <!-- F&B Cost % -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">F&B Cost %</div>
                    <div class="mt-2 text-3xl font-bold text-purple-600">
                        {{ number_format($kpis['fnb_cost_percentage'], 1) }}%
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">of F&B Revenue</div>
                </div>

                <!-- RevPAR -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">RevPAR</div>
                    <div class="mt-2 text-3xl font-bold text-indigo-600">
                        Rp {{ number_format($kpis['revenue_per_available_room'], 0) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Revenue per Available Room</div>
                </div>
            </div>

            <!-- Budget Alerts -->
            @if(count($alerts) > 0)
            <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Budget Alert: {{ count($alerts) }} kategori melebihi budget
                        </p>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach(array_slice($alerts, 0, 3) as $alert)
                                <li>
                                    <strong>{{ $alert['category_name'] }}:</strong>
                                    Overbudget Rp {{ number_format($alert['variance'], 0) }}
                                    ({{ number_format($alert['variance_percentage'], 1) }}%)
                                </li>
                                @endforeach
                                @if(count($alerts) > 3)
                                <li class="text-xs">... dan {{ count($alerts) - 3 }} kategori lainnya</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Comparative Analysis -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Comparative Analysis</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Current Period -->
                        <div class="border-r dark:border-gray-700 pr-4">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                Current ({{ $comparative['current']['period'] }})
                            </div>
                            <div class="space-y-2 text-sm">
                                <div><strong>Revenue:</strong> Rp {{ number_format($comparative['current']['revenue'], 0) }}</div>
                                <div><strong>Expense:</strong> Rp {{ number_format($comparative['current']['expense'], 0) }}</div>
                                <div><strong>GOP:</strong> Rp {{ number_format($comparative['current']['gop'], 0) }}</div>
                            </div>
                        </div>

                        <!-- Month-over-Month -->
                        <div class="border-r dark:border-gray-700 pr-4">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                MoM (vs {{ $comparative['mom']['period'] }})
                            </div>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <strong>Revenue:</strong>
                                    <span class="{{ $comparative['mom']['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $comparative['mom']['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['revenue_change'], 1) }}%
                                    </span>
                                </div>
                                <div>
                                    <strong>Expense:</strong>
                                    <span class="{{ $comparative['mom']['expense_change'] <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $comparative['mom']['expense_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['expense_change'], 1) }}%
                                    </span>
                                </div>
                                <div>
                                    <strong>GOP:</strong>
                                    <span class="{{ $comparative['mom']['gop_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $comparative['mom']['gop_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['gop_change'], 1) }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Year-over-Year -->
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                YoY (vs {{ $comparative['yoy']['period'] }})
                            </div>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <strong>Revenue:</strong>
                                    <span class="{{ $comparative['yoy']['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $comparative['yoy']['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['revenue_change'], 1) }}%
                                    </span>
                                </div>
                                <div>
                                    <strong>Expense:</strong>
                                    <span class="{{ $comparative['yoy']['expense_change'] <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $comparative['yoy']['expense_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['expense_change'], 1) }}%
                                    </span>
                                </div>
                                <div>
                                    <strong>GOP:</strong>
                                    <span class="{{ $comparative['yoy']['gop_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $comparative['yoy']['gop_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['gop_change'], 1) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Revenue & Expense Trend -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Revenue & Expense Trend (12 Months)</h3>
                    <canvas id="trendChart" height="250"></canvas>
                </div>

                <!-- Revenue Breakdown -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Revenue Breakdown</h3>
                    <canvas id="revenueChart" height="250"></canvas>
                </div>

                <!-- Expense Breakdown -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Expense by Department</h3>
                    <canvas id="expenseChart" height="250"></canvas>
                </div>

                <!-- Budget vs Actual -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Budget vs Actual</h3>
                    <canvas id="budgetChart" height="250"></canvas>
                </div>
            </div>

            <!-- Forecast Section -->
            @if(count($forecast) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Forecast (Next 3 Months)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Month</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Revenue Forecast</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Expense Forecast</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">GOP Forecast</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($forecast as $f)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $f['month'] }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100">Rp {{ number_format($f['revenue_forecast'], 0) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100">Rp {{ number_format($f['expense_forecast'], 0) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100">Rp {{ number_format($f['gop_forecast'], 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        * Forecast based on historical trend analysis
                    </div>
                </div>
            </div>
            @endif

            <!-- P&L Report Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Periode:</strong> {{ \Carbon\Carbon::create(2000, $month, 1)->format('F') }} {{ $year }}
                        </p>
                    </div>

                    <!-- P&L Report Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-800 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                        Deskripsi
                                    </th>
                                    <th colspan="3" class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider border-l border-gray-600">
                                        Current Month
                                    </th>
                                    <th colspan="3" class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider border-l border-gray-600">
                                        Year to Date (YTD)
                                    </th>
                                </tr>
                                <tr class="bg-gray-700 dark:bg-gray-800">
                                    <th class="px-6 py-2"></th>
                                    <th class="px-6 py-2 text-right text-xs font-medium text-gray-300 uppercase">Actual</th>
                                    <th class="px-6 py-2 text-right text-xs font-medium text-gray-300 uppercase">Budget</th>
                                    <th class="px-6 py-2 text-right text-xs font-medium text-gray-300 uppercase border-r border-gray-600">Variance</th>
                                    <th class="px-6 py-2 text-right text-xs font-medium text-gray-300 uppercase">Actual</th>
                                    <th class="px-6 py-2 text-right text-xs font-medium text-gray-300 uppercase">Budget</th>
                                    <th class="px-6 py-2 text-right text-xs font-medium text-gray-300 uppercase">Variance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- REVENUE SECTION -->
                                <tr class="bg-green-50 dark:bg-green-900">
                                    <td colspan="7" class="px-6 py-3 text-sm font-bold text-green-800 dark:text-green-200 uppercase">
                                        REVENUE (PENDAPATAN)
                                    </td>
                                </tr>

                                @foreach($pnlData['categories'] as $category)
                                    @if($category['type'] === 'revenue')
                                        @include('financial.partials.category-row', ['category' => $category])
                                    @endif
                                @endforeach

                                <!-- Total Revenue -->
                                <tr class="bg-green-100 dark:bg-green-800 border-t-2 border-green-600">
                                    <td class="px-6 py-3 text-sm font-bold text-green-900 dark:text-green-100">
                                        TOTAL REVENUE
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-green-900 dark:text-green-100">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['actual_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-green-900 dark:text-green-100">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['budget_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold border-r border-gray-400 {{ $pnlData['totals']['total_revenue']['variance_current'] >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['variance_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-green-900 dark:text-green-100">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['actual_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-green-900 dark:text-green-100">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['budget_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold {{ $pnlData['totals']['total_revenue']['variance_ytd'] >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['total_revenue']['variance_ytd'], 0, ',', '.') }}
                                    </td>
                                </tr>

                                <!-- EXPENSES SECTION -->
                                <tr class="bg-red-50 dark:bg-red-900">
                                    <td colspan="7" class="px-6 py-3 text-sm font-bold text-red-800 dark:text-red-200 uppercase">
                                        EXPENSES (PENGELUARAN)
                                    </td>
                                </tr>

                                @foreach($pnlData['categories'] as $category)
                                    @if($category['type'] === 'expense')
                                        @include('financial.partials.category-row', ['category' => $category])
                                    @endif
                                @endforeach

                                <!-- Total Expenses -->
                                <tr class="bg-red-100 dark:bg-red-800 border-t-2 border-red-600">
                                    <td class="px-6 py-3 text-sm font-bold text-red-900 dark:text-red-100">
                                        TOTAL EXPENSES
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-red-900 dark:text-red-100">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['actual_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-red-900 dark:text-red-100">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['budget_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold border-r border-gray-400 {{ $pnlData['totals']['total_expenses']['variance_current'] <= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['variance_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-red-900 dark:text-red-100">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['actual_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-red-900 dark:text-red-100">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['budget_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-bold {{ $pnlData['totals']['total_expenses']['variance_ytd'] <= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['total_expenses']['variance_ytd'], 0, ',', '.') }}
                                    </td>
                                </tr>

                                <!-- GROSS OPERATING PROFIT -->
                                <tr class="bg-blue-100 dark:bg-blue-800 border-t-4 border-blue-600">
                                    <td class="px-6 py-4 text-base font-bold text-blue-900 dark:text-blue-100 uppercase">
                                        GROSS OPERATING PROFIT (GOP)
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-bold text-blue-900 dark:text-blue-100">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['actual_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-bold text-blue-900 dark:text-blue-100">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['budget_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-bold border-r border-gray-400 {{ $pnlData['totals']['gross_operating_profit']['variance_current'] >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['variance_current'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-bold text-blue-900 dark:text-blue-100">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['actual_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-bold text-blue-900 dark:text-blue-100">
                                        Rp {{ number_format($pnlData['totals']['gross_operating_profit']['budget_ytd'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-bold {{ $pnlData['totals']['gross_operating_profit']['variance_ytd'] >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
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
