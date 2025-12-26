<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\DailyOccupancy;
use App\Http\Traits\CalculatesBarPrices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomPricingController extends Controller
{
    use CalculatesBarPrices;

    /**
     * Get room types and current pricing for a property
     *
     * @param Request $request
     * @param Property $property
     * @return JsonResponse
     */
    public function index(Request $request, Property $property): JsonResponse
    {
        // Verify that the API key belongs to this property
        $apiKey = $request->input('authenticated_api_key');

        // PERBAIKAN: Tambahkan (int) agar perbandingan String vs Integer tetap dianggap sama
        if ((int) $apiKey->property_id !== (int) $property->id) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'This API key is not authorized for this property',
            ], 403);
        }

        // Get current occupancy for today
        $occupancyToday = DailyOccupancy::where('property_id', $property->id)
            ->where('date', today()->toDateString())
            ->first();

        $occupiedRooms = $occupancyToday ? $occupancyToday->occupied_rooms : 0;

        // Calculate active BAR level
        $activeBarLevel = $this->getActiveBarLevel($occupiedRooms, $property);
        $activeBarName = $this->getActiveBarName($activeBarLevel);

        // Get all active room types for this property
        $roomTypes = $property->roomTypes()
            ->with('pricingRule')
            ->orderBy('name')
            ->get()
            ->map(function ($roomType) use ($activeBarLevel) {
                $activePrice = $this->calculateActiveBarPrice($roomType, $activeBarLevel);

                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'type' => $roomType->type, // 'hotel' or 'mice'
                    'bottom_rate' => (float) $roomType->bottom_rate,
                    'current_price' => (float) $activePrice,
                    'pricing_rule' => $roomType->pricingRule ? [
                        'publish_rate' => (float) $roomType->pricingRule->publish_rate,
                        'starting_bar' => $roomType->pricingRule->starting_bar,
                        'percentage_increase' => (float) $roomType->pricingRule->percentage_increase,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'property' => [
                    'id' => $property->id,
                    'name' => $property->name,
                    'address' => $property->address,
                    'phone_number' => $property->phone_number,
                    'total_rooms' => $property->total_rooms,
                ],
                'occupancy' => [
                    'date' => today()->toDateString(),
                    'occupied_rooms' => $occupiedRooms,
                    'available_rooms' => max(0, $property->total_rooms - $occupiedRooms),
                    'occupancy_percentage' => $property->total_rooms > 0
                        ? round(($occupiedRooms / $property->total_rooms) * 100, 2)
                        : 0,
                ],
                'pricing' => [
                    'active_bar_level' => $activeBarLevel,
                    'active_bar_name' => $activeBarName,
                    'bar_thresholds' => [
                        'bar_1' => $property->bar_1,
                        'bar_2' => $property->bar_2,
                        'bar_3' => $property->bar_3,
                        'bar_4' => $property->bar_4,
                        'bar_5' => $property->bar_5,
                    ],
                ],
                'room_types' => $roomTypes,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get pricing for a specific room type
     *
     * @param Request $request
     * @param Property $property
     * @param int $roomTypeId
     * @return JsonResponse
     */
    public function show(Request $request, Property $property, int $roomTypeId): JsonResponse
    {
        // Verify that the API key belongs to this property
        $apiKey = $request->input('authenticated_api_key');

        // PERBAIKAN: Tambahkan (int) di sini juga
        if ((int) $apiKey->property_id !== (int) $property->id) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'This API key is not authorized for this property',
            ], 403);
        }

        // Find the room type
        $roomType = $property->roomTypes()
            ->with('pricingRule')
            ->find($roomTypeId);

        if (!$roomType) {
            return response()->json([
                'error' => 'Not found',
                'message' => 'Room type not found for this property',
            ], 404);
        }

        // Get current occupancy for today
        $occupancyToday = DailyOccupancy::where('property_id', $property->id)
            ->where('date', today()->toDateString())
            ->first();

        $occupiedRooms = $occupancyToday ? $occupancyToday->occupied_rooms : 0;

        // Calculate active BAR level and price
        $activeBarLevel = $this->getActiveBarLevel($occupiedRooms, $property);
        $activePrice = $this->calculateActiveBarPrice($roomType, $activeBarLevel);

        return response()->json([
            'success' => true,
            'data' => [
                'room_type' => [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'type' => $roomType->type,
                    'bottom_rate' => (float) $roomType->bottom_rate,
                    'current_price' => (float) $activePrice,
                    'pricing_rule' => $roomType->pricingRule ? [
                        'publish_rate' => (float) $roomType->pricingRule->publish_rate,
                        'starting_bar' => $roomType->pricingRule->starting_bar,
                        'percentage_increase' => (float) $roomType->pricingRule->percentage_increase,
                    ] : null,
                ],
                'occupancy' => [
                    'date' => today()->toDateString(),
                    'occupied_rooms' => $occupiedRooms,
                    'active_bar_level' => $activeBarLevel,
                ],
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}