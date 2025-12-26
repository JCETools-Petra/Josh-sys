<?php

namespace Database\Factories;

use App\Models\RoomType;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'name' => fake()->randomElement(['Standard', 'Deluxe', 'Suite', 'Executive']),
            'description' => fake()->paragraph(),
            'base_price' => fake()->numberBetween(300000, 1500000),
            'max_occupancy' => fake()->numberBetween(1, 4),
            'bed_type' => fake()->randomElement(['single', 'double', 'twin', 'king']),
        ];
    }
}
