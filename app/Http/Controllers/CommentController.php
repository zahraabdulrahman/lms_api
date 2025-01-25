<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\CommentResource;
use App\Models\Course;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CommentController extends Controller implements HasMiddleware
{
    public static function middleware(){
        return [
            new Middleware('auth:sanctum')
        ];
    }

    public function store(Request $request, Course $course)
    {
        $validatedData = $request->validate([
            'comment' => 'required|string|max:250',
        ]);

        $user = $request->user();
        $comment = new Comment();
        $comment->comment = $validatedData['comment'];
        $comment->user_id = $user->id;
        $comment->course_id = $course->id;
        $comment->save();

        return new CommentResource($comment); // Use resource
    }


    public function update(Request $request,  Comment $comment) //updating a comment
    {
        $comment = Comment::findOrFail($id); //finding comment

        $validate_data = $request->validate([
            'comment' => "required|string|max:250",
        ]);

        $comment->update($validatedData);
        
        return response()->json(["message"=>'comment updated successfully'], 200);
    }

    public function index(Request $request, Course $course)
    {
        $comments = $course->comments()->paginate(10); // Get paginated comments for the course
    
        if ($comments->isEmpty()) {
            return response()->json(["message" => "no comments found"], 404);
        }
    
        return CommentResource::collection($comments);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete(); 

        return response()->json(['message' => 'Comment deleted successfully'], 200);
    }
}
