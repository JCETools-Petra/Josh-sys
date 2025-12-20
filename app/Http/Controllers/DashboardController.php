<?php

namespace App\Http\Controllers;

use App\Models\RoomStay;
use App\Models\HotelRoom;
use App\Models\FnbOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        // Date range for analytics (default: last 7 days)
        $days = $request->get('days', 7);
        $endDate = today();
        $startDate = today()->subDays($days - 1);

        // Today's Stats
        $todayCheckIns = RoomStay::where('property_id', $property->id)
            ->whereDate('actual_check_in', today())
            ->count();

        $todayCheckOuts = RoomStay::where('property_id', $property->id)
            ->whereDate('actual_check_out', today())
            ->count();

        $currentlyOccupied = RoomStay::where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->count();

        $totalRooms = HotelRoom::where('property_id', $property->id)->count();
        $occupancyRate = $totalRooms > 0 ? ($currentlyOccupied / $totalRooms) * 100 : 0;

        // Revenue stats for selected period
        $roomRevenue = RoomStay::where('property_id', $property->id)
            ->whereBetween('actual_check_in', [$startDate, $endDate])
            ->sum('total_room_charge');

        $fnbRevenue = FnbOrder::where('property_id', $property->id)
            ->whereBetween('order_time', [$startDate, $endDate->copy()->endOfDay()])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $totalRevenue = $roomRevenue + $fnbRevenue;

        // Daily revenue trend for chart
        $dailyRevenue = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');

            $dayRoomRevenue = RoomStay::where('property_id', $property->id)
                ->whereDate('actual_check_in', $currentDate)
                ->sum('total_room_charge');

            $dayFnbRevenue = FnbOrder::where('property_id', $property->id)
                ->whereDate('order_time', $currentDate)
                ->where('payment_status', 'paid')
                ->sum('total_amount');

            $dailyRevenue[] = [
                'date' => $currentDate->format('Y-m-d'),
                'room_revenue' => $dayRoomRevenue,
                'fnb_revenue' => $dayFnbRevenue,
                'total_revenue' => $dayRoomRevenue + $dayFnbRevenue,
            ];

            $currentDate->addDay();
        }

        // Occupancy trend for chart
        $occupancyTrend = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $occupied = RoomStay::where('property_id', $property->id)
                ->whereDate('check_in_date', '<=', $currentDate)
                ->whereDate('check_out_date', '>', $currentDate)
                ->where('status', 'checked_in')
                ->count();

            $rate = $totalRooms > 0 ? ($occupied / $totalRooms) * 100 : 0;

            $occupancyTrend[] = [
                'date' => $currentDate->format('Y-m-d'),
                'occupied' => $occupied,
                'rate' => round($rate, 1),
            ];

            $currentDate->addDay();
        }

        // Room type statistics
        $roomTypeStats = HotelRoom::where('property_id', $property->id)
            ->with(['roomType', 'currentStay'])
            ->get()
            ->groupBy('room_type_id')
            ->map(function ($rooms) {
                $occupied = $rooms->filter(function ($room) {
                    return $room->currentStay !== null;
                })->count();

                $total = $rooms->count();
                $rate = $total > 0 ? ($occupied / $total) * 100 : 0;

                return [
                    'name' => $rooms->first()->roomType->name ?? 'Unknown',
                    'total' => $total,
                    'occupied' => $occupied,
                    'available' => $total - $occupied,
                    'rate' => round($rate, 1),
                ];
            })->values();

        // Top F&B items (last 30 days)
        $topFnbItems = FnbOrder::where('property_id', $property->id)
            ->whereBetween('order_time', [today()->subDays(30), today()->endOfDay()])
            ->with('items.menuItem')
            ->get()
            ->flatMap(function ($order) {
                return $order->items;
            })
            ->groupBy('menu_item_id')
            ->map(function ($items) {
                return [
                    'name' => $items->first()->menuItem->name ?? 'Unknown',
                    'quantity' => $items->sum('quantity'),
                    'revenue' => $items->sum('subtotal'),
                ];
            })
            ->sortByDesc('quantity')
            ->take(5)
            ->values();

        // Upcoming arrivals (next 3 days)
        $upcomingArrivals = RoomStay::where('property_id', $property->id)
            ->whereBetween('check_in_date', [today()->addDay(), today()->addDays(3)])
            ->where('status', 'reserved')
            ->with(['guest', 'hotelRoom'])
            ->orderBy('check_in_date')
            ->get();

        // Upcoming departures (next 3 days)
        $upcomingDepartures = RoomStay::where('property_id', $property->id)
            ->whereBetween('check_out_date', [today()->addDay(), today()->addDays(3)])
            ->where('status', 'checked_in')
            ->with(['guest', 'hotelRoom'])
            ->orderBy('check_out_date')
            ->get();

        return view('dashboard.analytics', compact(
            'property',
            'todayCheckIns',
            'todayCheckOuts',
            'currentlyOccupied',
            'totalRooms',
            'occupancyRate',
            'roomRevenue',
            'fnbRevenue',
            'totalRevenue',
            'dailyRevenue',
            'occupancyTrend',
            'roomTypeStats',
            'topFnbItems',
            'upcomingArrivals',
            'upcomingDepartures',
            'days'
        ));
    }
}
