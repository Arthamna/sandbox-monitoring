<?php

namespace App\Services;

use App\Models\ProxmoxNode;
use App\Models\Sandbox;

class NodeScoringService{
    public function score(ProxmoxNode $node): array {
        $active = Sandbox::query()
            ->where('proxmox_node_id', $node->id)
            ->where('status', 'active')
            ->count();

        $pending = Sandbox::query()
            ->where('proxmox_node_id', $node->id)
            ->where('status', ['queued', 'provisioning'])
            ->count();

        $score = $node->status === 'online' 
            ? ((int) $node->weight + $active - $pending) 
            : -999;
        
        return [
            'id' => $node->id,
            'username' => $node->username,
            'api_url' => $node->api_url,
            'status' => $node->status,
            'weight' => $node->weight,
            'capacity' => $node->capacity,
            'active_sandboxes' => $active,
            'pending_sandboxes' => $pending,
            'score' => $score,
            'last_seen_at' => $node->last_seen_at,
        ];
                
    }
}