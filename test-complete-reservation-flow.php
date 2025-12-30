<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\HotelRoom;
use App\Models\RoomStay;
use App\Services\FrontOfficeService;
use Carbon\Carbon;

echo "=== TEST COMPLETE RESERVATION FLOW ===\n\n";

// Get user with property
$user = User::whereNotNull('property_id')->first();
if (!$user) {
    echo "❌ No user with property found\n";
    exit(1);
}

echo "✓ User: {$user->name}\n";
echo "✓ Property: {$user->property->name} (ID: {$user->property_id})\n\n";

// Authenticate
auth()->login($user);

// Get first available room
$room = HotelRoom::where('property_id', $user->property_id)
    ->where('status', 'vacant_clean')
    ->with('roomType')
    ->first();

if (!$room) {
    echo "❌ No available rooms found\n";
    exit(1);
}

echo "✓ Room: {$room->room_number} ({$room->roomType->name})\n";
echo "✓ Rate: Rp " . number_format($room->roomType->price, 0, ',', '.') . "\n\n";

// Test data
$checkIn = Carbon::tomorrow();
$checkOut = Carbon::tomorrow()->addDays(2);
$nights = $checkIn->diffInDays($checkOut);
$roomRate = $room->roomType->price > 0 ? $room->roomType->price : 500000; // Default 500k if not set
$totalRoomCharge = $roomRate * $nights;
$tax = $totalRoomCharge * 0.10;
$service = $totalRoomCharge * 0.05;

$reservationData = [
    'property_id' => $user->property_id,
    'hotel_room_id' => $room->id,
    'guest' => [
        'first_name' => 'Test',
        'last_name' => 'Reservation',
        'email' => 'test.reservation@example.com',
        'phone' => '08123456789',
        'id_type' => 'ktp',
        'id_number' => '3273012345678901',
        'address' => 'Jl. Test No. 123',
        'city' => 'Jakarta',
    ],
    'check_in_date' => $checkIn->format('Y-m-d'),
    'check_out_date' => $checkOut->format('Y-m-d'),
    'room_rate_per_night' => $roomRate,
    'total_room_charge' => $totalRoomCharge,
    'tax_amount' => $tax,
    'service_charge' => $service,
    'adults' => 2,
    'children' => 0,
    'source' => 'walk_in',
    'special_requests' => 'Test reservation via script',
];

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 1: Create Valid Reservation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Check-in: {$checkIn->format('d M Y')}\n";
echo "Check-out: {$checkOut->format('d M Y')}\n";
echo "Nights: {$nights}\n";
echo "Total: Rp " . number_format($totalRoomCharge + $tax + $service, 0, ',', '.') . "\n\n";

try {
    $service = new FrontOfficeService();
    $roomStay = $service->createReservation($reservationData);

    echo "✅ RESERVATION CREATED SUCCESSFULLY!\n\n";
    echo "Details:\n";
    echo "  Confirmation #: {$roomStay->confirmation_number}\n";
    echo "  Guest: {$roomStay->guest->full_name}\n";
    echo "  Room: {$roomStay->hotelRoom->room_number}\n";
    echo "  Status: {$roomStay->status}\n";
    echo "  Check-in: {$roomStay->check_in_date->format('d M Y')}\n";
    echo "  Check-out: {$roomStay->check_out_date->format('d M Y')}\n\n";

    // Now test double booking prevention
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "TEST 2: Prevent Double Booking\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Attempting to create another reservation for:\n";
    echo "  Same room: {$room->room_number}\n";
    echo "  Same dates: {$checkIn->format('d M Y')} - {$checkOut->format('d M Y')}\n\n";

    try {
        $duplicateData = $reservationData;
        $duplicateData['guest']['email'] = 'another.guest@example.com';
        $duplicateData['guest']['phone'] = '08198765432';

        $service->createReservation($duplicateData);
        echo "❌ FAILED! Double booking was allowed!\n";
    } catch (\Exception $e) {
        echo "✅ DOUBLE BOOKING PREVENTED!\n";
        echo "Error message: {$e->getMessage()}\n\n";
    }

    // Test overlapping dates
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "TEST 3: Prevent Overlapping Dates\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    $overlapCheckIn = $checkOut->copy()->subDay();
    $overlapCheckOut = $checkOut->copy()->addDay();

    echo "Attempting overlapping reservation:\n";
    echo "  Room: {$room->room_number}\n";
    echo "  Original: {$checkIn->format('d M Y')} - {$checkOut->format('d M Y')}\n";
    echo "  Overlap:  {$overlapCheckIn->format('d M Y')} - {$overlapCheckOut->format('d M Y')}\n\n";

    try {
        $overlapData = $reservationData;
        $overlapData['check_in_date'] = $overlapCheckIn->format('Y-m-d');
        $overlapData['check_out_date'] = $overlapCheckOut->format('Y-m-d');
        $overlapData['guest']['email'] = 'overlap.test@example.com';
        $overlapData['guest']['phone'] = '08111222333';

        $service->createReservation($overlapData);
        echo "❌ FAILED! Overlapping booking was allowed!\n";
    } catch (\Exception $e) {
        echo "✅ OVERLAPPING PREVENTED!\n";
        echo "Error message: {$e->getMessage()}\n\n";
    }

    // Verify calendar data
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "TEST 4: Verify Calendar Data\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    $calendarReservations = RoomStay::where('property_id', $user->property_id)
        ->whereIn('status', ['reserved', 'checked_in'])
        ->with(['guest', 'hotelRoom.roomType'])
        ->get();

    echo "Reservations in calendar: {$calendarReservations->count()}\n\n";

    foreach ($calendarReservations as $res) {
        echo "  • {$res->guest->full_name} - Room {$res->hotelRoom->room_number}\n";
        echo "    {$res->check_in_date->format('d M')} - {$res->check_out_date->format('d M')} ({$res->status})\n";
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ALL TESTS COMPLETED!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Next Steps:\n";
    echo "1. Visit http://127.0.0.1:8000/frontoffice\n";
    echo "2. Try to create a reservation - you should see SUCCESS or ERROR notification\n";
    echo "3. Check calendar at http://127.0.0.1:8000/property/calendar\n";
    echo "4. Try double booking - should show error notification\n\n";

    echo "To clean up test data:\n";
    echo "DELETE FROM room_stays WHERE confirmation_number = '{$roomStay->confirmation_number}';\n";

} catch (\Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n{$e->getTraceAsString()}\n";
}
