<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\FnbMenuItem;
use App\Models\FnbOrder;
use App\Models\FnbOrderItem;
use App\Models\HotelRoom;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestaurantController extends Controller
{
    /**
     * Display restaurant POS dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Get today's statistics
        $todayOrders = FnbOrder::where('property_id', $property->id)
            ->today()
            ->count();

        $todayRevenue = FnbOrder::where('property_id', $property->id)
            ->today()
            ->where('payment_status', 'paid')
            ->sum('subtotal');

        $pendingOrders = FnbOrder::where('property_id', $property->id)
            ->pending()
            ->with(['items.menuItem', 'hotelRoom', 'guest'])
            ->latest()
            ->get();

        return view('restaurant.index', compact(
            'property',
            'todayOrders',
            'todayRevenue',
            'pendingOrders'
        ));
    }

    /**
     * Show POS (Point of Sale) interface.
     */
    public function pos()
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        $menuItems = FnbMenuItem::where('property_id', $property->id)
            ->available()
            ->get()
            ->groupBy('category');

        $occupiedRooms = HotelRoom::where('property_id', $property->id)
            ->occupied()
            ->with('currentStay.guest')
            ->get();

        return view('restaurant.pos', compact('property', 'menuItems', 'occupiedRooms'));
    }

    /**
     * Create new order.
     */
    public function createOrder(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $validated = $request->validate([
            'order_type' => 'required|in:dine_in,room_service,takeaway,delivery',
            'table_number' => 'nullable|string',
            'hotel_room_id' => 'nullable|exists:hotel_rooms,id',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:fnb_menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get guest_id and room_stay_id if room service
            $guestId = null;
            $roomStayId = null;

            if ($validated['order_type'] === 'room_service' && $validated['hotel_room_id']) {
                $room = HotelRoom::with('currentStay')->findOrFail($validated['hotel_room_id']);
                if ($room->currentStay) {
                    $roomStayId = $room->currentStay->id;
                    $guestId = $room->currentStay->guest_id;
                }
            }

            // Create order
            $order = FnbOrder::create([
                'property_id' => $property->id,
                'order_type' => $validated['order_type'],
                'guest_id' => $guestId,
                'room_stay_id' => $roomStayId,
                'hotel_room_id' => $validated['hotel_room_id'] ?? null,
                'table_number' => $validated['table_number'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'special_instructions' => $validated['special_instructions'] ?? null,
                'order_time' => now(),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'taken_by' => auth()->id(),
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                $menuItem = FnbMenuItem::findOrFail($item['menu_item_id']);

                FnbOrderItem::create([
                    'fnb_order_id' => $order->id,
                    'fnb_menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'special_instructions' => $item['special_instructions'] ?? null,
                    'status' => 'pending',
                ]);
            }

            // Recalculate order totals
            $order->recalculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'order' => $order->load('items.menuItem'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(FnbOrder $order, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,delivered,completed,cancelled',
        ]);

        $order->update([
            'status' => $validated['status'],
            'status_changed_at' => now(),
        ]);

        // If completed, trigger observer to update daily income
        if ($validated['status'] === 'completed') {
            $order->update(['payment_status' => 'paid']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status order berhasil diupdate',
        ]);
    }

    /**
     * Process payment for order.
     */
    public function processPayment(FnbOrder $order, Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,ewallet,transfer,room_charge',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $order->update([
                'payment_method' => $validated['payment_method'],
                'paid_amount' => $validated['paid_amount'],
                'paid_at' => now(),
                'payment_status' => $validated['paid_amount'] >= $order->total_amount ? 'paid' : 'partial',
                'status' => 'completed',
                'status_changed_at' => now(),
            ]);

            // If room charge, add to room stay bill
            if ($validated['payment_method'] === 'room_charge' && $order->room_stay_id) {
                // Room charges will be settled at checkout
                $order->update(['payment_status' => 'charge_to_room']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menu management - list all menu items.
     */
    public function menuIndex(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $query = FnbMenuItem::where('property_id', $property->id);

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $menuItems = $query->orderBy('category')
            ->orderBy('name')
            ->paginate(20);

        return view('restaurant.menu.index', compact('property', 'menuItems'));
    }

    /**
     * Show form to create new menu item.
     */
    public function menuCreate()
    {
        $user = auth()->user();
        $property = $user->property;

        return view('restaurant.menu.create', compact('property'));
    }

    /**
     * Store new menu item.
     */
    public function menuStore(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:breakfast,lunch,dinner,appetizer,main_course,dessert,beverage,snack,alcohol',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_available' => 'nullable|boolean',
        ]);

        // Handle checkbox - if not checked, set to false
        $validated['is_available'] = $request->has('is_available') ? true : false;

        FnbMenuItem::create(array_merge($validated, [
            'property_id' => $property->id,
        ]));

        return redirect()->route('restaurant.menu.index')
            ->with('success', 'Menu item berhasil ditambahkan');
    }

    /**
     * Show form to edit menu item.
     */
    public function menuEdit(FnbMenuItem $menuItem)
    {
        $user = auth()->user();
        $property = $user->property;

        // Ensure menu item belongs to user's property
        if ($menuItem->property_id !== $property->id) {
            abort(403);
        }

        return view('restaurant.menu.edit', compact('property', 'menuItem'));
    }

    /**
     * Update menu item.
     */
    public function menuUpdate(Request $request, FnbMenuItem $menuItem)
    {
        $user = auth()->user();
        $property = $user->property;

        // Ensure menu item belongs to user's property
        if ($menuItem->property_id !== $property->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:breakfast,lunch,dinner,appetizer,main_course,dessert,beverage,snack,alcohol',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_available' => 'nullable|boolean',
        ]);

        // Handle checkbox - if not checked, set to false
        $validated['is_available'] = $request->has('is_available') ? true : false;

        $menuItem->update($validated);

        return redirect()->route('restaurant.menu.index')
            ->with('success', 'Menu item berhasil diupdate');
    }

    /**
     * Delete menu item.
     */
    public function menuDestroy(FnbMenuItem $menuItem)
    {
        $user = auth()->user();
        $property = $user->property;

        // Ensure menu item belongs to user's property
        if ($menuItem->property_id !== $property->id) {
            abort(403);
        }

        $menuItem->delete();

        return redirect()->route('restaurant.menu.index')
            ->with('success', 'Menu item berhasil dihapus');
    }

    /**
     * Toggle menu item availability.
     */
    public function menuToggleAvailability(FnbMenuItem $menuItem)
    {
        $user = auth()->user();
        $property = $user->property;

        // Ensure menu item belongs to user's property
        if ($menuItem->property_id !== $property->id) {
            abort(403);
        }

        $menuItem->update([
            'is_available' => !$menuItem->is_available
        ]);

        return response()->json([
            'success' => true,
            'is_available' => $menuItem->is_available,
        ]);
    }
}
