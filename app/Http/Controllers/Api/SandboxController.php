<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvisionSandboxRequest;
use App\Http\Resources\SandboxResource;
use App\Http\Responses\ApiResponse;
use App\Models\Sandbox;
use App\Services\CtfLogger;
use App\Services\ProxmoxApiService;
use App\Services\SandboxActivateService;
use App\Services\SandboxProvisionService;

class SandboxController extends Controller
{
    public function index()
    {
        $sandboxes = Sandbox::with(['owner', 'node'])->orderByDesc('started_at')->paginate(20);

        return ApiResponse::success('Daftar sandbox berhasil diambil.', SandboxResource::collection($sandboxes));
    }

    public function store(ProvisionSandboxRequest $request, SandboxProvisionService $service)
    {
        $sandbox = $service->provision($request->validated());

        CtfLogger::info('admin:sandbox:provision', "Sandbox {$sandbox->id} berhasil diprovision oleh admin.", [
            'sandbox_id' => $sandbox->id,
            'admin_id' => $request->user()->id,
            'admin_username' => $request->user()->username,
            'owner_user_id' => $sandbox->owner_user_id,
            'kind' => $sandbox->kind,
            'vmid' => $sandbox->vmid,
            'node' => $sandbox->node->node_name ?? null,
        ]);

        return ApiResponse::success('Sandbox berhasil diprovision.', SandboxResource::make($sandbox->load(['owner', 'node'])), 201);
    }

    public function activate(Sandbox $sandbox, SandboxActivateService $service)
    {
        $sandbox = $service->activate($sandbox);

        CtfLogger::info('admin:sandbox:activate', "Sandbox {$sandbox->id} berhasil diaktifkan oleh admin.", [
            'sandbox_id' => $sandbox->id,
            'admin_id' => request()->user()->id,
            'admin_username' => request()->user()->username,
            'vmid' => $sandbox->vmid,
            'type' => $sandbox->type,
            'node' => $sandbox->node->node_name ?? null,
        ]);

        return ApiResponse::success('Sandbox berhasil diaktifkan.', SandboxResource::make($sandbox->load(['owner', 'node'])));
    }

    public function show(Sandbox $sandbox, ProxmoxApiService $proxmoxApiService)
    {
        $sandbox->load(['owner', 'node']);

        if ($sandbox->vmid && $sandbox->node) {
            $config = $proxmoxApiService->getVmConfig(
                $sandbox->node->node_name,
                $sandbox->vmid,
                'lxc'
            );
            $sandbox->setAttribute('proxmox_config', $config);
        }

        return ApiResponse::success('Sandbox berhasil diambil.', SandboxResource::make($sandbox));
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
