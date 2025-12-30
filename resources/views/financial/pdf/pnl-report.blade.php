<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>P&L Report - {{ $property->name }}</title>
    <style>
        @page {
            margin: 20px;
            size: A4 landscape;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #1a1a1a;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .header p {
            margin: 3px 0;
            font-size: 10px;
            color: #888;
        }
        .kpi-section {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .kpi-card {
            text-align: center;
            flex: 1;
        }
        .kpi-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        .kpi-value {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #1f2937;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        th.text-right, td.text-right {
            text-align: right;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8px;
        }
        .section-header {
            background-color: #10b981;
            color: white;
            font-weight: bold;
            padding: 6px 4px;
        }
        .section-header.expense {
            background-color: #ef4444;
        }
        .total-row {
            background-color: #d1fae5;
            font-weight: bold;
            border-top: 2px solid #10b981;
        }
        .total-row.expense {
            background-color: #fee2e2;
            border-top: 2px solid #ef4444;
        }
        .gop-row {
            background-color: #dbeafe;
            font-weight: bold;
            border-top: 3px solid #2563eb;
            font-size: 10px;
        }
        .indent-1 { padding-left: 15px; }
        .indent-2 { padding-left: 30px; }
        .indent-3 { padding-left: 45px; }
        .positive { color: #10b981; }
        .negative { color: #ef4444; }
        .comparative-section {
            margin-top: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .comparative-section h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
        }
        .comparative-grid {
            display: flex;
            justify-content: space-between;
        }
        .comparative-col {
            flex: 1;
            padding: 0 10px;
        }
        .comparative-col h4 {
            margin: 0 0 8px 0;
            font-size: 10px;
            color: #666;
        }
        .comparative-item {
            margin-bottom: 5px;
            font-size: 8px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 7px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PROFIT & LOSS STATEMENT</h1>
        <h2>{{ $property->name }}</h2>
        <p>Period: {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</p>
        <p>Generated: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- KPI Summary -->
    <div class="kpi-section">
        <div class="kpi-card">
            <div class="kpi-label">GOP %</div>
            <div class="kpi-value">{{ number_format($kpis['gop_percentage'], 1) }}%</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Labor Cost %</div>
            <div class="kpi-value">{{ number_format($kpis['labor_cost_percentage'], 1) }}%</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">F&B Cost %</div>
            <div class="kpi-value">{{ number_format($kpis['fnb_cost_percentage'], 1) }}%</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">RevPAR</div>
            <div class="kpi-value">{{ number_format($kpis['revenue_per_available_room'], 0) }}</div>
        </div>
    </div>

    <!-- P&L Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Description</th>
                <th class="text-right" style="width: 11%;">Current Actual</th>
                <th class="text-right" style="width: 11%;">Current Budget</th>
                <th class="text-right" style="width: 11%;">Current Variance</th>
                <th class="text-right" style="width: 11%;">YTD Actual</th>
                <th class="text-right" style="width: 11%;">YTD Budget</th>
                <th class="text-right" style="width: 11%;">YTD Variance</th>
            </tr>
        </thead>
        <tbody>
            <!-- REVENUE SECTION -->
            <tr class="section-header">
                <td colspan="7">REVENUE</td>
            </tr>
            @foreach($pnlData['categories'] as $category)
                @if($category['type'] === 'revenue')
                    @include('financial.pdf.partials.category-row-pdf', ['category' => $category])
                @endif
            @endforeach
            <tr class="total-row">
                <td>TOTAL REVENUE</td>
                <td class="text-right">{{ number_format($pnlData['totals']['total_revenue']['actual_current'], 0) }}</td>
                <td class="text-right">{{ number_format($pnlData['totals']['total_revenue']['budget_current'], 0) }}</td>
                <td class="text-right {{ $pnlData['totals']['total_revenue']['variance_current'] >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($pnlData['totals']['total_revenue']['variance_current'], 0) }}
                </td>
                <td class="text-right">{{ number_format($pnlData['totals']['total_revenue']['actual_ytd'], 0) }}</td>
                <td class="text-right">{{ number_format($pnlData['totals']['total_revenue']['budget_ytd'], 0) }}</td>
                <td class="text-right {{ $pnlData['totals']['total_revenue']['variance_ytd'] >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($pnlData['totals']['total_revenue']['variance_ytd'], 0) }}
                </td>
            </tr>

            <!-- EXPENSES SECTION -->
            <tr class="section-header expense">
                <td colspan="7">EXPENSES</td>
            </tr>
            @foreach($pnlData['categories'] as $category)
                @if($category['type'] === 'expense')
                    @include('financial.pdf.partials.category-row-pdf', ['category' => $category])
                @endif
            @endforeach
            <tr class="total-row expense">
                <td>TOTAL EXPENSES</td>
                <td class="text-right">{{ number_format($pnlData['totals']['total_expenses']['actual_current'], 0) }}</td>
                <td class="text-right">{{ number_format($pnlData['totals']['total_expenses']['budget_current'], 0) }}</td>
                <td class="text-right {{ $pnlData['totals']['total_expenses']['variance_current'] <= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($pnlData['totals']['total_expenses']['variance_current'], 0) }}
                </td>
                <td class="text-right">{{ number_format($pnlData['totals']['total_expenses']['actual_ytd'], 0) }}</td>
                <td class="text-right">{{ number_format($pnlData['totals']['total_expenses']['budget_ytd'], 0) }}</td>
                <td class="text-right {{ $pnlData['totals']['total_expenses']['variance_ytd'] <= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($pnlData['totals']['total_expenses']['variance_ytd'], 0) }}
                </td>
            </tr>

            <!-- GOP -->
            <tr class="gop-row">
                <td>GROSS OPERATING PROFIT</td>
                <td class="text-right">{{ number_format($pnlData['totals']['gross_operating_profit']['actual_current'], 0) }}</td>
                <td class="text-right">{{ number_format($pnlData['totals']['gross_operating_profit']['budget_current'], 0) }}</td>
                <td class="text-right {{ $pnlData['totals']['gross_operating_profit']['variance_current'] >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($pnlData['totals']['gross_operating_profit']['variance_current'], 0) }}
                </td>
                <td class="text-right">{{ number_format($pnlData['totals']['gross_operating_profit']['actual_ytd'], 0) }}</td>
                <td class="text-right">{{ number_format($pnlData['totals']['gross_operating_profit']['budget_ytd'], 0) }}</td>
                <td class="text-right {{ $pnlData['totals']['gross_operating_profit']['variance_ytd'] >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($pnlData['totals']['gross_operating_profit']['variance_ytd'], 0) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Comparative Analysis -->
    <div class="comparative-section">
        <h3>Comparative Analysis</h3>
        <div class="comparative-grid">
            <div class="comparative-col">
                <h4>Current ({{ $comparative['current']['period'] }})</h4>
                <div class="comparative-item"><strong>Revenue:</strong> Rp {{ number_format($comparative['current']['revenue'], 0) }}</div>
                <div class="comparative-item"><strong>Expense:</strong> Rp {{ number_format($comparative['current']['expense'], 0) }}</div>
                <div class="comparative-item"><strong>GOP:</strong> Rp {{ number_format($comparative['current']['gop'], 0) }}</div>
            </div>
            <div class="comparative-col">
                <h4>MoM (vs {{ $comparative['mom']['period'] }})</h4>
                <div class="comparative-item">
                    <strong>Revenue:</strong>
                    <span class="{{ $comparative['mom']['revenue_change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $comparative['mom']['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['revenue_change'], 1) }}%
                    </span>
                </div>
                <div class="comparative-item">
                    <strong>Expense:</strong>
                    <span class="{{ $comparative['mom']['expense_change'] <= 0 ? 'positive' : 'negative' }}">
                        {{ $comparative['mom']['expense_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['expense_change'], 1) }}%
                    </span>
                </div>
                <div class="comparative-item">
                    <strong>GOP:</strong>
                    <span class="{{ $comparative['mom']['gop_change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $comparative['mom']['gop_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['mom']['gop_change'], 1) }}%
                    </span>
                </div>
            </div>
            <div class="comparative-col">
                <h4>YoY (vs {{ $comparative['yoy']['period'] }})</h4>
                <div class="comparative-item">
                    <strong>Revenue:</strong>
                    <span class="{{ $comparative['yoy']['revenue_change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $comparative['yoy']['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['revenue_change'], 1) }}%
                    </span>
                </div>
                <div class="comparative-item">
                    <strong>Expense:</strong>
                    <span class="{{ $comparative['yoy']['expense_change'] <= 0 ? 'positive' : 'negative' }}">
                        {{ $comparative['yoy']['expense_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['expense_change'], 1) }}%
                    </span>
                </div>
                <div class="comparative-item">
                    <strong>GOP:</strong>
                    <span class="{{ $comparative['yoy']['gop_change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $comparative['yoy']['gop_change'] >= 0 ? '+' : '' }}{{ number_format($comparative['yoy']['gop_change'], 1) }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>
