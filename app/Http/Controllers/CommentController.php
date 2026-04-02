<?php

namespace App\Http\Controllers;

use App\Http\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService)
    {
    }

    /**
     * List comments for a meeting.
     */
    public function index(Request $request, $meetingId)
    {
        return $this->commentService->index($request, $meetingId);
    }

    /**
     * Create a comment for a meeting.
     */
    public function store(Request $request, $meetingId)
    {
        return $this->commentService->store($request, $meetingId);
    }


    public function update(Request $request, $id)
    {
        return $this->commentService->update($request, $id);
    }

    /**
     * Delete a comment (author or admin only).
     */
    public function destroy($id)
    {
        return $this->commentService->destroy($id);
    }
}
