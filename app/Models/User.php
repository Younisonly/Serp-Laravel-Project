<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'image',
        'status',
        // removed 'date' — now using created_at
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verify_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ✅ Remove: public $timestamps = false;
    // Laravel will now auto-fill created_at and updated_at
}