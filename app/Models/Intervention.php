<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intervention extends Model
{
    use Auditable, HasCreatorTracking, HasDomainScope, HasFactory;

    protected $fillable = [
        'domain_id',
        'subdomain_id',
        'name',
        'is_active',
        'status',
        'created_by_entity',
        'created_by_user_id',
        'creator_username',
        'creator_entity_id',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function subdomain()
    {
        return $this->belongsTo(Subdomain::class);
    }
}
