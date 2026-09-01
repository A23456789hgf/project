<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValueChainFinancing extends Model
{
    protected $fillable = [
        'value_chain_id',
        'value_chain_financing_type_id',
        'entity_type',
        'internal_entity_id',
        'authority_id',
        'created_by',
    ];

    public function valueChain()
    {
        return $this->belongsTo(ValueChain::class);
    }

    public function financingType()
    {
        return $this->belongsTo(ValueChainFinancingType::class, 'value_chain_financing_type_id');
    }

    public function internalEntity()
    {
        return $this->belongsTo(InternalEntity::class);
    }

    public function authority()
    {
        return $this->belongsTo(Authority::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getEntityNameAttribute()
    {
        if ($this->entity_type === 'internal' && $this->internalEntity) {
            return $this->internalEntity->name;
        } elseif ($this->entity_type === 'external' && $this->authority) {
            return $this->authority->agency_name ?? $this->authority->name;
        }

        return 'غير محدد';
    }

    public function getParentEntityNameAttribute()
    {
        if ($this->entity_type === 'internal' && $this->internalEntity && $this->internalEntity->parent) {
            return $this->internalEntity->parent->name;
        } elseif ($this->entity_type === 'external' && $this->authority && $this->authority->parent) {
            return $this->authority->parent->agency_name ?? $this->authority->parent->name;
        }

        return 'ـ';
    }
}
