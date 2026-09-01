<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_request_id',
        'governorate_id',
        'directorate_id',
        'sub_area_id',
        'village_id',
    ];

    /**
     * العلاقة مع المشروع (Project)
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * العلاقة مع المحافظة (Governorate)
     */
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    /**
     * العلاقة مع المديرية (Directorate)
     */
    public function directorate()
    {
        return $this->belongsTo(Directorate::class);
    }

    /**
     * العلاقة مع المنطقة الفرعية (SubArea)
     */
    public function subArea()
    {
        return $this->belongsTo(SubArea::class);
    }

    /**
     * العلاقة مع القرية (Village)
     */
    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
