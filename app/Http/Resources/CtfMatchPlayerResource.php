<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CtfMatchPlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => UserResource::make($this),
            'side' => $this->pivot->side ?? null,
            'score' => (int) ($this->pivot->score ?? 0),
            'joined_at' => $this->pivot->created_at ?? null,
        ];
    }
}