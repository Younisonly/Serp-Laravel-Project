<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NotificationForm extends Model
{
    protected $table = 'notification_forms';

    protected $fillable = [
        'key',
        'category',
        'title_template',
        'body_template',
        'default_title',
        'default_body',
        'placeholders',
        'is_active',
        'icon',
        'action_type',
        'priority',
        'owner_type',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public static function getActiveCacheKey(): string
    {
        return 'notification_forms:active';
    }

    public static function getSingleCacheKey(string $key): string
    {
        return "notification_form:{$key}";
    }

    public static function allActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::getActiveCacheKey(), 3600, function () {
            return self::where('is_active', true)->get()->keyBy('key');
        });
    }

    public static function findByKey(string $key): ?self
    {
        return Cache::remember(self::getSingleCacheKey($key), 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::getActiveCacheKey());
    }

    public static function flushSingleCache(string $key): void
    {
        Cache::forget(self::getSingleCacheKey($key));
    }

    protected static function booted(): void
    {
        static::saved(function ($form) {
            $form::flushCache();
            $form::flushSingleCache($form->key);
        });
        static::deleted(function ($form) {
            $form::flushCache();
        });
    }
}
