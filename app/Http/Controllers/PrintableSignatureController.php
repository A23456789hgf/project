<?php

namespace App\Http\Controllers;

use App\Models\PrintableSignature;
use App\Traits\HandlesDataVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrintableSignatureController extends Controller
{
    use HandlesDataVisibility;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('signatures.view');
        $query = PrintableSignature::query();

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('job_title', 'like', "%$search%");
        }

        $signatures = $query->orderBy('display_order')->orderBy('created_at', 'desc')->paginate(request('per_page', 15));

        return view('signatures.index', compact('signatures'));
    }

    public function create()
    {
        $this->authorize('signatures.create');

        return view('signatures.create');
    }

    public function store(Request $request)
    {
        $this->authorize('signatures.create');
        $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $data = $request->except('signature_image');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('signature_image')) {
            $path = $request->file('signature_image')->store('signatures', 'public');
            $data['signature_path'] = $path;
        }

        PrintableSignature::create($data);

        session()->flash('success', 'تم إضافة التوقيع بنجاح');

        return redirect()->route('signatures.index');
    }

    public function edit(PrintableSignature $signature)
    {
        $this->authorize('signatures.edit');

        return view('signatures.edit', compact('signature'));
    }

    public function update(Request $request, PrintableSignature $signature)
    {
        $this->authorize('signatures.edit');
        $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $data = $request->except('signature_image');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('signature_image')) {
            // Delete old signature if exists
            if ($signature->signature_path) {
                Storage::disk('public')->delete($signature->signature_path);
            }
            $path = $request->file('signature_image')->store('signatures', 'public');
            $data['signature_path'] = $path;
        }

        $signature->update($data);

        session()->flash('success', 'تم تحديث التوقيع بنجاح');

        return redirect()->route('signatures.index');
    }

    public function destroy(PrintableSignature $signature)
    {
        $this->authorize('signatures.delete');
        if ($signature->signature_path) {
            Storage::disk('public')->delete($signature->signature_path);
        }
        $signature->delete();

        session()->flash('success', 'تم حذف التوقيع بنجاح');

        return redirect()->route('signatures.index');
    }

    /**
     * API to get all active signatures for the modal.
     */
    public function getActiveSignatures()
    {
        return response()->json(PrintableSignature::active()->orderBy('display_order')->get());
    }
}
