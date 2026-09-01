<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectEntity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'entity_name',
    ];

    /**
     * Get the project that owns the entity.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
