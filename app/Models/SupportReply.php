<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportReply extends Model
{
    use HasFactory;

    protected $table = 'support_replies';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'is_admin',
        'is_system',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_system' => 'boolean',
    ];

    protected $with = ['user'];

    public function ticket()
    {
        return $this->belongsTo(Support::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
