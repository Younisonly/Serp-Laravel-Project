<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopSubscription extends Model
{
    use HasFactory;

    protected $table = 'shop_subscriptions';

    protected $fillable = [
        'shop_id',
        'plan_id',
        'status',
        'started_at',
        'expires_at',
        'cancelled_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(VerificationPlan::class, 'plan_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
