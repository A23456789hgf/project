<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasActiveScope;
use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValueChainFinancingType extends Model
{
    use Auditable, HasActiveScope, HasCreatorTracking, HasDomainScope;

    protected $table = 'value_chain_financing_types';

    protected $fillable = [
        'name',
        'is_active',
        'import_batch',
        'created_by_entity',
        'created_by_user_id',
        'creator_username',
        'creator_entity_id',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
