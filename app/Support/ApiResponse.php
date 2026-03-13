<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return static::success($data, $message, 201);
    }

    public static function error(string $message, int $status = 500, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return static::error($message, 404);
    }

    public static function validationError(mixed $errors, string $message = 'Validation failed'): JsonResponse
    {
        return static::error($message, 422, $errors);
    }

    public static function serverError(string $message = 'An unexpected error occurred'): JsonResponse
    {
        return static::error($message, 500);
    }

    /**
     * Return a paginated response — flattens resource collection + pagination meta
     * into a single consistent envelope instead of nesting data.data.
     */
    public static function paginated(AnonymousResourceCollection $resource, string $message = 'Success'): JsonResponse
    {
        $payload = $resource->toResponse(request())->getData(true);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $payload['data'],
            'meta'    => [
                'current_page' => $payload['meta']['current_page'],
                'last_page'    => $payload['meta']['last_page'],
                'per_page'     => $payload['meta']['per_page'],
                'total'        => $payload['meta']['total'],
                'from'         => $payload['meta']['from'],
                'to'           => $payload['meta']['to'],
            ],
            'links' => $payload['links'],
        ]);
    }
}
