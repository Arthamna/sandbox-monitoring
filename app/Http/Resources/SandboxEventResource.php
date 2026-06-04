<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SandboxEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sandbox_id' => $this->sandbox_id,
            'actor_user_id' => $this->actor_user_id,
            'event_type' => $this->event_type,
            'payload_example' => $this->payload_example,
            'created_at' => $this->created_at,

            'actor' => UserResource::make($this->whenLoaded('actor')),
        ];
    }
}
