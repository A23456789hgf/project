<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDiscussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'parent_id',
        'message',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(TaskDiscussion::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(TaskDiscussion::class, 'parent_id');
    }
}
