<?php

namespace App\Http\Controllers;

use App\Http\Services\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(private readonly RoomService $roomService)
    {
    }

    /**
     * Display a listing of rooms
     */
    public function index()
    {
        return $this->roomService->index();
    }

    /**
     * Store a newly created room
     */
    public function store(Request $request)
    {
        return $this->roomService->store($request);
    }

    /**
     * Display the specified room
     */
    public function show($id)
    {
        return $this->roomService->show($id);
    }

    /**
     * Update the specified room
     */
    public function update(Request $request, $id)
    {
        return $this->roomService->update($request, $id);
    }

    /**
     * Remove the specified room
     */
    public function destroy($id)
    {
        return $this->roomService->destroy($id);
    }

    /**
     * Get available rooms for a specific date/time
     */
    public function available(Request $request)
    {
        return $this->roomService->available($request);
    }
}
