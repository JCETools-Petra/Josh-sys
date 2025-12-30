<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi 2FA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
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
            border-bottom: 3px solid #3B82F6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #3B82F6;
            margin: 0;
            font-size: 24px;
        }
        .code-box {
            background-color: #DBEAFE;
            border: 2px solid #3B82F6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #1E40AF;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .warning {
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
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Kode Verifikasi 2FA</h1>
        </div>

        <p>Halo <strong>{{ $user->name }}</strong>,</p>

        <p>Seseorang (mungkin Anda) mencoba login ke akun {{ config('app.name') }} Anda. Untuk keamanan akun, silakan verifikasi menggunakan kode berikut:</p>

        <div class="code-box">
            <div style="font-size: 12px; color: #6B7280; margin-bottom: 10px;">KODE VERIFIKASI</div>
            <div class="code">{{ $code }}</div>
            <div style="font-size: 12px; color: #6B7280; margin-top: 10px;">Berlaku selama 10 menit</div>
        </div>

        <p>Masukkan kode ini di halaman verifikasi untuk melanjutkan.</p>

        <div class="warning">
            <strong>⚠️ Penting:</strong><br>
            • Jangan berikan kode ini kepada siapapun, termasuk staf {{ config('app.name') }}<br>
            • Kode ini hanya berlaku selama 10 menit<br>
            • Jika Anda tidak mencoba login, abaikan email ini dan segera ubah password Anda
        </div>

        <p style="color: #6B7280; font-size: 13px;">
            <strong>Informasi Login:</strong><br>
            Waktu: {{ now()->format('d M Y, H:i') }} WIB<br>
            IP Address: {{ request()->ip() }}
        </p>

        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>Email otomatis, mohon tidak membalas.</p>
            <p style="margin-top: 10px;">Jika ada pertanyaan, hubungi tim support kami.</p>
        </div>
    </div>
</body>
</html>
