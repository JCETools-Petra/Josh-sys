<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomPricingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API endpoints with API key authentication
Route::middleware(['api.key'])->group(function () {
    // Get all room types and pricing for a property
    Route::get('/properties/{property}/room-pricing', [RoomPricingController::class, 'index'])
        ->name('api.properties.room-pricing.index');

    // Get pricing for a specific room type
    Route::get('/properties/{property}/room-pricing/{roomTypeId}', [RoomPricingController::class, 'show'])
        ->name('api.properties.room-pricing.show');
});
