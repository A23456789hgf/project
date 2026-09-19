<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Subdomain;
use Illuminate\Http\Request;

class SubdomainController extends Controller
{
    // عرض قائمة المجالات الفرعية مع فلترة، بحث، فرز، وتصفح صفحات
    public function index(Request $request)
    {
        $this->authorize('viewAny', Subdomain::class);
        $query = Subdomain::with('domain');

        // فلترة حسب domain_id
        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        // بحث نصي في الاسم
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // فرز
        if ($request->filled('sort')) {
            $sortField = ltrim($request->sort, '-');
            $sortDirection = $request->sort[0] === '-' ? 'desc' : 'asc';

            if (in_array($sortField, ['id', 'name', 'created_at'])) {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        // جلب البيانات مع التصفح (pagination)
        $subdomains = $query->paginate($perPage)->withQueryString();
        $domains = Domain::all();

        return view('configuration.subdomain.index', compact('subdomains', 'domains'));
    }

    // صفحة إضافة مجال فرعي جديد
    public function create()
    {
        $this->authorize('create', Subdomain::class);
        $domains = Domain::all();

        return view('configuration.subdomain.create', compact('domains'));
    }

    // حفظ المجال الفرعي الجديد
    public function store(Request $request)
    {
        $this->authorize('create', Subdomain::class);
        $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'name' => 'required|string|max:255|unique:subdomains,name,NULL,id,domain_id,'.$request->domain_id,
        ]);

        Subdomain::create($request->only('domain_id', 'name'));

        return redirect()->route('subdomains.index')->with('success', 'تمت إضافة المجال الفرعي بنجاح');
    }

    // صفحة تعديل مجال فرعي
    public function edit(Subdomain $subdomain)
    {
        $this->authorize('update', Subdomain::class);
        $domains = Domain::all();

        return view('configuration.subdomain.edit', compact('subdomain', 'domains'));
    }

    // تحديث مجال فرعي
    public function update(Request $request, Subdomain $subdomain)
    {
        $this->authorize('update', Subdomain::class);
        $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'name' => 'required|string|max:255|unique:subdomains,name,'.$subdomain->id.',id,domain_id,'.$request->domain_id,
        ]);

        $subdomain->update($request->only('domain_id', 'name'));

        return redirect()->route('subdomains.index')->with('success', 'تم تحديث المجال الفرعي بنجاح');
    }

    // حذف مجال فرعي
    public function destroy(Subdomain $subdomain)
    {
        $this->authorize('delete', Subdomain::class);
        $subdomain->delete();

        return redirect()->route('subdomains.index')->with('success', 'تم حذف المجال الفرعي بنجاح');
    }

    // جلب المجالات الفرعية حسب المجال الرئيسي (للاستخدام في AJAX)
    public function fetchByDomain($domainId)
    {
        try {
            $subdomains = Subdomain::where('domain_id', $domainId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json($subdomains);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ في جلب البيانات'], 500);
        }
    }
}
