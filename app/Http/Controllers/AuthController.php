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
    public function register(RegisterRequest $request, RegisterAction $action)
    {
        $result = $action->handle($request->validated());

        return ApiResponse::created([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ], 'User registered successfully');
    }

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

    public function user(Request $request)
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return ApiResponse::success(null, 'Successfully logged out');
    }
}
