<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Resources\CommentResource; // Import the resource
use App\Models\Comment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response; // Import Response class
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $admin;

    private Course $course;

    private User $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::factory()->create(['role' => 'student']);
        $this->otherStudent = User::factory()->create(['role' => 'student']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->course = Course::factory()->create();
    }

    public function test_authenticated_student_can_create_comment_with_valid_data()
    {
        Sanctum::actingAs($this->student);
        $data = ['comment' => fake()->sentence];

        $response = $this->postJson("/api/courses/{$this->course->id}/comments", $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $comment = Comment::where('user_id', $this->student->id)
            ->where('course_id', $this->course->id)
            ->first();

        $response->assertJson(
            (new CommentResource($comment))->response()->getData(true)
        );

        $this->assertDatabaseHas('comments', $data + ['user_id' => $this->student->id, 'course_id' => $this->course->id]);
    }

    public function test_authenticated_user_but_not_student_cannot_create_comment()
    {
        Sanctum::actingAs($this->admin);
        $data = ['comment' => fake()->paragraph];

        $response = $this->postJson("/api/courses/{$this->course->id}/comments", $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN); // Use constant
    }

    public function test_unauthenticated_user_cannot_create_comment()
    {
        $data = ['comment' => fake()->paragraph];
        $response = $this->postJson("/api/courses/{$this->course->id}/comments", $data);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED); // Use constant
    }

    public function test_comment_creation_with_invalid_data()
    {
        Sanctum::actingAs($this->student);
        $data = ['comment' => null];

        $response = $this->postJson("/api/courses/{$this->course->id}/comments", $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY) // Use constant
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_comment_list_is_accessible_to_authenticated_users()
    {
        Sanctum::actingAs($this->student);
        Comment::factory(5)->create(['course_id' => $this->course->id, 'user_id' => $this->student->id]);

        $response = $this->getJson("/api/courses/{$this->course->id}/comments");

        $response->assertStatus(Response::HTTP_OK) // Use constant
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'comment',
                        'course' => ['id', 'title', 'start_date', 'end_date'],
                        'student' => ['id', 'name'],
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_comment_list_is_not_accessible_to_unauthenticated_users()
    {
        $response = $this->getJson("/api/courses/{$this->course->id}/comments");
        $response->assertStatus(Response::HTTP_UNAUTHORIZED); // Use constant
    }

    public function test_student_can_update_own_comment()
    {
        $comment = Comment::factory()->create(['user_id' => $this->student->id, 'course_id' => $this->course->id]);
        Sanctum::actingAs($this->student);
        $data = ['comment' => 'Updated comment'];

        $response = $this->putJson("/api/courses/{$this->course->id}/comments/{$comment->id}", $data);

        $response->assertStatus(Response::HTTP_OK);

        $updatedComment = Comment::find($comment->id); // Refresh from DB

        $response->assertJson(
            (new CommentResource($updatedComment))->response()->getData(true)
        );

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'comment' => 'Updated comment']);
    }

    public function test_student_cannot_update_another_students_comment()
    {
        $comment = Comment::factory()->create(['user_id' => $this->otherStudent->id, 'course_id' => $this->course->id]);
        Sanctum::actingAs($this->student);
        $data = ['comment' => 'Attempted update'];

        $response = $this->putJson("/api/courses/{$this->course->id}/comments/{$comment->id}", $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseMissing('comments', ['comment' => 'Attempted update']);
    }

    public function test_admin_can_update_any_comment()
    {
        $comment = Comment::factory()->create(['user_id' => $this->student->id, 'course_id' => $this->course->id]);
        Sanctum::actingAs($this->admin);
        $data = ['comment' => 'Admin updated comment'];

        $response = $this->putJson("/api/courses/{$this->course->id}/comments/{$comment->id}", $data);

        $response->assertStatus(Response::HTTP_OK);

        $updatedComment = Comment::find($comment->id); // Refresh from DB

        $response->assertJson(
            (new CommentResource($updatedComment))->response()->getData(true)
        );

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'comment' => 'Admin updated comment']);
    }

    public function test_student_can_delete_own_comment()
    {
        $comment = Comment::factory()->create(['user_id' => $this->student->id, 'course_id' => $this->course->id]);
        Sanctum::actingAs($this->student);

        $response = $this->deleteJson("/api/courses/{$this->course->id}/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_student_cannot_delete_another_students_comment()
    {
        $comment = Comment::factory()->create(['user_id' => $this->otherStudent->id, 'course_id' => $this->course->id]);
        Sanctum::actingAs($this->student);

        $response = $this->deleteJson("/api/courses/{$this->course->id}/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_admin_can_delete_any_comment()
    {
        $comment = Comment::factory()->create(['user_id' => $this->student->id, 'course_id' => $this->course->id]);
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/courses/{$this->course->id}/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}
