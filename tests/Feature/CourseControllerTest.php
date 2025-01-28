<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $instructor;

    private User $student;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->instructor = User::factory()->create(['role' => 'instructor']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->course = Course::factory()->create();
    }

    public function test_admin_can_create_course_with_valid_data()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'title' => 'New Course',
            'price' => 100,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-15',
            'details' => 'details',
            'instructor_name' => 'ins name',
        ];

        $response = $this->postJson('/api/courses', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $course = Course::where('title', $data['title'])->first();

        $response->assertJson(
            (new CourseResource($course))->response()->getData(true)
        );

        $this->assertDatabaseHas('courses', $data);
    }

    public function test_instructor_can_create_course_with_valid_data()
    {
        Sanctum::actingAs($this->instructor);

        $data = [
            'title' => 'New Course by Instructor',
            'price' => 120,
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-15',
            'details' => 'more details',
            'instructor_name' => 'ins name 2',
        ];

        $response = $this->postJson('/api/courses', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $course = Course::where('title', $data['title'])->first();

        $response->assertJson(
            (new CourseResource($course))->response()->getData(true)
        );

        $this->assertDatabaseHas('courses', $data);
    }

    public function test_student_cannot_create_course()
    {
        Sanctum::actingAs($this->student);

        $data = [
            'title' => 'New Course',
            'price' => 100,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-15',
            'details' => 'details',
            'instructor_name' => 'ins name',
        ];
        $response = $this->postJson('/api/courses', $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_admin_can_update_course_with_valid_data()
    {
        Sanctum::actingAs($this->admin);

        $data = ['title' => 'Updated Course by Admin'];

        $response = $this->putJson('/api/courses/'.$this->course->id, $data);

        $response->assertStatus(Response::HTTP_OK);

        $updatedCourse = Course::find($this->course->id);

        $response->assertJson(
            (new CourseResource($updatedCourse))->response()->getData(true)
        );

        $this->assertDatabaseHas('courses', ['id' => $this->course->id, 'title' => 'Updated Course by Admin']);
    }

    public function test_instructor_can_update_course_with_valid_data()
    {
        Sanctum::actingAs($this->instructor);
        $data = ['title' => 'Updated Course by Instructor'];

        $response = $this->putJson('/api/courses/'.$this->course->id, $data);

        $response->assertStatus(Response::HTTP_OK);

        $updatedCourse = Course::find($this->course->id);

        $response->assertJson(
            (new CourseResource($updatedCourse))->response()->getData(true)
        );

        $this->assertDatabaseHas('courses', ['id' => $this->course->id, 'title' => 'Updated Course by Instructor']);
    }

    public function test_course_list_filtering()
    {
        Sanctum::actingAs($this->student);
        Course::factory()->create(['title' => 'Laravel Advanced']);
        $response = $this->getJson('/api/courses?title=Laravel');
        $response->assertJsonCount(1, 'data');
    }

    public function test_student_cannot_update_course()
    {
        Sanctum::actingAs($this->student);
        $data = ['title' => 'Attempt to Update by Student'];

        $response = $this->putJson('/api/courses/'.$this->course->id, $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_admin_can_soft_delete_course()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/courses/'.$this->course->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('courses', ['id' => $this->course->id]);
    }

    public function test_instructor_cannot_delete_course()
    {
        Sanctum::actingAs($this->instructor);

        $response = $this->deleteJson('/api/courses/'.$this->course->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_student_cannot_delete_course()
    {
        Sanctum::actingAs($this->student);

        $response = $this->deleteJson('/api/courses/'.$this->course->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_course_creation_with_invalid_data()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'title' => null,
            'price' => 'not a number',
            'start_date' => 'invalid date',
            'end_date' => 'invalid date',
            'details' => null,
            'instructor_name' => null,
        ];

        $response = $this->postJson('/api/courses', $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['title', 'price', 'start_date', 'end_date', 'instructor_name']);
    }

    public function test_course_update_with_invalid_data()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'title' => null,
            'price' => 'not a number',
            'start_date' => 'invalid date',
            'end_date' => 'invalid date',
            'details' => null,
            'instructor_name' => null,
        ];

        $response = $this->putJson('/api/courses/'.$this->course->id, $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['title', 'price', 'start_date', 'end_date']);
    }
}
