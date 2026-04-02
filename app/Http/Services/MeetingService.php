<?php

namespace App\Http\Services;

use App\Models\Agenda;
use App\Models\Meeting;
use App\Models\MomEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingService
{
    public function index(Request $request): JsonResponse
    {
        $query = Meeting::with(['scheduler', 'room', 'attendees', 'agenda']);

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('date', [$request->from_date, $request->to_date]);
        }

        $meetings = $query->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json($meetings);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'objective' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_id' => 'required|exists:rooms,id',
            'attendees' => 'nullable|array',
            'attendees.*' => 'exists:users,id',
        ]);

        $conflictingMeetings = Meeting::where('room_id', $request->room_id)
            ->where('date', $request->date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->exists();

        if ($conflictingMeetings) {
            return response()->json([
                'message' => 'Room is not available at the specified time',
            ], 422);
        }

        $meeting = Meeting::create([
            'title' => $request->title,
            'objective' => $request->objective,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'scheduled',
            'scheduled_by' => Auth::id(),
            'room_id' => $request->room_id,
        ]);

        Agenda::create([
            'meeting_id' => $meeting->id,
        ]);

        MomEntry::create([
            'meeting_id' => $meeting->id,
            'title' => 'Meeting Minutes',
            'notes' => '',
            'summary' => null,
            'file_path' => null,
        ]);

        if ($request->has('attendees')) {
            $meeting->attendees()->attach($request->attendees, [
                'status' => 'invited',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json($meeting->load(['scheduler', 'room', 'attendees', 'agenda']), 201);
    }

    public function show(int|string $id): JsonResponse
    {
        $meeting = Meeting::with(['scheduler', 'room', 'attendees', 'agenda', 'momEntries'])
            ->findOrFail($id);

        return response()->json($meeting);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $meeting = Meeting::findOrFail($id);

        if ($meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'objective' => 'nullable|string',
            'date' => 'sometimes|required|date',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'room_id' => 'sometimes|required|exists:rooms,id',
            'status' => 'sometimes|required|in:scheduled,in_progress,completed,cancelled',
        ]);

        if ($request->has('date') || $request->has('start_time') || $request->has('end_time') || $request->has('room_id')) {
            $date = $request->get('date', $meeting->date);
            $startTime = $request->get('start_time', $meeting->start_time);
            $endTime = $request->get('end_time', $meeting->end_time);
            $roomId = $request->get('room_id', $meeting->room_id);

            $conflictingMeetings = Meeting::where('room_id', $roomId)
                ->where('date', $date)
                ->where('status', '!=', 'cancelled')
                ->where('id', '!=', $meeting->id)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($subQuery) use ($startTime, $endTime) {
                            $subQuery->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->exists();

            if ($conflictingMeetings) {
                return response()->json([
                    'message' => 'Room is not available at the specified time',
                ], 422);
            }
        }

        $meeting->update($request->only([
            'title', 'objective', 'date', 'start_time', 'end_time', 'room_id', 'status',
        ]));

        return response()->json($meeting->load(['scheduler', 'room', 'attendees', 'agenda']));
    }

    public function destroy(int|string $id): JsonResponse
    {
        $meeting = Meeting::findOrFail($id);

        if ($meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $meeting->delete();

        return response()->json(['message' => 'Meeting deleted successfully']);
    }

    public function myMeetings(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Meeting::with(['scheduler', 'room', 'attendees', 'agenda'])
            ->where(function ($query) use ($user) {
                $query->where('scheduled_by', $user->id)
                    ->orWhereHas('attendees', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id);
                    });
            });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('date', [$request->from_date, $request->to_date]);
        }

        $meetings = $query->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json($meetings);
    }

    public function addAttendees(Request $request, int|string $id): JsonResponse
    {
        $meeting = Meeting::findOrFail($id);

        if ($meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'attendees' => 'required|array',
            'attendees.*' => 'exists:users,id',
        ]);

        $currentAttendees = $meeting->attendees()->pluck('user_id')->toArray();
        $newAttendees = array_diff($request->attendees, $currentAttendees);

        if (!empty($newAttendees)) {
            $attendeeData = [];
            foreach ($newAttendees as $userId) {
                $attendeeData[$userId] = [
                    'status' => 'invited',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $meeting->attendees()->attach($attendeeData);
        }

        return response()->json([
            'message' => 'Attendees added successfully',
            'meeting' => $meeting->load(['scheduler', 'room', 'attendees', 'agenda']),
        ]);
    }

    public function removeAttendees(Request $request, int|string $id): JsonResponse
    {
        $meeting = Meeting::findOrFail($id);

        if ($meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'attendees' => 'required|array',
            'attendees.*' => 'exists:users,id',
        ]);

        $meeting->attendees()->detach($request->attendees);

        return response()->json([
            'message' => 'Attendees removed successfully',
            'meeting' => $meeting->load(['scheduler', 'room', 'attendees', 'agenda']),
        ]);
    }

    public function updateStatus(Request $request, int|string $id): JsonResponse
    {
        $meeting = Meeting::findOrFail($id);

        if ($meeting->scheduled_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        $meeting->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Meeting status updated successfully',
            'meeting' => $meeting->load(['scheduler', 'room', 'attendees', 'agenda']),
        ]);
    }

    public function upcoming(): JsonResponse
    {
        $user = Auth::user();
        $now = now();

        $meetings = Meeting::with(['scheduler', 'room', 'attendees', 'agenda'])
            ->where(function ($query) use ($user) {
                $query->where('scheduled_by', $user->id)
                    ->orWhereHas('attendees', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id);
                    });
            })
            ->where('status', 'scheduled')
            ->where(function ($query) use ($now) {
                $query->where('date', '>', $now->toDateString())
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery->where('date', $now->toDateString())
                            ->where('start_time', '>', $now->format('H:i'));
                    });
            })
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json($meetings);
    }

    public function past(): JsonResponse
    {
        $user = Auth::user();
        $now = now();

        $meetings = Meeting::with(['scheduler', 'room', 'attendees', 'agenda'])
            ->where(function ($query) use ($user) {
                $query->where('scheduled_by', $user->id)
                    ->orWhereHas('attendees', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id);
                    });
            })
            ->where(function ($query) use ($now) {
                $query->whereIn('status', ['completed', 'cancelled'])
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery->where('date', '<', $now->toDateString())
                            ->orWhere(function ($timeQuery) use ($now) {
                                $timeQuery->where('date', $now->toDateString())
                                    ->where('end_time', '<', $now->format('H:i'));
                            });
                    });
            })
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json($meetings);
    }

    public function joinMeeting(int|string $id): JsonResponse
    {
        $meeting = Meeting::findOrFail($id);
        $userId = Auth::id();

        $meeting->attendees()->syncWithoutDetaching([
            $userId => [
                'status' => 'accepted',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return response()->json(['message' => 'Joined meeting successfully']);
    }

    public function leaveMeeting(int|string $id): JsonResponse
    {
        $meeting = Meeting::findOrFail($id);
        $userId = Auth::id();

        $meeting->attendees()->syncWithoutDetaching([
            $userId => [
                'status' => 'invited',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return response()->json(['message' => 'Left meeting successfully']);
    }
}
