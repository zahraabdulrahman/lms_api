<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->instructor1 = User::factory()->create(['role' => 'instructor']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->course1 = Course::factory()->create(); // No instructor_id needed here
        $this->course2 = Course::factory()->create();// No instructor_id needed here
    }

    public function testAdminCanCreateCourse()
    {
        $this->actingAs($this->admin)
            ->postJson('/api/courses', [
                'title' => 'New Course',
                'price' => 100,
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-15',
                'details' => 'details',
                'instructor_name' => "ins name"
            ])
            ->assertStatus(201);
    }

    public function testInstructorCanCreateCourse()
    {
        $this->actingAs($this->instructor1)
            ->postJson('/api/courses', [
                'title' => 'New Course',
                'price' => 100,
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-15',
                'details' => 'details',
                'instructor_name' => "ins name"
            ])
            ->assertStatus(201);
    }

    public function testStudentCannotCreateCourse()
    {
        $this->actingAs($this->student)
            ->postJson('/api/courses', [
                'title' => 'New Course',
                'price' => 100,
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-15',
                'details' => 'details',
                'instructor_name' => "ins name"
            ])
            ->assertStatus(403);
    }

    public function testAdminCanUpdateCourse()
    {
        $this->actingAs($this->admin)
            ->putJson('/api/courses/' . $this->course1->id, ['title' => 'Updated Course'])
            ->assertStatus(200);
    }

    public function testInstructorCanUpdateCourse() // Simplified test name
    {
        $this->actingAs($this->instructor1)
            ->putJson('/api/courses/' . $this->course1->id, ['title' => 'Updated Course by Instructor'])
            ->assertStatus(200);
    }

    public function testStudentCannotUpdateCourse()
    {
        $this->actingAs($this->student)
            ->putJson('/api/courses/' . $this->course1->id, ['title' => 'Attempt to Update by Student'])
            ->assertStatus(403);
    }

    public function testAdminCanDeleteCourse()
    {
        $this->actingAs($this->admin)
            ->deleteJson('/api/courses/' . $this->course1->id)
            ->assertStatus(200);
    }

    public function testInstructorCannotDeleteCourse()
    {
        $this->actingAs($this->instructor1)
            ->deleteJson('/api/courses/' . $this->course1->id)
            ->assertStatus(403);
    }

    public function testStudentCannotDeleteCourse()
    {
        $this->actingAs($this->student)
            ->deleteJson('/api/courses/' . $this->course1->id)
            ->assertStatus(403);
    }
}