<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Intervention;
use App\Models\Subdomain;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InterventionController extends Controller
{
    /**
     * عرض قائمة التدخلات (مع إظهار المعلقة قيد المراجعة أولاً)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Intervention::class);
        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $interventions = Intervention::with(['domain', 'subdomain'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('configuration.interventions.index', compact('interventions'));
    }

    /**
     * عرض نموذج إنشاء تدخل جديد
     */
    public function create()
    {
        $this->authorize('create', Intervention::class);
        $domains = Domain::select('id', 'name')->get();
        $subdomains = Subdomain::select('id', 'name')->get();

        return view('configuration.interventions.create', compact('domains', 'subdomains'));
    }

    /**
     * حفظ تدخل جديد في قاعدة البيانات (مباشرة كـ معتمد من قبل المدير)
     */
    public function store(Request $request)
    {
        $this->authorize('create', Intervention::class);
        $validator = Validator::make($request->all(), [
            'domain_id' => 'required|exists:domains,id',
            'subdomain_id' => 'required|exists:subdomains,id',
            'name' => 'required|string|max:255|unique:interventions',
            'is_active' => 'sometimes|boolean',
        ], [
            'domain_id.required' => 'حقل المجال الرئيسي مطلوب',
            'subdomain_id.required' => 'حقل المجال الفرعي مطلوب',
            'name.required' => 'حقل اسم التدخل مطلوب',
            'name.unique' => 'اسم التدخل مستخدم مسبقاً',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Intervention::create([
            'domain_id' => $request->domain_id,
            'subdomain_id' => $request->subdomain_id,
            'name' => $request->name,
            'is_active' => $request->boolean('is_active', true),
            'status' => 'approved',
        ]);

        return redirect()->route('interventions.index')
            ->with('success', 'تم إضافة التدخل بنجاح واعتماده.');
    }

    /**
     * عرض نموذج تعديل تدخل
     */
    public function edit(Intervention $intervention)
    {
        $this->authorize('update', Intervention::class);
        $domains = Domain::select('id', 'name')->get();
        $subdomains = Subdomain::select('id', 'name')->get();

        return view('configuration.interventions.edit', compact('intervention', 'domains', 'subdomains'));
    }

    /**
     * تحديث التدخل في قاعدة البيانات (واعتماده)
     */
    public function update(Request $request, Intervention $intervention)
    {
        $this->authorize('update', Intervention::class);
        $validator = Validator::make($request->all(), [
            'domain_id' => 'required|exists:domains,id',
            'subdomain_id' => 'required|exists:subdomains,id',
            'name' => 'required|string|max:255|unique:interventions,name,'.$intervention->id,
            'is_active' => 'sometimes|boolean',
        ], [
            'domain_id.required' => 'حقل المجال الرئيسي مطلوب',
            'subdomain_id.required' => 'حقل المجال الفرعي مطلوب',
            'name.required' => 'حقل اسم التدخل مطلوب',
            'name.unique' => 'اسم التدخل مستخدم مسبقاً',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $intervention->update([
            'domain_id' => $request->domain_id,
            'subdomain_id' => $request->subdomain_id,
            'name' => $request->name,
            'is_active' => $request->boolean('is_active', $intervention->is_active),
            'status' => 'approved',
        ]);

        return redirect()->route('interventions.index')
            ->with('success', 'تم تحديث التدخل واعتماده بنجاح.');
    }

    /**
     * اعتماد التدخل المقترح من قبل المستخدم وإتاحته بشكل دائم
     */
    public function approve(Intervention $intervention)
    {
        $intervention->update([
            'status' => 'approved',
            'is_active' => true,
        ]);

        try {
            app(NotificationService::class)->notifyLookupApproval('التدخل', $intervention->name, 'approved', $intervention->created_by, auth()->user());
        } catch (\Exception $e) {
            \Log::error('Notification error in InterventionController approve: '.$e->getMessage());
        }

        return redirect()->route('interventions.index')
            ->with('success', "تم اعتماد التدخل '{$intervention->name}' وإضافته بشكل دائم لقائمة التدخلات.");
    }

    /**
     * رفض التدخل المقترح
     */
    public function reject(Intervention $intervention)
    {
        $intervention->update([
            'status' => 'rejected',
            'is_active' => false,
        ]);

        try {
            app(NotificationService::class)->notifyLookupApproval('التدخل', $intervention->name, 'rejected', $intervention->created_by, auth()->user());
        } catch (\Exception $e) {
            \Log::error('Notification error in InterventionController reject: '.$e->getMessage());
        }

        return redirect()->route('interventions.index')
            ->with('success', "تم رفض التدخل '{$intervention->name}' وإيقاف تفعيله.");
    }

    /**
     * حذف تدخل من قاعدة البيانات
     */
    public function destroy(Intervention $intervention)
    {
        $this->authorize('delete', Intervention::class);
        try {
            $intervention->delete();

            return redirect()->route('interventions.index')
                ->with('success', 'تم حذف التدخل بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء محاولة الحذف: '.$e->getMessage());
        }
    }

    /**
     * جلب المجالات الفرعية بناءً على المجال الرئيسي
     */
    public function getSubdomains($domain_id)
    {
        $subdomains = Subdomain::where('domain_id', $domain_id)
            ->select('id', 'name')
            ->get();

        return response()->json($subdomains);
    }
}
