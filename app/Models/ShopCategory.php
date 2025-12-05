<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopCategory extends Model
{
    use HasFactory;

    protected $table = 'shop_categories';

    protected $fillable = [
        'name',
        'provides', // 1-product, 2-service, 3-both
    ];
}
