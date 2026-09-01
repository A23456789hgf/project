<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     * يتم تنفيذه قبل إنشاء المستخدم في قاعدة البيانات
     */
    public function creating(User $user): void
    {
        // توليد user_id تلقائياً إذا كان فارغاً
        if (empty($user->user_id)) {
            $user->user_id = $this->generateUserId();
        }

        // تعيين اسم المستخدم إذا كان فارغاً
        if (empty($user->username) && ! empty($user->email)) {
            $user->username = $this->generateUsername($user->email);
        }

        // تعيين الحالة الافتراضية إذا لم يتم تحديدها
        if (empty($user->status)) {
            $user->status = 'active';
        }

        // تسجيل معلومات الإنشاء
        if (auth()->check()) {
            $user->created_by = auth()->id();
            $user->updated_by = auth()->id();
        }

        Log::info('Creating new user', [
            'email' => $user->email,
            'user_id' => $user->user_id,
            'generated_by' => auth()->id() ?? 'system',
        ]);
    }

    /**
     * Handle the User "created" event.
     * يتم تنفيذه بعد إنشاء المستخدم في قاعدة البيانات
     */
    public function created(User $user): void
    {
        Log::info('User created successfully', [
            'id' => $user->id,
            'user_id' => $user->user_id,
            'email' => $user->email,
        ]);

        // يمكن إضافة إرسال بريد ترحيبي هنا
        // $this->sendWelcomeEmail($user);
    }

    /**
     * Handle the User "updating" event.
     * يتم تنفيذه قبل تحديث بيانات المستخدم
     */
    public function updating(User $user): void
    {
        // تحديث updated_by إذا كان المستخدم مسجلاً دخوله
        if (auth()->check()) {
            $user->updated_by = auth()->id();
        }

        // تسجيل التغييرات
        $changes = $user->getDirty();
        if (! empty($changes)) {
            Log::info('Updating user', [
                'user_id' => $user->id,
                'changes' => $changes,
                'updated_by' => auth()->id() ?? 'system',
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // يمكن إضافة أي إجراءات بعد التحديث هنا
    }

    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        // منع حذف المستخدم إذا كان لديه سجلات مرتبطة
        if ($user->createdUsers()->count() > 0 ||
            $user->auditLogs()->count() > 0) {

            Log::warning('Attempt to delete user with related records', [
                'user_id' => $user->id,
                'created_users_count' => $user->createdUsers()->count(),
                'audit_logs_count' => $user->auditLogs()->count(),
            ]);

            // يمكن إلغاء الحذف هنا أو تنفيذ حذف ناعم
            // throw new \Exception('Cannot delete user with related records');
        }

        Log::info('Deleting user', [
            'user_id' => $user->id,
            'deleted_by' => auth()->id() ?? 'system',
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::info('User deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Log::info('User restored', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        Log::info('User force deleted', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * توليد معرف مستخدم فريد
     */
    private function generateUserId(): string
    {
        $prefix = 'EMP';

        // الخيار 1: استخدام البادئة + رقم عشوائي (مضمون الفردية)
        do {
            // 6 أرقام عشوائية
            $randomNumber = mt_rand(100000, 999999);
            $userId = $prefix.$randomNumber;

            // التحقق من عدم وجود المستخدم مسبقاً
            $exists = User::where('user_id', $userId)->exists();
        } while ($exists);

        return $userId;

        /*
        // الخيار 2: استخدام البادئة + تاريخ + رقم تسلسلي
        $date = date('ymd');
        $lastUser = User::where('user_id', 'like', $prefix . $date . '%')
            ->orderBy('user_id', 'desc')
            ->first();

        if ($lastUser) {
            $lastNumber = (int) substr($lastUser->user_id, 9);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        */
    }

    /**
     * توليد اسم مستخدم من الإيميل
     */
    private function generateUsername(string $email): string
    {
        // استخراج الجزء قبل @ من الإيميل
        $username = strtok($email, '@');

        // إزالة الأحرف الخاصة
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', $username);

        // التحقق من عدم تكرار اسم المستخدم
        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername.$counter;
            $counter++;
        }

        return $username;
    }

    /**
     * إرسال بريد ترحيبي (اختياري)
     */
    private function sendWelcomeEmail(User $user): void
    {
        try {
            // يمكن تنفيذ إرسال البريد هنا
            // Mail::to($user->email)->send(new WelcomeEmail($user));

            Log::info('Welcome email queued for user', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
