<?php

namespace App\Http\Controllers;

use App\Events\FnbOrderCompleted;
use App\Models\ActivityLog;
use App\Models\Property;
use App\Models\FnbMenuItem;
use App\Models\FnbOrder;
use App\Models\FnbOrderItem;
use App\Models\HotelRoom;
use App\Models\Guest;
use App\Models\RoomStay;
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
            'hotel_room_id' => 'required_if:order_type,room_service|nullable|exists:hotel_rooms,id',
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

                // Verify menu item belongs to user's property
                if ($menuItem->property_id !== $property->id) {
                    throw new \Exception("Menu item {$menuItem->name} tidak tersedia di properti ini.");
                }

                // Verify menu item is available
                if (!$menuItem->is_available || !$menuItem->isCurrentlyAvailable()) {
                    throw new \Exception("Menu item {$menuItem->name} saat ini tidak tersedia.");
                }

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

            // Log activity
            $orderTypeLabel = match($order->order_type) {
                'dine_in' => 'Dine In',
                'room_service' => 'Room Service',
                'takeaway' => 'Take Away',
                'delivery' => 'Delivery',
                default => $order->order_type
            };
            $locationInfo = $order->order_type === 'room_service' && $order->hotelRoom
                ? " ke kamar {$order->hotelRoom->room_number}"
                : ($order->table_number ? " di meja {$order->table_number}" : '');

            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'action' => 'create',
                'description' => auth()->user()->name . " membuat order {$orderTypeLabel}{$locationInfo}, nomor order: {$order->order_number}, total: Rp " . number_format($order->total_amount, 0, ',', '.') . ", jumlah item: " . $order->items->count(),
                'loggable_id' => $order->id,
                'loggable_type' => FnbOrder::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

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
        // Verify order belongs to user's property
        if ($order->property_id !== auth()->user()->property_id) {
            abort(403, 'Anda tidak memiliki akses ke order ini.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,delivered,completed,cancelled',
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
            'status_changed_at' => now(),
        ]);

        // Don't auto-mark as paid when completing (payment should be processed separately)

        // Log activity
        $statusLabel = match($validated['status']) {
            'pending' => 'pending',
            'confirmed' => 'dikonfirmasi',
            'preparing' => 'sedang diproses',
            'ready' => 'siap disajikan',
            'delivered' => 'diantar',
            'completed' => 'selesai',
            'cancelled' => 'dibatalkan',
            default => $validated['status']
        };

        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $order->property_id,
            'action' => 'update',
            'description' => auth()->user()->name . " mengubah status order {$order->order_number} dari {$oldStatus} menjadi {$statusLabel}",
            'loggable_id' => $order->id,
            'loggable_type' => FnbOrder::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

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
        // Verify order belongs to user's property
        if ($order->property_id !== auth()->user()->property_id) {
            abort(403, 'Anda tidak memiliki akses ke order ini.');
        }

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
                // Check credit limit before allowing room charge
                $roomStay = $order->roomStay;

                if (!$roomStay) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Room stay not found for this order',
                    ], 400);
                }

                // Calculate total outstanding charges (unpaid room charges)
                $outstandingCharges = $this->calculateOutstandingRoomCharges($roomStay);

                // Default credit limit: Rp 5,000,000 (can be made configurable per property)
                $creditLimit = $order->property->settings['room_charge_credit_limit'] ?? 5000000;

                // Check if adding this order would exceed credit limit
                if (($outstandingCharges + $order->total_amount) > $creditLimit) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Credit limit exceeded. Outstanding charges: Rp ' . number_format($outstandingCharges, 0, ',', '.') .
                                   ', Credit limit: Rp ' . number_format($creditLimit, 0, ',', '.') .
                                   '. Please settle outstanding charges or use another payment method.',
                    ], 400);
                }

                // Room charges will be settled at checkout
                $order->update(['payment_status' => 'charge_to_room']);
            }

            // Log activity
            $paymentMethodLabel = match($validated['payment_method']) {
                'cash' => 'tunai',
                'card' => 'kartu',
                'ewallet' => 'e-wallet',
                'transfer' => 'transfer bank',
                'room_charge' => 'charge ke kamar',
                default => $validated['payment_method']
            };

            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $order->property_id,
                'action' => 'update',
                'description' => auth()->user()->name . " memproses pembayaran order {$order->order_number}, metode: {$paymentMethodLabel}, jumlah: Rp " . number_format($validated['paid_amount'], 0, ',', '.'),
                'loggable_id' => $order->id,
                'loggable_type' => FnbOrder::class,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            // Dispatch event for automatic folio update if room charge
            if ($validated['payment_method'] === 'room_charge') {
                event(new FnbOrderCompleted($order->fresh()));
            }

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
    public function menuIndex()
    {
        $user = auth()->user();
        $property = $user->property;

        $menuItems = FnbMenuItem::where('property_id', $property->id)
            ->orderBy('category')
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
            'is_available' => 'boolean',
        ]);

        $menuItem = FnbMenuItem::create(array_merge($validated, [
            'property_id' => $property->id,
        ]));

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
            'action' => 'create',
            'description' => auth()->user()->name . " menambahkan menu item '{$menuItem->name}', kategori: {$menuItem->category}, harga: Rp " . number_format($menuItem->price, 0, ',', '.'),
            'loggable_id' => $menuItem->id,
            'loggable_type' => FnbMenuItem::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

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
            'is_available' => 'boolean',
        ]);

        $oldName = $menuItem->name;
        $menuItem->update($validated);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
            'action' => 'update',
            'description' => auth()->user()->name . " mengupdate menu item '{$oldName}' menjadi '{$menuItem->name}', kategori: {$menuItem->category}, harga: Rp " . number_format($menuItem->price, 0, ',', '.'),
            'loggable_id' => $menuItem->id,
            'loggable_type' => FnbMenuItem::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

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

        $menuName = $menuItem->name;
        $menuItem->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
            'action' => 'delete',
            'description' => auth()->user()->name . " menghapus menu item '{$menuName}'",
            'loggable_id' => $menuItem->id,
            'loggable_type' => FnbMenuItem::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

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

        $oldAvailability = $menuItem->is_available;
        $menuItem->update([
            'is_available' => !$menuItem->is_available
        ]);

        // Log activity
        $statusLabel = $menuItem->is_available ? 'tersedia' : 'tidak tersedia';
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
            'action' => 'update',
            'description' => auth()->user()->name . " mengubah ketersediaan menu '{$menuItem->name}' menjadi {$statusLabel}",
            'loggable_id' => $menuItem->id,
            'loggable_type' => FnbMenuItem::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'is_available' => $menuItem->is_available,
        ]);
    }

    /**
     * Show order history with filters.
     */
    public function orderHistory(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $query = FnbOrder::where('property_id', $property->id)
            ->with(['items.menuItem', 'hotelRoom', 'guest', 'takenByUser']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('order_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('order_time', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by order type
        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->latest('order_time')->paginate(20);

        return view('restaurant.history', compact('property', 'orders'));
    }

    /**
     * Show order detail.
     */
    public function orderDetail(FnbOrder $order)
    {
        $user = auth()->user();
        $property = $user->property;

        // Ensure order belongs to user's property
        if ($order->property_id !== $property->id) {
            abort(403);
        }

        $order->load(['items.menuItem', 'hotelRoom', 'guest', 'takenByUser', 'servedByUser']);

        return view('restaurant.order-detail', compact('property', 'order'));
    }

    /**
     * Show sales report.
     */
    public function salesReport(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $startDate = $request->filled('start_date') ? $request->start_date : today()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date') ? $request->end_date : today()->toDateString();

        // ⚡ PERFORMANCE FIX: Get overall stats with single query instead of 4 separate queries
        $stats = FnbOrder::where('property_id', $property->id)
            ->whereBetween('order_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN payment_status = "paid" THEN subtotal ELSE 0 END) as total_revenue,
                SUM(CASE WHEN payment_status = "paid" THEN tax_amount ELSE 0 END) as total_tax,
                SUM(CASE WHEN payment_status = "paid" THEN service_charge ELSE 0 END) as total_service
            ')
            ->first();

        $totalOrders = $stats->total_orders ?? 0;
        $totalRevenue = $stats->total_revenue ?? 0;
        $totalTax = $stats->total_tax ?? 0;
        $totalService = $stats->total_service ?? 0;

        // Orders by type
        $ordersByType = FnbOrder::where('property_id', $property->id)
            ->whereBetween('order_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('order_type', DB::raw('count(*) as count'), DB::raw('sum(subtotal) as revenue'))
            ->groupBy('order_type')
            ->get();

        // Top selling items
        $topItems = FnbOrderItem::whereHas('order', function($q) use ($property, $startDate, $endDate) {
                $q->where('property_id', $property->id)
                  ->whereBetween('order_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                  ->where('payment_status', 'paid');
            })
            ->with('menuItem')
            ->select('fnb_menu_item_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(quantity * unit_price) as total_revenue'))
            ->groupBy('fnb_menu_item_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Daily revenue trend
        $dailyRevenue = FnbOrder::where('property_id', $property->id)
            ->whereBetween('order_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid')
            ->select(DB::raw('DATE(order_time) as date'), DB::raw('count(*) as orders'), DB::raw('sum(subtotal) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'view',
            'description' => $user->name . " melihat laporan penjualan Restaurant periode {$startDate} s/d {$endDate}, total order: {$totalOrders}, revenue: Rp " . number_format($totalRevenue, 0, ',', '.'),
            'loggable_id' => $property->id,
            'loggable_type' => \App\Models\Property::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('restaurant.sales-report', compact(
            'property',
            'startDate',
            'endDate',
            'totalOrders',
            'totalRevenue',
            'totalTax',
            'totalService',
            'ordersByType',
            'topItems',
            'dailyRevenue'
        ));
    }

    /**
     * Calculate outstanding room charges for a room stay.
     * This includes unpaid F&B orders and other charges to room.
     */
    private function calculateOutstandingRoomCharges(RoomStay $roomStay): float
    {
        // Get all unpaid F&B orders charged to room
        $fnbCharges = FnbOrder::where('room_stay_id', $roomStay->id)
            ->where('payment_status', 'charge_to_room')
            ->sum('total_amount');

        // Get room rate charges (if not paid yet)
        $roomCharges = 0;
        if ($roomStay->payment_status !== 'paid' && $roomStay->payment_status !== 'completed') {
            $roomCharges = $roomStay->total_amount ?? 0;
        }

        // Get any other additional charges that might be added to the folio
        // (This can be expanded based on your folio system)

        return $fnbCharges + $roomCharges;
    }

    /**
     * Search guest by room number (API endpoint for POS).
     */
    public function guestByRoom($roomNumber)
    {
        $user = auth()->user();
        $property = $user->property;

        // Find the room
        $room = HotelRoom::where('property_id', $property->id)
            ->where('room_number', $roomNumber)
            ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => "Kamar {$roomNumber} tidak ditemukan",
            ], 404);
        }

        // Find active room stay for this room
        $roomStay = $room->roomStays()
            ->where('status', 'checked_in')
            ->with(['guest', 'hotelRoom'])
            ->first();

        if (!$roomStay) {
            return response()->json([
                'success' => false,
                'message' => "Kamar {$roomNumber} sedang tidak ditempati",
            ], 404);
        }

        // Calculate current outstanding charges
        $outstandingCharges = $this->calculateOutstandingRoomCharges($roomStay);
        $creditLimit = $property->settings['room_charge_credit_limit'] ?? 5000000;
        $availableCredit = $creditLimit - $outstandingCharges;

        return response()->json([
            'success' => true,
            'data' => [
                'room_stay_id' => $roomStay->id,
                'room_number' => $room->room_number,
                'guest' => [
                    'id' => $roomStay->guest->id,
                    'full_name' => $roomStay->guest->full_name,
                    'phone' => $roomStay->guest->phone,
                    'email' => $roomStay->guest->email,
                ],
                'folio' => [
                    'outstanding_charges' => $outstandingCharges,
                    'credit_limit' => $creditLimit,
                    'available_credit' => max(0, $availableCredit),
                    'formatted_outstanding' => 'Rp ' . number_format($outstandingCharges, 0, ',', '.'),
                    'formatted_credit_limit' => 'Rp ' . number_format($creditLimit, 0, ',', '.'),
                    'formatted_available' => 'Rp ' . number_format(max(0, $availableCredit), 0, ',', '.'),
                ],
                'check_in_date' => $roomStay->actual_check_in?->format('d M Y'),
                'expected_check_out' => $roomStay->expected_check_out?->format('d M Y'),
            ],
        ]);
    }
}
