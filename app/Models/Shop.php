<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        'business_type',
        'subcategory',
        'price_range',
        'amenities',
        'operating_hours',
        'address',
        'lat',
        'lng',
        'followers_number',
        'raters_number',
        'stars_rate',
        'views',
        'joined_at',
        'points',
        'is_featured',
        'verification_files',
        'tax_number',
        'commercial_registration',
        'license_number',
        'terms_accepted_at',
        'terms_ip',
        'verification_status',
        'verification_note',
        'reviewed_at',
        'reviewed_by',
        'suspended_at',
        'suspension_reason',
        'contact_email',
        'website',
        'instagram',
        'X',
        'whatsapp',
        'facebook',
        'social_media',
        'delivery_radius_km',
        'min_order_amount',
        'delivery_fee',
        'estimated_delivery_min',
        'estimated_delivery_max',
        'seating_capacity',
        'has_takeout',
        'has_delivery',
        'has_reservations',
        'has_outdoor_seating',
        'appointment_required',
        'service_duration_min',
        'payment_methods',
        'languages',
        'tags',
    ];

    protected $casts = [
        'open_at'            => 'datetime:H:i:s',
        'close_at'           => 'datetime:H:i:s',
        'joined_at'          => 'datetime',
        'terms_accepted_at'  => 'datetime',
        'reviewed_at'        => 'datetime',
        'suspended_at'       => 'datetime',
        'verification_files' => 'array',
        'amenities'          => 'array',
        'operating_hours'    => 'array',
        'social_media'       => 'array',
        'payment_methods'    => 'array',
        'languages'          => 'array',
        'tags'               => 'array',
        'lat'                => 'decimal:8',
        'lng'                => 'decimal:8',
        'stars_rate'         => 'float',
        'points'             => 'float',
        'is_featured'        => 'boolean',
        'has_takeout'        => 'boolean',
        'has_delivery'       => 'boolean',
        'has_reservations'   => 'boolean',
        'has_outdoor_seating'=> 'boolean',
        'appointment_required' => 'boolean',
        'delivery_radius_km' => 'float',
        'min_order_amount'   => 'float',
        'delivery_fee'       => 'float',
    ];

    protected static function booted(): void
    {
        static::saved(function ($shop) {
            Cache::forget("shop_{$shop->id}");
            Cache::forget("shop_owner_{$shop->owner_id}");
        });

        static::deleted(function ($shop) {
            Cache::forget("shop_{$shop->id}");
            Cache::forget("shop_owner_{$shop->owner_id}");
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'shop_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'owner_id')
                    ->where('table', 'shops');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(Image::class, 'owner_id')
                    ->where('table', 'shops')
                    ->where('is_primary', true)
                    ->take(1);
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

    public function likes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Follower::class, 'followed_shop_id');
    }

    public function scopeOpenNow($query)
    {
        $now = now()->format('H:i:s');
        $today = now()->format('l');
        return $query->where('status', 'open')
            ->where('open_at', '<=', $now)
            ->where('close_at', '>=', $now)
            ->where(function ($q) use ($today) {
                $q->whereNull('days_open')
                  ->orWhere('days_open', 'like', "%{$today}%");
            });
    }

    public function scopeNearby($query, float $lat, float $lng, float $radius = 50)
    {
        $latDelta = $radius / 111.0;
        $lngDelta = $radius / (111.0 * cos(deg2rad($lat)));

        $minLat = $lat - $latDelta;
        $maxLat = $lat + $latDelta;
        $minLng = $lng - $lngDelta;
        $maxLng = $lng + $lngDelta;

        $latRad = deg2rad($lat);
        $lngRad = deg2rad($lng);

        $haversine = "(6371 * ACOS(
            COS(RADIANS(lat)) * COS($latRad) *
            COS(RADIANS(lng) - $lngRad) +
            SIN(RADIANS(lat)) * SIN($latRad)
        ))";

        return $query->whereNotNull('lat')
            ->whereNotNull('lng')
            ->whereBetween('lat', [$minLat, $maxLat])
            ->whereBetween('lng', [$minLng, $maxLng])
            ->select('*', DB::raw("{$haversine} AS distance"))
            ->having('distance', '<', $radius);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->logo) return null;
        $pathInfo = pathinfo($this->logo);
        return $pathInfo['dirname'] . '/thumbs/' . $pathInfo['basename'];
    }

    public function getIsOpenAttribute(): bool
    {
        if ($this->status !== 'open') return false;

        $now = now();
        $openAt = $this->open_at;
        $closeAt = $this->close_at;

        if (!$openAt || !$closeAt) return true;

        $openTime = strtotime($openAt);
        $closeTime = strtotime($closeAt);
        $currentTime = strtotime($now->format('H:i:s'));

        if ($openTime === false || $closeTime === false) return true;

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }
}
