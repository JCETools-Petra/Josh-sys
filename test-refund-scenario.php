<?php

/**
 * Test Script for Refund & Email Features
 *
 * This script creates a test scenario to demonstrate:
 * 1. Booking confirmation email
 * 2. Check-in confirmation email
 * 3. Checkout with refund scenario
 * 4. Invoice email
 * 5. Refund notification email
 *
 * Usage: C:/xampp/php/php.exe test-refund-scenario.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Guest;
use App\Models\HotelRoom;
use App\Models\RoomStay;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== Refund & Email Test Scenario ===" . PHP_EOL . PHP_EOL;

try {
    // Get first property
    $property = Property::first();
    if (!$property) {
        throw new Exception("No property found. Please create a property first.");
    }
    echo "✓ Using property: {$property->name}" . PHP_EOL;

    // Get any room (we'll use it for testing regardless of status)
    $room = HotelRoom::where('property_id', $property->id)->first();

    if (!$room) {
        throw new Exception("No rooms found in this property.");
    }

    // Save original status to restore later
    $originalRoomStatus = $room->status;
    echo "✓ Using room: {$room->room_number}" . PHP_EOL;

    // Create or get test guest with email
    $uniquePhone = '0812' . rand(10000000, 99999999);
    $guest = Guest::where('email', 'test.refund@example.com')->first();

    if (!$guest) {
        $guest = Guest::create([
            'property_id' => $property->id,
            'first_name' => 'Test',
            'last_name' => 'Refund',
            'phone' => $uniquePhone,
            'email' => 'test.refund@example.com',
            'id_number' => 'TEST' . rand(100000, 999999),
            'id_card_type' => 'ktp',
            'id_card_number' => 'TEST' . rand(100000, 999999),
            'address' => 'Test Address for Refund Scenario',
            'city' => 'Test City',
            'country' => 'Indonesia',
        ]);
    }
    echo "✓ Test guest: {$guest->full_name} ({$guest->email})" . PHP_EOL . PHP_EOL;

    // Start transaction
    DB::beginTransaction();

    // 1. Create Room Stay (Reservation with high deposit)
    echo "STEP 1: Creating reservation with high deposit..." . PHP_EOL;

    $checkInDate = Carbon::now();
    $checkOutDate = Carbon::now()->addDay();
    $nights = $checkInDate->diffInDays($checkOutDate);

    $roomStay = RoomStay::create([
        'property_id' => $property->id,
        'guest_id' => $guest->id,
        'hotel_room_id' => $room->id,
        'room_type_id' => $room->room_type_id,
        'confirmation_number' => 'TEST-' . strtoupper(uniqid()),
        'check_in_date' => $checkInDate,
        'check_out_date' => $checkOutDate,
        'actual_check_in' => null,
        'actual_check_out' => null,
        'adults' => 1,
        'children' => 0,
        'room_rate_per_night' => 150000,
        'nights' => $nights,
        'total_room_charge' => 150000 * $nights,
        'status' => 'reserved',
        'booking_source' => 'walk_in',
        'deposit_amount' => 1000000, // High deposit to trigger refund
        'paid_amount' => 1000000,
    ]);

    // Create reservation record
    $reservation = Reservation::create([
        'property_id' => $property->id,
        'room_stay_id' => $roomStay->id,
        'guest_id' => $guest->id,
        'hotel_room_id' => $room->id,
        'reservation_number' => $roomStay->confirmation_number,
        'confirmation_number' => $roomStay->confirmation_number,
        'check_in_date' => $checkInDate,
        'check_out_date' => $checkOutDate,
        'adults' => 1,
        'children' => 0,
        'room_rate_per_night' => 150000,
        'nights' => $nights,
        'total_room_charge' => 150000 * $nights,
        'status' => 'confirmed',
        'booking_source' => 'walk_in',
        'deposit_amount' => 1000000,
    ]);

    // Create deposit payment
    Payment::create([
        'property_id' => $property->id,
        'payable_type' => RoomStay::class,
        'payable_id' => $roomStay->id,
        'payment_method' => 'cash',
        'amount' => 1000000,
        'payment_date' => now(),
        'notes' => 'Deposit payment',
    ]);

    echo "  ✓ Reservation created: {$roomStay->confirmation_number}" . PHP_EOL;
    echo "  ✓ Deposit paid: Rp 1,000,000" . PHP_EOL;
    echo "  📧 Booking confirmation email queued" . PHP_EOL . PHP_EOL;

    // 2. Check-in
    echo "STEP 2: Checking in guest..." . PHP_EOL;

    $roomStay->update([
        'status' => 'checked_in',
        'actual_check_in' => now(),
    ]);

    $room->update([
        'status' => 'occupied',
        'current_guest_name' => $guest->full_name,
    ]);

    echo "  ✓ Guest checked in to room {$room->room_number}" . PHP_EOL;
    echo "  📧 Check-in confirmation email queued" . PHP_EOL . PHP_EOL;

    // 3. Checkout with refund
    echo "STEP 3: Processing checkout (refund scenario)..." . PHP_EOL;

    // Calculate total bill
    $totalRoomCharge = $roomStay->total_room_charge;
    $taxAmount = $totalRoomCharge * 0.10;
    $serviceCharge = $totalRoomCharge * 0.05;
    $totalBill = $totalRoomCharge + $taxAmount + $serviceCharge;

    $roomStay->update([
        'status' => 'checked_out',
        'actual_check_out' => now(),
        'tax_amount' => $taxAmount,
        'service_charge' => $serviceCharge,
    ]);

    $room->update([
        'status' => 'dirty',
        'current_guest_name' => null,
    ]);

    // Calculate refund
    $depositPaid = $roomStay->paid_amount;
    $balanceDue = $totalBill - $depositPaid;

    echo "  Total Bill: Rp " . number_format($totalBill, 0, ',', '.') . PHP_EOL;
    echo "  Deposit Paid: Rp " . number_format($depositPaid, 0, ',', '.') . PHP_EOL;
    echo "  Balance: Rp " . number_format($balanceDue, 0, ',', '.') . PHP_EOL;

    if ($balanceDue < 0) {
        $refundAmount = abs($balanceDue);
        echo "  ⚠️  REFUND SCENARIO DETECTED" . PHP_EOL;
        echo "  Refund Amount: Rp " . number_format($refundAmount, 0, ',', '.') . PHP_EOL;

        // Create refund
        $refund = Refund::create([
            'property_id' => $property->id,
            'room_stay_id' => $roomStay->id,
            'original_payment_id' => $roomStay->payments()->first()?->id,
            'amount' => $refundAmount,
            'refund_method' => 'bank_transfer',
            'status' => 'pending',
            'reason' => 'Deposit melebihi total tagihan (TEST SCENARIO)',
            'notes' => 'This is a test refund created by test script',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder_name' => $guest->full_name,
        ]);

        echo "  ✓ Refund created: {$refund->refund_number}" . PHP_EOL;
        echo "  ✓ Status: {$refund->status}" . PHP_EOL;
        echo "  ✓ Method: {$refund->refund_method}" . PHP_EOL;
        echo "  📧 Invoice email queued" . PHP_EOL;
        echo "  📧 Refund notification email queued" . PHP_EOL . PHP_EOL;
    }

    DB::commit();

    echo "=== TEST SCENARIO COMPLETED SUCCESSFULLY ===" . PHP_EOL . PHP_EOL;

    echo "Next Steps:" . PHP_EOL;
    echo "1. Start queue worker to process emails:" . PHP_EOL;
    echo "   C:/xampp/php/php.exe artisan queue:work --tries=3" . PHP_EOL . PHP_EOL;

    echo "2. Check refund in web interface:" . PHP_EOL;
    echo "   Navigate to: Refunds menu" . PHP_EOL;
    echo "   Refund Number: {$refund->refund_number}" . PHP_EOL . PHP_EOL;

    echo "3. Check jobs in queue:" . PHP_EOL;
    echo "   C:/xampp/php/php.exe artisan tinker --execute=\"echo DB::table('jobs')->count();\"" . PHP_EOL . PHP_EOL;

    echo "4. Monitor email logs:" . PHP_EOL;
    echo "   tail -f storage/logs/laravel.log" . PHP_EOL . PHP_EOL;

    echo "5. View refund details at:" . PHP_EOL;
    echo "   URL: " . url('/refunds/' . $refund->id) . PHP_EOL . PHP_EOL;

    // Print summary
    echo "=== Test Data Summary ===" . PHP_EOL;
    echo "Guest: {$guest->full_name}" . PHP_EOL;
    echo "Email: {$guest->email}" . PHP_EOL;
    echo "Confirmation: {$roomStay->confirmation_number}" . PHP_EOL;
    echo "Room: {$room->room_number}" . PHP_EOL;
    echo "Refund Number: {$refund->refund_number}" . PHP_EOL;
    echo "Refund Amount: Rp " . number_format($refund->amount, 0, ',', '.') . PHP_EOL;
    echo "Refund Status: {$refund->status}" . PHP_EOL;

} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}
