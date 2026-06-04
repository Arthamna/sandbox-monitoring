<?php

namespace App\Services;

use App\Models\ProxmoxNode;
use App\Models\Sandbox;
use Illuminate\Support\Collection;

class LoadBalancerService{

    public function __construct(
        protected NodeScoringService $nodeScoringService
    ){

    }

    public function nodes(): Collection
    {
        return ProxmoxNode::query()
            ->get()
            ->map(fn (ProxmoxNode $node) => $this->nodeScoringService->score($node))
            ->sortByDesc('score')
            ->values(); 
    }

    public function bestNode(): ProxmoxNode
    {
        $best = $this->nodes()->first();

        if (! $best || $best['score'] <= -999 ) {
            throw new \Exception('No nodes available');
        }

        return ProxmoxNode::findOrFail($best['id']);
    }

    public function rebalanceQueuedSandboxes(bool $dryRun = false): array {
        $moved = 0;
        $checked = 0;
        $changes = [];

        $sandboxes = Sandbox::query()
            ->where('status', 'queued')
            ->orderBy('started_at')
            ->get();

        foreach ($sandboxes as $sandbox) {
            $checked++;

            $bestNode = $this->bestNode();

            if ($sandbox->proxmox_node_id !== $bestNode->id) {
                $changes[] = [
                    'sandbox_id' => $sandbox->id,
                    'from' => $sandbox->proxmox_node_id,
                    'to' => $bestNode->id,
                ];

                // dryRun; testing
                if (! $dryRun) {
                    $sandbox->update([
                        'proxmox_node_id' => $bestNode->id,
                    ]);
                }

                $moved++;
            }
        }

        return [
            'moved' => $moved,
            'checked' => $checked,
            'dryRun' => $dryRun,
            'changes' => $changes
        ];
    }
}