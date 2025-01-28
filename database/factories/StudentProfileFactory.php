<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'price' => fake()->randomFloat(2, 100, 1000),
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+2 months', '+1 year'),
            'details' => fake()->optional()->paragraph,
        ];
    }
}