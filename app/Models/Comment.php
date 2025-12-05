<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Add this
use App\Models\CommentReaction; // Add this

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';
    protected $guarded = [];

    // Add the fields that can be filled
    protected $fillable = [
        'commenter_id',
        'commentable_type',
        'commented_id',
        'content',
        'status',
        'reply_content',
        'replyer_type',
        'replied_at'
    ];

    // Cast dates
    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // The user who made the comment
    public function commenter()
    {
        return $this->belongsTo(User::class, 'commenter_id');
    }

    // Reactions (likes/dislikes)
    public function reactions()
    {
        return $this->hasMany(CommentReaction::class, 'comment_id');
    }

    // Accessor: Return reply as structured data (without DB query)
    public function getReplyAttribute()
    {
        if ($this->reply_content) {
            return [
                'content' => $this->reply_content,
                'replyer' => $this->replyer_type ?? 'admin', // default
                'created_at' => $this->replied_at ?? $this->updated_at,
            ];
        }

        return null;
    }

    // Safe accessors for user data
    public function getUserNameAttribute()
    {
        return $this->commenter?->name ?? 'مستخدم محذوف';
    }

    public function getUserImageAttribute()
    {
        return $this->commenter && $this->commenter->image
            ? $this->commenter->image  // <-- raw value from users table
            : '';
    }
    
}