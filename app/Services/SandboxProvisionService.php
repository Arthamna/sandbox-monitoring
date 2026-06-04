<?php

namespace App\Services;

use App\Models\Sandbox;
use Illuminate\Support\Facades\DB;

class SandboxProvisionService
{
    public function __construct(
        protected LoadBalancerService $loadBalancerService
    ) {
    }

    public function provision(array $data): Sandbox
    {
        return DB::transaction(function () use ($data) {
            $node = $this->loadBalancerService->bestNode();

            return Sandbox::create([
                'owner_user_id'   => $data['owner_user_id'],
                'proxmox_node_id' => $node->id,
                'kind'            => $data['kind'],
                'status'          => 'queued',
                'config'          => $data['config'] ?? [],
            ]);
        });
    }
}