<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationPlan extends Model
{
    use HasFactory;

    protected $table = 'verification_plans';

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'features',
        'badge_preview',
        'display_order',
        'is_active',
        'is_recommended',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'badge_preview' => 'array',
        'is_active' => 'boolean',
        'is_recommended' => 'boolean',
    ];

    public function discounts()
    {
        return $this->hasMany(PlanDiscount::class, 'plan_id');
    }

    public function activeDiscounts()
    {
        return $this->hasMany(PlanDiscount::class, 'plan_id')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            });
    }

    public function subscriptions()
    {
        return $this->hasMany(ShopSubscription::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('display_order');
    }
}
