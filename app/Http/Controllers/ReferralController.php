<?php

namespace App\Http\Controllers;

use App\Exports\ReferralTopicExport;
use App\Models\InternalEntity;
use App\Models\ReferralActivity;
use App\Models\ReferralTopic;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReferralController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'isAdmin') ? $user->isAdmin() : false;

        $topicsQuery = ReferralTopic::with(['creator', 'entity', 'latestActivity']);

        if (! $isAdmin) {
            // A normal user only sees topics they created or where they have an assigned activity
            $topicsQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('activities', function ($aq) use ($user) {
                        $aq->where('to_user_id', $user->id)
                            ->orWhere('from_user_id', $user->id);
                    });
            });
        }

        $topics = $topicsQuery->latest()->paginate(15);

        $departments = InternalEntity::where('parent_id', $user->entity_id)
            ->orWhere('id', $user->entity_id)
            ->active()
            ->get();

        return view('project_financing.topic_referrals.index', compact('topics', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'master_titles' => 'nullable|array',
            'master_titles.*' => 'required|string|max:255',
            'referrals' => 'required|array|min:1',
            'referrals.*.to_department_id' => 'required|exists:internal_entities,id',
            'referrals.*.referral_text' => 'required|string',
            'referrals.*.attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $topic = ReferralTopic::create([
                'topic_number' => ReferralTopic::generateTopicNumber(),
                'subject' => $request->subject,
                'master_titles' => $request->master_titles,
                'created_by' => $user->id,
                'entity_id' => $user->entity_id,
            ]);

            foreach ($request->referrals as $index => $referralData) {
                $attachmentPaths = [];
                if ($request->hasFile("referrals.$index.attachments")) {
                    foreach ($request->file("referrals.$index.attachments") as $file) {
                        $attachmentPaths[] = $file->store('referrals/attachments', 'public');
                    }
                }

                ReferralActivity::create([
                    'topic_id' => $topic->id,
                    'referral_number' => ReferralActivity::generateActivityNumber($topic->topic_number),
                    'from_user_id' => $user->id,
                    'from_department_id' => $user->entity_id,
                    'to_department_id' => $referralData['to_department_id'],
                    'referral_text' => $referralData['referral_text'],
                    'attachments' => $attachmentPaths,
                    'referral_date' => now(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'تم إنشاء الإحالات بنجاح برقم: '.$topic->topic_number);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'حدث خطأ أثناء إنشاء الإحالة: '.$e->getMessage());
        }
    }

    public function show(ReferralTopic $topic)
    {
        $topic->load(['activities.fromUser', 'activities.fromDepartment', 'activities.toDepartment', 'activities.responder']);

        // Generate QR Code for the topic (required by show.blade.php report section)
        $qrCodeData = '';
        if (class_exists(QrCode::class)) {
            try {
                $qrCode = QrCode::create($topic->subject);
                $writer = new PngWriter;
                $result = $writer->write($qrCode);
                $qrCodeData = $result->getDataUri();
            } catch (\Throwable $e) {
            }
        }

        $user = Auth::user();
        $departments = InternalEntity::where('parent_id', $user->entity_id)
            ->orWhere('id', $user->entity_id)
            ->active()
            ->get();

        return view('project_financing.topic_referrals.show', compact('topic', 'departments', 'qrCodeData'));
    }

    public function refer(Request $request, ReferralTopic $topic)
    {
        $request->validate([
            'referrals' => 'required|array|min:1',
            'referrals.*.to_department_id' => 'required|exists:internal_entities,id',
            'referrals.*.referral_text' => 'required|string',
            'referrals.*.attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            foreach ($request->referrals as $index => $referralData) {
                $attachmentPaths = [];
                if ($request->hasFile("referrals.$index.attachments")) {
                    foreach ($request->file("referrals.$index.attachments") as $file) {
                        $attachmentPaths[] = $file->store('referrals/attachments', 'public');
                    }
                }

                ReferralActivity::create([
                    'topic_id' => $topic->id,
                    'referral_number' => ReferralActivity::generateActivityNumber($topic->topic_number),
                    'from_user_id' => $user->id,
                    'from_department_id' => $user->entity_id,
                    'to_department_id' => $referralData['to_department_id'],
                    'referral_text' => $referralData['referral_text'],
                    'attachments' => $attachmentPaths,
                    'referral_date' => now(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'تمت الإحالات بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'حدث خطأ أثناء الإحالة: '.$e->getMessage());
        }
    }

    public function respond(Request $request, ReferralActivity $activity)
    {
        $request->validate([
            'response_text' => 'required|string',
            'response_attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = Auth::user();

        $attachmentPaths = [];
        if ($request->hasFile('response_attachments')) {
            foreach ($request->file('response_attachments') as $file) {
                $attachmentPaths[] = $file->store('referrals/responses', 'public');
            }
        }

        $activity->update([
            'response_text' => $request->response_text,
            'response_date' => now(),
            'response_attachments' => $attachmentPaths,
            'responded_by' => $user->id,
        ]);

        return redirect()->back()->with('success', 'تم الرد على الإحالة بنجاح');
    }

    public function print(ReferralActivity $activity)
    {
        $topic = $activity->topic()->with(['activities.fromUser', 'activities.fromDepartment', 'activities.toDepartment', 'activities.responder'])->first();

        // Generate QR Code for the topic
        $qrCodeData = '';
        if (class_exists(QrCode::class)) {
            try {
                $qrCode = QrCode::create($topic->subject);
                $writer = new PngWriter;
                $result = $writer->write($qrCode);
                $qrCodeData = $result->getDataUri();
            } catch (\Throwable $e) {
            }
        }

        return view('project_financing.topic_referrals.print', compact('topic', 'qrCodeData'));
    }

    public function printTopic(ReferralTopic $topic)
    {
        $topic->load(['activities.fromUser', 'activities.fromDepartment', 'activities.toDepartment', 'activities.responder']);

        // Generate QR Code for the topic
        $qrCodeData = '';
        if (class_exists(QrCode::class)) {
            try {
                $qrCode = QrCode::create($topic->subject);
                $writer = new PngWriter;
                $result = $writer->write($qrCode);
                $qrCodeData = $result->getDataUri();
            } catch (\Throwable $e) {
            }
        }

        return view('project_financing.topic_referrals.print', compact('topic', 'qrCodeData'));
    }

    public function exportExcel(ReferralTopic $topic)
    {
        // Clear any output buffers to prevent file corruption
        if (ob_get_length()) {
            ob_end_clean();
        }

        return Excel::download(new ReferralTopicExport($topic), 'referral_topic_'.$topic->id.'.xlsx');
    }
}
