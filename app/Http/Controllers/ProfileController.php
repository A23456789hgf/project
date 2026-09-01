<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = auth()->user()->load(['role', 'entity']);

        return view('profile.show', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'كلمة المرور الحالية غير صحيحة',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        AuditLogService::log(
            action: 'change_password',
            module: 'Profile',
            description: 'قام المستخدم بتغيير كلمة المرور الخاصة به',
            modelType: 'User',
            modelId: $user->id
        );

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }

    public function setupSignature(Request $request)
    {
        $request->validate([
            'signature_data' => 'required|string',
        ]);

        $user = auth()->user();
        $signatureData = $request->signature_data;

        // التحقق من base64 image
        if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
            $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
            $type = strtolower($type[1]);

            if (! in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'نوع الصورة غير مدعوم.',
                ], 400);
            }

            $signatureData = base64_decode($signatureData);

            if ($signatureData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء قراءة التوقيع.',
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'البيانات المرسلة ليست صورة صحيحة.',
            ], 400);
        }

        // اسم الملف
        $fileName = 'signatures/'.$user->user_id.'_'.time().'.'.$type;

        // حذف التوقيع القديم
        if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
            Storage::disk('public')->delete($user->signature_path);
        }

        // حفظ الجديد
        Storage::disk('public')->put($fileName, $signatureData);

        $isUpdate = ! empty($user->signature_path);

        $user->signature_path = $fileName;
        $saved = $user->save();

        Log::info("Signature setup for user {$user->id}, saved: ".($saved ? 'true' : 'false')." path: {$fileName}");

        AuditLogService::log(
            action: $isUpdate ? 'update_signature' : 'setup_signature',
            module: 'Profile',
            description: $isUpdate
                ? 'قام المستخدم بتحديث التوقيع الإلكتروني الخاص به'
                : 'قام المستخدم بتعيين التوقيع الإلكتروني الخاص به',
            modelType: 'User',
            modelId: $user->id
        );

        session()->flash('success', $isUpdate ? 'تم تحديث التوقيع الإلكتروني بنجاح' : 'تم حفظ التوقيع الإلكتروني بنجاح');

        return response()->json([
            'success' => true,
            'message' => $isUpdate
                ? 'تم تحديث التوقيع الإلكتروني بنجاح'
                : 'تم حفظ التوقيع الإلكتروني بنجاح',
            'signature_url' => asset('storage/'.$fileName),
        ]);
    }
}
