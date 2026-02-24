<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // ✅ ALL fields that can be mass-assigned
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'image',
        'status',
        'type',
        // ✅ These columns already exist in DB - just add to $fillable:
        'country',
        'city',
        'gender',
        'birth_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verify_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
    ];
}