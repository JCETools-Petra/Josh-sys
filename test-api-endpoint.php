<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\RoomStay;
use App\Http\Controllers\PropertyIncomeController;
use Illuminate\Http\Request;

echo "=== DEBUG: Test Calendar API Endpoint ===" . PHP_EOL . PHP_EOL;

// Simulate authenticated user
$user = User::whereNotNull('property_id')->first();
if (!$user) {
    echo "❌ No user with property found" . PHP_EOL;
    exit(1);
}

echo "✓ User: {$user->name}" . PHP_EOL;
echo "✓ Property: {$user->property->name} (ID: {$user->property_id})" . PHP_EOL . PHP_EOL;

// Manually authenticate
auth()->login($user);

// Create controller instance
$controller = new PropertyIncomeController(app(\App\Services\ReservationPriceService::class));

// Create empty request
$request = Request::create('/property/calendar-data', 'GET');

// Call the method
try {
    $response = $controller->getCalendarData($request);
    $data = $response->getData(true);

    echo "=== API RESPONSE ===" . PHP_EOL;
    echo "Status: ✅ SUCCESS" . PHP_EOL . PHP_EOL;

    echo "Events Count: " . count($data['events']) . PHP_EOL . PHP_EOL;

    if (empty($data['events'])) {
        echo "❌ NO EVENTS RETURNED!" . PHP_EOL;
        echo "This is the problem - API returns empty array" . PHP_EOL . PHP_EOL;

        // Check why
        echo "Checking database directly..." . PHP_EOL;
        $roomStays = RoomStay::where('property_id', $user->property_id)
            ->where(function($query) {
                $query->whereIn('status', ['reserved', 'checked_in'])
                      ->orWhere(function($q) {
                          $q->where('status', 'checked_out')
                            ->where('check_out_date', '>=', now()->subDays(7));
                      });
            })
            ->count();

        echo "Room stays in DB: {$roomStays}" . PHP_EOL;

        if ($roomStays > 0) {
            echo "❌ DATA EXISTS but not returned by API!" . PHP_EOL;
        } else {
            echo "❌ NO DATA in database matching filter!" . PHP_EOL;
        }

    } else {
        echo "✅ Events returned successfully!" . PHP_EOL . PHP_EOL;

        foreach ($data['events'] as $event) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
            echo "Event ID: {$event['id']}" . PHP_EOL;
            echo "Title: {$event['title']}" . PHP_EOL;
            echo "Start: {$event['start']}" . PHP_EOL;
            echo "End: {$event['end']}" . PHP_EOL;
            echo "Color: {$event['color']}" . PHP_EOL;
            echo "Status: {$event['extendedProps']['status']}" . PHP_EOL;
            echo PHP_EOL;
        }
    }

    echo "=== CHART DATA ===" . PHP_EOL;
    if (isset($data['chartData'])) {
        echo "Labels count: " . count($data['chartData']['labels']) . PHP_EOL;
        echo "Data points: " . count($data['chartData']['data']) . PHP_EOL;
    }

    echo PHP_EOL . "=== FULL JSON RESPONSE ===" . PHP_EOL;
    echo json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo PHP_EOL . "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
