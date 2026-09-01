<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the projects that belong to this target category.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
