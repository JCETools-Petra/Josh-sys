<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Notification</title>
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
            background-color: #F59E0B;
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
        .refund-box {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
            border: 2px solid #F59E0B;
            padding: 25px;
            margin: 20px 0;
            border-radius: 8px;
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
            padding: 10px 0;
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
        .highlight-box {
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
        .steps-box {
            background-color: #FEF3C7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .step {
            display: flex;
            margin: 15px 0;
        }
        .step-number {
            background-color: #F59E0B;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 28px;">Refund Notification</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">{{ $property->name }}</p>
    </div>

    <div class="content">
        <p>Dear {{ $guest->full_name }},</p>

        <p>Thank you for staying with us. We are writing to inform you that a refund has been initiated for your recent stay.</p>

        <div class="refund-box">
            <div style="margin-bottom: 10px;">
                <svg style="width: 48px; height: 48px; margin: 0 auto; display: block; color: #D97706;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h2 style="margin: 15px 0 5px 0; color: #92400E; font-size: 16px;">Refund Amount</h2>
            <p style="margin: 0; font-size: 36px; font-weight: bold; color: #D97706;">
                Rp {{ number_format($refund->amount, 0, ',', '.') }}
            </p>
        </div>

        <div class="info-box">
            <h3 style="margin: 0 0 15px 0; color: #1F2937;">Refund Details</h3>
            <div class="info-row">
                <span class="label">Refund Number:</span>
                <span class="value">{{ $refund->refund_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Refund Method:</span>
                <span class="value">{{ $refund->refund_method_label }}</span>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="value" style="color: #D97706;">{{ $refund->status_label }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date Initiated:</span>
                <span class="value">{{ $refund->created_at->format('d M Y, H:i') }}</span>
            </div>
            @if($refund->reason)
            <div class="info-row">
                <span class="label">Reason:</span>
                <span class="value">{{ $refund->reason }}</span>
            </div>
            @endif
        </div>

        <div class="info-box">
            <h3 style="margin: 0 0 15px 0; color: #1F2937;">Related Stay Information</h3>
            <div class="info-row">
                <span class="label">Confirmation Number:</span>
                <span class="value">{{ $roomStay->confirmation_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Room Number:</span>
                <span class="value">Room {{ $roomStay->hotelRoom->room_number ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Check-in:</span>
                <span class="value">{{ $roomStay->actual_check_in ? $roomStay->actual_check_in->format('d M Y') : 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Check-out:</span>
                <span class="value">{{ $roomStay->actual_check_out ? $roomStay->actual_check_out->format('d M Y') : 'N/A' }}</span>
            </div>
        </div>

        @if($refund->refund_method === 'bank_transfer' && $refund->bank_name)
        <div class="info-box" style="background-color: #DBEAFE; border: 2px solid #3B82F6;">
            <h3 style="margin: 0 0 15px 0; color: #1E3A8A;">Bank Transfer Details</h3>
            @if($refund->bank_name)
            <div class="info-row" style="border-color: #BFDBFE;">
                <span class="label">Bank Name:</span>
                <span class="value">{{ $refund->bank_name }}</span>
            </div>
            @endif
            @if($refund->account_number)
            <div class="info-row" style="border-color: #BFDBFE;">
                <span class="label">Account Number:</span>
                <span class="value">{{ $refund->account_number }}</span>
            </div>
            @endif
            @if($refund->account_holder_name)
            <div class="info-row" style="border-color: #BFDBFE;">
                <span class="label">Account Holder:</span>
                <span class="value">{{ $refund->account_holder_name }}</span>
            </div>
            @endif
        </div>
        @endif

        <div class="steps-box">
            <h3 style="margin: 0 0 20px 0; color: #92400E;">What Happens Next?</h3>

            @if($refund->refund_method === 'cash')
            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <strong>Visit Our Reception</strong><br>
                    <span style="color: #6B7280; font-size: 14px;">Please visit our reception desk with a valid ID to collect your cash refund.</span>
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div>
                    <strong>Sign Acknowledgment</strong><br>
                    <span style="color: #6B7280; font-size: 14px;">You'll need to sign a receipt confirming the refund amount received.</span>
                </div>
            </div>
            @elseif($refund->refund_method === 'bank_transfer')
            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <strong>Processing Time</strong><br>
                    <span style="color: #6B7280; font-size: 14px;">Bank transfers typically take 2-5 business days to process.</span>
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div>
                    <strong>Verify Account Details</strong><br>
                    <span style="color: #6B7280; font-size: 14px;">Please verify that the bank details provided are correct. Contact us immediately if any information needs to be updated.</span>
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div>
                    <strong>Confirmation</strong><br>
                    <span style="color: #6B7280; font-size: 14px;">You will receive a notification once the refund has been processed and transferred.</span>
                </div>
            </div>
            @elseif(in_array($refund->refund_method, ['credit_card', 'debit_card']))
            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <strong>Processing Time</strong><br>
                    <span style="color: #6B7280; font-size: 14px;">Card refunds typically take 5-10 business days to appear in your account.</span>
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div>
                    <strong>Refund to Original Card</strong><br>
                    <span style="color: #6B7280; font-size: 14px;">The refund will be credited to the same card used for the original deposit payment.</span>
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div>
                    <strong>Check Your Statement</strong><br>
                    <span style="color: #6B7280; font-size: 14px;">Please check your card statement. The refund will appear as a credit from {{ $property->name }}.</span>
                </div>
            </div>
            @else
            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <strong>Contact Our Team</strong><br>
                    <span style="color: #6B7280; font-size: 14px;">Please contact our reception for specific instructions on how to receive your refund.</span>
                </div>
            </div>
            @endif
        </div>

        <div class="highlight-box">
            <h4 style="margin: 0 0 10px 0; color: #1E3A8A;">Questions About Your Refund?</h4>
            <p style="margin: 0; color: #1E40AF;">If you have any questions or concerns about this refund, please don't hesitate to contact us:</p>
            <p style="margin: 10px 0 0 0;">
                @if($property->phone)
                <strong style="color: #1E3A8A;">Phone:</strong> {{ $property->phone }}<br>
                @endif
                @if($property->email)
                <strong style="color: #1E3A8A;">Email:</strong> {{ $property->email }}
                @endif
            </p>
        </div>

        <div style="background-color: #ECFDF5; border-left: 4px solid #059669; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <h4 style="margin: 0 0 10px 0; color: #065F46;">Thank You!</h4>
            <p style="margin: 0; color: #047857;">We appreciate your patronage and hope to welcome you back to {{ $property->name }} in the future. Thank you for choosing us!</p>
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
                This is an automated notification. Please keep this email for your records.<br>
                Reference Number: {{ $refund->refund_number }}
            </p>
        </div>
    </div>
</body>
</html>
