<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainRouter extends Model
{
    // اسم الجدول (اختياري لأن Laravel يستنتجه من اسم الموديل)
    protected $table = 'main_routers';

    // الحقول المسموح بالإدخال الجماعي لها
    protected $fillable = [
        'main_router',
        'is_active',
    ];

    // إذا أردت أن يكون is_active دائماً Boolean
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subRouters()
    {
        return $this->hasMany(SubRouter::class);
    }

    /**
     * Get the projects that belong to this main router.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
