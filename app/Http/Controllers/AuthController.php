<?php
namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        return $this->authService->login($request);
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(Request $request)
    {
        return $this->authService->logout($request);
    }

    /**
     * Get the authenticated User
     */
    public function user(Request $request)
    {
        return $this->authService->user($request);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        return $this->authService->refresh($request);
    }
}
