<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\RegisterAction;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/register',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['first_name', 'last_name', 'email', 'password'],
            properties: [
                new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                new OA\Property(property: 'last_name',  type: 'string', example: 'Doe'),
                new OA\Property(property: 'email',      type: 'string', format: 'email'),
                new OA\Property(property: 'password',   type: 'string', minLength: 6),
            ]
        )),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 201, description: 'User registered successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function register(RegisterRequest $request, RegisterAction $action)
    {
        $result = $action->handle($request->validated());

        return ApiResponse::created([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ], 'User registered successfully');
    }

    #[OA\Post(
        path: '/login',
        summary: 'Login and receive access token',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email',    type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string'),
            ]
        )),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Login successful'),
            new OA\Response(response: 401, description: 'Invalid credentials'),
        ]
    )]
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

    #[OA\Get(
        path: '/user',
        summary: 'Get authenticated user profile',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'User profile'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function user(Request $request)
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Revoke current access token',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logged out successfully'),
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return ApiResponse::success(null, 'Successfully logged out');
    }
}
