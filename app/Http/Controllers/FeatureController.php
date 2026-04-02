<?php

namespace App\Http\Controllers;

use App\Http\Services\FeatureService;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public function __construct(private readonly FeatureService $featureService)
    {
    }

    /**
     * Display a listing of features
     */
    public function index()
    {
        return $this->featureService->index();
    }

    /**
     * Store a newly created feature
     */
    public function store(Request $request)
    {
        return $this->featureService->store($request);
    }

    /**
     * Display the specified feature
     */
    public function show($id)
    {
        return $this->featureService->show($id);
    }
  
    /**
     * Remove the specified feature
     */
    public function destroy($id)
    {
        return $this->featureService->destroy($id);
    }

    /**
     * Attach feature to room
     */
    public function attachToRoom(Request $request, $id)
    {
        return $this->featureService->attachToRoom($request, $id);
    }

    /**
     * Detach feature from room
     */
    public function detachFromRoom(Request $request, $id)
    {
        return $this->featureService->detachFromRoom($request, $id);
    }
}
