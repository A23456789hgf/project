<?php

namespace App\Models;

use App\Traits\CachesDropdowns;
use App\Traits\HasActiveScope;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Directorate extends Model
{
    use CachesDropdowns, HasActiveScope, HasDomainScope, HasFactory;

    protected $fillable = [
        'status', 'governorate_id', 'name', 'is_active'];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function subAreas()
    {
        return $this->hasMany(SubArea::class, 'directorate_id');
    }
}
