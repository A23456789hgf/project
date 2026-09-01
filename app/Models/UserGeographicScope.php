<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGeographicScope extends Model
{
    use HasFactory;

    protected $table = 'user_geographic_scopes';

    protected $fillable = ['user_id', 'governorate_id', 'directorate_id'];

    protected static function booted()
    {
        static::saved(function ($scope) {
            if ($scope->user) {
                $scope->user->clearDomainCache();
            }
        });

        static::deleted(function ($scope) {
            if ($scope->user) {
                $scope->user->clearDomainCache();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function directorate()
    {
        return $this->belongsTo(Directorate::class);
    }
}
