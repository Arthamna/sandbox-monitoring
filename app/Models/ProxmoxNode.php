<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProxmoxNode extends Model
{
    use HasUuids;
    //
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'api_url',
        'status', // in ['online', 'offline'],
        'weight',
        'last_seen_at',
        'capacity',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'capacity' => 'array',
    ];
}
