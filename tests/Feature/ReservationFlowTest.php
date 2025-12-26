<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Guest;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\HotelRoom;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        $this->property = Property::factory()->create();
        $this->user = User::factory()->create([
            'property_id' => $this->property->id,
            'role' => 'fo',
        ]);
    }

    public function test_can_create_new_reservation()
    {
        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['property_id' => $this->property->id]);

        $reservationData = [
            'guest_id' => $guest->id,
            'property_id' => $this->property->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => today()->addDay()->format('Y-m-d'),
            'check_out_date' => today()->addDays(3)->format('Y-m-d'),
            'room_rate_per_night' => 500000,
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('reservations.store'), $reservationData);

        $this->assertDatabaseHas('reservations', [
            'guest_id' => $guest->id,
            'property_id' => $this->property->id,
        ]);
    }

    public function test_can_check_in_reservation()
    {
        $reservation = Reservation::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'confirmed',
            'check_in_date' => today(),
        ]);

        $room = HotelRoom::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'vacant_clean',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('front-office.check-in', $reservation), [
                'room_id' => $room->id,
            ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'checked_in',
        ]);
    }

    public function test_can_check_out_reservation()
    {
        $reservation = Reservation::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'checked_in',
            'check_out_date' => today(),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('front-office.check-out', $reservation));

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'checked_out',
        ]);
    }

    public function test_can_cancel_reservation()
    {
        $reservation = Reservation::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('reservations.update', $reservation), [
                'status' => 'cancelled',
            ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }
}
