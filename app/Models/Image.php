<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'table',       // e.g. 'shops', 'products'
        'owner_id',    // the related model's id
        'url',         // image URL
        'is_primary',  // true if this is the primary image
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the owning model of the image (polymorphic style).
     *
     * This assumes 'table' contains the related table name.
     */
    public function owner()
    {
        // You might want to use a true polymorphic relationship instead of manually storing 'table'
        return $this->belongsTo(
            $this->resolveOwnerModelClass(),
            'owner_id'
        );
    }

    /**
     * Resolve the model class name from the 'table' field.
     * Update this mapping according to your application needs.
     */
    protected function resolveOwnerModelClass()
    {
        $map = [
            'shops'    => \App\Models\Shop::class,
            'products' => \App\Models\Product::class,
            // Add more table => Model mappings here...
        ];

        return $map[$this->table] ?? null;
    }
}
