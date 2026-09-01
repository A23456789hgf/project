<?php

// app/Models/Governorate.php

namespace App\Models;

use App\Traits\CachesDropdowns;
use App\Traits\HasActiveScope;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    use CachesDropdowns, HasActiveScope, HasDomainScope, HasFactory;

    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع المديريات (districts / directorates)
     */
    public function districts()
    {
        return $this->hasMany(Directorate::class, 'governorate_id');
    }

    /**
     * علاقة اختيارية مع المديريات باستخدام الاسم الموجود في قاعدة البيانات
     * يمكنك استخدام directorates() بدلاً من districts() إذا كان الجدول اسمه directorates
     */
    public function directorates()
    {
        return $this->hasMany(Directorate::class, 'governorate_id');
    }

    /**
     * Helper method to check if the governorate is active
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Scope to get only active governorates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
