<?php

namespace App\Http\Controllers;

use App\Http\Services\AgendaTopicService;
use Illuminate\Http\Request;

class AgendaTopicController extends Controller
{
    public function __construct(private readonly AgendaTopicService $agendaTopicService)
    {
    }

    /**
     * Display a listing of agenda topics
     */
    public function index(Request $request)
    {
        return $this->agendaTopicService->index($request);
    }

    /**
     * Store a newly created agenda topic for a specific agenda
     */
    public function storeForAgenda(Request $request, $agendaId)
    {
        return $this->agendaTopicService->storeForAgenda($request, $agendaId);
    }

    /**
     * Display the specified agenda topic
     */
    public function show($id)
    {
        return $this->agendaTopicService->show($id);
    }

    /**
     * Update the specified agenda topic
     */
    public function update(Request $request, $id)
    {
        return $this->agendaTopicService->update($request, $id);
    }

    /**
     * Remove the specified agenda topic
     */
    public function destroy($id)
    {
        return $this->agendaTopicService->destroy($id);
    }

    /**
     * Get topics by agenda ID
     */
    public function getByAgenda($agendaId)
    {
        return $this->agendaTopicService->getByAgenda($agendaId);
    }

    /**
     * Reorder agenda topics
     */
    public function reorder(Request $request, $agendaId)
    {
        return $this->agendaTopicService->reorder($request, $agendaId);
    }

    /**
     * Assign a topic to a different owner
     */
    public function assignOwner(Request $request, $id)
    {
        return $this->agendaTopicService->assignOwner($request, $id);
    }

    /**
     * Get topics assigned to the authenticated user
     */
    public function myTopics()
    {
        return $this->agendaTopicService->myTopics();
    }
}
