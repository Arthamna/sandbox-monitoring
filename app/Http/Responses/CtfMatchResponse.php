<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class CtfMatchResponse
{
    public static function created(mixed $match): JsonResponse
    {
        return ApiResponse::success('Match berhasil dibuat.', $match, 201);
    }

    public static function joined(mixed $match): JsonResponse
    {
        return ApiResponse::success('Player berhasil join match.', $match);
    }

    public static function submitted(mixed $match): JsonResponse
    {
        return ApiResponse::success('Submission berhasil diproses.', $match);
    }

    public static function scoreboard(mixed $match): JsonResponse
    {
        return ApiResponse::success('Scoreboard berhasil diambil.', $match);
    }
}