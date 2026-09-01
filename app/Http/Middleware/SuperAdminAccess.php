<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        // تحقق من المستخدم الحالي
        if (Auth::check()) {
            $adminRole = Role::where('name', 'Admin')->first();
            if (Auth::user()->role_id === ($adminRole ? $adminRole->id : 1)) {
                return $next($request);
            }
        }

        // تحقق من بيانات تسجيل الدخول
        $username = $request->input('username'); // الآن نستخدم اسم المستخدم
        $password = $request->input('password');

        // البيانات الافتراضية من .env
        if ($username === env('SUPER_ADMIN_USERNAME', 'admin') &&
            $password === env('SUPER_ADMIN_PASSWORD', 'superpassword')) {

            // إنشاء مستخدم Super Admin مؤقت
            $superAdmin = new User;
            $superAdmin->id = 0; // id وهمي
            $superAdmin->username = $username;
            $superAdmin->name = 'Super Admin';
            $adminRole = Role::where('name', 'Admin')->first();
            $superAdmin->role_id = $adminRole ? $adminRole->id : null;
            $superAdmin->exists = false; // غير موجود في قاعدة البيانات

            // تسجيل الدخول مباشرة
            Auth::login($superAdmin);

            return redirect()->route('dashboard'); // أو أي صفحة رئيسية
        }

        return $next($request);
    }
}
