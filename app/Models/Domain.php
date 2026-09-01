<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use Auditable, HasCreatorTracking, HasDomainScope;

    protected $table = 'domains';

    // الحقول التي يمكن ملؤها جماعياً (mass assignable)
    protected $fillable = [
        'status',
        'name',
        'is_active',
        'created_by_entity',
        'created_by_user_id',
        'creator_username',
        'creator_entity_id',
    ];

    /**
     * العلاقة مع المجالات الفرعية (Subdomains)
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function subdomains()
    {
        return $this->hasMany(Subdomain::class);
    }
}
