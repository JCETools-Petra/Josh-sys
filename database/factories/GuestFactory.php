<?php

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestFactory extends Factory
{
    protected $model = Guest::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'id_card_type' => fake()->randomElement(['ktp', 'sim', 'passport']),
            'id_card_number' => fake()->numerify('##########'),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'country' => 'Indonesia',
            'nationality' => 'Indonesian',
        ];
    }
}
