<?php

namespace App\Console\Commands;

use App\Models\Guest;
use App\Models\RoomStay;
use App\Services\GuestCommunicationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendGuestNotifications extends Command
{
    protected $signature = 'guests:send-notifications';
    protected $description = 'Send automated notifications to guests (pre-arrival, post-stay, birthday)';

    private GuestCommunicationService $communicationService;

    public function __construct(GuestCommunicationService $communicationService)
    {
        parent::__construct();
        $this->communicationService = $communicationService;
    }

    public function handle()
    {
        $this->info('Starting guest notifications...');

        // Send pre-arrival notifications
        $this->sendPreArrivalNotifications();

        // Send post-stay follow-ups
        $this->sendPostStayFollowUps();

        // Send birthday greetings
        $this->sendBirthdayGreetings();

        $this->info('Guest notifications completed!');
        return 0;
    }

    private function sendPreArrivalNotifications()
    {
        $this->info('Checking pre-arrival notifications...');

        // 7 days before arrival
        $sevenDaysAhead = Carbon::now()->addDays(7)->startOfDay();
        $roomStays7d = RoomStay::whereDate('check_in_date', $sevenDaysAhead)
            ->whereIn('status', ['reserved', 'pending_checkin'])
            ->with(['guest', 'property', 'roomType'])
            ->get();

        foreach ($roomStays7d as $roomStay) {
            if ($this->communicationService->sendPreArrivalMessage($roomStay, 7)) {
                $this->info("✓ Sent 7-day reminder to {$roomStay->guest->name}");
            }
        }

        // 3 days before arrival
        $threeDaysAhead = Carbon::now()->addDays(3)->startOfDay();
        $roomStays3d = RoomStay::whereDate('check_in_date', $threeDaysAhead)
            ->whereIn('status', ['reserved', 'pending_checkin'])
            ->with(['guest', 'property', 'roomType'])
            ->get();

        foreach ($roomStays3d as $roomStay) {
            if ($this->communicationService->sendPreArrivalMessage($roomStay, 3)) {
                $this->info("✓ Sent 3-day reminder to {$roomStay->guest->name}");
            }
        }

        // 1 day before arrival
        $tomorrow = Carbon::now()->addDay()->startOfDay();
        $roomStays1d = RoomStay::whereDate('check_in_date', $tomorrow)
            ->whereIn('status', ['reserved', 'pending_checkin'])
            ->with(['guest', 'property', 'roomType'])
            ->get();

        foreach ($roomStays1d as $roomStay) {
            if ($this->communicationService->sendPreArrivalMessage($roomStay, 1)) {
                $this->info("✓ Sent 1-day reminder to {$roomStay->guest->name}");
            }
        }

        $total = $roomStays7d->count() + $roomStays3d->count() + $roomStays1d->count();
        $this->info("Pre-arrival notifications: {$total} sent");
    }

    private function sendPostStayFollowUps()
    {
        $this->info('Checking post-stay follow-ups...');

        // Send thank you message 1 day after checkout
        $yesterday = Carbon::now()->subDay()->startOfDay();

        $recentCheckouts = RoomStay::whereDate('check_out_date', $yesterday)
            ->where('status', 'checked_out')
            ->whereDoesntHave('activityLogs', function($query) {
                $query->where('action', 'post_stay_message_sent')
                    ->whereDate('created_at', '>=', Carbon::now()->subDays(2));
            })
            ->with(['guest', 'property'])
            ->get();

        $sent = 0;
        foreach ($recentCheckouts as $roomStay) {
            if ($this->communicationService->sendPostStayMessage($roomStay)) {
                // Log that we sent the message
                \App\Models\ActivityLog::create([
                    'user_id' => null,
                    'property_id' => $roomStay->property_id,
                    'action' => 'post_stay_message_sent',
                    'description' => "Post-stay thank you sent to {$roomStay->guest->name}",
                ]);

                $this->info("✓ Sent post-stay message to {$roomStay->guest->name}");
                $sent++;
            }
        }

        $this->info("Post-stay follow-ups: {$sent} sent");
    }

    private function sendBirthdayGreetings()
    {
        $this->info('Checking birthday greetings...');

        $today = Carbon::now();
        $currentDay = $today->day;
        $currentMonth = $today->month;

        // Find guests with birthday today (assuming date_of_birth exists)
        $guests = Guest::whereNotNull('phone')
            ->whereHas('property')
            ->whereRaw('DAY(date_of_birth) = ?', [$currentDay])
            ->whereRaw('MONTH(date_of_birth) = ?', [$currentMonth])
            ->whereDoesntHave('activityLogs', function($query) use ($today) {
                $query->where('action', 'birthday_greeting_sent')
                    ->whereDate('created_at', $today);
            })
            ->with('property')
            ->get();

        $sent = 0;
        foreach ($guests as $guest) {
            if ($this->communicationService->sendBirthdayGreeting($guest, $guest->property_id)) {
                // Log that we sent birthday greeting
                \App\Models\ActivityLog::create([
                    'user_id' => null,
                    'property_id' => $guest->property_id,
                    'action' => 'birthday_greeting_sent',
                    'description' => "Birthday greeting sent to {$guest->name}",
                ]);

                $this->info("✓ Sent birthday greeting to {$guest->name}");
                $sent++;
            }
        }

        $this->info("Birthday greetings: {$sent} sent");
    }
}
