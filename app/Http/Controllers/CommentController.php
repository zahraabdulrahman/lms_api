<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $this->authorize('create', Comment::class); // Authorize the general creation action

        $validatedData = $request->validate([
            'comment' => 'required|string|max:250',
        ]);

        $comment = $course->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $validatedData['comment'],
        ]);

        return (new CommentResource($comment))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, Course $course, Comment $comment)
    {
        if ($comment->course_id !== $course->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $this->authorize('update', $comment);

        $validatedData = $request->validate([
            'comment' => 'required|string|max:250',
        ]);

        $comment->update($validatedData);

        return new CommentResource($comment);
    }

    public function index(Request $request, Course $course)
    {
        $comments = $course->comments()->paginate(10);

        if ($comments->isEmpty()) {
            return response()->json(['message' => 'No comments found'], Response::HTTP_NOT_FOUND);
        }

        return CommentResource::collection($comments);
    }

    public function destroy(Request $request, Course $course, Comment $comment)
    {
        if ($comment->course_id !== $course->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }
}
