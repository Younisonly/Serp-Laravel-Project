<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $comment_id
 * @property int $liker_id
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CommentReaction extends Model
{
    use HasFactory;

    protected $table = 'comment_reactions';
    protected $fillable = [
        'comment_id',
        'liker_id',
        'value',
    ];

    // The comment being reacted to
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    // The user who liked/disliked
    public function user()
    {
        return $this->belongsTo(User::class, 'liker_id');
    }
}