<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\CtfLog;
use Illuminate\Http\Request;

class CtfLogController extends Controller
{
    /**
     * List all logs with optional filtering.
     */
    public function index(Request $request)
    {
        $query = CtfLog::query()->orderByDesc('created_at');

        if ($request->filled('level')) {
            $query->level($request->level);
        }

        if ($request->filled('source')) {
            $query->where('source', 'like', "%{$request->source}%");
        }

        $logs = $query->paginate(50);

        return ApiResponse::success('Logs berhasil diambil.', $logs);
    }
}
