<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class LoadBalancerResponse
{
    public static function nodes(mixed $nodes): JsonResponse
    {
        return ApiResponse::success('Daftar node berhasil diambil.', $nodes);
    }

    public static function nodeUpdated(mixed $node): JsonResponse
    {
        return ApiResponse::success('Node berhasil diperbarui.', $node);
    }

    public static function rebalance(array $result): JsonResponse
    {
        return ApiResponse::success('Rebalance selesai.', $result);
    }
}