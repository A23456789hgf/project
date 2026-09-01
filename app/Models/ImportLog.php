<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_name',
        'model_class',
        'file_name',
        'user_id',
        'status',
        'total_records',
        'successful_records',
        'failed_records',
        'errors',
        'started_at',
        'completed_at',
        'rolled_back_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function records()
    {
        return $this->hasMany(ImportLogRecord::class);
    }
}
