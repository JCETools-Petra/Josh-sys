<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\HotelRoom;
use App\Models\User;
use Illuminate\Http\Request;

class HotelRoomController extends Controller
{
    /**
     * Menampilkan daftar kamar hotel untuk properti tertentu.
     */
    public function index(Request $request)
    {
        $propertyId = $request->get('property_id');
        $properties = Property::all();

        $query = HotelRoom::with(['property', 'roomType', 'assignedHousekeeper']);

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Filter by floor
        if ($request->has('floor') && $request->floor != 'all') {
            $query->where('floor', $request->floor);
        }

        // Search by room number
        if ($request->has('search') && $request->search) {
            $query->where('room_number', 'like', '%' . $request->search . '%');
        }

        $rooms = $query->orderBy('property_id')
                       ->orderBy('floor')
                       ->orderBy('room_number')
                       ->paginate(20);

        // Get unique floors for filter
        $floors = HotelRoom::when($propertyId, function($q) use ($propertyId) {
            return $q->where('property_id', $propertyId);
        })->distinct()->pluck('floor')->filter()->sort()->values();

        // Get statistics
        $stats = [];
        if ($propertyId) {
            $stats = [
                'total' => HotelRoom::where('property_id', $propertyId)->count(),
                'vacant_clean' => HotelRoom::where('property_id', $propertyId)->where('status', 'vacant_clean')->count(),
                'vacant_dirty' => HotelRoom::where('property_id', $propertyId)->where('status', 'vacant_dirty')->count(),
                'occupied' => HotelRoom::where('property_id', $propertyId)->where('status', 'occupied')->count(),
                'maintenance' => HotelRoom::where('property_id', $propertyId)->whereIn('status', ['maintenance', 'out_of_order'])->count(),
            ];
        }

        return view('admin.hotel-rooms.index', compact('rooms', 'properties', 'propertyId', 'floors', 'stats'));
    }

    /**
     * Menampilkan form untuk membuat kamar hotel baru.
     */
    public function create(Request $request)
    {
        $properties = Property::all();
        $propertyId = $request->get('property_id');

        $roomTypes = $propertyId
            ? RoomType::where('property_id', $propertyId)->get()
            : collect();

        $housekeepers = User::where('role', 'hk')->get();

        return view('admin.hotel-rooms.create', compact('properties', 'roomTypes', 'housekeepers', 'propertyId'));
    }

    /**
     * Menyimpan kamar hotel baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'room_number' => 'required|string|max:20',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'nullable|string|max:10',
            'capacity' => 'required|integer|min:1',
            'is_smoking' => 'boolean',
            'status' => 'required|in:vacant_clean,vacant_dirty,occupied,maintenance,out_of_order,blocked',
            'assigned_hk_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        // Check if room number already exists for this property
        $exists = HotelRoom::where('property_id', $validated['property_id'])
                           ->where('room_number', $validated['room_number'])
                           ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Nomor kamar sudah ada untuk properti ini');
        }

        HotelRoom::create($validated);

        return redirect()->route('admin.hotel-rooms.index', ['property_id' => $validated['property_id']])
            ->with('success', 'Kamar berhasil ditambahkan');
    }

    /**
     * Menampilkan form untuk mengedit kamar hotel.
     */
    public function edit(HotelRoom $hotelRoom)
    {
        $properties = Property::all();
        $roomTypes = RoomType::where('property_id', $hotelRoom->property_id)->get();
        $housekeepers = User::where('role', 'hk')->get();

        return view('admin.hotel-rooms.edit', compact('hotelRoom', 'properties', 'roomTypes', 'housekeepers'));
    }

    /**
     * Memperbarui kamar hotel.
     */
    public function update(Request $request, HotelRoom $hotelRoom)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'room_number' => 'required|string|max:20',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'nullable|string|max:10',
            'capacity' => 'required|integer|min:1',
            'is_smoking' => 'boolean',
            'status' => 'required|in:vacant_clean,vacant_dirty,occupied,maintenance,out_of_order,blocked',
            'assigned_hk_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        // Check if room number already exists for this property (excluding current room)
        $exists = HotelRoom::where('property_id', $validated['property_id'])
                           ->where('room_number', $validated['room_number'])
                           ->where('id', '!=', $hotelRoom->id)
                           ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Nomor kamar sudah ada untuk properti ini');
        }

        $hotelRoom->update($validated);

        return redirect()->route('admin.hotel-rooms.index', ['property_id' => $validated['property_id']])
            ->with('success', 'Kamar berhasil diupdate');
    }

    /**
     * Menghapus kamar hotel.
     */
    public function destroy(HotelRoom $hotelRoom)
    {
        $propertyId = $hotelRoom->property_id;

        // Check if room has any stays
        if ($hotelRoom->roomStays()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus kamar yang memiliki riwayat booking');
        }

        $hotelRoom->delete();

        return redirect()->route('admin.hotel-rooms.index', ['property_id' => $propertyId])
            ->with('success', 'Kamar berhasil dihapus');
    }

    /**
     * Bulk update room status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:hotel_rooms,id',
            'status' => 'required|in:vacant_clean,vacant_dirty,occupied,maintenance,out_of_order,blocked',
        ]);

        $updated = HotelRoom::whereIn('id', $validated['room_ids'])
            ->update([
                'status' => $validated['status'],
                'last_cleaned_at' => $validated['status'] === 'vacant_clean' ? now() : null,
            ]);

        return redirect()->back()
            ->with('success', "{$updated} kamar berhasil diupdate");
    }

    /**
     * Get room types for a property (AJAX).
     */
    public function getRoomTypes($propertyId)
    {
        $roomTypes = RoomType::where('property_id', $propertyId)->get();

        return response()->json($roomTypes);
    }
}