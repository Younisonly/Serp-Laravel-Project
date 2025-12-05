<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductCategory;


class Product extends Model
{
    use HasFactory;

    // If your table name is exactly "products", no need to set $table.
    // protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
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
        'discount_dates' => 'array', // JSON column
        'warranty_start' => 'date',
        'warranty_end' => 'date',
        'discount_start' => 'date',
        'discount_end' => 'date',
        'stars_rate' => 'float',
        'price' => 'float',
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


    
    public function city() { return $this->belongsTo(Cities::class, 'city_id'); }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }

}
