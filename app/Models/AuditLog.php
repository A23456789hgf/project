<?php

namespace App\Models;

use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasDomainScope, HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'entity_name',
        'action',
        'module',
        'url',
        'method',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |-------------------------------------------------------------
    | 🔥 TRANSLATIONS (خفيفة بدون regex الثقيل)
    |-------------------------------------------------------------
    */

    public function getTranslatedActionAttribute(): string
    {
        static $actions = [
            'create' => 'إضافة',
            'created' => 'إضافة',
            'update' => 'تعديل',
            'updated' => 'تعديل',
            'delete' => 'حذف',
            'deleted' => 'حذف',
            'login' => 'تسجيل دخول',
            'logout' => 'تسجيل خروج',
            'login_failed' => 'فشل دخول',
            'export' => 'تصدير بيانات',
            'import' => 'استيراد بيانات',
            'view' => 'استعراض',
            'approve' => 'اعتماد',
            'reject' => 'رفض',
            'register' => 'إنشاء حساب',
            'reset_password' => 'استعادة كلمة المرور',
        ];

        return $actions[strtolower($this->action)] ?? $this->action;
    }

    public function getTranslatedModuleAttribute(): string
    {
        if (! $this->module) {
            return 'عام';
        }

        static $modules = [
            'Authentication' => 'نظام الدخول',
            'Projects' => 'المشاريع',
            'Correspondence' => 'المراسلات',
            'User Management' => 'إدارة المستخدمين',
            'Permissions' => 'الصلاحيات',
            'Settings' => 'الإعدادات',
            'Administration' => 'الإدارة العامة',
            'General' => 'عام',
            'InternalEntity' => 'الجهات الداخلية',
            'Authority' => 'الجهات الإشرافية',
            'AuditLogs' => 'سجل الأنشطة',
        ];

        return $modules[$this->module] ?? $this->module;
    }

    /*
    |-------------------------------------------------------------
    | ⚠️ DESCRIPTION (تم تبسيطه وإلغاء regex الثقيل)
    |-------------------------------------------------------------
    */

    public function getTranslatedDescriptionAttribute(): string
    {
        if (! $this->description) {
            return '-';
        }

        // 🔥 نسخة خفيفة بدون preg_match
        $text = $this->description;

        $models = [
            'InternalEntity' => 'جهة داخلية',
            'Authority' => 'جهة إشرافية',
            'User' => 'مستخدم',
            'Role' => 'دور/صلاحية',
            'Project' => 'مشروع',
            'Program' => 'برنامج',
            'Activity' => 'نشاط',
            'Procedure' => 'إجراء',
            'ApprovalFlow' => 'مسار اعتماد',
        ];

        foreach ($models as $key => $value) {
            if (str_contains($text, $key)) {
                $text = str_replace($key, $value, $text);
            }
        }

        return $text;
    }

    /*
    |-------------------------------------------------------------
    | ⚡ USER AGENT (خفيف جداً بدون regex كثيرة)
    |-------------------------------------------------------------
    */

    public function getTranslatedUserAgentAttribute(): string
    {
        if (! $this->user_agent) {
            return 'غير معروف';
        }

        $ua = strtolower($this->user_agent);

        $device = str_contains($ua, 'mobile') ? 'جهاز محمول'
            : (str_contains($ua, 'tablet') ? 'جهاز لوحي' : 'جهاز كمبيوتر');

        $browser = 'متصفح غير معروف';

        if (str_contains($ua, 'chrome')) {
            $browser = 'كروم';
        } elseif (str_contains($ua, 'firefox')) {
            $browser = 'فايرفوكس';
        } elseif (str_contains($ua, 'safari')) {
            $browser = 'سفاري';
        } elseif (str_contains($ua, 'edge')) {
            $browser = 'إيدج';
        } elseif (str_contains($ua, 'opera')) {
            $browser = 'أوبرا';
        }

        $os = '';

        if (str_contains($ua, 'windows')) {
            $os = ' - ويندوز';
        } elseif (str_contains($ua, 'android')) {
            $os = ' - أندرويد';
        } elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) {
            $os = ' - iOS';
        } elseif (str_contains($ua, 'mac')) {
            $os = ' - ماك';
        } elseif (str_contains($ua, 'linux')) {
            $os = ' - لينكس';
        }

        return "{$device} ({$browser}){$os}";
    }

    /*
    |-------------------------------------------------------------
    | ⚡ DATE (خفيف جداً)
    |-------------------------------------------------------------
    */

    public function getArabicDateAttribute(): string
    {
        return optional($this->created_at)->format('Y-m-d H:i') ?? '-';
    }
}
