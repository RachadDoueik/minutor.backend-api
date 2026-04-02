<?php

namespace App\Http\Services;

use App\Models\Agenda;
use App\Models\Meeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaService
{
    public function index(Request $request): JsonResponse
    {
        $query = Agenda::with(['meeting', 'topics.owner']);

        if ($request->has('meeting_id')) {
            $query->where('meeting_id', $request->meeting_id);
        }

        $agendas = $query->orderBy('created_at', 'desc')->get();

        return response()->json($agendas);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'meeting_id' => 'required|exists:meetings,id',
        ]);

        $meeting = Meeting::findOrFail($request->meeting_id);
        if ($meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized to create agenda for this meeting'], 403);
        }

        $existingAgenda = Agenda::where('meeting_id', $request->meeting_id)->first();
        if ($existingAgenda) {
            return response()->json(['message' => 'Agenda already exists for this meeting'], 422);
        }

        $agenda = Agenda::create($request->only(['meeting_id']));

        return response()->json($agenda->load(['meeting', 'topics.owner']), 201);
    }

    public function show(int|string $id): JsonResponse
    {
        $agenda = Agenda::with(['meeting.scheduler', 'meeting.room', 'topics.owner'])
            ->findOrFail($id);

        return response()->json($agenda);
    }

    public function destroy(int|string $id): JsonResponse
    {
        $agenda = Agenda::findOrFail($id);

        if ($agenda->meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $agenda->delete();

        return response()->json(['message' => 'Agenda deleted successfully']);
    }

    public function getByMeeting(int|string $meetingId): JsonResponse
    {
        Meeting::findOrFail($meetingId);

        $agenda = Agenda::with(['topics.owner'])
            ->where('meeting_id', $meetingId)
            ->first();

        if (!$agenda) {
            return response()->json(['message' => 'No agenda found for this meeting'], 404);
        }

        return response()->json($agenda);
    }

    public function createOrUpdate(Request $request, int|string $meetingId): JsonResponse
    {
        $meeting = Meeting::findOrFail($meetingId);

        if ($meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $agenda = Agenda::updateOrCreate(
            ['meeting_id' => $meetingId],
            [
                'title' => $request->title,
                'description' => $request->description,
            ]
        );

        return response()->json($agenda->load(['meeting', 'topics.owner']));
    }
}
