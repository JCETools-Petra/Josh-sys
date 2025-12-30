<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\RoomStay;

echo "=== DEBUG CALENDAR DISPLAY ISSUE ===\n\n";

// Get user with sales role
$salesUser = User::where('role', 'sales')->whereNotNull('property_id')->first();

if (!$salesUser) {
    echo "❌ No sales user found\n";
    exit(1);
}

echo "User Information:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Name: {$salesUser->name}\n";
echo "Email: {$salesUser->email}\n";
echo "Role: {$salesUser->role}\n";
echo "Property ID: {$salesUser->property_id}\n";
echo "Property Name: {$salesUser->property->name}\n";
echo "Email Verified: " . ($salesUser->email_verified_at ? 'YES' : 'NO') . "\n\n";

// Check calendar access
echo "Calendar Access Check:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$allowedRoles = ['pengguna_properti', 'owner', 'sales'];
$hasAccess = in_array($salesUser->role, $allowedRoles);

echo "Allowed roles: " . implode(', ', $allowedRoles) . "\n";
echo "User role: {$salesUser->role}\n";
echo "Has access: " . ($hasAccess ? '✅ YES' : '❌ NO') . "\n\n";

// Get calendar data
echo "Calendar Data:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$roomStays = RoomStay::where('property_id', $salesUser->property_id)
    ->where(function($query) {
        $query->whereIn('status', ['reserved', 'checked_in'])
              ->orWhere(function($q) {
                  $q->where('status', 'checked_out')
                    ->where('check_out_date', '>=', now()->subDays(7));
              });
    })
    ->with(['guest', 'hotelRoom.roomType'])
    ->orderBy('check_in_date', 'asc')
    ->get();

echo "Total reservations: {$roomStays->count()}\n\n";

if ($roomStays->isEmpty()) {
    echo "❌ NO RESERVATIONS FOUND!\n";
} else {
    echo "Reservations that should appear in calendar:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    foreach ($roomStays as $stay) {
        $guestName = $stay->guest ? $stay->guest->full_name : 'Unknown';
        $roomNumber = $stay->hotelRoom ? $stay->hotelRoom->room_number : 'Unknown';

        echo "• Guest: {$guestName}\n";
        echo "  Room: {$roomNumber}\n";
        echo "  Check-in: {$stay->check_in_date->format('d M Y')}\n";
        echo "  Check-out: {$stay->check_out_date->format('d M Y')}\n";
        echo "  Status: {$stay->status}\n";
        echo "  Confirmation: {$stay->confirmation_number}\n\n";
    }
}

// Check API endpoint simulation
echo "API Endpoint Simulation:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$events = $roomStays->map(function($roomStay) {
    $color = match($roomStay->status) {
        'reserved' => '#3B82F6',
        'confirmed' => '#10B981',
        'checked_in' => '#F59E0B',
        'checked_out' => '#6B7280',
        'cancelled' => '#EF4444',
        'no_show' => '#9333EA',
        default => '#6B7280',
    };

    $guestName = $roomStay->guest ? $roomStay->guest->full_name : 'Guest';
    $roomNumber = $roomStay->hotelRoom ? 'Room ' . $roomStay->hotelRoom->room_number : '';
    $title = "{$guestName}";
    if ($roomNumber) {
        $title .= " | {$roomNumber}";
    }

    return [
        'id' => $roomStay->id,
        'title' => $title,
        'start' => $roomStay->check_in_date->format('Y-m-d'),
        'end' => $roomStay->check_out_date->format('Y-m-d'),
        'color' => $color,
        'status' => $roomStay->status,
    ];
})->toArray();

echo "Events array:\n";
echo json_encode(['events' => $events], JSON_PRETTY_PRINT) . "\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TROUBLESHOOTING STEPS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "1. Login as: {$salesUser->email}\n";
echo "2. Visit: http://127.0.0.1:8000/property/calendar\n";
echo "3. Open browser console (F12) and check for:\n";
echo "   - JavaScript errors\n";
echo "   - Network tab for /property/calendar-data request\n";
echo "   - Response data in the request\n\n";

echo "4. If calendar is blank:\n";
echo "   - Clear browser cache (Ctrl+Shift+R)\n";
echo "   - Check if FullCalendar JS is loaded\n";
echo "   - Verify API response matches the JSON above\n\n";

echo "5. Expected to see {$roomStays->count()} events on the calendar\n\n";
