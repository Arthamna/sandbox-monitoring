<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SandboxResource extends JsonResource
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
            'owner_user_id' => $this->owner_user_id,
            'proxmox_node_id' => $this->proxmox_node_id,
            'kind' => $this->kind,
            'type' => $this->type,
            'status' => $this->status,
            'vmid' => $this->vmid,
            'ip_address' => $this->ip_address,
            'config' => $this->config,
            'proxmox_upid' => $this->proxmox_upid,
            'created_at' => $this->created_at,
            'started_at' => $this->started_at,
            'stopped_at' => $this->stopped_at,

            'proxmox_config' => $this->when(isset($this->resource->proxmox_config), $this->resource->proxmox_config ?? null),

            'owner' => UserResource::make($this->whenLoaded('owner')),
            'node' => ProxmoxNodeResource::make($this->whenLoaded('node')),
        ];

    }
}
