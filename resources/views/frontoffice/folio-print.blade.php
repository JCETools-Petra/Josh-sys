<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Folio - {{ $roomStay->confirmation_number }}</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .property-name {
            font-size: 14pt;
            color: #666;
            margin-bottom: 10px;
        }

        .header .folio-title {
            font-size: 18pt;
            font-weight: bold;
            margin-top: 10px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .info-box h2 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .info-label {
            color: #666;
            font-size: 10pt;
        }

        .info-value {
            font-weight: bold;
            font-size: 10pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table caption {
            font-size: 12pt;
            font-weight: bold;
            text-align: left;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #333;
            color: #fff;
            padding: 8px;
            text-align: left;
            font-size: 10pt;
        }

        table th.text-right,
        table td.text-right {
            text-align: right;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10pt;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .summary-box {
            border: 2px solid #333;
            padding: 20px;
            background-color: #f0f8ff;
            margin-top: 30px;
        }

        .summary-box h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11pt;
        }

        .summary-row.total {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 13pt;
            font-weight: bold;
        }

        .summary-row.balance {
            border-top: 3px double #333;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 14pt;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }

        .status-checked-in {
            background-color: #d4edda;
            color: #155724;
        }

        .status-checked-out {
            background-color: #e7e7e7;
            color: #383838;
        }

        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .print-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12pt;
            text-decoration: none;
            display: inline-block;
        }

        .print-button:hover {
            background-color: #0056b3;
        }

        .back-button {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12pt;
            text-decoration: none;
            display: inline-block;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="action-buttons no-print">
        <button onclick="window.history.back()" class="back-button">← Kembali</button>
        <button onclick="window.print()" class="print-button">Print Folio</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $roomStay->property->name }}</h1>
            <div class="property-name">
                @if($roomStay->property->address)
                    {{ $roomStay->property->address }}<br>
                @endif
                @if($roomStay->property->phone)
                    Tel: {{ $roomStay->property->phone }}
                @endif
            </div>
            <div class="folio-title">GUEST FOLIO</div>
            <div style="margin-top: 10px; font-size: 11pt;">
                Confirmation #: <strong>{{ $roomStay->confirmation_number }}</strong>
            </div>
        </div>

        <!-- Guest & Stay Information -->
        <div class="info-grid">
            <div class="info-box">
                <h2>Guest Information</h2>
                <div class="info-row">
                    <span class="info-label">Guest Name:</span>
                    <span class="info-value">{{ $roomStay->guest->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $roomStay->guest->email ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $roomStay->guest->phone_number ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">ID Number:</span>
                    <span class="info-value">{{ $roomStay->guest->id_number ?? '-' }}</span>
                </div>
            </div>

            <div class="info-box">
                <h2>Stay Details</h2>
                <div class="info-row">
                    <span class="info-label">Room Number:</span>
                    <span class="info-value">{{ $roomStay->hotelRoom->room_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Room Type:</span>
                    <span class="info-value">{{ $roomStay->hotelRoom->roomType->name ?? 'Standard' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Check-in:</span>
                    <span class="info-value">{{ $roomStay->check_in_date->format('d M Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Check-out:</span>
                    <span class="info-value">{{ $roomStay->check_out_date->format('d M Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nights:</span>
                    <span class="info-value">{{ $roomStay->check_in_date->diffInDays($roomStay->check_out_date) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        @if($roomStay->status === 'checked_in')
                            <span class="status-badge status-checked-in">Checked In</span>
                        @elseif($roomStay->status === 'checked_out')
                            <span class="status-badge status-checked-out">Checked Out</span>
                        @else
                            <span class="status-badge">{{ ucfirst($roomStay->status) }}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Daily Room Charges -->
        @if(count($dailyCharges) > 0)
        <table>
            <caption>Daily Room Charges</caption>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyCharges as $charge)
                <tr>
                    <td>{{ $charge['date']->format('d M Y') }}</td>
                    <td>{{ $charge['description'] }}</td>
                    <td class="text-right">Rp {{ number_format($charge['rate'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- F&B Orders -->
        @if($roomStay->fnbOrders->count() > 0)
        <table>
            <caption>Restaurant Charges</caption>
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Order #</th>
                    <th>Items</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomStay->fnbOrders as $order)
                <tr>
                    <td>{{ $order->order_time->format('d M Y H:i') }}</td>
                    <td>#{{ $order->order_number }}</td>
                    <td>
                        @foreach($order->items as $item)
                            {{ $item->quantity }}x {{ $item->menuItem->name }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Room Changes -->
        @if($roomChanges->count() > 0)
        <table>
            <caption>Room Changes History</caption>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>From Room</th>
                    <th>To Room</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomChanges as $change)
                <tr>
                    <td>{{ $change->processed_at->format('d M Y H:i') }}</td>
                    <td>{{ $change->oldRoom->room_number }}</td>
                    <td>{{ $change->newRoom->room_number }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $change->reason)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Payments -->
        <table>
            <caption>Payments Received</caption>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roomStay->payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('d M Y H:i') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                    <td>{{ $payment->reference_number ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">No payments recorded</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Folio Summary -->
        <div class="summary-box">
            <h2>FOLIO SUMMARY</h2>

            <div class="summary-row">
                <span>Room Charges:</span>
                <span>Rp {{ number_format($roomCharges, 0, ',', '.') }}</span>
            </div>

            @if($breakfastCharges > 0)
            <div class="summary-row">
                <span>Breakfast Charges:</span>
                <span>Rp {{ number_format($breakfastCharges, 0, ',', '.') }}</span>
            </div>
            @endif

            @if($fnbCharges > 0)
            <div class="summary-row">
                <span>F&B Charges:</span>
                <span>Rp {{ number_format($fnbCharges, 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="summary-row" style="border-top: 1px solid #999; padding-top: 5px; margin-top: 5px;">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

            <div class="summary-row">
                <span>Tax (10%):</span>
                <span>Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
            </div>

            <div class="summary-row">
                <span>Service Charge (5%):</span>
                <span>Rp {{ number_format($serviceCharge, 0, ',', '.') }}</span>
            </div>

            @if($roomStay->discount_amount > 0)
            <div class="summary-row">
                <span>Discount:</span>
                <span style="color: #c00;">- Rp {{ number_format($roomStay->discount_amount, 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="summary-row total">
                <span>Total Charges:</span>
                <span>Rp {{ number_format($totalCharges, 0, ',', '.') }}</span>
            </div>

            <div class="summary-row">
                <span>Total Payments:</span>
                <span style="color: #080;">Rp {{ number_format($totalPayments, 0, ',', '.') }}</span>
            </div>

            <div class="summary-row balance">
                <span>BALANCE DUE:</span>
                <span style="color: {{ $balance > 0 ? '#c00' : '#080' }};">
                    Rp {{ number_format($balance, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for staying with us!</p>
            <p>Printed on: {{ now()->format('d M Y H:i:s') }}</p>
            @if($roomStay->checkedInBy)
                <p>Checked in by: {{ $roomStay->checkedInBy->name }}</p>
            @endif
            @if($roomStay->checkedOutBy)
                <p>Checked out by: {{ $roomStay->checkedOutBy->name }}</p>
            @endif
        </div>
    </div>

    <script>
        // Auto-print on load (optional - can be removed if not desired)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
