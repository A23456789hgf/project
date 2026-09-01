<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_history_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that read the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the activity history (notification)
     */
    public function activityHistory()
    {
        return $this->belongsTo(ProjectActivityHistory::class, 'activity_history_id');
    }
}
