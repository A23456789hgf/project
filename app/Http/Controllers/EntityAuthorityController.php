<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EntityAuthorityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('entity-authorities.view');

        $settings = [
            'active_source' => SystemSetting::get('active_entity_source', 'both'),
            'visibility_internal' => SystemSetting::get('visibility_internal', '1'),
            'visibility_external' => SystemSetting::get('visibility_external', '1'),
        ];

        return view('configuration.entity_authorities.index', compact('settings'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        Gate::authorize('entity-authorities.modify-source');

        $request->validate([
            'active_source' => 'required|in:internal,external,both',
            'visibility_internal' => 'nullable|boolean',
            'visibility_external' => 'nullable|boolean',
        ]);

        SystemSetting::set('active_entity_source', $request->active_source, 'entity_authorities');

        if (Gate::allows('entity-authorities.manage-internal')) {
            SystemSetting::set('visibility_internal', $request->boolean('visibility_internal') ? '1' : '0', 'entity_authorities');
        }

        if (Gate::allows('entity-authorities.manage-external')) {
            SystemSetting::set('visibility_external', $request->boolean('visibility_external') ? '1' : '0', 'entity_authorities');
        }

        session()->flash('success', __('تم تحديث صلاحيات الجهات بنجاح'));

        return redirect()->back();
    }
}
