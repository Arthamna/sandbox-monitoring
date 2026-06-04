<?php

namespace App\Services;

use App\Models\Sandbox;
use Illuminate\Support\Facades\DB;

class SandboxActivateService
{
    /**
     * Activate a provisioned sandbox with the vmid and ip_address
     * returned by Proxmox after the VM is spun up.
     */
    public function activate(Sandbox $sandbox, array $data): Sandbox
    {
        return DB::transaction(function () use ($sandbox, $data) {
            $sandbox->update([
                'vmid'       => $data['vmid'],
                'ip_address' => $data['ip_address'],
                'status'     => 'active',
                'started_at' => now(),
            ]);

            return $sandbox->fresh();
        });
    }
}
