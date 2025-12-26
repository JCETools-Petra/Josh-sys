<?php

namespace Database\Factories;

use App\Models\DailyOccupancy;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyOccupancyFactory extends Factory
{
    protected $model = DailyOccupancy::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'date' => fake()->date(),
            'occupied_rooms' => fake()->numberBetween(0, 50),
            'reservasi_ota' => fake()->numberBetween(0, 30),
            'reservasi_properti' => fake()->numberBetween(0, 20),
        ];
    }
}
