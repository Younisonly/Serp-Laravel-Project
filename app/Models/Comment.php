<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $fillable = [
        'parent_id',
        'commenter_id',
        'commentable_type',
        'commented_id',
        'content',
        'status',
        'reply_content',
        'replyer_type',
        'replied_at',
        'reports_count',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function commenter()
    {
        return $this->belongsTo(User::class, 'commenter_id');
    }

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class, 'comment_id');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reported_id')
            ->where('report_table', 'comments');
    }

    public function getReplyAttribute()
    {
        if ($this->reply_content) {
            return [
                'content' => $this->reply_content,
                'replyer' => $this->replyer_type ?? 'admin',
                'created_at' => $this->replied_at ?? $this->updated_at,
            ];
        }

        return null;
    }

    public function getUserNameAttribute()
    {
        return $this->commenter?->name ?? 'مستخدم محذوف';
    }

    public function getUserImageAttribute()
    {
        return $this->commenter && $this->commenter->image
            ? $this->commenter->image
            : '';
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
