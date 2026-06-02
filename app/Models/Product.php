<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductCategory;
use App\Models\Like;


class Product extends Model
{
    use HasFactory;

    // If your table name is exactly "products", no need to set $table.
    // protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'highlights',
        'specifications',
        'tags',
        'brand',
        'model_number',
        'color',
        'size',
        'material',
        'weight',
        'shipping_info',
        'return_policy',
        'barcode',
        'price',
        'shop_id',
        'views',
        'warranty',
        'warranty_start',
        'warranty_end',
        'warranty_description',
        'discount',
        'discount_start',
        'discount_end',
        'discount_dates',
        'stars_rate',
        'raters_number',
        'type_id',
    ];

    protected $casts = [
        'warranty' => 'boolean',
        'discount' => 'float',
        'discount_dates' => 'array',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
        'discount_start' => 'date',
        'discount_end' => 'date',
        'stars_rate' => 'float',
        'price' => 'float',
        'highlights' => 'array',
        'specifications' => 'array',
        'tags' => 'array',
        'weight' => 'float',
    ];

    // Relationships
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

public function category()
{
    return $this->belongsTo(ProductCategory::class, 'type_id');
}


    // app/Models/Product.php

public function primaryImage()
{
    return $this->hasOne(Image::class, 'owner_id')
        ->where('table', 'products')   // matches your migration's "table" column
        ->where('is_primary', 1);
}

public function images()
    {
        return $this->hasMany(Image::class, 'owner_id')
            ->where('table', 'products');
    }


    
    public function city() { return $this->belongsTo(cities::class, 'city_id'); }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }

    public function likes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
