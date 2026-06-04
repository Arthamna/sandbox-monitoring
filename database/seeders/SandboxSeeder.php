<?php

namespace Database\Seeders;

use App\Models\ProxmoxNode;
use App\Models\Sandbox;
use App\Models\User;
use App\Services\CtfLogger;
use Illuminate\Database\Seeder;

class SandboxSeeder extends Seeder
{
    public function run(): void
    {
        $players = User::where('role', 'player')->get();
        $nodes = ProxmoxNode::all();

        if ($players->isEmpty() || $nodes->isEmpty()) {
            CtfLogger::warning('seeder:SandboxSeeder', 'Skipped – no players or nodes found.');
            return;
        }

        $statuses = ['queued', 'active', 'active'];
        $kinds = ['lxc', 'qemu', 'lxc'];
        $created = 0;

        foreach ($players as $index => $player) {
            $node = $nodes[$index % $nodes->count()];

            Sandbox::create([
                'owner_user_id' => $player->id,
                'proxmox_node_id' => $node->id,
                'kind' => $kinds[$index] ?? 'lxc',
                'status' => $statuses[$index] ?? 'queued',
                'vmid' => $statuses[$index] === 'active' ? 100 + $index : null,
                'ip_address' => $statuses[$index] === 'active' ? "10.10.{$index}.2" : null,
                'config' => [
                    'cpu' => 2,
                    'memory_mb' => 2048,
                    'disk_gb' => 20,
                    'template' => 'ubuntu-22.04-ctf',
                ],
                'started_at' => $statuses[$index] === 'active' ? now()->subMinutes(30 - $index * 5) : null,
            ]);

            $created++;
        }

        CtfLogger::info('seeder:SandboxSeeder', "Seeded {$created} sandboxes.", [
            'statuses' => $statuses,
        ]);
    }
}
