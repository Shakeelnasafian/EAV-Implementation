<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\RegisterAction;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Register a new user and return a created API response with an access token and user resource.
     *
     * @param \App\Http\Requests\RegisterRequest $request Validated registration input.
     * @param \App\Actions\Auth\RegisterAction $action Performs registration and returns an array with keys `token` (string) and `user` (User model).
     * @return \Illuminate\Http\JsonResponse JSON response containing `token`, `user` (wrapped in UserResource), and a success message.
     */
    public function register(RegisterRequest $request, RegisterAction $action)
    {
        $result = $action->handle($request->validated());

        return ApiResponse::created([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ], 'User registered successfully');
    }

    /**
     * Authenticate a user and return an API response with an access token and user data.
     *
     * @param LoginRequest $request Incoming validated login credentials (`email` and `password`).
     * @param LoginAction $action Handles authentication and returns an array with `token` and `user` on success.
     * @return \Illuminate\Http\Response An API response containing `token` and `user` on successful authentication, or an error response with HTTP status 401 when credentials are invalid.
     */
    public function login(LoginRequest $request, LoginAction $action)
    {
        $result = $action->handle($request->only('email', 'password'));

        if (!$result) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        return ApiResponse::success([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ], 'Login successful');
    }

    /**
     * Return the currently authenticated user wrapped in a UserResource inside a success ApiResponse.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request (used to obtain the authenticated user).
     * @return \App\Http\Responses\ApiResponse An API success response containing the authenticated user as a `UserResource`.
     */
    public function user(Request $request)
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    /**
     * Revoke the authenticated user's current token and return a standardized success response.
     *
     * @param Request $request The incoming HTTP request; its authenticated user’s token will be revoked.
     * @return mixed An API response with no data and the message "Successfully logged out".
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return ApiResponse::success(null, 'Successfully logged out');
    }
}
