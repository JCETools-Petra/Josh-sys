<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in Confirmation</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .welcome-box {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: center;
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
        .amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }
        .amenity-badge {
            background-color: #EEF2FF;
            color: #4F46E5;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .highlight-box {
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
        .wifi-box {
            background-color: #DBEAFE;
            border: 2px dashed #3B82F6;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 28px;">Welcome!</h1>
        <p style="margin: 10px 0 0 0; font-size: 18px; opacity: 0.9;">You're Checked In</p>
        <p style="margin: 5px 0 0 0; font-size: 16px; opacity: 0.8;">{{ $property->name }}</p>
    </div>

    <div class="content">
        <div class="welcome-box">
            <h2 style="margin: 0 0 10px 0; color: #4F46E5; font-size: 24px;">Hello, {{ $guest->first_name }}!</h2>
            <p style="margin: 0; font-size: 16px; color: #6B7280;">We're delighted to have you with us. Your room is ready and we hope you have a wonderful stay!</p>
        </div>

        <div class="info-box">
            <h3 style="margin: 0 0 15px 0; color: #1F2937;">Your Room Details</h3>
            <div class="info-row">
                <span class="label">Room Number:</span>
                <span class="value" style="font-size: 18px; color: #4F46E5;">{{ $room->room_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Room Type:</span>
                <span class="value">{{ $room->roomType->name ?? 'Standard' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Check-in Time:</span>
                <span class="value">{{ $roomStay->actual_check_in->format('d M Y, H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Expected Check-out:</span>
                <span class="value">{{ $roomStay->check_out_date->format('d M Y') }} at {{ $property->check_out_time ?? '12:00' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Confirmation Number:</span>
                <span class="value">{{ $roomStay->confirmation_number }}</span>
            </div>
        </div>

        @if($property->wifi_network || $property->wifi_password)
        <div class="wifi-box">
            <h3 style="margin: 0 0 10px 0; color: #1E40AF;">
                <svg style="width: 24px; height: 24px; display: inline-block; vertical-align: middle;" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.778 8.222c-4.296-4.296-11.26-4.296-15.556 0A1 1 0 01.808 6.808c5.076-5.077 13.308-5.077 18.384 0a1 1 0 01-1.414 1.414zM14.95 11.05a7 7 0 00-9.9 0 1 1 0 01-1.414-1.414 9 9 0 0112.728 0 1 1 0 01-1.414 1.414zM12.12 13.88a3 3 0 00-4.242 0 1 1 0 01-1.415-1.415 5 5 0 017.072 0 1 1 0 01-1.415 1.415zM9 16a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z"></path>
                </svg>
                WiFi Information
            </h3>
            @if($property->wifi_network)
            <p style="margin: 5px 0;"><strong>Network:</strong> {{ $property->wifi_network }}</p>
            @endif
            @if($property->wifi_password)
            <p style="margin: 5px 0;"><strong>Password:</strong> <code style="background: white; padding: 3px 8px; border-radius: 4px; font-size: 16px;">{{ $property->wifi_password }}</code></p>
            @endif
        </div>
        @endif

        <div class="highlight-box">
            <h4 style="margin: 0 0 10px 0; color: #92400E;">Important Reminders</h4>
            <ul style="margin: 0; padding-left: 20px; color: #78350F;">
                <li>Check-out time is {{ $property->check_out_time ?? '12:00' }}. Late check-out may incur additional charges.</li>
                <li>Please keep your room key safe. Lost keys will be charged Rp 50,000.</li>
                <li>Smoking is only allowed in designated areas.</li>
                <li>Please inform reception if you need any assistance or additional amenities.</li>
                @if($roomStay->deposit_amount > 0)
                <li>Deposit paid: Rp {{ number_format($roomStay->deposit_amount, 0, ',', '.') }}. This will be settled during check-out.</li>
                @endif
            </ul>
        </div>

        <div class="info-box">
            <h3 style="margin: 0 0 15px 0; color: #1F2937;">Hotel Facilities & Services</h3>
            <div class="amenities">
                <span class="amenity-badge">24/7 Reception</span>
                <span class="amenity-badge">Free WiFi</span>
                <span class="amenity-badge">Room Service</span>
                <span class="amenity-badge">Housekeeping</span>
                <span class="amenity-badge">Restaurant</span>
                <span class="amenity-badge">Laundry Service</span>
            </div>
            <p style="margin: 15px 0 0 0; color: #6B7280; font-size: 14px;">
                Need anything? Just dial <strong style="color: #4F46E5;">0</strong> from your room phone or contact our reception.
            </p>
        </div>

        <div style="background-color: #ECFDF5; border-left: 4px solid #059669; padding: 15px; margin: 20px 0; border-radius: 4px; text-align: center;">
            <h4 style="margin: 0 0 10px 0; color: #065F46;">Need Assistance?</h4>
            <p style="margin: 0; color: #047857;">Our team is here to help make your stay comfortable.</p>
            @if($property->phone)
            <p style="margin: 10px 0 0 0;"><strong style="color: #065F46;">Reception:</strong> {{ $property->phone }}</p>
            @endif
        </div>

        <div style="background-color: #EEF2FF; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #4F46E5;">Enjoy Your Stay!</h3>
            <p style="margin: 0; color: #6B7280;">We hope you have a wonderful experience. If there's anything we can do to make your stay more comfortable, please don't hesitate to ask.</p>
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
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>
