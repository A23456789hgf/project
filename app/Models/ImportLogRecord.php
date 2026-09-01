<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLogRecord extends Model
{
    protected $fillable = [
        'import_log_id',
        'model_type',
        'model_id',
        'action',
        'original_data',
    ];

    protected $casts = [
        'original_data' => 'array',
    ];

    public function importLog()
    {
        return $this->belongsTo(ImportLog::class);
    }

    public function model()
    {
        return $this->morphTo();
    }
}
