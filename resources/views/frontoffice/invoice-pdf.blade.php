<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $roomStay->confirmation_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2563eb;
        }

        .logo-section h1 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .logo-section p {
            color: #666;
            font-size: 12px;
        }

        .invoice-info {
            text-align: right;
        }

        .invoice-info h2 {
            color: #2563eb;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .invoice-info p {
            font-size: 12px;
            color: #666;
        }

        .details-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .detail-box {
            flex: 1;
        }

        .detail-box h3 {
            color: #2563eb;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .detail-box p {
            margin-bottom: 5px;
            font-size: 13px;
        }

        .detail-box strong {
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table thead {
            background-color: #2563eb;
            color: white;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }

        table tbody tr {
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:hover {
            background-color: #f9fafb;
        }

        table td {
            padding: 12px;
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .totals-section {
            margin-left: auto;
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }

        .total-row.grand-total {
            border-top: 2px solid #2563eb;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .print-button {
            background-color: #2563eb;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .print-button:hover {
            background-color: #1d4ed8;
        }

        @media print {
            .print-button {
                display: none;
            }

            body {
                padding: 0;
            }

            .invoice-container {
                border: none;
                padding: 20px;
            }
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-checked-in {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-checked-out {
            background-color: #dcfce7;
            color: #166534;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="logo-section">
                <h1>{{ $roomStay->property->name }}</h1>
                <p>{{ $roomStay->property->address ?? 'Alamat Property' }}</p>
                <p>Telp: {{ $roomStay->property->phone_number ?? '-' }}</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong>No:</strong> {{ $roomStay->confirmation_number }}</p>
                <p><strong>Tanggal:</strong> {{ now()->format('d M Y') }}</p>
                <p>
                    <span class="status-badge status-{{ $roomStay->status === 'checked_in' ? 'checked-in' : 'checked-out' }}">
                        {{ $roomStay->status === 'checked_in' ? 'Checked In' : 'Checked Out' }}
                    </span>
                </p>
            </div>
        </div>

        <div class="details-section">
            <div class="detail-box">
                <h3>Informasi Tamu</h3>
                <p><strong>Nama:</strong> {{ $roomStay->guest->full_name }}</p>
                <p><strong>Email:</strong> {{ $roomStay->guest->email ?? '-' }}</p>
                <p><strong>Telepon:</strong> {{ $roomStay->guest->phone }}</p>
                <p><strong>ID Type:</strong> {{ strtoupper($roomStay->guest->id_type) }}</p>
                <p><strong>ID Number:</strong> {{ $roomStay->guest->id_number }}</p>
            </div>
            <div class="detail-box">
                <h3>Informasi Menginap</h3>
                <p><strong>Kamar:</strong> {{ $roomStay->hotelRoom->room_number }} - {{ $roomStay->hotelRoom->roomType->name }}</p>
                <p><strong>Check-In:</strong> {{ \Carbon\Carbon::parse($roomStay->check_in_date)->format('d M Y, H:i') }}</p>
                <p><strong>Check-Out:</strong> {{ \Carbon\Carbon::parse($roomStay->check_out_date)->format('d M Y, H:i') }}</p>
                <p><strong>Jumlah Malam:</strong> {{ \Carbon\Carbon::parse($roomStay->check_in_date)->diffInDays(\Carbon\Carbon::parse($roomStay->check_out_date)) }} malam</p>
                <p><strong>Tamu:</strong> {{ $roomStay->adults }} Dewasa, {{ $roomStay->children }} Anak</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Room Charge</strong><br>
                        <small>{{ \Carbon\Carbon::parse($roomStay->check_in_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($roomStay->check_out_date)->format('d M Y') }}</small><br>
                        <small>{{ $roomStay->hotelRoom->room_number }} - {{ $roomStay->hotelRoom->roomType->name }}</small>
                    </td>
                    <td class="text-right">{{ \Carbon\Carbon::parse($roomStay->check_in_date)->diffInDays(\Carbon\Carbon::parse($roomStay->check_out_date)) }}</td>
                    <td class="text-right">Rp {{ number_format($roomStay->room_rate_per_night, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($roomStay->total_room_charge, 0, ',', '.') }}</td>
                </tr>

                @if($roomStay->fnbOrders->count() > 0)
                    @foreach($roomStay->fnbOrders as $order)
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <strong>F&B - {{ $item->menuItem->name }}</strong><br>
                                <small>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, H:i') }}</small>
                            </td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount'), 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span>Pajak (10%):</span>
                <span>Rp {{ number_format($roomStay->tax_amount, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span>Service Charge (5%):</span>
                <span>Rp {{ number_format($roomStay->service_charge, 0, ',', '.') }}</span>
            </div>
            <div class="total-row grand-total">
                <span>GRAND TOTAL:</span>
                <span>Rp {{ number_format($roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($roomStay->special_requests)
        <div style="margin-top: 20px; padding: 15px; background-color: #f9fafb; border-left: 3px solid #2563eb;">
            <strong>Permintaan Khusus:</strong><br>
            {{ $roomStay->special_requests }}
        </div>
        @endif

        @if($roomStay->payments->count() > 0)
        <div style="margin-top: 20px; padding: 15px; background-color: #f0f9ff; border-left: 3px solid #3b82f6;">
            <strong>Payment Details:</strong><br>
            @foreach($roomStay->payments as $payment)
            <div style="margin-top: 8px;">
                <strong>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}:</strong>
                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                @if($payment->card_number_last4)
                    (****{{ $payment->card_number_last4 }})
                @endif
                @if($payment->reference_number)
                    (Ref: {{ $payment->reference_number }})
                @endif
                @if($payment->notes)
                    <br><small style="color: #666;">{{ $payment->notes }}</small>
                @endif
            </div>
            @endforeach
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                <strong>Total Paid: Rp {{ number_format($roomStay->payments->sum('amount'), 0, ',', '.') }}</strong>
            </div>
        </div>
        @endif

        <div class="footer">
            <p><strong>Terima kasih atas kunjungan Anda!</strong></p>
            <p>Invoice ini digenerate otomatis oleh sistem pada {{ now()->format('d M Y, H:i') }}</p>
            <p style="margin-top: 10px; font-size: 11px;">Untuk pertanyaan, hubungi {{ $roomStay->property->phone_number ?? 'Front Desk' }}</p>
        </div>
    </div>

    <script>
        // Auto print when opened in new tab (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
