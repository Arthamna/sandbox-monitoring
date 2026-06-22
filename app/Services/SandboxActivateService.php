<?php

namespace App\Services;

use App\Models\Sandbox;
use Illuminate\Support\Facades\DB;

class SandboxActivateService
{
    public function __construct(
        protected ProxmoxApiService $proxmoxApiService
    ) {
    }

    /**
     * Activate a provisioned sandbox by starting the VM/container on Proxmox.
     *
     * @param  Sandbox  $sandbox  The sandbox to activate.
     * @param  string   $type     The virtualization type ('qemu' or 'lxc').
     * @return Sandbox
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function activate(Sandbox $sandbox, string $type = null): Sandbox
    {
        return DB::transaction(function () use ($sandbox, $type) {
            $sandbox->load('node');

            $actualType = $type ?? $sandbox->type ?? 'lxc';

            $upid = $this->proxmoxApiService->startVm(
                $sandbox->node->node_name,
                $sandbox->vmid,
                $actualType
            );

            $sandbox->update([
                'proxmox_upid' => $upid,
                'status'       => 'active',
                'started_at'   => now(),
            ]);

            return $sandbox->fresh();
        });
    }
}
