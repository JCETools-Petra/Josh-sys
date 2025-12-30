<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Property;
use App\Models\LostAndFound;
use App\Models\Guest;
use App\Models\HotelRoom;
use Illuminate\Http\Request;

class LostAndFoundController extends Controller
{
    /**
     * Display lost and found items.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $query = LostAndFound::where('property_id', $property->id)
            ->with(['hotelRoom', 'foundBy', 'guest', 'claimedByGuest', 'releasedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'LIKE', "%{$search}%")
                  ->orWhere('item_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderBy('date_found', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'stored' => LostAndFound::where('property_id', $property->id)->stored()->count(),
            'claimed' => LostAndFound::where('property_id', $property->id)->claimed()->count(),
            'ready_disposal' => LostAndFound::where('property_id', $property->id)->readyForDisposal()->count(),
        ];

        return view('housekeeping.lost-found.index', compact('items', 'stats', 'property'));
    }

    /**
     * Show form to create new item.
     */
    public function create()
    {
        $user = auth()->user();
        $property = $user->property;

        $rooms = $property->hotelRooms()
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        return view('housekeeping.lost-found.create', compact('property', 'rooms'));
    }

    /**
     * Store new item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:electronics,clothing,documents,jewelry,accessories,others',
            'description' => 'required|string',
            'color' => 'nullable|string|max:50',
            'brand' => 'nullable|string|max:100',
            'hotel_room_id' => 'nullable|exists:hotel_rooms,id',
            'location_found' => 'nullable|string|max:255',
            'date_found' => 'required|date',
            'storage_location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();
        $property = $user->property;

        // Get guest info if found in room
        $guestId = null;
        $roomStayId = null;
        if ($validated['hotel_room_id']) {
            $room = HotelRoom::with('currentStay')->find($validated['hotel_room_id']);
            if ($room && $room->currentStay) {
                $guestId = $room->currentStay->guest_id;
                $roomStayId = $room->currentStay->id;
            }
        }

        $item = LostAndFound::create([
            'property_id' => $property->id,
            'item_name' => $validated['item_name'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'color' => $validated['color'],
            'brand' => $validated['brand'],
            'hotel_room_id' => $validated['hotel_room_id'],
            'location_found' => $validated['location_found'] ?? ($validated['hotel_room_id'] ? "Room " . HotelRoom::find($validated['hotel_room_id'])->room_number : null),
            'date_found' => $validated['date_found'],
            'found_by' => $user->id,
            'guest_id' => $guestId,
            'room_stay_id' => $roomStayId,
            'status' => 'stored',
            'storage_location' => $validated['storage_location'],
            'disposal_date' => now()->addDays(90), // 90 days from now
            'notes' => $validated['notes'],
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'create',
            'description' => $user->name . " mencatat barang temuan: {$item->item_name}, lokasi: {$item->location_found}, nomor: {$item->item_number}",
            'loggable_id' => $item->id,
            'loggable_type' => LostAndFound::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('housekeeping.lost-found.index')
            ->with('success', "Barang temuan berhasil dicatat. Nomor: {$item->item_number}");
    }

    /**
     * Show item detail.
     */
    public function show(LostAndFound $lostAndFound)
    {
        $lostAndFound->load(['hotelRoom', 'foundBy', 'guest', 'roomStay', 'claimedByGuest', 'releasedBy', 'property']);

        return view('housekeeping.lost-found.show', compact('lostAndFound'));
    }

    /**
     * Show claim form.
     */
    public function showClaimForm(LostAndFound $lostAndFound)
    {
        if ($lostAndFound->status !== 'stored') {
            return redirect()->back()
                ->with('error', 'Barang ini tidak dapat diklaim');
        }

        // Search guests for autocomplete
        $property = $lostAndFound->property;
        $guests = Guest::whereHas('roomStays', function($q) use ($property) {
            $q->where('property_id', $property->id);
        })->limit(100)->get();

        return view('housekeeping.lost-found.claim', compact('lostAndFound', 'guests'));
    }

    /**
     * Process claim.
     */
    public function claim(Request $request, LostAndFound $lostAndFound)
    {
        $validated = $request->validate([
            'claimed_by_guest' => 'nullable|exists:guests,id',
            'claimed_by_name' => 'required_without:claimed_by_guest|string|max:255',
            'claimed_by_phone' => 'required_without:claimed_by_guest|string|max:20',
            'claim_notes' => 'nullable|string',
        ]);

        if ($lostAndFound->status !== 'stored') {
            return redirect()->back()
                ->with('error', 'Barang ini tidak dapat diklaim');
        }

        $user = auth()->user();

        $lostAndFound->update([
            'status' => 'claimed',
            'claimed_at' => now(),
            'claimed_by_guest' => $validated['claimed_by_guest'],
            'claimed_by_name' => $validated['claimed_by_name'] ?? null,
            'claimed_by_phone' => $validated['claimed_by_phone'] ?? null,
            'claim_notes' => $validated['claim_notes'],
            'released_by' => $user->id,
        ]);

        $claimantName = $validated['claimed_by_guest']
            ? Guest::find($validated['claimed_by_guest'])->full_name
            : $validated['claimed_by_name'];

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $lostAndFound->property_id,
            'action' => 'update',
            'description' => $user->name . " memproses klaim barang '{$lostAndFound->item_name}' ({$lostAndFound->item_number}) oleh {$claimantName}",
            'loggable_id' => $lostAndFound->id,
            'loggable_type' => LostAndFound::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('housekeeping.lost-found.index')
            ->with('success', "Barang berhasil diklaim oleh {$claimantName}");
    }

    /**
     * Mark item for disposal.
     */
    public function dispose(LostAndFound $lostAndFound)
    {
        if ($lostAndFound->status !== 'stored') {
            return redirect()->back()
                ->with('error', 'Hanya barang dengan status stored yang bisa dibuang');
        }

        $lostAndFound->update([
            'status' => 'disposed',
            'disposal_date' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $lostAndFound->property_id,
            'action' => 'update',
            'description' => auth()->user()->name . " membuang barang '{$lostAndFound->item_name}' ({$lostAndFound->item_number}) karena tidak diklaim selama 90+ hari",
            'loggable_id' => $lostAndFound->id,
            'loggable_type' => LostAndFound::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', 'Barang ditandai sebagai dibuang');
    }

    /**
     * Bulk dispose items ready for disposal.
     */
    public function bulkDispose()
    {
        $user = auth()->user();
        $property = $user->property;

        $items = LostAndFound::where('property_id', $property->id)
            ->readyForDisposal()
            ->get();

        $count = $items->count();

        if ($count === 0) {
            return redirect()->back()
                ->with('info', 'Tidak ada barang yang siap dibuang');
        }

        foreach ($items as $item) {
            $item->update([
                'status' => 'disposed',
                'disposal_date' => now(),
            ]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'update',
            'description' => $user->name . " membuang {$count} barang (bulk disposal) yang tidak diklaim selama 90+ hari",
            'loggable_id' => null,
            'loggable_type' => LostAndFound::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', "{$count} barang berhasil dibuang");
    }
}
