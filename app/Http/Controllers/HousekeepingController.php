<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\HotelRoom;
use App\Models\User;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    /**
     * Display housekeeping dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Get room statistics
        $dirtyRooms = $property->hotelRooms()->dirty()->count();
        $cleanRooms = $property->hotelRooms()->available()->count();
        $occupiedRooms = $property->hotelRooms()->occupied()->count();
        $maintenanceRooms = $property->hotelRooms()->needsMaintenance()->count();

        // Get rooms needing attention
        $roomsToClean = $property->hotelRooms()
            ->dirty()
            ->with(['roomType', 'assignedHousekeeper'])
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        // Get housekeeping staff
        $housekeepers = User::where('role', 'hk')
            ->where('property_id', $property->id)
            ->get();

        return view('housekeeping.index', compact(
            'property',
            'dirtyRooms',
            'cleanRooms',
            'occupiedRooms',
            'maintenanceRooms',
            'roomsToClean',
            'housekeepers'
        ));
    }

    /**
     * Update room status.
     */
    public function updateRoomStatus(HotelRoom $room, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:vacant_clean,vacant_dirty,occupied,maintenance,out_of_order,blocked',
        ]);

        $room->update([
            'status' => $validated['status'],
            'last_cleaned_at' => $validated['status'] === 'vacant_clean' ? now() : $room->last_cleaned_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status kamar berhasil diupdate',
        ]);
    }

    /**
     * Assign housekeeper to room.
     */
    public function assignHousekeeper(HotelRoom $room, Request $request)
    {
        $validated = $request->validate([
            'assigned_hk_user_id' => 'required|exists:users,id',
        ]);

        $room->update([
            'assigned_hk_user_id' => $validated['assigned_hk_user_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping staff berhasil di-assign',
        ]);
    }

    /**
     * Mark room as clean.
     */
    public function markAsClean(HotelRoom $room)
    {
        $room->markAsClean();

        return redirect()->back()
            ->with('success', "Kamar {$room->room_number} telah ditandai sebagai bersih");
    }

    /**
     * Mark multiple rooms as clean.
     */
    public function bulkMarkAsClean(Request $request)
    {
        $validated = $request->validate([
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:hotel_rooms,id',
        ]);

        HotelRoom::whereIn('id', $validated['room_ids'])->update([
            'status' => 'vacant_clean',
            'last_cleaned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => count($validated['room_ids']) . ' kamar berhasil ditandai sebagai bersih',
        ]);
    }
}
