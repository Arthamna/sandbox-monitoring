<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SandboxEvent extends Model
{
    public $timestamps = false;
    //
    
    protected $casts = [
        'payload_example' => 'array',
    ];

    protected $fillable = [
        'sandbox_id',
        'actor_user_id',
        'event_type',
        'payload',
    ];

    public function sandbox()
    {
        return $this->belongsTo(Sandbox::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }


}
