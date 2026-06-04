<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivateSandboxRequest;
use App\Http\Requests\ProvisionSandboxRequest;
use App\Http\Requests\StoreSandboxRequest;
use App\Http\Resources\SandboxResource;
use App\Http\Responses\ApiResponse;
use App\Models\Sandbox;
use App\Services\SandboxActivateService;
use App\Services\SandboxProvisionService;
use Illuminate\Http\Request;

class SandboxController extends Controller
{
    public function store(ProvisionSandboxRequest $request, SandboxProvisionService $service)
    {
        $sandbox = $service->provision($request->validated());

        return ApiResponse::success('Sandbox berhasil diprovision.', SandboxResource::make($sandbox->load(['owner', 'node'])), 201);
    }

    public function activate(ActivateSandboxRequest $request, Sandbox $sandbox, SandboxActivateService $service)
    {
        $sandbox = $service->activate($sandbox, $request->validated());

        return ApiResponse::success('Sandbox berhasil diaktifkan.', SandboxResource::make($sandbox->load(['owner', 'node'])));
    }

    public function show(Sandbox $sandbox)
    {
        return ApiResponse::success('Sandbox berhasil diambil.', SandboxResource::make($sandbox->load(['owner', 'node'])));
    }

    public function stream(Sandbox $sandbox)
    {
        $sandbox->load(['owner', 'node']);

        // stream, so real-event
        return response()->stream(function () use ($sandbox) {
            $lastEventId = (int) ($sandbox->events()->max('id') ?? 0);

            echo "event: snapshot\n";
            echo 'data: ' . json_encode(
                (new SandboxResource($sandbox))->resolve(request())
            ) . "\n\n";
            // directly send value
            @ob_flush();
            @flush();
            
            //
            while (! connection_aborted()) {
                $events = $sandbox->events()
                    ->with('actor')
                    ->where('id', '>', $lastEventId)
                    ->orderBy('id')
                    ->get();

                foreach ($events as $event) {
                    $lastEventId = (int) $event->id;

                    echo "event: sandbox-event\n";
                    echo 'data: ' . json_encode(
                        (new \App\Http\Resources\SandboxEventResource($event))->resolve(request())
                    ) . "\n\n";
                }

                @ob_flush();
                @flush();

                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
