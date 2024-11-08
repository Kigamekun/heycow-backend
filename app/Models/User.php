<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'phone_number',
        'role',
        'gender',
        'bio',
        'avatar',
        'farm_id',
        'is_pengangon', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function farms()
    {
        return $this->hasOne(Farm::class, 'user_id', 'id');
    }

    // Di dalam model User
    public function updatePengangonStatus($status)
    {
        $this->update(['is_pengangon' => $status]);
    }

}
