<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\HotelRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousekeepingTest extends TestCase
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
            'role' => 'hk',
        ]);
    }

    public function test_housekeeping_can_access_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('housekeeping.index'));

        $response->assertStatus(200);
    }

    public function test_can_mark_room_as_clean()
    {
        $room = HotelRoom::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'vacant_dirty',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('housekeeping.mark-clean', $room));

        $this->assertDatabaseHas('hotel_rooms', [
            'id' => $room->id,
            'status' => 'vacant_clean',
        ]);
    }

    public function test_can_update_room_status()
    {
        $room = HotelRoom::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'vacant_clean',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('housekeeping.update-status', $room), [
                'status' => 'maintenance',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('hotel_rooms', [
            'id' => $room->id,
            'status' => 'maintenance',
        ]);
    }

    public function test_can_bulk_mark_rooms_as_clean()
    {
        $rooms = HotelRoom::factory()->count(3)->create([
            'property_id' => $this->property->id,
            'status' => 'vacant_dirty',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('housekeeping.bulk-mark-clean'), [
                'room_ids' => $rooms->pluck('id')->toArray(),
            ]);

        $response->assertStatus(200);

        foreach ($rooms as $room) {
            $this->assertDatabaseHas('hotel_rooms', [
                'id' => $room->id,
                'status' => 'vacant_clean',
            ]);
        }
    }
}
