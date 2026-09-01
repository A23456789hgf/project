<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'syncable_type',
        'syncable_id',
        'sync_type',
        'status',
        'message',
        'response_data',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];

    public function syncable()
    {
        return $this->morphTo();
    }
}
