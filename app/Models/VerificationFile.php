<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'url',
        'type',
        'status',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
