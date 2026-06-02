<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follower extends Model
{
    protected $table = 'followers';

    protected $fillable = [
        'follower_id',
        'followed_shop_id',
    ];

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'followed_shop_id');
    }
}
