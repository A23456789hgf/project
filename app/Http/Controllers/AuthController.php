<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PasswordReset;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SmppSmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        // تحديد الحقل المستخدم (قد يكون phone أو username)
        $loginField = $request->has('phone') ? 'phone' : 'username';
        $username = trim($request->input($loginField));
        $password = $request->password;

        $request->validate([
            $loginField => 'required|string',
            'password' => 'required|string',
        ], [
            $loginField.'.required' => 'اسم المستخدم أو رقم الهاتف مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        // Super Admin bypass
        $saUsername = env('SUPER_ADMIN_USERNAME');
        $saPassword = env('SUPER_ADMIN_PASSWORD');

        if ($saUsername && $saPassword && $username === $saUsername && $password === $saPassword) {
            // Create a temporary Super Admin user instance that is disconnected from the DB
            $superAdmin = new class extends User
            {
                public $exists = true;

                public $incrementing = false;

                public function save(array $options = [])
                {
                    return true;
                }

                public function update(array $attributes = [], array $options = [])
                {
                    return true;
                }

                public function push()
                {
                    return true;
                }

                public function delete()
                {
                    return true;
                }

                public function getPermissionsAttribute()
                {
                    return collect([]);
                }

                public function permissions()
                {
                    return new class
                    {
                        public function pluck()
                        {
                            return collect(['*']);
                        }
                    };
                }
            };

            $superAdmin->id = 0;
            $superAdmin->user_id = 'SYSTEM_SA';
            $superAdmin->username = $saUsername;

            $adminRole = Role::where('name', 'Admin')->first();
            $superAdmin->role_id = $adminRole ? $adminRole->id : null;
            $superAdmin->status = 'Active';

            Auth::login($superAdmin, $request->filled('remember'));

            // ✅ تسجيل نجاح عملية الدخول لـ Super Admin
            Log::info('✅ SUPER ADMIN LOGIN SUCCESS', [
                'username' => $saUsername,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ]);

            try {
                AuditLogService::log(
                    action: 'login',
                    module: 'Authentication',
                    description: 'Super Admin logged in via bypass (fully disconnected)'
                );
            } catch (\Exception $e) {
                // Silently fail if DB is unavailable
            }

            session(['user_permissions' => ['*']]);

            // ✅ التحقق من نجاح تسجيل الدخول قبل التوجيه
            if (! Auth::check()) {
                Log::error('❌ SUPER ADMIN AUTH FAILED', [
                    'username' => $saUsername,
                    'ip' => $request->ip(),
                ]);
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'حدث خطأ في تسجيل الدخول'], 422);
                }

                return back()->withErrors(['error' => 'حدث خطأ في تسجيل الدخول'])->withInput();
            }

            Log::info('🔍 SUPER ADMIN SESSION CHECK', [
                'session_id' => session()->getId(),
                'user_id' => auth()->id(),
                'authenticated' => Auth::check(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'csrf_token' => csrf_token(),
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        // البحث عن المستخدم مع إلغاء جميع النطاقات العامة (Global Scopes)
        $user = User::withoutGlobalScopes()
            ->where(function ($query) use ($username) {
                $query->where('username', $username)
                    ->orWhere('email', $username)
                    ->orWhere('user_id', $username)
                    ->orWhere('phone', $username);
            })
            ->first();

        // تسجيل سبب عدم العثور للمساعدة في التصحيح
        if (! $user) {
            // ❌ تسجيل فشل عملية الدخول - المستخدم غير موجود
            Log::warning('❌ LOGIN FAILED: User not found', [
                'identifier' => $username,
                'login_field' => $loginField,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ]);

            AuditLogService::log(
                action: 'login_failed',
                module: 'Authentication',
                description: 'محاولة تسجيل دخول فاشلة: اسم المستخدم غير موجود ('.$username.')',
                modelType: 'User'
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'البيانات المدخلة غير صحيحة'], 422);
            }

            return back()->withErrors([$loginField => 'البيانات المدخلة غير صحيحة'])->withInput();
        }

        // التحقق من حالة المستخدم (Disabled)
        if ($user->status == 'Disabled') {
            // ❌ تسجيل فشل عملية الدخول - الحساب معطل
            Log::warning('❌ LOGIN FAILED: Account disabled', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ]);

            AuditLogService::log(
                action: 'login_failed',
                module: 'Authentication',
                description: 'محاولة تسجيل دخول فاشلة: الحساب معطل',
                modelType: 'User',
                modelId: $user->id
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'حسابك معطل من قبل الإدارة'], 422);
            }

            return back()->withErrors([$loginField => 'حسابك معطل من قبل الإدارة'])->withInput();
        }

        // التحقق من صحة كلمة المرور
        if (! Hash::check($password, $user->password)) {
            // ❌ تسجيل فشل عملية الدخول - كلمة مرور خاطئة
            Log::warning('❌ LOGIN FAILED: Invalid password', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ]);

            AuditLogService::log(
                action: 'login_failed',
                module: 'Authentication',
                description: 'محاولة تسجيل دخول فاشلة: كلمة مرور غير صحيحة',
                modelType: 'User',
                modelId: $user->id
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'كلمة المرور غير صحيحة'], 422);
            }

            return back()->withErrors(['password' => 'كلمة المرور غير صحيحة'])->withInput();
        }

        // ✅ تسجيل نجاح عملية الدخول
        Log::info('✅ LOGIN SUCCESS', [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => Carbon::now()->toDateTimeString(),
        ]);

        // محاولة تسجيل الدخول
        Auth::login($user, $request->filled('remember'));

        // ✅ التحقق من نجاح تسجيل الدخول
        if (! Auth::check()) {
            Log::error('❌ AUTH FAILED AFTER LOGIN ATTEMPT', [
                'user_id' => $user->id,
                'username' => $user->username,
                'session_id' => session()->getId(),
                'ip' => $request->ip(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'حدث خطأ في تسجيل الدخول، يرجى المحاولة مرة أخرى'], 422);
            }

            return back()->withErrors(['error' => 'حدث خطأ في تسجيل الدخول، يرجى المحاولة مرة أخرى'])->withInput();
        }

        // ✅ تسجيل معلومات الجلسة
        Log::info('🔍 SESSION DATA AFTER LOGIN', [
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
            'username' => auth()->user()->username,
            'authenticated' => Auth::check(),
        ]);

        // Generate and store unique token
        $monitoringToken = Str::uuid()->toString();
        session(['monitoring_token' => $monitoringToken]);

        // Attach permissions to session
        try {
            $permissions = $user->permissions()->pluck('slug')->toArray();
            session(['user_permissions' => $permissions]);

            Log::info('🔑 PERMISSIONS LOADED', [
                'user_id' => $user->id,
                'permissions_count' => count($permissions),
                'permissions' => $permissions,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ PERMISSIONS LOAD ERROR', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            session(['user_permissions' => []]);
        }

        AuditLogService::log(
            action: 'login',
            module: 'Authentication',
            description: 'تسجيل الدخول بنجاح - تم إنشاء توكن المراقبة وتحديث الصلاحيات',
            modelType: 'User',
            modelId: $user->id
        );

        // ✅ التحقق من صلاحية الوصول للداشبورد
        $canAccessDashboard = $user->can('access_dashboard') ?? false;
        Log::info('🔍 DASHBOARD ACCESS CHECK', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'can_access_dashboard' => $canAccessDashboard,
            'is_super_admin' => $user->role_id == 1,
        ]);

        // إذا لم يكن لديه صلاحية، حاول التوجيه إلى صفحة أخرى
        if (! $canAccessDashboard && $user->role_id != 1) {
            Log::warning('⚠️ USER CANNOT ACCESS DASHBOARD', [
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'redirecting_to' => route('home'),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'ليس لديك صلاحية للداشبورد'], 403);
            }

            return redirect()->route('home')->with('warning', 'ليس لديك صلاحية للداشبورد');
        }

        // ✅ محاولة التوجيه مع fallback
        try {
            $redirectUrl = route('dashboard');
            Log::info('🔄 REDIRECTING TO', ['url' => $redirectUrl]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'csrf_token' => csrf_token(),
                ]);
            }

            return redirect()->intended($redirectUrl);
        } catch (\Exception $e) {
            Log::error('❌ REDIRECT ERROR', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'csrf_token' => csrf_token(),
                ]);
            }

            return redirect('/')->with('error', 'تم تسجيل الدخول ولكن حدث خطأ في التوجيه');
        }
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $roles = Role::orderBy('name')->get();

        return view('auth.register', compact('roles'));
    }

    public function register(Request $request)
    {
        $validator = $request->validate([
            'username' => 'required|string|unique:users,username|min:3|max:255',
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
        ], [
            'username.required' => 'اسم المستخدم مطلوب',
            'username.unique' => 'اسم المستخدم موجود بالفعل',
            'username.min' => 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل',
            'name.required' => 'الاسم مطلوب',
            'name.min' => 'الاسم يجب أن يكون 3 أحرف على الأقل',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني موجود بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'role_id.required' => 'الدور مطلوب',
        ]);

        $user = User::create([
            'username' => $request->username,
            'user_id' => strtoupper(substr($request->username, 0, 3).date('YmdHis')),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'department' => $request->department,
            'status' => 'Active',
        ]);

        // ✅ تسجيل عملية التسجيل
        Log::info('✅ USER REGISTERED', [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => Carbon::now()->toDateTimeString(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'register',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'تسجيل حساب جديد',
            'ip_address' => $request->ip(),
        ]);

        Auth::login($user);

        // ✅ التحقق من نجاح تسجيل الدخول بعد التسجيل
        if (! Auth::check()) {
            Log::error('❌ REGISTRATION AUTH FAILED', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')->with('error', 'تم التسجيل ولكن حدث خطأ في تسجيل الدخول');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.exists' => 'هذا البريد الإلكتروني غير مسجل في النظام',
        ]);

        $user = User::where('email', $request->email)->first();
        $token = Str::random(60);

        PasswordReset::where('user_id', $user->id)->delete();

        PasswordReset::create([
            'user_id' => $user->id,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        // ✅ تسجيل طلب إعادة تعيين كلمة المرور
        Log::info('📧 PASSWORD RESET REQUESTED', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => Carbon::now()->toDateTimeString(),
        ]);

        return back()->with('status', 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني');
    }

    public function showResetPassword($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (! $passwordReset) {
            Log::warning('❌ PASSWORD RESET SHOW: Invalid token', [
                'token' => $token,
            ]);

            return redirect()->route('login')->withErrors(['token' => 'الرابط غير صحيح أو انتهت صلاحيته']);
        }

        $expiresAt = Carbon::parse($passwordReset->created_at)->addHours(1);
        if (Carbon::now()->isAfter($expiresAt)) {
            $passwordReset->delete();
            Log::warning('❌ PASSWORD RESET SHOW: Token expired', [
                'user_id' => $passwordReset->user_id,
                'created_at' => $passwordReset->created_at,
            ]);

            return redirect()->route('forgot-password')->withErrors(['token' => 'انتهت صلاحية الرابط']);
        }

        return view('auth.reset-password', ['token' => $token, 'email' => $passwordReset->user->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.exists' => 'هذا البريد الإلكتروني غير مسجل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $passwordReset = PasswordReset::where('token', $request->token)->first();

        if (! $passwordReset) {
            // ❌ تسجيل فشل إعادة تعيين كلمة المرور
            Log::warning('❌ PASSWORD RESET FAILED: Invalid token', [
                'token' => $request->token,
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ]);

            return back()->withErrors(['token' => 'الرابط غير صحيح']);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            // ❌ تسجيل فشل إعادة تعيين كلمة المرور
            Log::warning('❌ PASSWORD RESET FAILED: User not found', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ]);

            return back()->withErrors(['email' => 'المستخدم غير موجود']);
        }

        $expiresAt = Carbon::parse($passwordReset->created_at)->addHours(1);
        if (Carbon::now()->isAfter($expiresAt)) {
            $passwordReset->delete();

            // ❌ تسجيل فشل إعادة تعيين كلمة المرور - انتهت الصلاحية
            Log::warning('❌ PASSWORD RESET FAILED: Token expired', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ]);

            return redirect()->route('forgot-password')->withErrors(['token' => 'انتهت صلاحية الرابط']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        $passwordReset->delete();

        // ✅ تسجيل نجاح إعادة تعيين كلمة المرور
        Log::info('✅ PASSWORD RESET SUCCESS', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => Carbon::now()->toDateTimeString(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'reset_password',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'إعادة تعيين كلمة المرور',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('login')->with('status', 'تم إعادة تعيين كلمة المرور بنجاح');
    }

    public function logout(Request $request)
    {
        $user = auth()->user();

        // ✅ تسجيل عملية تسجيل الخروج
        if ($user) {
            Log::info('🔓 LOGOUT', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ]);
        }

        AuditLogService::log(
            action: 'logout',
            module: 'Authentication',
            description: 'تسجيل الخروج',
            modelType: 'User',
            modelId: auth()->id()
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * دالة مساعدة للتحقق من حالة الجلسة (للتشخيص)
     */
    public function checkSession()
    {
        request()->session()->save(); // Release session lock for long polling

        Log::info('🔍 SESSION STATUS CHECK', [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'user_permissions' => session('user_permissions', []),
            'monitoring_token' => session('monitoring_token', null),
        ]);

        if (Auth::check()) {
            $user = Auth::user();

            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                ],
                'session_id' => session()->getId(),
                'permissions' => session('user_permissions', []),
            ]);
        }

        return response()->json([
            'authenticated' => false,
            'session_id' => session()->getId(),
        ], 401);
    }

    /**
     * إرسال رمز OTP لاستعادة كلمة المرور عبر رقم الهاتف
     */
    public function sendOtp(Request $request, SmppSmsService $smsService)
    {
        $loginField = $request->has('phone') ? 'phone' : 'username';
        $username = trim($request->input($loginField));

        $request->validate([
            $loginField => 'required|string',
        ], [
            $loginField.'.required' => 'اسم المستخدم أو رقم الهاتف مطلوب',
        ]);

        $user = User::withoutGlobalScopes()
            ->where(function ($query) use ($username) {
                $query->where('username', $username)
                    ->orWhere('email', $username)
                    ->orWhere('user_id', $username)
                    ->orWhere('phone', $username);
            })
            ->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        if (! $user->phone) {
            return response()->json(['success' => false, 'message' => 'لا يوجد رقم هاتف مسجل لهذا المستخدم لإرسال رمز التحقق'], 400);
        }

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        PasswordReset::where('user_id', $user->id)->delete();

        PasswordReset::create([
            'user_id' => $user->id,
            'token' => Hash::make($otp),
            'created_at' => Carbon::now(),
        ]);

        // Send OTP via SMS
        $message = 'رمز استعادة كلمة المرور الخاص بك في نظام إدارة المشاريع هو: '.$otp;
        $smsService->sendSMS($user->id, $user->phone, $message, 'password_reset_otp');

        return response()->json(['success' => true, 'message' => 'تم إرسال رمز التحقق بنجاح إلى رقم هاتفك']);
    }

    /**
     * التحقق من صحة رمز OTP
     */
    public function verifyOtp(Request $request)
    {
        $loginField = $request->has('phone') ? 'phone' : 'username';
        $username = trim($request->input($loginField));

        $request->validate([
            $loginField => 'required|string',
            'otp' => 'required|string|size:6',
        ], [
            $loginField.'.required' => 'اسم المستخدم أو رقم الهاتف مطلوب',
            'otp.required' => 'رمز التحقق مطلوب',
            'otp.size' => 'رمز التحقق يجب أن يتكون من 6 أرقام',
        ]);

        $user = User::withoutGlobalScopes()
            ->where(function ($query) use ($username) {
                $query->where('username', $username)
                    ->orWhere('email', $username)
                    ->orWhere('user_id', $username)
                    ->orWhere('phone', $username);
            })
            ->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير مسجل'], 404);
        }

        $passwordReset = PasswordReset::where('user_id', $user->id)->first();

        if (! $passwordReset || ! Hash::check($request->otp, $passwordReset->token)) {
            return response()->json(['success' => false, 'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية'], 400);
        }

        // Check expiry (e.g. 15 minutes)
        $expiresAt = Carbon::parse($passwordReset->created_at)->addMinutes(15);
        if (Carbon::now()->isAfter($expiresAt)) {
            $passwordReset->delete();

            return response()->json(['success' => false, 'message' => 'انتهت صلاحية رمز التحقق. يرجى طلب رمز جديد'], 400);
        }

        return response()->json(['success' => true, 'message' => 'تم التحقق من الرمز بنجاح']);
    }

    /**
     * تعيين كلمة المرور الجديدة
     */
    public function resetPasswordWithOtp(Request $request)
    {
        $loginField = $request->has('phone') ? 'phone' : 'username';
        $username = trim($request->input($loginField));

        $request->validate([
            $loginField => 'required|string',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ], [
            $loginField.'.required' => 'اسم المستخدم أو رقم الهاتف مطلوب',
            'otp.required' => 'رمز التحقق مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $user = User::withoutGlobalScopes()
            ->where(function ($query) use ($username) {
                $query->where('username', $username)
                    ->orWhere('email', $username)
                    ->orWhere('user_id', $username)
                    ->orWhere('phone', $username);
            })
            ->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        $passwordReset = PasswordReset::where('user_id', $user->id)->first();

        if (! $passwordReset || ! Hash::check($request->otp, $passwordReset->token)) {
            return response()->json(['success' => false, 'message' => 'رمز التحقق غير صحيح'], 400);
        }

        $expiresAt = Carbon::parse($passwordReset->created_at)->addMinutes(15);
        if (Carbon::now()->isAfter($expiresAt)) {
            $passwordReset->delete();

            return response()->json(['success' => false, 'message' => 'انتهت صلاحية رمز التحقق'], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $passwordReset->delete();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'reset_password_otp',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'إعادة تعيين كلمة المرور عبر رمز OTP (SMS)',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true, 'message' => 'تم تعيين كلمة المرور الجديدة بنجاح']);
    }

    public function showForceChangePassword()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! Auth::user()->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-change-password');
    }

    public function forceChangePassword(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->must_change_password) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password|max:50',
        ], [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'password.required' => 'كلمة المرور الجديدة مطلوبة',
            'password.min' => 'كلمة المرور الجديدة يجب أن تكون 8 خانات على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'password.different' => 'يجب أن تكون كلمة المرور الجديدة مختلفة عن كلمة المرور الحالية',
            'password.max' => 'كلمة المرور طويلة جداً (الحد الأقصى 50 حرفاً)',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة'])->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'change_password_forced',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'قام المستخدم بتحديث كلمة المرور الافتراضية بنجاح عند تسجيل الدخول الأول',
            'ip_address' => $request->ip(),
        ]);

        session()->flash('success', 'تم تحديث كلمة المرور بنجاح ودخول النظام.');

        return redirect()->route('dashboard');
    }
}
