<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Sandbox extends Model
{
    //
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'owner_user_id', 
        'proxmox_node_id', 
        'kind', 
        'type',
        'status',
        'vmid', 
        'ip_address', 
        'config', 
        'proxmox_upid',
        'started_at', 
        'stopped_at',
    ];

    protected $casts = [
        'config' => 'array',
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];
    
    /*

    readability purpose, make accessing object easier

    -> $user = User::find($vm->owner_user_id);
        echo $user->name;

    -> $vm = VirtualMachine::find($id);
        echo $vm->owner->name;
    
    */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function node()
    {
        return $this->belongsTo(ProxmoxNode::class, 'proxmox_node_id');
    }

    public function events()    
    {
        return $this->hasMany(SandboxEvent::class);
    }

}
