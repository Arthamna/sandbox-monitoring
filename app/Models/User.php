<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasUuids, HasApiTokens;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'username',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    get result faster on user side : $user->sandboxes != $sandbox->owner
    */
    
    public function sandboxes()
    {
        return $this->hasMany(Sandbox::class, 'owner_user_id');
    }

    public function ctfMatches()
    {
        return $this->belongsToMany(CtfMatch::class, 'ctf_match_players')
            ->withPivot(['side', 'score', 'created_at']);
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

}
