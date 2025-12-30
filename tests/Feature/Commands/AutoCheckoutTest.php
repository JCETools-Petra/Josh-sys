<?php

namespace Tests\Feature\Commands;

use App\Models\Reservation;
use App\Models\Property;
use App\Models\HotelRoom;
use App\Models\RoomAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_auto_checkout_processes_overdue_reservations()
    {
        $property = Property::factory()->create();
        $room = HotelRoom::factory()->create([
            'property_id' => $property->id,
            'status' => 'occupied',
        ]);

        $reservation = Reservation::factory()->create([
            'property_id' => $property->id,
            'status' => 'checked_in',
            'check_out_date' => today()->subDay(),
        ]);

        RoomAssignment::factory()->create([
            'reservation_id' => $reservation->id,
            'hotel_room_id' => $room->id,
        ]);

        $this->artisan('reservations:auto-checkout')
            ->assertExitCode(0);

        $reservation->refresh();
        $room->refresh();

        $this->assertEquals('checked_out', $reservation->status);
        $this->assertNotNull($reservation->actual_check_out);
        $this->assertEquals('vacant_dirty', $room->status);
    }

    public function test_auto_checkout_skips_future_checkouts()
    {
        $reservation = Reservation::factory()->create([
            'status' => 'checked_in',
            'check_out_date' => today()->addDay(),
        ]);

        $this->artisan('reservations:auto-checkout')
            ->assertExitCode(0);

        $reservation->refresh();

        $this->assertEquals('checked_in', $reservation->status);
    }

    public function test_auto_checkout_handles_empty_results()
    {
        $this->artisan('reservations:auto-checkout')
            ->expectsOutput('No reservations to auto-checkout.')
            ->assertExitCode(0);
    }
}
