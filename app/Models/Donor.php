<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    use HasActiveScope, HasFactory;

    // تحديد اسم الجدول إذا كان غير مطابق للاسم الافتراضي (اختياري هنا)
    protected $table = 'donors';

    // تحديد الحقول القابلة للتعبئة
    protected $fillable = ['name', 'is_active'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
