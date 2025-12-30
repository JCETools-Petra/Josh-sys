<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\RoomStay;

echo "=== Calendar Data Preview ===" . PHP_EOL . PHP_EOL;

$roomStays = RoomStay::where('property_id', 13)
    ->where(function($query) {
        $query->whereIn('status', ['reserved', 'checked_in'])
              ->orWhere(function($q) {
                  $q->where('status', 'checked_out')
                    ->where('check_out_date', '>=', now()->subDays(7));
              });
    })
    ->with('guest', 'hotelRoom')
    ->orderBy('check_in_date', 'asc')
    ->get();

echo "Total events yang akan muncul di kalender: " . $roomStays->count() . PHP_EOL . PHP_EOL;

if ($roomStays->isEmpty()) {
    echo "❌ TIDAK ADA DATA!" . PHP_EOL;
    echo "Ini berarti filter terlalu ketat atau tidak ada reservasi aktif." . PHP_EOL;
} else {
    foreach ($roomStays as $stay) {
        $color = match($stay->status) {
            'reserved' => '🔵 BIRU',
            'checked_in' => '🟠 ORANGE',
            'checked_out' => '⚫ GRAY',
            default => '⚪ WHITE',
        };

        echo "{$color} • {$stay->guest->full_name} | Room {$stay->hotelRoom->room_number}" . PHP_EOL;
        echo "  Check-in: {$stay->check_in_date->format('d M Y')} → Check-out: {$stay->check_out_date->format('d M Y')}" . PHP_EOL;
        echo "  Status: {$stay->status}" . PHP_EOL;
        echo "  Confirmation: {$stay->confirmation_number}" . PHP_EOL;
        echo PHP_EOL;
    }
}

echo PHP_EOL . "Silakan refresh kalender Anda: http://127.0.0.1:8000/property/calendar" . PHP_EOL;
