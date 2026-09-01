<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubRouter extends Model
{
    use HasFactory;

    protected $fillable = ['main_router_id', 'sub_router', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mainRouter()
    {
        return $this->belongsTo(MainRouter::class);
    }

    /**
     * Get the projects that belong to this sub router.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
