<?php

namespace App\Services;

use App\Models\Sandbox;
use App\Models\SandboxEvent;

class SandboxEventService
{
    public function record(Sandbox $sandbox, array $data): SandboxEvent
    {
        return $sandbox->events()->create([
            'actor_user_id' => $data['actor_user_id'] ?? null,
            'event_type' => $data['event_type'],
            'payload' => json_encode($data['payload']),
        ]);
    }
}