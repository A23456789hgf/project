<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SmppSmsService;
use Illuminate\Http\Request;

class SmsAdminController extends Controller
{
    public function settings()
    {
        $this->authorize('sms.settings.view');

        // Load the SMS events settings, for example:
        $events = [
            'project_approved' => SystemSetting::get('sms_event_project_approved', '0'),
            'user_registered' => SystemSetting::get('sms_event_user_registered', '0'),
        ];

        return view('configuration.sms.settings', compact('events'));
    }

    public function updateSettings(Request $request)
    {
        $this->authorize('sms.settings.update');

        $events = [
            'sms_event_project_approved',
            'sms_event_user_registered',
        ];

        foreach ($events as $event) {
            $val = $request->has($event) ? '1' : '0';
            SystemSetting::set($event, $val, 'sms');
        }

        return redirect()->route('configuration.sms.settings')->with('success', 'تم تحديث إعدادات الرسائل النصية بنجاح.');
    }

    public function logs(Request $request)
    {
        $this->authorize('sms.logs.view');

        $query = SmsLog::with(['recipient', 'sender'])->latest();

        if ($request->filled('phone_number')) {
            $query->where('phone_number', 'like', '%'.$request->phone_number.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // recipient search by name or username
        if ($request->filled('recipient')) {
            $query->whereHas('recipient', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->recipient.'%')
                    ->orWhere('username', 'like', '%'.$request->recipient.'%');
            });
        }

        // sender search
        if ($request->filled('sender')) {
            $query->whereHas('sender', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->sender.'%')
                    ->orWhere('username', 'like', '%'.$request->sender.'%');
            });
        }

        $logs = $query->paginate(20)->appends($request->all());

        return view('configuration.sms.logs', compact('logs'));
    }

    public function manual()
    {
        $this->authorize('sms.manage');

        // Load active users with phone numbers
        $users = User::whereNotNull('phone')->where('status', 'Active')->get(['id', 'name', 'phone']);

        return view('configuration.sms.manual', compact('users'));
    }

    public function sendManual(Request $request, SmppSmsService $smsService)
    {
        $this->authorize('sms.manual.send');

        $request->validate([
            'message' => 'required|string|max:500',
            'recipient_type' => 'required|in:all,selected',
            'user_ids' => 'required_if:recipient_type,selected|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $message = $request->message;
        $users = collect();

        if ($request->recipient_type === 'all') {
            $users = User::whereNotNull('phone')->where('status', 'Active')->get();
        } else {
            $users = User::whereIn('id', $request->user_ids)->whereNotNull('phone')->get();
        }

        if ($users->isEmpty()) {
            return back()->with('error', 'لم يتم العثور على مستخدمين لديهم أرقام هواتف.');
        }

        $smsService->sendToMultiple($users, $message, 'manual_sms');

        return redirect()->route('configuration.sms.logs')->with('success', 'تم إرسال الرسائل وتوثيقها بنجاح.');
    }
}
