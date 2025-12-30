<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== CHECK USER ACCESS TO CALENDAR ===" . PHP_EOL . PHP_EOL;

echo "Masukkan User ID yang sedang login: ";
$userId = trim(fgets(STDIN));

if (empty($userId) || !is_numeric($userId)) {
    echo PHP_EOL . "Checking all users with property..." . PHP_EOL . PHP_EOL;

    $users = User::whereNotNull('property_id')->with('property')->get();

    foreach ($users as $user) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
        echo "ID: {$user->id}" . PHP_EOL;
        echo "Name: {$user->name}" . PHP_EOL;
        echo "Email: {$user->email}" . PHP_EOL;
        echo "Role: {$user->role}" . PHP_EOL;
        echo "Property: " . ($user->property ? $user->property->name : 'NONE') . PHP_EOL;
        echo "Email Verified: " . ($user->email_verified_at ? '✅ YES' : '❌ NO') . PHP_EOL;

        // Check if has access
        $hasAccess = in_array($user->role, ['pengguna_properti', 'owner']);
        $canAccess = $hasAccess && $user->email_verified_at && $user->property;

        echo "Can Access Calendar: " . ($canAccess ? '✅ YES' : '❌ NO') . PHP_EOL;

        if (!$canAccess) {
            echo "Reason: ";
            if (!$hasAccess) echo "Wrong role (need: pengguna_properti or owner) ";
            if (!$user->email_verified_at) echo "Email not verified ";
            if (!$user->property) echo "No property assigned ";
            echo PHP_EOL;
        }
        echo PHP_EOL;
    }

    exit;
}

$user = User::with('property')->find($userId);

if (!$user) {
    echo "❌ User not found!" . PHP_EOL;
    exit(1);
}

echo "User Details:" . PHP_EOL;
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
echo "ID: {$user->id}" . PHP_EOL;
echo "Name: {$user->name}" . PHP_EOL;
echo "Email: {$user->email}" . PHP_EOL;
echo "Role: {$user->role}" . PHP_EOL;
echo "Property ID: {$user->property_id}" . PHP_EOL;
echo "Property: " . ($user->property ? $user->property->name : 'NONE') . PHP_EOL;
echo "Email Verified: " . ($user->email_verified_at ? '✅ YES at ' . $user->email_verified_at : '❌ NO') . PHP_EOL;
echo PHP_EOL;

// Check access requirements
echo "Access Requirements Check:" . PHP_EOL;
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;

$hasCorrectRole = in_array($user->role, ['pengguna_properti', 'owner']);
echo "1. Role (need: pengguna_properti OR owner): " . ($hasCorrectRole ? '✅ PASS' : '❌ FAIL') . PHP_EOL;

$isVerified = $user->email_verified_at !== null;
echo "2. Email Verified: " . ($isVerified ? '✅ PASS' : '❌ FAIL') . PHP_EOL;

$hasProperty = $user->property !== null;
echo "3. Has Property: " . ($hasProperty ? '✅ PASS' : '❌ FAIL') . PHP_EOL;

echo PHP_EOL;

if ($hasCorrectRole && $isVerified && $hasProperty) {
    echo "✅ USER CAN ACCESS CALENDAR!" . PHP_EOL;
    echo PHP_EOL;
    echo "Calendar URL: http://127.0.0.1:8000/property/calendar" . PHP_EOL;
    echo "API URL: http://127.0.0.1:8000/property/calendar-data" . PHP_EOL;
} else {
    echo "❌ USER CANNOT ACCESS CALENDAR!" . PHP_EOL;
    echo PHP_EOL;
    echo "Fix required:" . PHP_EOL;
    if (!$hasCorrectRole) {
        echo "  - Change role to 'pengguna_properti' or 'owner'" . PHP_EOL;
        echo "    UPDATE users SET role='pengguna_properti' WHERE id={$user->id};" . PHP_EOL;
    }
    if (!$isVerified) {
        echo "  - Verify email" . PHP_EOL;
        echo "    UPDATE users SET email_verified_at=NOW() WHERE id={$user->id};" . PHP_EOL;
    }
    if (!$hasProperty) {
        echo "  - Assign property" . PHP_EOL;
        echo "    UPDATE users SET property_id=13 WHERE id={$user->id}; -- Change 13 to correct property ID" . PHP_EOL;
    }
}

echo PHP_EOL;
