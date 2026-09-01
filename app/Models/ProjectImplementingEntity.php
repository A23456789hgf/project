<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectImplementingEntity extends Model
{
    use HasFactory;

    // اسم الجدول
    protected $table = 'implementing_entities';

    // الحقول القابلة للتعبئة
    protected $fillable = [
        'project_id',
        'project_request_id',
        'authority_type',
        'authority_id',
        'internal_entity_id',
        'parent_id',
    ];

    /**
     * العلاقة مع المشروع
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * العلاقة مع الجهة
     */
    public function authority()
    {
        return $this->belongsTo(Authority::class, 'authority_id')->withoutGlobalScope('active_only');
    }

    /**
     * العلاقة مع الجهة الأب (لو فيه Parent)
     */
    public function parentAuthority()
    {
        return $this->belongsTo(Authority::class, 'parent_id')->withoutGlobalScope('active_only');
    }

    public function parent()
    {
        return $this->parentAuthority();
    }

    /**
     * العلاقة مع الجهة الداخلية (إذا كان النوع داخلي)
     */
    public function internalEntity()
    {
        return $this->belongsTo(InternalEntity::class, 'internal_entity_id')->withoutGlobalScope('active_only');
    }

    /**
     * العلاقة مع الجهة الداخلية الأب
     */
    public function parentInternalEntity()
    {
        return $this->belongsTo(InternalEntity::class, 'parent_id')->withoutGlobalScope('active_only');
    }

    /**
     * Accessor for entity_id
     */
    public function getEntityIdAttribute()
    {
        return $this->authority_type === 'internal' ? $this->internal_entity_id : $this->authority_id;
    }

    public function getInternalEntityIdAttribute()
    {
        return $this->attributes['internal_entity_id'] ?? null;
    }
}
