<?php

namespace App\Models;

use App\Traits\CachesDropdowns;
use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubArea extends Model
{
    use CachesDropdowns, HasActiveScope, HasFactory;

    protected $fillable = [
        'status', 'governorate_id', 'directorate_id', 'name', 'is_active', 'import_batch'];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function directorate()
    {
        return $this->belongsTo(Directorate::class);
    }

    public function villages()
    {
        return $this->hasMany(Village::class);
    }

    /**
     * Scope: filter sub-areas based on the logged-in user's role module_geo_scopes.
     * Use explicitly when needed, NOT as a global scope.
     */
    public function scopeVisibleToUser($query, $user = null)
    {
        $user = $user ?: auth()->user();
        if (! $user) {
            return $query->whereRaw('0=1');
        }

        $scope = $user->getModuleGeoScope('sub-areas');

        if ($scope === 'all') {
            return $query;
        }

        if ($scope === 'none') {
            return $query->whereRaw('0=1');
        }

        if ($scope === 'same_governorate') {
            $userGovId = $user->getAssignedGovernorateId();

            return $userGovId ? $query->where('governorate_id', $userGovId) : $query->whereRaw('1=1');
        }

        if ($scope === 'same_directorate') {
            $userDirId = $user->getAssignedDirectorateId();

            return $userDirId ? $query->where('directorate_id', $userDirId) : $query->whereRaw('1=1');
        }

        return $query->whereRaw('0=1');
    }
}
