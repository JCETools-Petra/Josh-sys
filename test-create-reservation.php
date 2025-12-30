<?php

/**
 * Test Script - Create Reservation for Dec 31
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Guest;
use App\Models\HotelRoom;
use App\Models\RoomStay;
use App\Models\Property;
use App\Services\FrontOfficeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== Test Create Reservation for Dec 31 ===" . PHP_EOL . PHP_EOL;

try {
    // Get property
    $property = Property::find(13); // Sunnyday Inn
    if (!$property) {
        throw new Exception("Property not found");
    }
    echo "✓ Property: {$property->name}" . PHP_EOL;

    // Get available room
    $room = HotelRoom::where('property_id', $property->id)
        ->where('status', 'vacant_clean')
        ->first();

    if (!$room) {
        // Try any room
        $room = HotelRoom::where('property_id', $property->id)->first();
    }

    if (!$room) {
        throw new Exception("No rooms available");
    }

    echo "✓ Room: {$room->room_number}" . PHP_EOL;

    // Create guest data
    $guestData = [
        'property_id' => $property->id,
        'first_name' => 'Test',
        'last_name' => 'Dec31',
        'email' => 'test.dec31@example.com',
        'phone' => '081234567890',
        'id_type' => 'ktp',
        'id_number' => 'TEST31122024',
        'address' => 'Test Address',
        'city' => 'Test City',
    ];

    // Create reservation data
    $checkIn = Carbon::parse('2024-12-31');
    $checkOut = Carbon::parse('2025-01-01');
    $nights = $checkIn->diffInDays($checkOut);
    $roomRate = 200000;
    $totalRoomCharge = $roomRate * $nights;

    $reservationData = [
        'property_id' => $property->id,
        'hotel_room_id' => $room->id,
        'guest' => $guestData,
        'check_in_date' => $checkIn->format('Y-m-d'),
        'check_out_date' => $checkOut->format('Y-m-d'),
        'room_rate_per_night' => $roomRate,
        'total_room_charge' => $totalRoomCharge,
        'tax_amount' => $totalRoomCharge * 0.10,
        'service_charge' => $totalRoomCharge * 0.05,
        'adults' => 2,
        'children' => 0,
        'source' => 'walk_in',
        'special_requests' => 'Test reservation for Dec 31',
        'status' => 'reserved',
        'bar_level' => 1,
    ];

    echo "✓ Data prepared" . PHP_EOL;
    echo "  Check-in: {$checkIn->format('d M Y')}" . PHP_EOL;
    echo "  Check-out: {$checkOut->format('d M Y')}" . PHP_EOL;
    echo "  Nights: {$nights}" . PHP_EOL . PHP_EOL;

    // Use FrontOfficeService to create reservation
    $service = new FrontOfficeService();

    DB::beginTransaction();
    $roomStay = $service->createReservation($reservationData);
    DB::commit();

    echo "✅ RESERVATION CREATED SUCCESSFULLY!" . PHP_EOL . PHP_EOL;
    echo "Details:" . PHP_EOL;
    echo "  ID: {$roomStay->id}" . PHP_EOL;
    echo "  Confirmation: {$roomStay->confirmation_number}" . PHP_EOL;
    echo "  Guest: {$roomStay->guest->full_name}" . PHP_EOL;
    echo "  Room: {$roomStay->hotelRoom->room_number}" . PHP_EOL;
    echo "  Status: {$roomStay->status}" . PHP_EOL;
    echo "  Check-in: {$roomStay->check_in_date->format('d M Y')}" . PHP_EOL;
    echo "  Check-out: {$roomStay->check_out_date->format('d M Y')}" . PHP_EOL . PHP_EOL;

    echo "✓ Check calendar at: http://127.0.0.1:8000/property/calendar" . PHP_EOL;
    echo "✓ This reservation should now appear on the calendar!" . PHP_EOL;

} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}
