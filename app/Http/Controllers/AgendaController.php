<?php

namespace App\Http\Controllers;

use App\Http\Services\AgendaService;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function __construct(private readonly AgendaService $agendaService)
    {
    }

    /**
     * Display a listing of agendas
     */
    public function index(Request $request)
    {
        return $this->agendaService->index($request);
    }

    /**
     * Store a newly created agenda
     */
    public function store(Request $request)
    {
        return $this->agendaService->store($request);
    }

    /**
     * Display the specified agenda
     */
    public function show($id)
    {
        return $this->agendaService->show($id);
    }

   
    /**
     * Remove the specified agenda
     */
    public function destroy($id)
    {
        return $this->agendaService->destroy($id);
    }

    /**
     * Get agenda by meeting ID
     */
    public function getByMeeting($meetingId)
    {
        return $this->agendaService->getByMeeting($meetingId);
    }

    /**
     * Create or update agenda for a meeting
     */
    public function createOrUpdate(Request $request, $meetingId)
    {
        return $this->agendaService->createOrUpdate($request, $meetingId);
    }
}
