<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #059669;
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #E5E7EB;
        }
        .info-box {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th {
            background-color: #F9FAFB;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #E5E7EB;
            font-weight: 600;
            color: #374151;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #E5E7EB;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #F3F4F6;
            font-weight: 600;
        }
        .grand-total {
            background-color: #059669;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }
        .payment-box {
            background-color: #ECFDF5;
            border-left: 4px solid #059669;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .refund-box {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            color: #6B7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 28px;">INVOICE</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">{{ $property->name }}</p>
    </div>

    <div class="content">
        <div class="invoice-details">
            <div>
                <h3 style="margin: 0 0 10px 0; color: #374151;">Bill To:</h3>
                <p style="margin: 0; font-weight: 600; font-size: 16px;">{{ $guest->full_name }}</p>
                @if($guest->email)
                <p style="margin: 5px 0 0 0; color: #6B7280;">{{ $guest->email }}</p>
                @endif
                @if($guest->phone)
                <p style="margin: 5px 0 0 0; color: #6B7280;">{{ $guest->phone }}</p>
                @endif
            </div>
            <div style="text-align: right;">
                <h3 style="margin: 0 0 10px 0; color: #374151;">Invoice Details:</h3>
                <p style="margin: 0;"><strong>Invoice #:</strong> {{ $roomStay->confirmation_number }}</p>
                <p style="margin: 5px 0 0 0;"><strong>Date:</strong> {{ $roomStay->actual_check_out ? $roomStay->actual_check_out->format('d M Y') : now()->format('d M Y') }}</p>
                <p style="margin: 5px 0 0 0;"><strong>Room:</strong> {{ $roomStay->hotelRoom->room_number }}</p>
            </div>
        </div>

        <div class="info-box">
            <h3 style="margin: 0 0 10px 0; color: #374151;">Stay Information</h3>
            <p style="margin: 5px 0;"><strong>Check-in:</strong> {{ $roomStay->actual_check_in->format('d M Y, H:i') }}</p>
            <p style="margin: 5px 0;"><strong>Check-out:</strong> {{ $roomStay->actual_check_out ? $roomStay->actual_check_out->format('d M Y, H:i') : 'In Progress' }}</p>
            <p style="margin: 5px 0;"><strong>Duration:</strong> {{ $roomStay->nights }} {{ $roomStay->nights > 1 ? 'nights' : 'night' }}</p>
            <p style="margin: 5px 0;"><strong>Room Type:</strong> {{ $roomStay->hotelRoom->roomType->name ?? 'Standard' }}</p>
        </div>

        <h3 style="margin: 30px 0 15px 0; color: #374151;">Charges Breakdown</h3>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <!-- Room Charges -->
                <tr>
                    <td>
                        <strong>Room Charges</strong><br>
                        <span style="color: #6B7280; font-size: 14px;">{{ $roomStay->nights }} nights × Rp {{ number_format($roomStay->room_rate_per_night, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-right">Rp {{ number_format($roomStay->total_room_charge, 0, ',', '.') }}</td>
                </tr>

                <!-- Breakfast Charges -->
                @if($roomStay->total_breakfast_charge > 0)
                <tr>
                    <td>
                        <strong>Breakfast Charges</strong>
                    </td>
                    <td class="text-right">Rp {{ number_format($roomStay->total_breakfast_charge, 0, ',', '.') }}</td>
                </tr>
                @endif

                <!-- F&B Charges -->
                @if($roomStay->fnbOrders->count() > 0)
                @php
                    $fnbTotal = $roomStay->fnbOrders->sum('total_amount');
                @endphp
                <tr>
                    <td>
                        <strong>F&B Charges</strong><br>
                        @foreach($roomStay->fnbOrders as $order)
                        <span style="color: #6B7280; font-size: 13px; display: block; margin: 3px 0;">
                            Order #{{ $order->order_number }} - {{ $order->order_time->format('d M, H:i') }} (Rp {{ number_format($order->total_amount, 0, ',', '.') }})
                        </span>
                        @endforeach
                    </td>
                    <td class="text-right">Rp {{ number_format($fnbTotal, 0, ',', '.') }}</td>
                </tr>
                @endif

                <!-- Tax -->
                <tr>
                    <td><strong>Tax (10%)</strong></td>
                    <td class="text-right">Rp {{ number_format($roomStay->tax_amount, 0, ',', '.') }}</td>
                </tr>

                <!-- Service Charge -->
                <tr>
                    <td><strong>Service Charge (5%)</strong></td>
                    <td class="text-right">Rp {{ number_format($roomStay->service_charge, 0, ',', '.') }}</td>
                </tr>

                @if($roomStay->discount_amount > 0)
                <tr>
                    <td><strong>Discount</strong></td>
                    <td class="text-right" style="color: #059669;">- Rp {{ number_format($roomStay->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif

                <!-- Grand Total -->
                @php
                    $grandTotal = $roomStay->total_room_charge
                        + $roomStay->total_breakfast_charge
                        + $roomStay->fnbOrders->sum('total_amount')
                        + $roomStay->tax_amount
                        + $roomStay->service_charge
                        - ($roomStay->discount_amount ?? 0);
                @endphp
                <tr class="grand-total">
                    <td><strong>GRAND TOTAL</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Payments Received -->
        @if($roomStay->payments->count() > 0)
        <div class="payment-box">
            <h3 style="margin: 0 0 15px 0; color: #065F46;">Payments Received</h3>
            @foreach($roomStay->payments as $payment)
            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #D1FAE5;">
                <div>
                    <strong>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</strong><br>
                    <span style="font-size: 13px; color: #047857;">{{ $payment->payment_date->format('d M Y, H:i') }}</span>
                    @if($payment->notes)
                    <br><span style="font-size: 12px; color: #6B7280; font-style: italic;">{{ $payment->notes }}</span>
                    @endif
                </div>
                <div style="font-weight: 600; color: #065F46;">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </div>
            </div>
            @endforeach
            <div style="display: flex; justify-content: space-between; padding: 12px 0; margin-top: 10px; font-size: 16px;">
                <strong style="color: #065F46;">Total Paid:</strong>
                <strong style="color: #065F46;">Rp {{ number_format($roomStay->payments->sum('amount'), 0, ',', '.') }}</strong>
            </div>
        </div>
        @endif

        <!-- Refunds -->
        @if($roomStay->refunds->where('status', '!=', 'cancelled')->count() > 0)
        <div class="refund-box">
            <h3 style="margin: 0 0 15px 0; color: #92400E;">Refunds</h3>
            @foreach($roomStay->refunds->where('status', '!=', 'cancelled') as $refund)
            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #FDE68A;">
                <div>
                    <strong>{{ $refund->refund_number }}</strong> - {{ ucfirst(str_replace('_', ' ', $refund->refund_method)) }}<br>
                    <span style="font-size: 13px; color: #78350F;">{{ $refund->status_label }}</span>
                    @if($refund->reason)
                    <br><span style="font-size: 12px; color: #6B7280; font-style: italic;">{{ $refund->reason }}</span>
                    @endif
                </div>
                <div style="font-weight: 600; color: #92400E;">
                    Rp {{ number_format($refund->amount, 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Balance Summary -->
        @php
            $totalPaid = $roomStay->payments->sum('amount');
            $totalRefunded = $roomStay->refunds->where('status', '!=', 'cancelled')->sum('amount');
            $balance = $grandTotal - $totalPaid + $totalRefunded;
        @endphp

        <table style="margin-top: 20px;">
            <tbody>
                <tr class="total-row">
                    <td><strong>Balance Due</strong></td>
                    <td class="text-right">
                        <strong style="color: {{ $balance > 0 ? '#DC2626' : '#059669' }}; font-size: 18px;">
                            Rp {{ number_format(abs($balance), 0, ',', '.') }}
                            @if($balance < 0)
                            (Credit)
                            @elseif($balance == 0)
                            (Paid in Full)
                            @endif
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="background-color: #DBEAFE; border-left: 4px solid #3B82F6; padding: 15px; margin: 30px 0; border-radius: 4px;">
            <h4 style="margin: 0 0 10px 0; color: #1E3A8A;">Thank You!</h4>
            <p style="margin: 0; color: #1E40AF;">We appreciate your business and hope you enjoyed your stay at {{ $property->name }}. We look forward to welcoming you again soon!</p>
        </div>

        <div class="footer">
            <p><strong>{{ $property->name }}</strong></p>
            @if($property->address)
            <p style="margin: 5px 0;">{{ $property->address }}</p>
            @endif
            @if($property->phone)
            <p style="margin: 5px 0;">Phone: {{ $property->phone }}</p>
            @endif
            @if($property->email)
            <p style="margin: 5px 0;">Email: {{ $property->email }}</p>
            @endif

            <p style="margin-top: 20px; font-size: 12px; color: #9CA3AF;">
                This is an automated invoice. Please keep this for your records.<br>
                If you have any questions about this invoice, please contact us.
            </p>
        </div>
    </div>
</body>
</html>
