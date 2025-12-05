<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $reporter_id
 * @property int $reported_id
 * @property string $report_table
 * @property string $content
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';
    protected $guarded = [];

    // The user who reported
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    // Replies to this report (from admin/moderator)
    public function replies()
    {
        return $this->hasMany(Reply::class, 'report_id');
    }
}