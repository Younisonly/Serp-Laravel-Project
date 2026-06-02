<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    use HasFactory;

    protected $table = 'support';

    protected $fillable = [
        'sender_id',
        'title',
        'content',
        'status',
        'hidden_from_sender',
    ];

    protected $casts = [
        'hidden_from_sender' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replies()
    {
        return $this->hasMany(SupportReply::class, 'ticket_id');
    }
}
