<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Hotel',
            'address' => fake()->address(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'check_in_time' => '14:00:00',
            'check_out_time' => '12:00:00',
            'currency' => 'IDR',
            'tax_percentage' => 10,
        ];
    }
}
