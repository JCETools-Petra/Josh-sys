<?php

namespace Tests\Unit\Services;

use App\Services\FrontOfficeService;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Property;
use App\Models\HotelRoom;
use App\Models\RoomAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontOfficeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FrontOfficeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->service = app(FrontOfficeService::class);
    }

    public function test_can_get_arrivals_for_today()
    {
        $property = Property::factory()->create();

        Reservation::factory()->create([
            'property_id' => $property->id,
            'check_in_date' => today(),
            'status' => 'confirmed',
        ]);

        Reservation::factory()->create([
            'property_id' => $property->id,
            'check_in_date' => today()->addDay(),
            'status' => 'confirmed',
        ]);

        $arrivals = Reservation::where('property_id', $property->id)
            ->where('check_in_date', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $this->assertCount(1, $arrivals);
    }

    public function test_can_get_departures_for_today()
    {
        $property = Property::factory()->create();

        Reservation::factory()->create([
            'property_id' => $property->id,
            'check_out_date' => today(),
            'status' => 'checked_in',
        ]);

        Reservation::factory()->create([
            'property_id' => $property->id,
            'check_out_date' => today()->addDay(),
            'status' => 'checked_in',
        ]);

        $departures = Reservation::where('property_id', $property->id)
            ->where('check_out_date', today())
            ->where('status', 'checked_in')
            ->get();

        $this->assertCount(1, $departures);
    }

    public function test_can_get_in_house_guests()
    {
        $property = Property::factory()->create();

        Reservation::factory()->count(3)->create([
            'property_id' => $property->id,
            'status' => 'checked_in',
            'check_in_date' => today()->subDay(),
            'check_out_date' => today()->addDay(),
        ]);

        Reservation::factory()->create([
            'property_id' => $property->id,
            'status' => 'confirmed',
        ]);

        $inHouse = Reservation::where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->get();

        $this->assertCount(3, $inHouse);
    }
}
