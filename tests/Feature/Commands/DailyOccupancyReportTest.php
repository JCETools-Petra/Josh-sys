<?php

namespace Tests\Feature\Commands;

use App\Models\DailyOccupancy;
use App\Models\Property;
use App\Models\HotelRoom;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyOccupancyReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_generates_occupancy_report_for_today()
    {
        $property = Property::factory()->create();

        HotelRoom::factory()->count(10)->create([
            'property_id' => $property->id,
        ]);

        Reservation::factory()->count(5)->create([
            'property_id' => $property->id,
            'status' => 'checked_in',
            'check_in_date' => today()->subDay(),
            'check_out_date' => today()->addDay(),
        ]);

        $this->artisan('reports:daily-occupancy')
            ->assertExitCode(0);

        $this->assertDatabaseHas('daily_occupancies', [
            'property_id' => $property->id,
            'date' => today(),
            'occupied_rooms' => 5,
        ]);
    }

    public function test_can_generate_report_for_specific_date()
    {
        $property = Property::factory()->create();
        $targetDate = today()->subDays(7);

        HotelRoom::factory()->count(10)->create([
            'property_id' => $property->id,
        ]);

        Reservation::factory()->count(3)->create([
            'property_id' => $property->id,
            'status' => 'checked_in',
            'check_in_date' => $targetDate->copy()->subDay(),
            'check_out_date' => $targetDate->copy()->addDay(),
        ]);

        $this->artisan('reports:daily-occupancy', ['--date' => $targetDate->format('Y-m-d')])
            ->assertExitCode(0);

        $this->assertDatabaseHas('daily_occupancies', [
            'property_id' => $property->id,
            'date' => $targetDate,
            'occupied_rooms' => 3,
        ]);
    }

    public function test_calculates_occupancy_rate_correctly()
    {
        $property = Property::factory()->create(['name' => 'Test Property']);

        HotelRoom::factory()->count(20)->create([
            'property_id' => $property->id,
        ]);

        Reservation::factory()->count(10)->create([
            'property_id' => $property->id,
            'status' => 'checked_in',
            'check_in_date' => today()->subDay(),
            'check_out_date' => today()->addDay(),
        ]);

        $this->artisan('reports:daily-occupancy')
            ->expectsOutput('Daily occupancy report completed.')
            ->assertExitCode(0);

        $occupancy = DailyOccupancy::where('property_id', $property->id)
            ->where('date', today())
            ->first();

        $this->assertEquals(10, $occupancy->occupied_rooms);
    }
}
