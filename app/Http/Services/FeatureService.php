<?php

namespace App\Http\Services;

use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeatureService
{
    public function index(): JsonResponse
    {
        $features = Feature::with('rooms')->get();

        return response()->json($features);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:features,name',
        ]);

        $feature = Feature::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json($feature, 201);
    }

    public function show(int|string $id): JsonResponse
    {
        $feature = Feature::with('rooms')->findOrFail($id);

        return response()->json($feature);
    }

    public function destroy(int|string $id): JsonResponse
    {
        $feature = Feature::findOrFail($id);

        $feature->rooms()->detach();
        $feature->delete();

        return response()->json([
            'message' => 'Feature deleted successfully',
        ]);
    }

    public function attachToRoom(Request $request, int|string $id): JsonResponse
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        $feature = Feature::findOrFail($id);
        $feature->rooms()->syncWithoutDetaching([$request->room_id]);

        return response()->json([
            'message' => 'Feature attached to room successfully',
        ]);
    }

    public function detachFromRoom(Request $request, int|string $id): JsonResponse
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        $feature = Feature::findOrFail($id);
        $feature->rooms()->detach($request->room_id);

        return response()->json([
            'message' => 'Feature detached from room successfully',
        ]);
    }
}
