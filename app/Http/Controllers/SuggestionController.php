<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use ArPHP\I18N\Arabic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuggestionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Suggestion::query()
            ->select([
                'suggestions.id',
                'suggestions.content',
                'suggestions.gregorian_date',
                'suggestions.hijri_date',
                'suggestions.is_completed',
                'suggestions.user_id',
                'suggestions.entity_id',
                'suggestions.created_at',
            ])
            ->with([
                'user:id,name',
                'entity:id,name',
            ]);

        if (! $user->hasPermission('suggestions.view')) {
            $this->applySuggestionsScope($query, $user);
        }

        $suggestions = $query->latest('suggestions.created_at')->limit(50)->get();

        return response()->json($suggestions);
    }

    public function store(Request $request)
    {
        $this->authorize('suggestions.create');
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        try {
            $user = Auth::user();
            $now = Carbon::now();
            $ts = $now->timestamp;

            // Hijri date — single ArPHP instance, single call
            $hijriDate = 'Unknown';
            try {
                $arPHP = new Arabic;
                $hijriDate = $arPHP->date('d M Y', $ts, 1);
            } catch (\Exception $e) {
                \Log::warning('ArPHP failed in SuggestionController: '.$e->getMessage());
            }

            // Resolve geo IDs once and ensure they are NULL if zero to avoid FK violations
            $govId = $user->getAssignedGovernorateId() ?: null;
            $dirId = $user->getAssignedDirectorateId() ?: null;

            $suggestion = Suggestion::create([
                'content' => $request->content,
                'gregorian_date' => $now->toDateString(),
                'hijri_date' => $hijriDate,
                'user_id' => $user->id,
                'entity_id' => $user->entity_id,
                'governorate_id' => $govId,
                'directorate_id' => $dirId,
                'geographic_scope_id' => $govId,
                'administrative_scope_id' => $user->entity_id,
                'creator_username' => $user->username,
                'creator_entity_id' => $user->entity_id,
                'created_by' => $user->id,
            ]);

            // Return a lean response — only what the frontend needs
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال المقترح بنجاح',
                'suggestion' => [
                    'id' => $suggestion->id,
                    'content' => $suggestion->content,
                    'gregorian_date' => $suggestion->gregorian_date,
                    'hijri_date' => $suggestion->hijri_date,
                    'is_completed' => false,
                    'user' => ['id' => $user->id, 'name' => $user->name],
                    'entity' => $user->entity_id ? ['id' => $user->entity_id, 'name' => optional($user->internalEntity)->name] : null,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Suggestion Storage Failure: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'عذراً، حدث خطأ أثناء الحفظ: '.$e->getMessage(),
            ], 500);
        }
    }

    public function toggleComplete($id)
    {
        $this->authorize('suggestions.complete');
        $suggestion = Suggestion::findOrFail($id);

        $newState = ! $suggestion->is_completed;

        // update() fires full model events; use updateQuietly to skip audit for simple toggles
        $suggestion->updateQuietly(['is_completed' => $newState]);

        return response()->json(['success' => true, 'is_completed' => $newState]);
    }

    protected function applySuggestionsScope($query, $user)
    {
        $adminScope = $user->getModuleAdminScope('suggestions');
        $geoScope = $user->getModuleGeoScope('suggestions');

        $isGeoUser = ($user->governorate_id && $user->directorate_id) ||
            ($user->entity && $user->entity->governorate_id && $user->entity->directorate_id);

        if ($isGeoUser) {
            // Geographic user filtering
            if ($geoScope === 'none') {
                $query->whereRaw('1=0');

                return;
            }

            if ($geoScope === 'all') {
                // User said: "from other governorates"
                $uGovId = $user->getAssignedGovernorateId();
                if ($uGovId) {
                    $query->where('governorate_id', '!=', $uGovId);
                }
            } elseif ($geoScope === 'same_governorate') {
                $uGovId = $user->getAssignedGovernorateId();
                $query->where('governorate_id', $uGovId);
            } elseif ($geoScope === 'same_directorate') {
                $uDirId = $user->getAssignedDirectorateId();
                $query->where('directorate_id', $uDirId);
            } elseif ($geoScope === 'custom') {
                $this->applyAdministrativeScope($query, $user, $adminScope);
            }
        } else {
            // Central user filtering
            $this->applyAdministrativeScope($query, $user, $adminScope);
        }
    }

    protected function applyAdministrativeScope($query, $user, $scope)
    {
        switch ($scope) {
            case 'none':
                $query->whereRaw('1=0');
                break;
            case 'user':
                $query->where('created_by', $user->id);
                break;
            case 'own':
                $query->where('entity_id', $user->entity_id);
                break;
            case 'dept_in_gen_dir':
                $allowedIds = $this->getChildEntityIds($user->entity_id);
                $query->whereIn('entity_id', $allowedIds);
                break;
            case 'all':
                // Centralised system records - usually means everything
                break;
        }
    }

    protected function getChildEntityIds($entityId)
    {
        if (! $entityId) {
            return [];
        }

        return \Cache::remember("suggestion_child_entities_{$entityId}", now()->addMinutes(10), function () use ($entityId) {
            $parentId = \DB::table('internal_entities')->where('id', $entityId)->value('parent_id');
            if (! $parentId) {
                return [$entityId];
            }

            return \DB::table('internal_entities')
                ->where('parent_id', $parentId)
                ->orWhere('id', $parentId)
                ->pluck('id')
                ->toArray();
        });
    }
}
