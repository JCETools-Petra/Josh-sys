<?php

namespace Database\Factories;

use App\Models\RoomAssignment;
use App\Models\Reservation;
use App\Models\HotelRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomAssignmentFactory extends Factory
{
    protected $model = RoomAssignment::class;

    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'hotel_room_id' => HotelRoom::factory(),
            'assigned_at' => now(),
        ];
    }
}
