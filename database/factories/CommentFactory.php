<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'comment' => fake()->paragraph(),
            'user_id' => User::factory(), // Creates a related User
            'course_id' => Course::factory(), // Creates a related Course
        ];
    }
}
