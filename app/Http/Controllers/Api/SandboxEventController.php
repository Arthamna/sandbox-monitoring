<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSandboxEventRequest;
use App\Http\Resources\SandboxEventResource;
use App\Http\Responses\ApiResponse;
use App\Models\Sandbox;
use App\Services\SandboxEventService;
use Illuminate\Http\Request;

class SandboxEventController extends Controller
{
    public function store(
        StoreSandboxEventRequest $request,
        Sandbox $sandbox,
        SandboxEventService $service,
    ) {
        $event = $service->record($sandbox, $request->validated());

        return ApiResponse::success('Event berhasil disimpan.', SandboxEventResource::make($event->load('actor')), 201);
        
    }
}
