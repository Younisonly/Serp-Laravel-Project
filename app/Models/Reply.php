<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $report_id
 * @property string $content
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Reply extends Model
{
    use HasFactory;

    protected $table = 'replies';
    protected $fillable = [
        'report_id',
        'content',
        'status',
    ];

    // The report this reply belongs to
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}