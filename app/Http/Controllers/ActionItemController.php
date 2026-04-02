<?php

namespace App\Http\Controllers;

use App\Http\Services\ActionItemService;
use Illuminate\Http\Request;

class ActionItemController extends Controller
{
    public function __construct(private readonly ActionItemService $actionItemService)
    {
    }

    /**
     * List action items with optional filters: meeting_id, mom_entry_id, status, assigned_to.
     */
    public function index(Request $request)
    {
        return $this->actionItemService->index($request);
    }

    /**
     * Create an action item linked to a provided MoM entry.
     * - mom_entry_id must belong to the meeting where the user is the scheduler or an admin.
     * - If assigned_to is null, it is considered "for everyone" (stored as NULL).
     */
    public function store(Request $request)
    {
        return $this->actionItemService->store($request);
    }

    /**
     * Show a single action item
     */
    public function show($id)
    {
        return $this->actionItemService->show($id);
    }

    /**
     * Update an action item (owner scheduler/admin check).
     */
    public function update(Request $request, $id)
    {
        return $this->actionItemService->update($request, $id);
    }

    /**
     * Delete an action item (owner scheduler/admin check).
     */
    public function destroy($id)
    {
        return $this->actionItemService->destroy($id);
    }

    /**
     * Update only the status.
     */
    public function updateStatus(Request $request, $id)
    {
        return $this->actionItemService->updateStatus($request, $id);
    }

    /**
     * Assign or reassign an action item to a user.
     */
    public function assign(Request $request, $id)
    {
        return $this->actionItemService->assign($request, $id);
    }
}
