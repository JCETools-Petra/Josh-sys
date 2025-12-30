<?php

namespace App\Console\Commands;

use App\Models\RoomStay;
use App\Services\GuestCommunicationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    protected $signature = 'payments:send-reminders';
    protected $description = 'Send automated payment reminders for unpaid bills';

    private GuestCommunicationService $communicationService;

    public function __construct(GuestCommunicationService $communicationService)
    {
        parent::__construct();
        $this->communicationService = $communicationService;
    }

    public function handle()
    {
        $this->info('Starting payment reminders...');

        // Find checked-out room stays with unpaid balances
        $unpaidStays = RoomStay::where('status', 'checked_out')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('actual_check_out')
            ->with(['guest', 'property', 'payments', 'fnbOrders'])
            ->get();

        $this->info("Found {$unpaidStays->count()} unpaid room stays");

        $reminders1Day = 0;
        $reminders3Days = 0;
        $reminders7Days = 0;
        $reminders14Days = 0;

        foreach ($unpaidStays as $roomStay) {
            // Calculate balance due
            $fnbTotal = $roomStay->fnbOrders->sum('total_amount');
            $grandTotal = $roomStay->total_room_charge
                + $roomStay->total_breakfast_charge
                + $fnbTotal
                + $roomStay->tax_amount
                + $roomStay->service_charge
                - ($roomStay->discount_amount ?? 0);

            $totalPaid = $roomStay->payments->sum('amount');
            $balanceDue = $grandTotal - $totalPaid;

            // Skip if balance is less than Rp 1,000 (considered paid)
            if ($balanceDue < 1000) {
                continue;
            }

            // Calculate days since checkout
            $checkoutDate = Carbon::parse($roomStay->actual_check_out);
            $daysOverdue = now()->diffInDays($checkoutDate, false);

            // Check if we should send reminder based on last reminder sent
            $lastReminder = \App\Models\ActivityLog::where('loggable_id', $roomStay->id)
                ->where('loggable_type', RoomStay::class)
                ->where('action', 'payment_reminder_sent')
                ->latest('created_at')
                ->first();

            $daysSinceLastReminder = $lastReminder
                ? now()->diffInDays($lastReminder->created_at)
                : 999;

            // Send reminders at intervals: 1 day, 3 days, 7 days, 14 days after checkout
            $shouldSendReminder = false;
            $reminderType = null;

            if ($daysOverdue >= 14 && $daysSinceLastReminder >= 7) {
                $shouldSendReminder = true;
                $reminderType = '14-day';
                $reminders14Days++;
            } elseif ($daysOverdue >= 7 && $daysOverdue < 14 && $daysSinceLastReminder >= 7) {
                $shouldSendReminder = true;
                $reminderType = '7-day';
                $reminders7Days++;
            } elseif ($daysOverdue >= 3 && $daysOverdue < 7 && $daysSinceLastReminder >= 3) {
                $shouldSendReminder = true;
                $reminderType = '3-day';
                $reminders3Days++;
            } elseif ($daysOverdue >= 1 && $daysOverdue < 3 && !$lastReminder) {
                $shouldSendReminder = true;
                $reminderType = '1-day';
                $reminders1Day++;
            }

            if ($shouldSendReminder) {
                // Send reminder
                if ($this->communicationService->sendPaymentReminder($roomStay, $balanceDue, $daysOverdue)) {
                    // Log that we sent the reminder
                    \App\Models\ActivityLog::create([
                        'user_id' => null,
                        'property_id' => $roomStay->property_id,
                        'action' => 'payment_reminder_sent',
                        'description' => "Payment reminder ({$reminderType}) sent to {$roomStay->guest->name}. Balance: Rp " . number_format($balanceDue, 0, ',', '.') . ", Days overdue: {$daysOverdue}",
                        'loggable_id' => $roomStay->id,
                        'loggable_type' => RoomStay::class,
                    ]);

                    $this->info("✓ Sent {$reminderType} reminder to {$roomStay->guest->name} (Balance: Rp " . number_format($balanceDue, 0, ',', '.') . ")");
                } else {
                    $this->warn("✗ Failed to send reminder to {$roomStay->guest->name}");
                }
            }
        }

        $totalSent = $reminders1Day + $reminders3Days + $reminders7Days + $reminders14Days;
        $this->info("\nPayment reminders summary:");
        $this->info("1-day reminders: {$reminders1Day}");
        $this->info("3-day reminders: {$reminders3Days}");
        $this->info("7-day reminders: {$reminders7Days}");
        $this->info("14-day reminders: {$reminders14Days}");
        $this->info("Total sent: {$totalSent}");

        return 0;
    }
}
