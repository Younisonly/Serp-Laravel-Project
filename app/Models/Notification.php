<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'owner_id',
        'owner_type',
        'title',
        'content',
        'sender',
        'type',
        'action_type',
        'data',
        'is_read',
        'target_type',
        'target_id',
        'navigation_type',
        'metadata',
    ];

    protected $casts = [
        'data' => 'array',
        'metadata' => 'array',
        'is_read' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function form()
    {
        return $this->belongsTo(NotificationForm::class, 'type', 'key');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('owner_id', $userId)->where('owner_type', 'user');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true, 'updated_at' => now()]);
        }
    }
}
