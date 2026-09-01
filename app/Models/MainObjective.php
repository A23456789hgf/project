<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_request_id',
        'objective',
    ];

    /**
     * العلاقة مع المشروع.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * العلاقة مع الأهداف الفرعية (إن وجدت).
     *
     * يمكنك تعديل هذا القسم حسب ما إذا كانت هناك أهداف فرعية مرتبطة.
     */
}
