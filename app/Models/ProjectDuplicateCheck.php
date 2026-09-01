<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDuplicateCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name_input',
        'similarity_percentage',
        'matched_project_id',
        'matched_project_name',
        'match_level',
        'decision',
        'user_id',
        'input_details',
    ];

    protected $casts = [
        'similarity_percentage' => 'float',
        'input_details' => 'array',
    ];

    public function matchedProject()
    {
        return $this->belongsTo(Project::class, 'matched_project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
