<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $instructor;
    private User $student;
    private User $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users with explicit roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->instructor = User::factory()->create(['role' => 'instructor']);
        
        // Create students WITH profiles using your existing factory state
        $this->student = User::factory()->student()->create(['role' => 'student']);
        $this->otherStudent = User::factory()->student()->create(['role' => 'student']);
    }

    public function test_admin_can_create_student()
    {
        $studentData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'price' => 299.99,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'details' => 'Test student details'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/students', $studentData);

        $response->assertStatus(Response::HTTP_CREATED);
        
        // Verify user and profile creation
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'student'
        ]);
        
        $user = User::where('email', 'john@example.com')->first();
        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $user->id,
            'price' => 299.99
        ]);
    }

    public function test_non_admin_users_cannot_create_students()
    {
        // Test guest user
        $response = $this->postJson('/api/students', [
            'name' => 'Guest Student',
            'email' => 'guest@example.com',
            'password' => 'password123'
        ]);
        $response->assertUnauthorized();

        // Test student user (using factory state that creates profile)
        $student = User::factory()->student()->create(['role' => 'student']);
        $response = $this->actingAs($student)
            ->postJson('/api/students', [
                'name' => 'Student Created Student',
                'email' => 'student2@example.com',
                'password' => 'password123'
            ]);
        $response->assertForbidden();

        // Test instructor user
        $instructor = User::factory()->create(['role' => 'instructor']);
        $response = $this->actingAs($instructor)
            ->postJson('/api/students', [
                'name' => 'Instructor Created Student',
                'email' => 'instructor-student@example.com',
                'password' => 'password123'
            ]);
        $response->assertForbidden();
    }

    public function test_student_creation_creates_profile()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/students', [
                'name' => 'Test Student',
                'email' => 'test@example.com',
                'password' => 'ValidPassword123!',
                'price' => 199.99,
                'start_date' => '2024-03-01',
                'end_date' => '2024-06-01',
                'details' => 'Test details'
            ]);

        // Verify profile creation
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->studentProfile);
        $this->assertEquals(199.99, $user->studentProfile->price);
    }

    // Update Tests
    public function test_admin_can_update_any_student()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/students/{$this->student->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['data' => ['name' => 'Updated Name']]);

        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_student_can_update_own_profile()
    {
        Sanctum::actingAs($this->student);

        $response = $this->putJson("/api/students/{$this->student->id}", [
            'name' => 'My New Name'
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'name' => 'My New Name'
        ]);
    }

    public function test_student_cannot_update_others()
    {
        Sanctum::actingAs($this->student);

        $response = $this->putJson("/api/students/{$this->otherStudent->id}", [
            'name' => 'Hacked Name'
        ]);

        $response->assertForbidden();
    }

    // Show Tests
    public function test_admin_can_view_any_student()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/students/{$this->student->id}");
        $response->assertOk()
            ->assertJson(['data' => ['id' => $this->student->id]]);
    }

    public function test_student_can_view_own_profile()
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/students/{$this->student->id}");
        $response->assertOk()
            ->assertJson(['data' => ['id' => $this->student->id]]);
    }

    public function test_student_cannot_view_other_profiles()
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/students/{$this->otherStudent->id}");
        $response->assertForbidden();
    }

    // Delete Tests
    public function test_admin_can_delete_student()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/students/{$this->student->id}");
        $response->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $this->student->id]);
    }

    public function test_non_admin_cannot_delete()
    {
        // Instructor cannot delete
        Sanctum::actingAs($this->instructor);
        $response = $this->deleteJson("/api/students/{$this->student->id}");
        $response->assertForbidden();

        // Student cannot delete
        Sanctum::actingAs($this->student);
        $response = $this->deleteJson("/api/students/{$this->student->id}");
        $response->assertForbidden();
    }

    // Edge Cases
    public function test_access_nonexistent_user_returns_404()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->getJson("/api/students/9999");
        $response->assertNotFound();
    }

    public function test_update_with_invalid_data()
    {
        Sanctum::actingAs($this->student);

        $response = $this->putJson("/api/students/{$this->student->id}", [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }
  
}