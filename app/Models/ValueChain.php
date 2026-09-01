<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValueChain extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'created_by',
    ];

    public function parent()
    {
        return $this->belongsTo(ValueChain::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ValueChain::class, 'parent_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the value chain financings for this value chain.
     */
    public function financings()
    {
        return $this->hasMany(ValueChainFinancing::class, 'value_chain_id');
    }

    /**
     * Get the participating entities for this value chain.
     */
    public function participatingEntities()
    {
        return $this->hasMany(ValueChainParticipatingEntity::class, 'value_chain_id');
    }
}
