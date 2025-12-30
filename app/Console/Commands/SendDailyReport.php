<?php

namespace App\Console\Commands;

use App\Mail\DailyReportMail;
use App\Models\DailyIncome;
use App\Models\HotelRoom;
use App\Models\Inventory;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\RoomStay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyReport extends Command
{
    protected $signature = 'report:send-daily {--property_id=}';
    protected $description = 'Send daily summary report to management';

    public function handle()
    {
        $propertyId = $this->option('property_id');

        if ($propertyId) {
            $properties = Property::where('id', $propertyId)->get();
        } else {
            $properties = Property::all();
        }

        foreach ($properties as $property) {
            $this->info("Generating report for {$property->name}...");

            $reportData = $this->generateReportData($property);

            // Get management emails from property settings or users
            $managementEmails = $this->getManagementEmails($property);

            if (empty($managementEmails)) {
                $this->warn("No management emails found for {$property->name}");
                continue;
            }

            // Send email
            foreach ($managementEmails as $email) {
                Mail::to($email)->send(new DailyReportMail($property, $reportData));
            }

            $this->info("Report sent to: " . implode(', ', $managementEmails));
        }

        $this->info('Daily reports sent successfully!');
        return 0;
    }

    private function generateReportData(Property $property)
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        // Occupancy Statistics
        $totalRooms = $property->hotelRooms()->count();
        $occupiedRooms = $property->hotelRooms()->occupied()->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 2) : 0;

        // Room Status
        $roomsByStatus = [
            'available' => $property->hotelRooms()->available()->count(),
            'occupied' => $occupiedRooms,
            'dirty' => $property->hotelRooms()->dirty()->count(),
            'maintenance' => $property->hotelRooms()->needsMaintenance()->count(),
        ];

        // Today's Arrivals & Departures
        $arrivalsToday = RoomStay::where('property_id', $property->id)
            ->whereDate('check_in_date', $today)
            ->whereIn('status', ['reserved', 'pending_checkin'])
            ->count();

        $departuresToday = RoomStay::where('property_id', $property->id)
            ->whereDate('check_out_date', $today)
            ->where('status', 'checked_in')
            ->count();

        // Revenue (Yesterday)
        $yesterdayIncome = DailyIncome::where('property_id', $property->id)
            ->whereDate('date', $yesterday)
            ->first();

        $revenue = [
            'rooms' => $yesterdayIncome->total_rooms_revenue ?? 0,
            'fnb' => $yesterdayIncome->total_fb_revenue ?? 0,
            'mice' => $yesterdayIncome->mice_room_income ?? 0,
            'others' => $yesterdayIncome->others_income ?? 0,
            'total' => $yesterdayIncome->total_revenue ?? 0,
        ];

        // Payments Today
        $paymentsToday = Payment::where('property_id', $property->id)
            ->whereDate('created_at', $today)
            ->sum('amount');

        // Outstanding Balances
        $outstandingBalances = RoomStay::where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->where('balance_due', '>', 0)
            ->sum('balance_due');

        // Maintenance Issues
        $maintenanceOpen = MaintenanceRequest::where('property_id', $property->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $maintenanceUrgent = MaintenanceRequest::where('property_id', $property->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('priority', 'urgent')
            ->count();

        // Low Stock Items
        $lowStockItems = Inventory::where('property_id', $property->id)
            ->whereColumn('stock', '<=', 'minimum_standard_quantity')
            ->select('name', 'stock', 'minimum_standard_quantity', 'unit')
            ->limit(10)
            ->get();

        // Guest Statistics
        $currentGuests = RoomStay::where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->count();

        return [
            'date' => now()->format('d F Y'),
            'occupancy' => [
                'total_rooms' => $totalRooms,
                'occupied' => $occupiedRooms,
                'rate' => $occupancyRate,
            ],
            'rooms_status' => $roomsByStatus,
            'arrivals_today' => $arrivalsToday,
            'departures_today' => $departuresToday,
            'current_guests' => $currentGuests,
            'revenue' => $revenue,
            'payments_today' => $paymentsToday,
            'outstanding_balances' => $outstandingBalances,
            'maintenance' => [
                'open' => $maintenanceOpen,
                'urgent' => $maintenanceUrgent,
            ],
            'low_stock' => $lowStockItems,
        ];
    }

    private function getManagementEmails(Property $property)
    {
        // Get emails from users with 'admin' or 'owner' role for this property
        $emails = [];

        // Get property manager email if set
        $users = \App\Models\User::where('property_id', $property->id)
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'owner', 'manager']);
            })
            ->pluck('email')
            ->toArray();

        $emails = array_merge($emails, $users);

        // Add default email from settings if configured
        // You can add this to property settings table later

        return array_unique(array_filter($emails));
    }
}
