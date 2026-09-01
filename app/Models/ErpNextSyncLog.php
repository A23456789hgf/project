<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErpNextSyncLog extends Model
{
    use HasFactory;

    protected $table = 'erpnext_sync_logs';

    protected $fillable = [
        'project_id',
        'url',
        'response_code',
        'response_body',
        'error_message',
        'erpnext_docname',
        'status',
    ];

    protected $casts = [
        'response_code' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
