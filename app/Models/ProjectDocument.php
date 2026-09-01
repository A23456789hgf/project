<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProjectDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'document_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // السمات المحسوبة
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        } else {
            return $bytes.' bytes';
        }
    }

    public function getFileIconAttribute()
    {
        $mime = $this->mime_type;

        if (Str::contains($mime, 'pdf')) {
            return 'fa-file-pdf';
        }
        if (Str::contains($mime, 'word')) {
            return 'fa-file-word';
        }
        if (Str::contains($mime, 'excel') || Str::contains($mime, 'spreadsheet')) {
            return 'fa-file-excel';
        }
        if (Str::contains($mime, 'image')) {
            return 'fa-file-image';
        }
        if (Str::contains($mime, 'powerpoint') || Str::contains($mime, 'presentation')) {
            return 'fa-file-powerpoint';
        }

        return 'fa-file';
    }

    public function getDownloadUrlAttribute()
    {
        return route('projects.documents.download', [$this->project_id, $this->id]);
    }

    public function getPreviewUrlAttribute()
    {
        return route('projects.documents.show', [$this->project_id, $this->id]);
    }
}
