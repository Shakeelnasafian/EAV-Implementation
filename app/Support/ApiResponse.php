<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Create a standardized successful JSON HTTP response.
     *
     * The response payload contains `success` (true), `message`, and `data`. The HTTP status code sent with the response
     * is determined by `$status`.
     *
     * @param mixed  $data    The response payload data; may be null.
     * @param string $message A human-readable message describing the result.
     * @param int    $status  HTTP status code to send with the response (default 200).
     * @return JsonResponse   JSON response containing the standardized success payload.
     */
    public static function success(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Generate a 201 Created JSON response with a standard success payload.
     *
     * @param mixed $data Optional payload to include under the `data` key.
     * @param string $message Message describing the creation; used in the `message` key.
     * @return JsonResponse JSON response with structure `{ "success": true, "message": string, "data": mixed }` and HTTP status 201.
     */
    public static function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return static::success($data, $message, 201);
    }

    /**
     * Create a standardized JSON error response.
     *
     * @param string $message The error message to include in the response.
     * @param int $status The HTTP status code to send with the response.
     * @param mixed|null $errors Optional additional error details to include under the `errors` key.
     * @return \Illuminate\Http\JsonResponse JSON response with `success: false`, `message`, and optional `errors`, sent with the provided HTTP status.
     */
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

    /**
     * Create a 404 Not Found JSON error response.
     *
     * @param string $message The error message to include in the response payload.
     * @return JsonResponse The JSON response with `success` set to `false`, the provided message, and HTTP status 404.
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return static::error($message, 404);
    }

    /**
     * Return a 422 Unprocessable Entity JSON response that includes validation error details.
     *
     * The response payload follows the standard API format and includes `success: false`, the
     * provided message, and an `errors` key with the validation details.
     *
     * @param mixed  $errors  Validation error details to include in the response (e.g., array, MessageBag).
     * @param string $message Optional custom error message.
     * @return JsonResponse JSON response with the error payload and HTTP status 422.
     */
    public static function validationError(mixed $errors, string $message = 'Validation failed'): JsonResponse
    {
        return static::error($message, 422, $errors);
    }

    /**
     * Create a 500 Internal Server Error JSON response.
     *
     * @param string $message The error message included in the response.
     * @return JsonResponse JSON response with payload: ["success" => false, "message" => $message] and HTTP status 500.
     */
    public static function serverError(string $message = 'An unexpected error occurred'): JsonResponse
    {
        return static::error($message, 500);
    }
}
