<?php

namespace App\Http\Services;

use App\Models\Agenda;
use App\Models\AgendaTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaTopicService
{
    public function index(Request $request): JsonResponse
    {
        $query = AgendaTopic::with(['agenda.meeting', 'owner']);

        if ($request->has('agenda_id')) {
            $query->where('agenda_id', $request->agenda_id);
        }

        $topics = $query->orderBy('order', 'asc')->get();

        return response()->json($topics);
    }

    public function storeForAgenda(Request $request, int|string $agendaId): JsonResponse
    {
        $agenda = Agenda::with('meeting')->findOrFail($agendaId);
        if ($agenda->meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized to add topics to this agenda'], 403);
        }

        // If no order is provided, set it to the next available order
        if (!$request->has('order')) {
            $maxOrder = AgendaTopic::where('agenda_id', $agendaId)->max('order') ?? -1;
            $order = $maxOrder + 1;
        } else {
            $order = $request->order;
        }

        $topic = AgendaTopic::create([
            'agenda_id' => $agendaId,
            'owner_id' => $request->owner_id,
            'title' => $request->title,
            'estimated_duration' => $request->estimated_duration,
        ]);

        return response()->json($topic->load(['agenda.meeting', 'owner']), 201);
    }

    public function show(int|string $id): JsonResponse
    {
        $topic = AgendaTopic::with(['agenda.meeting.scheduler', 'owner'])
            ->findOrFail($id);

        return response()->json($topic);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $topic = AgendaTopic::with('agenda.meeting')->findOrFail($id);

        if (
            $topic->owner_id !== Auth::id() &&
            $topic->agenda->meeting->scheduled_by !== Auth::id() &&
            !Auth::user()->is_admin
        ) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'estimated_duration' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
        ]);

        $topic->update($request->only(['title', 'description', 'estimated_duration', 'order']));

        return response()->json($topic->load(['agenda.meeting', 'owner']));
    }

    public function destroy(int|string $id): JsonResponse
    {
        $topic = AgendaTopic::with('agenda.meeting')->findOrFail($id);

        if (
            $topic->owner_id !== Auth::id() &&
            $topic->agenda->meeting->scheduled_by !== Auth::id() &&
            !Auth::user()->is_admin
        ) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $topic->delete();

        return response()->json(['message' => 'Agenda topic deleted successfully']);
    }

    public function getByAgenda(int|string $agendaId): JsonResponse
    {
        Agenda::findOrFail($agendaId);

        $topics = AgendaTopic::with('owner')
            ->where('agenda_id', $agendaId)
            ->orderBy('order', 'asc')
            ->get();

        return response()->json($topics);
    }

    public function reorder(Request $request, int|string $agendaId): JsonResponse
    {
        $agenda = Agenda::with('meeting')->findOrFail($agendaId);

        if ($agenda->meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'topics' => 'required|array',
            'topics.*.id' => 'required|exists:agenda_topics,id',
            'topics.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->topics as $topicData) {
            AgendaTopic::where('id', $topicData['id'])
                ->where('agenda_id', $agendaId)
                ->update(['order' => $topicData['order']]);
        }

        $topics = AgendaTopic::with('owner')
            ->where('agenda_id', $agendaId)
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'message' => 'Topics reordered successfully',
            'topics' => $topics,
        ]);
    }

    public function assignOwner(Request $request, int|string $id): JsonResponse
    {
        $topic = AgendaTopic::with('agenda.meeting')->findOrFail($id);

        if ($topic->agenda->meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'owner_id' => 'required|exists:users,id',
        ]);

        $topic->update(['owner_id' => $request->owner_id]);

        return response()->json([
            'message' => 'Topic owner assigned successfully',
            'topic' => $topic->load(['agenda.meeting', 'owner']),
        ]);
    }

    public function myTopics(): JsonResponse
    {
        $topics = AgendaTopic::with(['agenda.meeting.scheduler', 'agenda.meeting.room'])
            ->where('owner_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($topics);
    }
}
