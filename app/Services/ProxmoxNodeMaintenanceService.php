<?php

namespace App\Services;

use App\Models\ProxmoxNode;

class ProxmoxNodeMaintenanceService
{
    public function sync(bool $dryRun = false): array
    {
        $updated = 0;

        $nodes = ProxmoxNode::query()->get();

        foreach ($nodes as $node) {
            $newStatus = $this->randomStatus();
            $newWeight = max(1, min(200, $node->weight + random_int(-10, 10)));

            if (! $dryRun) {
                $node->forceFill([
                    'status' => $newStatus,
                    'weight' => $newWeight,
                    'last_seen_at' => now(),
                ])->save();
            }

            $updated++;
        }

        return [
            'total' => $nodes->count(),
            'updated' => $updated,
            'dry_run' => $dryRun,
        ];
    }

    private function randomStatus(): string
    {
        $roll = random_int(1, 100);

        return match (true) {
            $roll <= 70 => 'online',
            $roll <= 90 => 'degraded',
            default => 'offline',
        };
    }
}