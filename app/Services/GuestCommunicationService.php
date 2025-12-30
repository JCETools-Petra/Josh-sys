<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\RoomStay;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuestCommunicationService
{
    /**
     * Send SMS via Twilio
     */
    public function sendSMS(string $phone, string $message): bool
    {
        try {
            if (!config('services.twilio.enabled', false)) {
                Log::info('Twilio disabled, SMS not sent', ['phone' => $phone]);
                return false;
            }

            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $from = env('TWILIO_WHATSAPP_FROM');

            if (!$sid || !$token) {
                Log::warning('Twilio credentials not configured');
                return false;
            }

            $client = new \Twilio\Rest\Client($sid, $token);
            $client->messages->create(
                $phone,
                [
                    'from' => $from,
                    'body' => $message
                ]
            );

            Log::info('SMS sent successfully', ['phone' => $phone]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send WhatsApp message via Fonnte
     */
    public function sendWhatsApp(string $phone, string $message): bool
    {
        try {
            $token = env('FONNTE_TOKEN');

            if (!$token) {
                Log::warning('Fonnte token not configured');
                return false;
            }

            // Clean phone number (remove +, spaces, etc)
            $phone = preg_replace('/[^0-9]/', '', $phone);

            // Ensure phone starts with country code (62 for Indonesia)
            if (!str_starts_with($phone, '62')) {
                if (str_starts_with($phone, '0')) {
                    $phone = '62' . substr($phone, 1);
                } else {
                    $phone = '62' . $phone;
                }
            }

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp sent successfully', ['phone' => $phone]);
                return true;
            } else {
                Log::error('WhatsApp send failed', [
                    'phone' => $phone,
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send pre-arrival message
     */
    public function sendPreArrivalMessage(RoomStay $roomStay, int $daysBeforeArrival): bool
    {
        $guest = $roomStay->guest;
        if (!$guest || !$guest->phone) {
            return false;
        }

        $property = $roomStay->property;
        $checkInDate = \Carbon\Carbon::parse($roomStay->check_in_date)->format('d M Y');

        $message = "Halo {$guest->name}! 👋\n\n";

        if ($daysBeforeArrival == 7) {
            $message .= "Kami mengingatkan bahwa check-in Anda di {$property->name} akan dilakukan 7 hari lagi pada {$checkInDate}.\n\n";
            $message .= "Kami sangat menantikan kedatangan Anda! 🏨\n\n";
        } elseif ($daysBeforeArrival == 3) {
            $message .= "Hanya 3 hari lagi menuju check-in Anda di {$property->name} pada {$checkInDate}! 🎉\n\n";
            $message .= "Jika ada permintaan khusus, silakan hubungi kami.\n\n";
        } elseif ($daysBeforeArrival == 1) {
            $message .= "Besok adalah hari check-in Anda di {$property->name}! 🌟\n\n";
            $message .= "Check-in: {$checkInDate}\n";
            $message .= "Kamar: {$roomStay->roomType->name}\n";
            $message .= "Waktu check-in: 14:00\n\n";
            $message .= "Kami siap menyambut Anda!\n\n";
        }

        if ($property->phone) {
            $message .= "Hubungi kami: {$property->phone}\n";
        }

        $message .= "\nSalam hangat,\n{$property->name}";

        return $this->sendWhatsApp($guest->phone, $message);
    }

    /**
     * Send post-stay thank you message
     */
    public function sendPostStayMessage(RoomStay $roomStay): bool
    {
        $guest = $roomStay->guest;
        if (!$guest || !$guest->phone) {
            return false;
        }

        $property = $roomStay->property;

        $message = "Terima kasih telah menginap di {$property->name}! 🙏\n\n";
        $message .= "Halo {$guest->name},\n\n";
        $message .= "Kami berharap Anda menikmati menginap bersama kami. ";
        $message .= "Kepuasan Anda adalah prioritas kami.\n\n";
        $message .= "Kami akan sangat menghargai jika Anda bersedia memberikan review tentang pengalaman Anda. ";
        $message .= "Masukan Anda sangat berarti bagi kami.\n\n";

        if ($property->phone) {
            $message .= "Untuk reservasi berikutnya, hubungi: {$property->phone}\n\n";
        }

        $message .= "Kami menantikan kunjungan Anda kembali! ✨\n\n";
        $message .= "Salam hangat,\n{$property->name}";

        return $this->sendWhatsApp($guest->phone, $message);
    }

    /**
     * Send birthday greeting
     */
    public function sendBirthdayGreeting(Guest $guest, ?int $propertyId = null): bool
    {
        if (!$guest->phone) {
            return false;
        }

        $property = $propertyId ? \App\Models\Property::find($propertyId) : $guest->property;

        $message = "🎉 SELAMAT ULANG TAHUN! 🎂\n\n";
        $message .= "Halo {$guest->name}!\n\n";
        $message .= "Seluruh tim {$property->name} mengucapkan Selamat Ulang Tahun! ";
        $message .= "Semoga hari istimewa Anda dipenuhi kebahagiaan. 🌟\n\n";
        $message .= "Sebagai hadiah ulang tahun, kami ingin memberikan penawaran khusus untuk Anda:\n\n";
        $message .= "✨ Diskon 20% untuk reservasi kamar\n";
        $message .= "✨ Complimentary upgrade (tergantung ketersediaan)\n";
        $message .= "✨ Welcome drink gratis\n\n";
        $message .= "Penawaran berlaku hingga akhir bulan ini.\n\n";

        if ($property->phone) {
            $message .= "Hubungi kami untuk reservasi: {$property->phone}\n\n";
        }

        $message .= "Salam hangat,\n{$property->name}";

        return $this->sendWhatsApp($guest->phone, $message);
    }

    /**
     * Send booking confirmation
     */
    public function sendBookingConfirmation(RoomStay $roomStay): bool
    {
        $guest = $roomStay->guest;
        if (!$guest || !$guest->phone) {
            return false;
        }

        $property = $roomStay->property;
        $checkIn = \Carbon\Carbon::parse($roomStay->check_in_date)->format('d M Y');
        $checkOut = \Carbon\Carbon::parse($roomStay->check_out_date)->format('d M Y');

        $message = "✅ KONFIRMASI RESERVASI\n\n";
        $message .= "Halo {$guest->name}!\n\n";
        $message .= "Reservasi Anda telah dikonfirmasi:\n\n";
        $message .= "📍 Hotel: {$property->name}\n";
        $message .= "🛏️ Kamar: {$roomStay->roomType->name}\n";
        $message .= "📅 Check-in: {$checkIn}\n";
        $message .= "📅 Check-out: {$checkOut}\n";
        $message .= "👥 Jumlah malam: {$roomStay->nights}\n\n";

        if ($roomStay->room_rate_per_night) {
            $message .= "💰 Harga: Rp " . number_format($roomStay->room_rate_per_night, 0, ',', '.') . "/malam\n\n";
        }

        $message .= "Terima kasih telah memilih {$property->name}!\n\n";

        if ($property->phone) {
            $message .= "Hubungi: {$property->phone}\n";
        }

        $message .= "\nSampai jumpa! 🏨";

        return $this->sendWhatsApp($guest->phone, $message);
    }

    /**
     * Send invoice via WhatsApp
     */
    public function sendInvoiceWhatsApp(RoomStay $roomStay): bool
    {
        $guest = $roomStay->guest;
        if (!$guest || !$guest->phone) {
            return false;
        }

        $property = $roomStay->property;

        // Calculate invoice totals
        $fnbTotal = $roomStay->fnbOrders->sum('total_amount');
        $grandTotal = $roomStay->total_room_charge
            + $roomStay->total_breakfast_charge
            + $fnbTotal
            + $roomStay->tax_amount
            + $roomStay->service_charge
            - ($roomStay->discount_amount ?? 0);

        $totalPaid = $roomStay->payments->sum('amount');
        $balance = $grandTotal - $totalPaid;

        $message = "🧾 INVOICE - {$property->name}\n\n";
        $message .= "Halo {$guest->name}!\n\n";
        $message .= "Terima kasih telah menginap bersama kami.\n\n";
        $message .= "📋 DETAIL INVOICE\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "Invoice #: {$roomStay->confirmation_number}\n";
        $message .= "Tanggal: " . now()->format('d M Y') . "\n";
        $message .= "Kamar: {$roomStay->hotelRoom->room_number}\n\n";

        $message .= "💰 RINCIAN BIAYA\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "Kamar ({$roomStay->nights} malam): Rp " . number_format($roomStay->total_room_charge, 0, ',', '.') . "\n";

        if ($roomStay->total_breakfast_charge > 0) {
            $message .= "Sarapan: Rp " . number_format($roomStay->total_breakfast_charge, 0, ',', '.') . "\n";
        }

        if ($fnbTotal > 0) {
            $message .= "F&B: Rp " . number_format($fnbTotal, 0, ',', '.') . "\n";
        }

        $message .= "Pajak: Rp " . number_format($roomStay->tax_amount, 0, ',', '.') . "\n";
        $message .= "Service: Rp " . number_format($roomStay->service_charge, 0, ',', '.') . "\n\n";

        $message .= "TOTAL: Rp " . number_format($grandTotal, 0, ',', '.') . "\n";
        $message .= "Terbayar: Rp " . number_format($totalPaid, 0, ',', '.') . "\n";
        $message .= "━━━━━━━━━━━━━━━━\n";

        if ($balance > 0) {
            $message .= "⚠️ Saldo Tersisa: Rp " . number_format($balance, 0, ',', '.') . "\n\n";
            $message .= "Mohon segera melakukan pelunasan.\n";
        } elseif ($balance < 0) {
            $message .= "✅ Kredit: Rp " . number_format(abs($balance), 0, ',', '.') . "\n\n";
        } else {
            $message .= "✅ LUNAS\n\n";
        }

        if ($property->phone) {
            $message .= "Hubungi: {$property->phone}\n";
        }

        $message .= "\nTerima kasih! 🙏\n{$property->name}";

        return $this->sendWhatsApp($guest->phone, $message);
    }

    /**
     * Send payment reminder
     */
    public function sendPaymentReminder(RoomStay $roomStay, float $balanceDue, int $daysOverdue): bool
    {
        $guest = $roomStay->guest;
        if (!$guest || !$guest->phone) {
            return false;
        }

        $property = $roomStay->property;

        $message = "⏰ PENGINGAT PEMBAYARAN\n\n";
        $message .= "Halo {$guest->name}!\n\n";
        $message .= "Kami ingin mengingatkan bahwa terdapat saldo pembayaran yang belum dilunasi:\n\n";
        $message .= "📋 Invoice #: {$roomStay->confirmation_number}\n";
        $message .= "💰 Saldo Tersisa: Rp " . number_format($balanceDue, 0, ',', '.') . "\n";
        $message .= "📅 Check-out: " . $roomStay->actual_check_out->format('d M Y') . "\n";

        if ($daysOverdue > 0) {
            $message .= "⚠️ Terlambat: {$daysOverdue} hari\n\n";
        } else {
            $message .= "\n";
        }

        $message .= "Mohon segera melakukan pelunasan untuk menghindari biaya keterlambatan.\n\n";

        if ($property->phone) {
            $message .= "Hubungi kami: {$property->phone}\n";
        }

        if ($property->bank_name && $property->bank_account_number) {
            $message .= "\n🏦 TRANSFER BANK\n";
            $message .= "Bank: {$property->bank_name}\n";
            $message .= "A/N: {$property->bank_account_name}\n";
            $message .= "No. Rek: {$property->bank_account_number}\n";
        }

        $message .= "\nTerima kasih atas perhatiannya.\n{$property->name}";

        return $this->sendWhatsApp($guest->phone, $message);
    }
}
