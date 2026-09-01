<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundingSource extends Model
{
    // السماح بالتعيين الجماعي فقط للحقل name
    protected $fillable = ['name', 'is_active'];

    public function fundedEntities(): HasMany
    {
        return $this->hasMany(FundedEntity::class);
    }
}
