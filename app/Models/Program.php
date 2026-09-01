<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasActiveScope;
use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use Auditable, HasActiveScope, HasCreatorTracking, HasDomainScope;

    protected $table = 'programs';  // هذا اسم جدول في قاعدة البيانات

    protected $fillable = [
        'status',
        'name',
        'is_active',
        'created_by_entity',
        'created_by_user_id',
        'creator_username',
        'creator_entity_id',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
