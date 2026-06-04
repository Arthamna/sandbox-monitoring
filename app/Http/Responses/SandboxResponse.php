<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class SandboxResponse
{
    public static function provisioned(mixed $sandbox): JsonResponse
    {
        return ApiResponse::success('Sandbox berhasil diprovision.', $sandbox, 201);
    }
}