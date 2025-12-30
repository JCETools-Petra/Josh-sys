<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Report - {{ $property->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0 0 10px 0;
        }
        .header .date {
            color: #666;
            font-size: 14px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            color: #4F46E5;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #E5E7EB;
            padding-bottom: 8px;
        }
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .metric-card {
            background-color: #F9FAFB;
            border-left: 4px solid #4F46E5;
            padding: 15px;
            border-radius: 4px;
        }
        .metric-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #1F2937;
        }
        .metric-value.success {
            color: #10B981;
        }
        .metric-value.warning {
            color: #F59E0B;
        }
        .metric-value.danger {
            color: #EF4444;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: #F3F4F6;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .table td {
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-warning {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            color: #92400E;
        }
        .alert-danger {
            background-color: #FEE2E2;
            border-left: 4px solid #EF4444;
            color: #991B1B;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Daily Report</h1>
            <div class="property-name" style="font-size: 20px; color: #1F2937; margin: 10px 0;">{{ $property->name }}</div>
            <div class="date">{{ $data['date'] }}</div>
        </div>

        <!-- Occupancy Section -->
        <div class="section">
            <div class="section-title">🏨 Occupancy Overview</div>
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Occupancy Rate</div>
                    <div class="metric-value {{ $data['occupancy']['rate'] >= 80 ? 'success' : ($data['occupancy']['rate'] >= 60 ? 'warning' : 'danger') }}">
                        {{ $data['occupancy']['rate'] }}%
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Rooms Occupied</div>
                    <div class="metric-value">
                        {{ $data['occupancy']['occupied'] }} / {{ $data['occupancy']['total_rooms'] }}
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Arrivals Today</div>
                    <div class="metric-value">{{ $data['arrivals_today'] }}</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Departures Today</div>
                    <div class="metric-value">{{ $data['departures_today'] }}</div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Room Status</th>
                        <th style="text-align: right;">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Available</td>
                        <td style="text-align: right;"><strong>{{ $data['rooms_status']['available'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Occupied</td>
                        <td style="text-align: right;"><strong>{{ $data['rooms_status']['occupied'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Dirty/Needs Cleaning</td>
                        <td style="text-align: right;"><strong>{{ $data['rooms_status']['dirty'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Under Maintenance</td>
                        <td style="text-align: right;"><strong>{{ $data['rooms_status']['maintenance'] }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Revenue Section -->
        <div class="section">
            <div class="section-title">💰 Revenue Summary (Yesterday)</div>
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Room Revenue</div>
                    <div class="metric-value">Rp {{ number_format($data['revenue']['rooms'], 0, ',', '.') }}</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">F&B Revenue</div>
                    <div class="metric-value">Rp {{ number_format($data['revenue']['fnb'], 0, ',', '.') }}</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">MICE Revenue</div>
                    <div class="metric-value">Rp {{ number_format($data['revenue']['mice'], 0, ',', '.') }}</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Total Revenue</div>
                    <div class="metric-value success">Rp {{ number_format($data['revenue']['total'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Financial Section -->
        <div class="section">
            <div class="section-title">💵 Financial Status</div>
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Payments Today</div>
                    <div class="metric-value success">Rp {{ number_format($data['payments_today'], 0, ',', '.') }}</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Outstanding Balances</div>
                    <div class="metric-value {{ $data['outstanding_balances'] > 0 ? 'warning' : 'success' }}">
                        Rp {{ number_format($data['outstanding_balances'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Alerts -->
        @if($data['maintenance']['open'] > 0)
        <div class="section">
            <div class="section-title">🔧 Maintenance Status</div>
            @if($data['maintenance']['urgent'] > 0)
            <div class="alert alert-danger">
                <strong>⚠️ Urgent Alert:</strong> {{ $data['maintenance']['urgent'] }} urgent maintenance request(s) pending!
            </div>
            @endif
            <div class="alert alert-warning">
                <strong>📋 Total Open:</strong> {{ $data['maintenance']['open'] }} maintenance request(s) need attention.
            </div>
        </div>
        @endif

        <!-- Low Stock Alerts -->
        @if($data['low_stock']->count() > 0)
        <div class="section">
            <div class="section-title">📦 Low Stock Alert</div>
            <div class="alert alert-warning">
                <strong>⚠️ Warning:</strong> {{ $data['low_stock']->count() }} item(s) below minimum stock level!
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th style="text-align: center;">Current Stock</th>
                        <th style="text-align: center;">Minimum Required</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['low_stock'] as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td style="text-align: center; color: #EF4444; font-weight: bold;">{{ $item->stock }} {{ $item->unit }}</td>
                        <td style="text-align: center;">{{ $item->minimum_standard_quantity }} {{ $item->unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Guest Statistics -->
        <div class="section">
            <div class="section-title">👥 Guest Statistics</div>
            <div class="metric-card">
                <div class="metric-label">Current In-House Guests</div>
                <div class="metric-value">{{ $data['current_guests'] }}</div>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated report generated by {{ config('app.name') }}</p>
            <p>Generated at {{ now()->format('d M Y H:i:s') }}</p>
            <p>🤖 Generated with <a href="https://claude.com/claude-code" style="color: #4F46E5;">Claude Code</a></p>
        </div>
    </div>
</body>
</html>
