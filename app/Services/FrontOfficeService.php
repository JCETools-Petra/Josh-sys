<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\RoomStay;
use App\Models\HotelRoom;
use App\Models\DailyOccupancy;
use App\Models\PropertySetting;
use App\Traits\HasPropertySettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FrontOfficeService
{
    use HasPropertySettings;
    /**
     * Process check-in for a guest.
     */
    public function checkIn(array $data): RoomStay
    {
        return DB::transaction(function () use ($data) {
            // Find or create guest
            $guest = $this->findOrCreateGuest($data['guest']);

            // Check if guest is already checked in somewhere
            $existingCheckIn = RoomStay::where('guest_id', $guest->id)
                ->where('property_id', $data['property_id'])
                ->where('status', 'checked_in')
                ->with('hotelRoom')
                ->first();

            if ($existingCheckIn) {
                throw new \Exception(
                    "Tamu {$guest->full_name} sudah check-in di kamar {$existingCheckIn->hotelRoom->room_number}. " .
                    "Silakan checkout terlebih dahulu sebelum check-in ke kamar lain."
                );
            }

            // Get the room with pessimistic lock to prevent double-booking
            $room = HotelRoom::lockForUpdate()->findOrFail($data['hotel_room_id']);

            // Validate room ownership
            if ($room->property_id !== $data['property_id']) {
                throw new \Exception("Room does not belong to this property.");
            }

            // Validate room availability
            if (!$room->isAvailable()) {
                throw new \Exception("Room {$room->room_number} is not available for check-in.");
            }

            // Check for overlapping bookings with lock to prevent race condition
            $overlapping = RoomStay::where('hotel_room_id', $room->id)
                ->whereIn('status', ['reserved', 'checked_in'])
                ->where(function ($query) use ($data) {
                    $query->whereBetween('check_in_date', [$data['check_in_date'], $data['check_out_date']])
                        ->orWhereBetween('check_out_date', [$data['check_in_date'], $data['check_out_date']])
                        ->orWhere(function ($q) use ($data) {
                            $q->where('check_in_date', '<=', $data['check_in_date'])
                              ->where('check_out_date', '>=', $data['check_out_date']);
                        });
                })
                ->lockForUpdate()
                ->exists();

            if ($overlapping) {
                throw new \Exception("Room {$room->room_number} has overlapping bookings.");
            }

            // Create room stay
            $roomStay = RoomStay::create([
                'property_id' => $data['property_id'],
                'hotel_room_id' => $room->id,
                'guest_id' => $guest->id,
                'room_type_id' => $room->room_type_id,
                'source' => $data['source'] ?? 'walk_in',
                'ota_name' => $data['ota_name'] ?? null,
                'ota_booking_id' => $data['ota_booking_id'] ?? null,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'actual_check_in' => now(),
                'room_rate_per_night' => $data['room_rate_per_night'],
                'bar_level' => $data['bar_level'] ?? null,
                'total_room_charge' => $data['total_room_charge'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'service_charge' => $data['service_charge'] ?? 0,
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'with_breakfast' => $data['with_breakfast'] ?? false,
                'breakfast_rate' => $data['breakfast_rate'] ?? null,
                'total_breakfast_charge' => $data['total_breakfast_charge'] ?? 0,
                'special_requests' => $data['special_requests'] ?? null,
                'status' => 'checked_in',
                'status_changed_at' => now(),
                'checked_in_by' => auth()->id(),
            ]);

            // Update room status to occupied
            $room->markAsOccupied();

            // Update daily occupancy
            $this->updateDailyOccupancy($data['property_id'], $data['check_in_date']);

            // Update guest statistics
            $guest->updateStatistics();

            return $roomStay;
        });
    }

    /**
     * Create reservation for future dates.
     */
    public function createReservation(array $data): RoomStay
    {
        return DB::transaction(function () use ($data) {
            // Find or create guest
            $guest = $this->findOrCreateGuest($data['guest']);

            // Get the room with pessimistic lock to prevent double-booking
            $room = HotelRoom::lockForUpdate()->findOrFail($data['hotel_room_id']);

            // Validate room ownership
            if ($room->property_id !== $data['property_id']) {
                throw new \Exception("Room does not belong to this property.");
            }

            // Check for overlapping bookings with lock to prevent race condition
            $overlapping = RoomStay::where('hotel_room_id', $room->id)
                ->whereIn('status', ['reserved', 'checked_in'])
                ->where(function ($query) use ($data) {
                    $query->whereBetween('check_in_date', [$data['check_in_date'], $data['check_out_date']])
                        ->orWhereBetween('check_out_date', [$data['check_in_date'], $data['check_out_date']])
                        ->orWhere(function ($q) use ($data) {
                            $q->where('check_in_date', '<=', $data['check_in_date'])
                              ->where('check_out_date', '>=', $data['check_out_date']);
                        });
                })
                ->lockForUpdate()
                ->exists();

            if ($overlapping) {
                throw new \Exception("Kamar {$room->room_number} sudah memiliki reservasi untuk tanggal tersebut. Silakan pilih kamar lain atau tanggal berbeda.");
            }

            // Create room stay with 'reserved' status
            $roomStay = RoomStay::create([
                'property_id' => $data['property_id'],
                'hotel_room_id' => $room->id,
                'guest_id' => $guest->id,
                'room_type_id' => $room->room_type_id,
                'source' => $data['source'] ?? 'walk_in',
                'ota_name' => $data['ota_name'] ?? null,
                'ota_booking_id' => $data['ota_booking_id'] ?? null,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'actual_check_in' => null, // Will be set on actual check-in
                'room_rate_per_night' => $data['room_rate_per_night'],
                'bar_level' => $data['bar_level'] ?? null,
                'total_room_charge' => $data['total_room_charge'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'service_charge' => $data['service_charge'] ?? 0,
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'special_requests' => $data['special_requests'] ?? null,
                'status' => 'reserved',
                'status_changed_at' => now(),
                'created_by' => auth()->id(),
            ]);

            // Update guest statistics
            $guest->updateStatistics();

            return $roomStay;
        });
    }

    /**
     * Process check-out for a guest.
     */
    public function checkOut(RoomStay $roomStay, array $data = []): RoomStay
    {
        return DB::transaction(function () use ($roomStay, $data) {
            // Update room stay
            $roomStay->update([
                'actual_check_out' => now(),
                'status' => 'checked_out',
                'status_changed_at' => now(),
                'checked_out_by' => auth()->id(),
            ]);

            // Mark room as dirty (needs cleaning)
            $roomStay->hotelRoom->markAsDirty();

            // Update daily occupancy
            $this->updateDailyOccupancy($roomStay->property_id, now()->toDateString());

            // Update guest statistics
            $roomStay->guest->updateStatistics();

            return $roomStay;
        });
    }

    /**
     * Find or create guest from data.
     */
    protected function findOrCreateGuest(array $guestData): Guest
    {
        // Try to find existing guest by email or phone
        $guest = null;

        if (!empty($guestData['email'])) {
            $guest = Guest::where('email', $guestData['email'])->first();
        }

        if (!$guest && !empty($guestData['phone'])) {
            $guest = Guest::where('phone', $guestData['phone'])->first();
        }

        // Create new guest if not found
        if (!$guest) {
            $guest = Guest::create($guestData);
        }

        return $guest;
    }

    /**
     * Update daily occupancy count.
     */
    public function updateDailyOccupancy(int $propertyId, string $date): void
    {
        // Extract date only (remove time component)
        $dateOnly = \Carbon\Carbon::parse($date)->format('Y-m-d');

        // Count occupied rooms for the property on this date
        $occupiedCount = RoomStay::where('property_id', $propertyId)
            ->where('status', 'checked_in')
            ->whereDate('check_in_date', '<=', $dateOnly)
            ->whereDate('check_out_date', '>', $dateOnly)
            ->count();

        // Update or create daily occupancy
        DailyOccupancy::updateOrCreate(
            [
                'property_id' => $propertyId,
                'date' => $dateOnly,
            ],
            [
                'occupied_rooms' => $occupiedCount,
                'reservasi_properti' => $occupiedCount, // For now, all from property
            ]
        );
    }

    /**
     * Get available rooms for a property on specific dates.
     */
    public function getAvailableRooms(int $propertyId, string $checkIn, string $checkOut)
    {
        $bookedRoomIds = RoomStay::where('property_id', $propertyId)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in_date', '<=', $checkIn)
                          ->where('check_out_date', '>=', $checkOut);
                    });
            })
            ->whereIn('status', ['reserved', 'checked_in'])
            ->pluck('hotel_room_id')
            ->toArray();

        return HotelRoom::where('property_id', $propertyId)
            ->whereNotIn('id', $bookedRoomIds)
            ->where('status', 'vacant_clean')
            ->with('roomType')
            ->get();
    }
}
