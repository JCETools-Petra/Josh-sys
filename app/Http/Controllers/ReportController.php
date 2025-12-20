<?php

namespace App\Http\Controllers;

use App\Models\RoomStay;
use App\Models\FnbOrder;
use App\Models\DailyOccupancy;
use App\Models\HotelRoom;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Show reports dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $property = $user->property;

        return view('reports.index', compact('property'));
    }

    /**
     * Generate daily sales report.
     */
    public function dailySales(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $date = $request->get('date', today()->toDateString());
        $targetDate = Carbon::parse($date);

        // Room Revenue
        $roomRevenue = RoomStay::where('property_id', $property->id)
            ->whereDate('check_in_date', '<=', $targetDate)
            ->whereDate('check_out_date', '>=', $targetDate)
            ->sum('total_room_charge');

        // F&B Revenue
        $fnbRevenue = FnbOrder::where('property_id', $property->id)
            ->whereDate('order_time', $targetDate)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        // Breakdown by payment method
        $paymentBreakdown = FnbOrder::where('property_id', $property->id)
            ->whereDate('order_time', $targetDate)
            ->where('payment_status', 'paid')
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        // Check-ins and Check-outs
        $checkIns = RoomStay::where('property_id', $property->id)
            ->whereDate('actual_check_in', $targetDate)
            ->count();

        $checkOuts = RoomStay::where('property_id', $property->id)
            ->whereDate('actual_check_out', $targetDate)
            ->count();

        // Occupancy
        $totalRooms = HotelRoom::where('property_id', $property->id)->count();
        $occupiedRooms = RoomStay::where('property_id', $property->id)
            ->whereDate('check_in_date', '<=', $targetDate)
            ->whereDate('check_out_date', '>', $targetDate)
            ->where('status', 'checked_in')
            ->count();

        $occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;

        return view('reports.daily-sales', compact(
            'property',
            'targetDate',
            'roomRevenue',
            'fnbRevenue',
            'paymentBreakdown',
            'checkIns',
            'checkOuts',
            'totalRooms',
            'occupiedRooms',
            'occupancyRate'
        ));
    }

    /**
     * Generate occupancy report.
     */
    public function occupancy(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $startDate = $request->get('start_date', today()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', today()->toDateString());

        $occupancyData = DailyOccupancy::where('property_id', $property->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $totalRooms = HotelRoom::where('property_id', $property->id)->count();

        return view('reports.occupancy', compact(
            'property',
            'occupancyData',
            'totalRooms',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Night audit report.
     */
    public function nightAudit(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $date = $request->get('date', today()->toDateString());
        $targetDate = Carbon::parse($date);

        // Room Statistics
        $totalRooms = HotelRoom::where('property_id', $property->id)->count();
        $occupiedRooms = RoomStay::where('property_id', $property->id)
            ->whereDate('check_in_date', '<=', $targetDate)
            ->whereDate('check_out_date', '>', $targetDate)
            ->where('status', 'checked_in')
            ->with(['guest', 'hotelRoom'])
            ->get();

        // Today's Activities
        $arrivals = RoomStay::where('property_id', $property->id)
            ->whereDate('actual_check_in', $targetDate)
            ->with(['guest', 'hotelRoom'])
            ->get();

        $departures = RoomStay::where('property_id', $property->id)
            ->whereDate('actual_check_out', $targetDate)
            ->with(['guest', 'hotelRoom'])
            ->get();

        // Expected for tomorrow
        $expectedArrivals = RoomStay::where('property_id', $property->id)
            ->whereDate('check_in_date', $targetDate->copy()->addDay())
            ->where('status', 'reserved')
            ->with(['guest', 'hotelRoom'])
            ->get();

        $expectedDepartures = RoomStay::where('property_id', $property->id)
            ->whereDate('check_out_date', $targetDate->copy()->addDay())
            ->where('status', 'checked_in')
            ->with(['guest', 'hotelRoom'])
            ->get();

        // Revenue
        $roomRevenue = RoomStay::where('property_id', $property->id)
            ->whereDate('check_in_date', '<=', $targetDate)
            ->whereDate('check_out_date', '>=', $targetDate)
            ->sum('total_room_charge');

        $fnbRevenue = FnbOrder::where('property_id', $property->id)
            ->whereDate('order_time', $targetDate)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $totalRevenue = $roomRevenue + $fnbRevenue;

        // Occupancy Rate
        $occupancyRate = $totalRooms > 0 ? ($occupiedRooms->count() / $totalRooms) * 100 : 0;

        // ADR (Average Daily Rate)
        $adr = $occupiedRooms->count() > 0 ? $roomRevenue / $occupiedRooms->count() : 0;

        // RevPAR (Revenue Per Available Room)
        $revpar = $totalRooms > 0 ? $roomRevenue / $totalRooms : 0;

        return view('reports.night-audit', compact(
            'property',
            'targetDate',
            'totalRooms',
            'occupiedRooms',
            'arrivals',
            'departures',
            'expectedArrivals',
            'expectedDepartures',
            'roomRevenue',
            'fnbRevenue',
            'totalRevenue',
            'occupancyRate',
            'adr',
            'revpar'
        ));
    }

    /**
     * F&B Sales Report.
     */
    public function fnbSales(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $startDate = $request->get('start_date', today()->toDateString());
        $endDate = $request->get('end_date', today()->toDateString());

        // Best Selling Items
        $bestSellers = \DB::table('fnb_order_items')
            ->join('fnb_orders', 'fnb_order_items.fnb_order_id', '=', 'fnb_orders.id')
            ->join('fnb_menu_items', 'fnb_order_items.fnb_menu_item_id', '=', 'fnb_menu_items.id')
            ->where('fnb_orders.property_id', $property->id)
            ->whereBetween('fnb_orders.order_time', [$startDate, $endDate])
            ->selectRaw('fnb_menu_items.name, SUM(fnb_order_items.quantity) as total_qty, SUM(fnb_order_items.subtotal) as total_sales')
            ->groupBy('fnb_menu_items.id', 'fnb_menu_items.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        // Sales by Category
        $categoryBreakdown = \DB::table('fnb_order_items')
            ->join('fnb_orders', 'fnb_order_items.fnb_order_id', '=', 'fnb_orders.id')
            ->join('fnb_menu_items', 'fnb_order_items.fnb_menu_item_id', '=', 'fnb_menu_items.id')
            ->where('fnb_orders.property_id', $property->id)
            ->whereBetween('fnb_orders.order_time', [$startDate, $endDate])
            ->selectRaw('fnb_menu_items.category, SUM(fnb_order_items.subtotal) as total_sales')
            ->groupBy('fnb_menu_items.category')
            ->get();

        // Total Revenue
        $totalRevenue = FnbOrder::where('property_id', $property->id)
            ->whereBetween('order_time', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        return view('reports.fnb-sales', compact(
            'property',
            'bestSellers',
            'categoryBreakdown',
            'totalRevenue',
            'startDate',
            'endDate'
        ));
    }
}
