<?php

namespace App\Http\Services;

use App\Models\Comment;
use App\Models\Meeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function index(Request $request, int|string $meetingId): JsonResponse
    {
        $meeting = Meeting::findOrFail($meetingId);
        $comments = $meeting->comments()->with('user')->orderBy('created_at', 'asc')->get();
        return response()->json($comments);
    }

    public function store(Request $request, int|string $meetingId): JsonResponse
    {
        $meeting = Meeting::findOrFail($meetingId);

        $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'meeting_id' => $meeting->id,
            'text' => $request->text,
        ]);

        return response()->json($comment->load('user'), 201);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $user = Auth::user();
        if ($comment->user_id !== $user->id && !$user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $comment->text = $request->text;
        $comment->save();

        return response()->json($comment->load('user'));
    }

    public function destroy(int|string $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $user = Auth::user();
        if ($comment->user_id !== $user->id && !$user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
