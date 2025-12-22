<?php

namespace App\Http\Controllers;

use App\Models\FnbOrder;
use Illuminate\Http\Request;

class KitchenDisplayController extends Controller
{
    /**
     * Display Kitchen Display System.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        // Get orders that are in kitchen workflow
        $newOrders = FnbOrder::where('property_id', $property->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['items.menuItem', 'hotelRoom'])
            ->orderBy('order_time', 'asc')
            ->get();

        $preparingOrders = FnbOrder::where('property_id', $property->id)
            ->where('status', 'preparing')
            ->with(['items.menuItem', 'hotelRoom'])
            ->orderBy('order_time', 'asc')
            ->get();

        $readyOrders = FnbOrder::where('property_id', $property->id)
            ->where('status', 'ready')
            ->with(['items.menuItem', 'hotelRoom'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('kitchen.display', compact('newOrders', 'preparingOrders', 'readyOrders'));
    }

    /**
     * Get orders data for AJAX refresh.
     */
    public function getOrders(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $newOrders = FnbOrder::where('property_id', $property->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['items.menuItem', 'hotelRoom'])
            ->orderBy('order_time', 'asc')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                    'room_number' => $order->hotelRoom->room_number ?? null,
                    'table_number' => $order->table_number,
                    'order_time' => $order->order_time->format('H:i'),
                    'waiting_time' => $order->order_time->diffForHumans(),
                    'items' => $order->items->map(function($item) {
                        return [
                            'name' => $item->menuItem->name,
                            'quantity' => $item->quantity,
                            'special_instructions' => $item->special_instructions,
                        ];
                    }),
                    'special_instructions' => $order->special_instructions,
                    'status' => $order->status,
                ];
            });

        $preparingOrders = FnbOrder::where('property_id', $property->id)
            ->where('status', 'preparing')
            ->with(['items.menuItem', 'hotelRoom'])
            ->orderBy('order_time', 'asc')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                    'room_number' => $order->hotelRoom->room_number ?? null,
                    'table_number' => $order->table_number,
                    'order_time' => $order->order_time->format('H:i'),
                    'waiting_time' => $order->order_time->diffForHumans(),
                    'items' => $order->items->map(function($item) {
                        return [
                            'name' => $item->menuItem->name,
                            'quantity' => $item->quantity,
                            'special_instructions' => $item->special_instructions,
                        ];
                    }),
                    'special_instructions' => $order->special_instructions,
                    'status' => $order->status,
                ];
            });

        $readyOrders = FnbOrder::where('property_id', $property->id)
            ->where('status', 'ready')
            ->with(['items.menuItem', 'hotelRoom'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                    'room_number' => $order->hotelRoom->room_number ?? null,
                    'table_number' => $order->table_number,
                    'order_time' => $order->order_time->format('H:i'),
                    'items' => $order->items->map(function($item) {
                        return [
                            'name' => $item->menuItem->name,
                            'quantity' => $item->quantity,
                        ];
                    }),
                ];
            });

        return response()->json([
            'newOrders' => $newOrders,
            'preparingOrders' => $preparingOrders,
            'readyOrders' => $readyOrders,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }
}
