<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCreatorTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntityOfficial extends Model
{
    use Auditable, HasCreatorTracking, HasFactory;

    protected $table = 'entity_officers';

    protected $fillable = [
        'entity_type',
        'internal_entity_id',
        'authority_id',
        'admin_name',
        'job_title',
        'creator_username',
        'creator_entity_id',
    ];

    public function internalEntity()
    {
        return $this->belongsTo(InternalEntity::class, 'internal_entity_id');
    }

    public function authority()
    {
        return $this->belongsTo(Authority::class, 'authority_id');
    }

    /**
     * Get the name of the entity based on type.
     */
    public function getEntityNameAttribute()
    {
        if ($this->entity_type === 'internal') {
            return $this->internalEntity?->name;
        }

        return $this->authority?->agency_name;
    }
}
