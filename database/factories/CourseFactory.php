<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'price' => fake()->numberBetween(100, 1000),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'details' => fake()->text(),
            'instructor_name' => fake()->name(), // You might consider removing this too
        ];
    }
}