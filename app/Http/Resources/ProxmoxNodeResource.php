<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProxmoxNodeResource extends JsonResource
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
            'name' => $this->name,
            'node_name' => $this->node_name,
            'api_url' => $this->api_url,
            'status' => $this->status,
            'weight' => $this->weight,
            'last_seen_at' => $this->last_seen_at,
            'capacity' => $this->capacity,
            'created_at' => $this->created_at,
        ];
    }
}
