<?php

namespace App\Http\Controllers;

use App\Http\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        return $this->userService->index($request);
    }

    /**
     * Display a listing of users for public access
     */
    public function indexPublic(Request $request)
    {
        return $this->userService->indexPublic($request);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        return $this->userService->store($request);
    }

    /**
     * Display the specified user by id
     */
    public function show($id)
    {
        return $this->userService->show($id);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        return $this->userService->update($request, $id);
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id)
    {
        return $this->userService->destroy($request, $id);
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request)
    {
        return $this->userService->profile($request);
    }

    /**
     * Update current user profile
     */
    public function updateProfile(Request $request)
    {
        return $this->userService->updateProfile($request);
    }

    /**
     * Update current user password
     */
    public function updatePassword(Request $request)
    {
        return $this->userService->updatePassword($request);
    }

    /**
     * Lock a user (set is_active to false)
     */
    public function lockUser($id)
    {
        return $this->userService->lockUser($id);
    }

    /**
     * Unlock a user (set is_active to true)
     */
    public function unlockUser($id)
    {
        return $this->userService->unlockUser($id);
    }
}
