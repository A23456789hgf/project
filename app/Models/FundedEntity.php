<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundedEntity extends Model
{
    protected $fillable = ['funding_source_id', 'name'];

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }
}
