<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CtfMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'challenge_key' => $this->challenge_key,
            'mode' => $this->mode,
            'status' => $this->status,
            'winner_user_id' => $this->winner_user_id,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,

            'winner' => UserResource::make($this->whenLoaded('winner')),
            'players' => CtfMatchPlayerResource::collection($this->whenLoaded('players')),
        ];
    }
}