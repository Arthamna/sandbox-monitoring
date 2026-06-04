<?php

namespace Database\Seeders;

use App\Models\Sandbox;
use App\Models\SandboxEvent;
use App\Models\User;
use App\Services\CtfLogger;
use Illuminate\Database\Seeder;

class SandboxEventSeeder extends Seeder
{
    public function run(): void
    {
        $sandboxes = Sandbox::where('status', 'active')->get();
        $players = User::where('role', 'player')->get()->keyBy('id');

        if ($sandboxes->isEmpty()) {
            CtfLogger::warning('seeder:SandboxEventSeeder', 'Skipped – no active sandboxes found.');
            return;
        }

        $eventTemplates = [
            [
                'event_type' => 'process_start',
                'payload' => '{"pid":1234,"cmd":"python3 exploit.py","cwd":"/home/ctf"}',
            ],
            [
                'event_type' => 'file_access',
                'payload' => '{"path":"/etc/shadow","action":"read","user":"ctf"}',
            ],
            [
                'event_type' => 'network_connection',
                'payload' => '{"dst_ip":"10.10.0.1","dst_port":4444,"proto":"tcp","direction":"outbound"}',
            ],
            [
                'event_type' => 'alert',
                'payload' => '{"rule":"reverse_shell_detected","severity":"critical","detail":"Outbound TCP to port 4444"}',
            ],
            [
                'event_type' => 'file_access',
                'payload' => '{"path":"/flag.txt","action":"read","user":"ctf"}',
            ],
            [
                'event_type' => 'process_start',
                'payload' => '{"pid":5678,"cmd":"nmap -sV 10.10.0.0/24","cwd":"/home/ctf"}',
            ],
        ];

        $created = 0;

        foreach ($sandboxes as $sandbox) {
            foreach ($eventTemplates as $template) {
                SandboxEvent::create([
                    'sandbox_id' => $sandbox->id,
                    'actor_user_id' => $sandbox->owner_user_id,
                    'event_type' => $template['event_type'],
                    'payload' => $template['payload'],
                ]);

                $created++;
            }
        }

        CtfLogger::info('seeder:SandboxEventSeeder', "Seeded {$created} sandbox events.", [
            'event_types' => array_unique(array_column($eventTemplates, 'event_type')),
        ]);
    }
}
