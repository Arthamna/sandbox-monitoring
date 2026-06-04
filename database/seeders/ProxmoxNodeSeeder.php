<?php

namespace Database\Seeders;

use App\Models\ProxmoxNode;
use App\Services\CtfLogger;
use Illuminate\Database\Seeder;

class ProxmoxNodeSeeder extends Seeder
{
    public function run(): void
    {
        $nodes = [
            [
                'username' => 'node-jkt-01',
                'api_url' => 'https://pve-jkt-01.internal:8006/api2/json',
                'status' => 'online',
                'weight' => 100,
                'last_seen_at' => now(),
                'capacity' => [
                    'max_cpu' => 32,
                    'max_memory_gb' => 128,
                    'max_sandboxes' => 50,
                ],
            ],
            [
                'username' => 'node-sgp-01',
                'api_url' => 'https://pve-sgp-01.internal:8006/api2/json',
                'status' => 'online',
                'weight' => 80,
                'last_seen_at' => now(),
                'capacity' => [
                    'max_cpu' => 16,
                    'max_memory_gb' => 64,
                    'max_sandboxes' => 30,
                ],
            ],
            [
                'username' => 'node-usw-01',
                'api_url' => 'https://pve-usw-01.internal:8006/api2/json',
                'status' => 'online',
                'weight' => 60,
                'last_seen_at' => now(),
                'capacity' => [
                    'max_cpu' => 8,
                    'max_memory_gb' => 32,
                    'max_sandboxes' => 20,
                ],
            ],
        ];

        foreach ($nodes as $data) {
            ProxmoxNode::create($data);
        }

        CtfLogger::info('seeder:ProxmoxNodeSeeder', 'Seeded ' . count($nodes) . ' proxmox nodes.', [
            'node_names' => array_column($nodes, 'username'),
        ]);
    }
}
