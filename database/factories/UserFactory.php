<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\StudentProfile;
//use database\factories\StudentProfileFactory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            // 'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['student', 'instructor', 'admin']),
        ];
    }

    public function student(): static
    {
        return $this->afterCreating(function (User $user) {
            StudentProfile::factory()->create(['user_id' => $user->id]);
        });
    }
}
