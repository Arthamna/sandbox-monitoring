<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponse
{
    public static function success(
        string $message,
        mixed $data = null,
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            $data = $data->resolve(request());
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => (object) $meta,
        ], $status);
    }

    public static function error(
        string $message,
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}