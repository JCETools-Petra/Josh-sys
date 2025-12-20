<?php
// File: check-api-key.php
// Jalankan dengan: php check-api-key.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKeyString = $argv[1] ?? null;

if (!$apiKeyString) {
    echo "Usage: php check-api-key.php <api_key>\n";
    exit(1);
}

$apiKey = \App\Models\ApiKey::where('key', $apiKeyString)->first();

if (!$apiKey) {
    echo "❌ API Key not found!\n";
    exit(1);
}

echo "✅ API Key Found!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Name:           {$apiKey->name}\n";
echo "Property ID:    {$apiKey->property_id}\n";
echo "Property Name:  {$apiKey->property->name}\n";
echo "Status:         " . ($apiKey->is_active ? '🟢 Active' : '🔴 Inactive') . "\n";
echo "Created:        {$apiKey->created_at->format('Y-m-d H:i:s')}\n";
echo "Last Used:      " . ($apiKey->last_used_at ? $apiKey->last_used_at->format('Y-m-d H:i:s') : 'Never') . "\n";
echo "Allowed Origins: " . ($apiKey->allowed_origins ?: 'All domains') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n🔗 Correct API endpoint:\n";
echo "https://hoteliermarket.my.id/api/properties/{$apiKey->property_id}/room-pricing\n";
