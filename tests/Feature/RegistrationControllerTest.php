<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Resources\RegistrationResource;
use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegistrationControllerTest extends TestCase
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
        $this->course = Course::factory()->create(['end_date' => now()->addDay()]);
    }

    public function test_student_can_register_for_course()
    {
        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/courses/{$this->course->id}/registrations");

        $response->assertStatus(Response::HTTP_CREATED);

        $registration = Registration::where('user_id', $this->student->id)
            ->where('course_id', $this->course->id)
            ->first();

        $response->assertJson(
            (new RegistrationResource($registration))->response()->getData(true)
        );

        $this->assertDatabaseHas('registrations', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
        ]);
    }

    public function test_student_cannot_register_for_same_course_twice()
    {
        Sanctum::actingAs($this->student);

        $this->postJson("/api/courses/{$this->course->id}/registrations");

        $response = $this->postJson("/api/courses/{$this->course->id}/registrations");

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(['message' => 'You are already registered for this course.']);
    }

    public function test_cannot_register_for_ended_course()
    {
        Sanctum::actingAs($this->student);

        $course = Course::factory()->create(['end_date' => now()->subDay()]);

        $response = $this->postJson("/api/courses/{$course->id}/registrations");
        
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_other_roles_cannot_register_for_course()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/courses/{$this->course->id}/registrations");
        $response->assertStatus(Response::HTTP_FORBIDDEN);

        Sanctum::actingAs($this->instructor);
        $response = $this->postJson("/api/courses/{$this->course->id}/registrations");
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_student_can_update_own_registration()
    {
        $registration = Registration::factory()->create(['user_id' => $this->student->id, 'course_id' => $this->course->id]);
        Sanctum::actingAs($this->student);
        $newCourse = Course::factory()->create(['end_date' => now()->addDay()]);
        $data = ['course_id' => $newCourse->id];

        $response = $this->putJson("/api/registrations/{$registration->id}", $data);

        $response->assertStatus(Response::HTTP_OK);

        $updatedRegistration = Registration::find($registration->id);

        $response->assertJson(
            (new RegistrationResource($updatedRegistration))->response()->getData(true)
        );

        $this->assertDatabaseHas('registrations', ['id' => $registration->id, 'course_id' => $newCourse->id, 'user_id' => $this->student->id]);
    }

    public function test_other_roles_cannot_update_registration()
    {
        $registration = Registration::factory()->create(['user_id' => $this->student->id, 'course_id' => $this->course->id]);

        Sanctum::actingAs($this->admin);
        $data = ['course_id' => $this->course->id];

        $response = $this->putJson("/api/registrations/{$registration->id}", $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        Sanctum::actingAs($this->instructor);

        $data = ['course_id' => $this->course->id];

        $response = $this->putJson("/api/registrations/{$registration->id}", $data);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_student_can_view_own_registrations_with_user_id()
    {
        Sanctum::actingAs($this->student);
        Registration::factory()->create(['user_id' => $this->student->id]);

        $response = $this->getJson('/api/registrations?user_id='.$this->student->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_student_can_view_own_registrations_without_user_id()
    {
        Sanctum::actingAs($this->student);
        Registration::factory()->create(['user_id' => $this->student->id]);

        $response = $this->getJson('/api/registrations'); // No query parameter

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_admin_can_view_all_registrations()
    {
        Sanctum::actingAs($this->admin);
        Registration::factory(3)->create();

        $response = $this->getJson('/api/registrations');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'course_id',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    public function test_student_cannot_view_other_students_registrations()
    {
        Sanctum::actingAs($this->student);
        $otherStudent = User::factory()->create(['role' => 'student']);
        Registration::factory()->create(['user_id' => $otherStudent->id, 'course_id' => $this->course->id]);

        $response = $this->getJson('/api/registrations?user_id='.$otherStudent->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_student_cannot_update_other_students_registrations()
    {
        Sanctum::actingAs($this->student);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $registration = Registration::factory()->create(['user_id' => $otherStudent->id, 'course_id' => $this->course->id]);
        $newCourse = Course::factory()->create(['end_date' => now()->addDay()]);
        $data = ['course_id' => $newCourse->id];

        $response = $this->putJson("/api/registrations/{$registration->id}", $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_student_cannot_view_single_other_students_registration()
    {
        Sanctum::actingAs($this->student);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $registration = Registration::factory()->create(['user_id' => $otherStudent->id, 'course_id' => $this->course->id]);

        $response = $this->getJson("/api/registrations/{$registration->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
