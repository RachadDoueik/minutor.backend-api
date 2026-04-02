<?php

namespace App\Http\Services;

use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoomService
{
    public function index(): JsonResponse
    {
        $rooms = Room::with('features')->get();

        return response()->json($rooms);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'is_taken' => 'sometimes|boolean',
            'features' => 'nullable|array',
        ]);

        $room = Room::create($request->only(['name', 'location', 'capacity', 'is_taken']));

        if ($request->has('features')) {
            $room->features()->attach($request->features);
        }

        return response()->json($room->load('features'), 201);
    }

    public function show(int|string $id): JsonResponse
    {
        $room = Room::with(['features', 'meetings'])->findOrFail($id);

        return response()->json($room);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|integer|min:1',
            'features' => 'nullable|array',
        ]);

        $room->update($request->only(['name', 'location', 'capacity', 'is_taken']));

        if ($request->has('features')) {
            $room->features()->sync($request->features);
        }

        return response()->json($room->load('features'));
    }

    public function destroy(int|string $id): JsonResponse
    {
        $room = Room::findOrFail($id);

        if ($room->meetings()->exists()) {
            throw ValidationException::withMessages([
                'room' => ['Cannot delete room that has associated meetings.'],
            ]);
        }

        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully',
        ]);
    }

    public function available(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $availableRooms = Room::whereDoesntHave('meetings', function ($query) use ($request) {
            $query->where('date', $request->date)
                ->where(function ($q) use ($request) {
                    $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                        ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                        ->orWhere(function ($subQ) use ($request) {
                            $subQ->where('start_time', '<=', $request->start_time)
                                ->where('end_time', '>=', $request->end_time);
                        });
                })
                ->where('status', '!=', 'cancelled');
        })->with('features')->get();

        return response()->json($availableRooms);
    }
}
