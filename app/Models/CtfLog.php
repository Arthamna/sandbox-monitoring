<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CtfLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'level',
        'source',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeSource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
