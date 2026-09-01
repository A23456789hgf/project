<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suggestion extends Model
{
    use Auditable, HasCreatorTracking, HasDomainScope, HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
        'gregorian_date',
        'hijri_date',
        'is_completed',
        'user_id',
        'entity_id',
        'governorate_id',
        'directorate_id',
        'geographic_scope_id',
        'administrative_scope_id',
        'creator_username',
        'creator_entity_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'gregorian_date' => 'date',
        'is_completed' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entity()
    {
        return $this->belongsTo(InternalEntity::class, 'entity_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function internalEntity()
    {
        return $this->belongsTo(InternalEntity::class, 'entity_id');
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function directorate()
    {
        return $this->belongsTo(Directorate::class);
    }
}
