<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'backlogo',
        'logo',
        'iconname',
        'open_at',
        'close_at',
        'days_open',
        'status',
        'owner_id',
        'category',
        'address',
        'lat',
        'lng',
        'followers_number',
        'raters_number',
        'stars_rate',
        'views',
        'joined_at',
        'points',

        /* verification block */
        'verification_files',
        'terms_accepted_at',
        'terms_ip',
        'verification_status',
        'verification_note',
        'reviewed_at',
        'reviewed_by',
        'suspended_at',
        'suspension_reason',

        /* contact / social */
        'contact_email',
        'website',
        'instagram',
        'X',
        'whatsapp',
        'facebook',
    ];

    protected $casts = [
        'open_at'            => 'datetime:H:i:s',
        'close_at'           => 'datetime:H:i:s',
        'joined_at'          => 'datetime',
        'terms_accepted_at'  => 'datetime',
        'reviewed_at'        => 'datetime',
        'suspended_at'       => 'datetime',
        'verification_files' => 'array',
        'lat'                => 'decimal:8',
        'lng'                => 'decimal:8',
        'stars_rate'         => 'float',
        'points'             => 'float',
        'category'           => 'boolean',
    ];

    /* ---------------- relationships ---------------- */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }



    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'owner_id')
                    ->where('table', 'shops');
    }
    
    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class, 'owner_id')
            ->where('table', 'shops');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function favorites()
{
    return $this->morphMany(Favorite::class, 'favorable');
}



}
