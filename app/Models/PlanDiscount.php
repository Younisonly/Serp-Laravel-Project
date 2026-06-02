<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanDiscount extends Model
{
    use HasFactory;

    protected $table = 'plan_discounts';

    protected $fillable = [
        'plan_id',
        'amount',
        'percentage',
        'reason_title',
        'reason_subtitle',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(VerificationPlan::class, 'plan_id');
    }
}
