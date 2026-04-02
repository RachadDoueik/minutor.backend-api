<?php

namespace App\Http\Controllers;

use App\Http\Services\MeetingService;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function __construct(private readonly MeetingService $meetingService)
    {
    }

    /**
     * Display a listing of meetings with optional filters
     */
    public function index(Request $request)
    {
        return $this->meetingService->index($request);
    }

    /**
     * Store a newly created meeting
     */
    public function store(Request $request)
    {
        return $this->meetingService->store($request);
    }

    /**
     * Display the specified meeting
     */
    public function show($id)
    {
        return $this->meetingService->show($id);
    }

    /**
     * Update the specified meeting
     */
    public function update(Request $request, $id)
    {
        return $this->meetingService->update($request, $id);
    }

    /**
     * Remove the specified meeting
     */
    public function destroy($id)
    {
        return $this->meetingService->destroy($id);
    }

    /**
     * Get meetings for the authenticated user
     */
    public function myMeetings(Request $request)
    {
        return $this->meetingService->myMeetings($request);
    }

    /**
     * Add attendees to a meeting
     */
    public function addAttendees(Request $request, $id)
    {
        return $this->meetingService->addAttendees($request, $id);
    }  

    /**
     * Remove attendees from a meeting
     */
    public function removeAttendees(Request $request, $id)
    {
        return $this->meetingService->removeAttendees($request, $id);
    }

    /**
     * Update meeting status
     */
    public function updateStatus(Request $request, $id)
    {
        return $this->meetingService->updateStatus($request, $id);
    }

    /**
     * Get upcoming meetings for the authenticated user
     */
    public function upcoming()
    {
        return $this->meetingService->upcoming();
    }

    /**
     * Get past meetings for the authenticated user
     */
    public function past()
    {
        return $this->meetingService->past();
    }

    /**
     * Join a meeting: mark the authenticated user's attendee status as accepted.
     */
    public function joinMeeting($id)
    {
        return $this->meetingService->joinMeeting($id);
    }

    /**
     * Leave a meeting: mark the authenticated user's attendee status as invited.
     */
    public function leaveMeeting($id)
    {
        return $this->meetingService->leaveMeeting($id);
    }
}
