<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CtfMatch extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'challenge_key', 
        'mode', 
        'status', 
        'winner_user_id',
        'started_at', 
        'ended_at',
    ];

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ctf_match_players')
            ->withPivot(['side', 'score', 'created_at']);
    }
}
