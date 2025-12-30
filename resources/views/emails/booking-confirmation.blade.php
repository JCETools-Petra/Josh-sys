<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3B82F6;
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
        .info-box {
            background-color: #F3F4F6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #E5E7EB;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #6B7280;
        }
        .value {
            color: #111827;
            font-weight: 500;
        }
        .highlight {
            background-color: #DBEAFE;
            border-left: 4px solid #3B82F6;
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
        .button {
            display: inline-block;
            background-color: #3B82F6;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 28px;">Booking Confirmed!</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">{{ $property->name }}</p>
    </div>

    <div class="content">
        <p>Dear {{ $guest->full_name }},</p>

        <p>Thank you for your reservation! We're delighted to confirm your booking. Below are the details of your stay:</p>

        <div class="highlight">
            <h3 style="margin: 0 0 10px 0; color: #1F2937;">Confirmation Number</h3>
            <p style="margin: 0; font-size: 24px; font-weight: bold; color: #3B82F6;">{{ $reservation->confirmation_number }}</p>
        </div>

        <div class="info-box">
            <h3 style="margin: 0 0 15px 0; color: #1F2937;">Guest Information</h3>
            <div class="info-row">
                <span class="label">Name:</span>
                <span class="value">{{ $guest->full_name }}</span>
            </div>
            @if($guest->email)
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ $guest->email }}</span>
            </div>
            @endif
            @if($guest->phone)
            <div class="info-row">
                <span class="label">Phone:</span>
                <span class="value">{{ $guest->phone }}</span>
            </div>
            @endif
        </div>

        <div class="info-box">
            <h3 style="margin: 0 0 15px 0; color: #1F2937;">Reservation Details</h3>
            <div class="info-row">
                <span class="label">Check-in:</span>
                <span class="value">{{ $reservation->check_in_date->format('l, d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Check-out:</span>
                <span class="value">{{ $reservation->check_out_date->format('l, d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Nights:</span>
                <span class="value">{{ $reservation->nights }} {{ $reservation->nights > 1 ? 'nights' : 'night' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Room Type:</span>
                <span class="value">{{ $room->roomType->name ?? 'Standard' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Room Number:</span>
                <span class="value">Room {{ $room->room_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Adults:</span>
                <span class="value">{{ $reservation->adults }}</span>
            </div>
            @if($reservation->children > 0)
            <div class="info-row">
                <span class="label">Children:</span>
                <span class="value">{{ $reservation->children }}</span>
            </div>
            @endif
        </div>

        <div class="info-box">
            <h3 style="margin: 0 0 15px 0; color: #1F2937;">Pricing</h3>
            <div class="info-row">
                <span class="label">Room Rate (per night):</span>
                <span class="value">Rp {{ number_format($reservation->room_rate_per_night, 0, ',', '.') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Total Room Charge:</span>
                <span class="value">Rp {{ number_format($reservation->total_room_charge, 0, ',', '.') }}</span>
            </div>
            @if($reservation->deposit_amount > 0)
            <div class="info-row" style="background-color: #ECFDF5;">
                <span class="label">Deposit Paid:</span>
                <span class="value" style="color: #059669;">Rp {{ number_format($reservation->deposit_amount, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        @if($reservation->special_requests)
        <div class="info-box">
            <h3 style="margin: 0 0 15px 0; color: #1F2937;">Special Requests</h3>
            <p style="margin: 0; color: #4B5563;">{{ $reservation->special_requests }}</p>
        </div>
        @endif

        <div style="background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <h4 style="margin: 0 0 10px 0; color: #92400E;">Important Information</h4>
            <ul style="margin: 0; padding-left: 20px; color: #78350F;">
                <li>Check-in time: {{ $property->check_in_time ?? '14:00' }}</li>
                <li>Check-out time: {{ $property->check_out_time ?? '12:00' }}</li>
                <li>Please bring a valid ID upon check-in</li>
                <li>Early check-in or late check-out subject to availability</li>
            </ul>
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
                This is an automated email. Please do not reply to this message.<br>
                If you have any questions, please contact us directly.
            </p>
        </div>
    </div>
</body>
</html>
