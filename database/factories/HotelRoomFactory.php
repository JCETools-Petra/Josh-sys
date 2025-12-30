<?php

namespace Database\Factories;

use App\Models\HotelRoom;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelRoomFactory extends Factory
{
    protected $model = HotelRoom::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'room_type_id' => RoomType::factory(),
            'room_number' => fake()->unique()->numberBetween(101, 999),
            'floor' => fake()->numberBetween(1, 10),
            'status' => fake()->randomElement(['vacant_clean', 'vacant_dirty', 'occupied', 'maintenance']),
            'last_cleaned_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function vacantClean(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'vacant_clean',
        ]);
    }

    public function vacantDirty(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'vacant_dirty',
        ]);
    }

    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'occupied',
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }
}
