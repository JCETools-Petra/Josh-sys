<?php

namespace Tests\Unit\Models;

use App\Models\Guest;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_guest_has_full_name_attribute()
    {
        $guest = Guest::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $guest->full_name);
    }

    public function test_guest_has_many_reservations()
    {
        $guest = Guest::factory()->create();
        Reservation::factory()->count(3)->create(['guest_id' => $guest->id]);

        $this->assertCount(3, $guest->reservations);
    }

    public function test_guest_email_is_unique()
    {
        $email = 'test@example.com';
        Guest::factory()->create(['email' => $email]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Guest::factory()->create(['email' => $email]);
    }

    public function test_guest_can_have_no_email()
    {
        $guest = Guest::factory()->create(['email' => null]);

        $this->assertNull($guest->email);
    }

    public function test_guest_phone_is_stored_correctly()
    {
        $guest = Guest::factory()->create(['phone' => '+62812345678']);

        $this->assertEquals('+62812345678', $guest->phone);
    }
}
