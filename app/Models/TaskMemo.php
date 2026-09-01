<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskMemo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_id',
        'created_by',
        'subject',
        'content',
        'signed_by',
        'signed_at',
        'signature_path',
        'correspondence_id',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function correspondence()
    {
        return $this->belongsTo(Correspondence::class);
    }
}
