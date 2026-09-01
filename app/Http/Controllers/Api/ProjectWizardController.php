<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Directorate;
use App\Models\Domain;
use App\Models\Entity;
use App\Models\FinancialItem;
use App\Models\FinancingType;
use App\Models\FundingSource;
use App\Models\Governorate;
use App\Models\Intervention;
use App\Models\Program;
use App\Models\Project;
use App\Models\SubArea;
use App\Models\Subdomain;
use App\Models\Village;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectWizardController extends Controller
{
    /**
     * Get initial data for project creation wizard
     */
    public function getInitialData()
    {
        try {
            $data = [
                'programs' => Program::select('id', 'program_name')->get(),
                'domains' => Domain::select('id', 'domain_name')->get(),
                'subdomains' => Subdomain::select('id', 'subdomain_name', 'domain_id')->get(),
                'interventions' => Intervention::select('id', 'intervention_name', 'subdomain_id')->get(),
                'governorates' => Governorate::select('id', 'governorate_name')->get(),
                'directorates' => Directorate::select('id', 'directorate_name', 'governorate_id')->get(),
                'sub_areas' => SubArea::select('id', 'sub_area_name', 'directorate_id')->get(),
                'villages' => Village::select('id', 'village_name', 'sub_area_id')->get(),
                'entities' => Entity::select('id', 'entity_name', 'entity_type_id')->with('entityType')->get(),
                'financial_items' => FinancialItem::select('id', 'item_name')->get(),
                'funding_sources' => FundingSource::select('id', 'source_name')->get(),
                'financing_types' => FinancingType::select('id', 'type_name')->get(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب البيانات الأولية: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Step 1: Project data and location
     */
    public function saveStep1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'program_id' => 'required|exists:programs,id',
            'domain_id' => 'required|exists:domains,id',
            'subdomain_id' => 'required|exists:subdomains,id',
            'intervention_id' => 'required|exists:interventions,id',
            'start_date_gregorian' => 'required|date',
            'start_date_hijri' => 'required|string',
            'end_date_gregorian' => 'required|date|after_or_equal:start_date_gregorian',
            'end_date_hijri' => 'required|string',
            'number_of_beneficiaries' => 'required|integer|min:1',
            'locations' => 'required|array|min:1',
            'locations.*.governorate_id' => 'required|exists:governorates,id',
            'locations.*.directorate_id' => 'required|exists:directorates,id',
            'locations.*.sub_area_id' => 'required|exists:sub_areas,id',
            'locations.*.village_id' => 'required|exists:villages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $projectData = $request->only([
                'project_name', 'program_id', 'domain_id', 'subdomain_id',
                'intervention_id', 'start_date_gregorian', 'start_date_hijri',
                'end_date_gregorian', 'end_date_hijri', 'number_of_beneficiaries',
            ]);

            $projectData['status'] = 'draft';
            $projectData['draft_saved_at'] = Carbon::now();

            $project = Project::create($projectData);

            // Save locations
            foreach ($request->locations as $location) {
                $project->locations()->create($location);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم حفظ الخطوة الأولى بنجاح',
                'project_id' => $project->id,
                'form_number' => $project->form_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في حفظ البيانات: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Step 2: Project details and objectives
     */
    public function saveStep2(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validator = Validator::make($request->all(), [
            'is_part_of_plan' => 'required|boolean',
            'plan_name' => 'nullable|string|max:255',
            'project_description' => 'required|string',
            'project_justification' => 'required|string',
            'project_goals' => 'required|string',
            'main_objectives' => 'required|array|min:1',
            'main_objectives.*.objective_description' => 'required|string',
            'main_objectives.*.objective_weight' => 'required|numeric|min:0|max:100',
            'special_objectives' => 'required|array|min:1',
            'special_objectives.*.objective_description' => 'required|string',
            'special_objectives.*.objective_weight' => 'required|numeric|min:0|max:100',
        ]);

        // Validate that special objectives weights sum to 100
        $validator->after(function ($validator) use ($request) {
            if ($request->has('special_objectives')) {
                $totalWeight = collect($request->special_objectives)->sum('objective_weight');
                if ($totalWeight != 100) {
                    $validator->errors()->add('special_objectives', 'مجموع أوزان الأهداف الخاصة يجب أن يساوي 100');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update project details
            $project->detail()->updateOrCreate(
                ['project_id' => $project->id],
                $request->only([
                    'is_part_of_plan', 'plan_name', 'project_description',
                    'project_justification', 'project_goals',
                ])
            );

            // Save main objectives
            $project->mainObjectives()->delete();
            foreach ($request->main_objectives as $objective) {
                $project->mainObjectives()->create($objective);
            }

            // Save special objectives
            $project->specialObjectives()->delete();
            foreach ($request->special_objectives as $objective) {
                $project->specialObjectives()->create($objective);
            }

            $project->update(['draft_saved_at' => Carbon::now()]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم حفظ الخطوة الثانية بنجاح',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في حفظ البيانات: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Step 3: Project stakeholders and risks
     */
    public function saveStep3(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validator = Validator::make($request->all(), [
            'entities' => 'required|array|min:1',
            'entities.*.entity_id' => 'required|exists:entities,id',
            'entities.*.role' => 'required|string|max:255',
            'entities.*.responsibilities' => 'required|string',
            'risks' => 'required|array|min:1',
            'risks.*.risk_description' => 'required|string',
            'risks.*.risk_level' => 'required|in:low,medium,high,critical',
            'risks.*.mitigation_strategy' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Save project entities
            $project->entities()->delete();
            foreach ($request->entities as $entity) {
                $project->entities()->create($entity);
            }

            // Save project risks
            $project->risks()->delete();
            foreach ($request->risks as $risk) {
                $project->risks()->create($risk);
            }

            $project->update(['draft_saved_at' => Carbon::now()]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم حفظ الخطوة الثالثة بنجاح',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في حفظ البيانات: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Step 4: Activities
     */
    public function saveStep4(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validator = Validator::make($request->all(), [
            'preliminary_activities' => 'required|array|min:1',
            'preliminary_activities.*.activity_name' => 'required|string|max:255',
            'preliminary_activities.*.activity_weight' => 'required|numeric|min:0|max:100',
            'preliminary_activities.*.actions' => 'required|array|min:1',
            'preliminary_activities.*.actions.*.action' => 'required|string|max:255',
            'preliminary_activities.*.actions.*.action_weight' => 'required|numeric|min:0|max:100',
            'preliminary_activities.*.actions.*.start_date' => 'required|date',
            'preliminary_activities.*.actions.*.end_date' => 'required|date|after_or_equal:preliminary_activities.*.actions.*.start_date',
            'executive_activities' => 'required|array|min:1',
            'executive_activities.*.activity_name' => 'required|string|max:255',
            'executive_activities.*.activity_weight' => 'required|numeric|min:0|max:100',
            'executive_activities.*.actions' => 'required|array|min:1',
            'executive_activities.*.actions.*.action' => 'required|string|max:255',
            'executive_activities.*.actions.*.action_weight' => 'required|numeric|min:0|max:100',
            'executive_activities.*.actions.*.start_date' => 'required|date',
            'executive_activities.*.actions.*.end_date' => 'required|date|after_or_equal:executive_activities.*.actions.*.start_date',
        ]);

        // Validate that action weights sum to 100 for each activity
        $validator->after(function ($validator) use ($request) {
            if ($request->has('preliminary_activities')) {
                foreach ($request->preliminary_activities as $index => $activity) {
                    if (isset($activity['actions'])) {
                        $totalWeight = collect($activity['actions'])->sum('action_weight');
                        if ($totalWeight != 100) {
                            $validator->errors()->add("preliminary_activities.{$index}.actions", 'مجموع أوزان إجراءات النشاط يجب أن يساوي 100');
                        }
                    }
                }
            }

            if ($request->has('executive_activities')) {
                foreach ($request->executive_activities as $index => $activity) {
                    if (isset($activity['actions'])) {
                        $totalWeight = collect($activity['actions'])->sum('action_weight');
                        if ($totalWeight != 100) {
                            $validator->errors()->add("executive_activities.{$index}.actions", 'مجموع أوزان إجراءات النشاط يجب أن يساوي 100');
                        }
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Save preliminary activities
            $project->preliminaryActivities()->delete();
            foreach ($request->preliminary_activities as $activity) {
                $preliminaryActivity = $project->preliminaryActivities()->create([
                    'activity_name' => $activity['activity_name'],
                    'activity_weight' => $activity['activity_weight'],
                ]);

                foreach ($activity['actions'] as $action) {
                    $preliminaryActivity->actions()->create([
                        'project_id' => $project->id,
                        'action' => $action['action'],
                        'action_weight' => $action['action_weight'],
                        'start_date' => $action['start_date'],
                        'end_date' => $action['end_date'],
                    ]);
                }
            }

            // Save executive activities
            $project->executiveActivities()->delete();
            foreach ($request->executive_activities as $activity) {
                $executiveActivity = $project->executiveActivities()->create([
                    'activity_name' => $activity['activity_name'],
                    'activity_weight' => $activity['activity_weight'],
                ]);

                foreach ($activity['actions'] as $action) {
                    $executiveActivity->actions()->create([
                        'project_id' => $project->id,
                        'action' => $action['action'],
                        'action_weight' => $action['action_weight'],
                        'start_date' => $action['start_date'],
                        'end_date' => $action['end_date'],
                    ]);
                }
            }

            $project->update(['draft_saved_at' => Carbon::now()]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم حفظ الخطوة الرابعة بنجاح',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في حفظ البيانات: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Step 5: Cost and funding
     */
    public function saveStep5(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validator = Validator::make($request->all(), [
            'total_cost' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'cost_breakdown' => 'nullable|string',
            'financings' => 'required|array|min:1',
            'financings.*.funding_source_id' => 'required|exists:funding_sources,id',
            'financings.*.financing_type_id' => 'required|exists:financing_types,id',
            'financings.*.amount' => 'required|numeric|min:0',
            'financings.*.percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Validate that financing percentages sum to 100
        $validator->after(function ($validator) use ($request) {
            if ($request->has('financings')) {
                $totalPercentage = collect($request->financings)->sum('percentage');
                if ($totalPercentage != 100) {
                    $validator->errors()->add('financings', 'مجموع نسب التمويل يجب أن يساوي 100%');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Save project cost
            $project->cost()->updateOrCreate(
                ['project_id' => $project->id],
                $request->only(['total_cost', 'currency', 'cost_breakdown'])
            );

            // Save project financings
            $project->financings()->delete();
            foreach ($request->financings as $financing) {
                $project->financings()->create($financing);
            }

            $project->update(['draft_saved_at' => Carbon::now()]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم حفظ الخطوة الخامسة بنجاح',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في حفظ البيانات: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get project data for review
     */
    public function getProjectReview($projectId)
    {
        try {
            $project = Project::with([
                'program',
                'domain',
                'subdomain',
                'intervention',
                'detail',
                'locations.governorate',
                'locations.directorate',
                'locations.subArea',
                'locations.village',
                'mainObjectives',
                'specialObjectives',
                'entities.entity',
                'risks',
                'preliminaryActivities.actions',
                'executiveActivities.actions',
                'cost',
                'financings.fundingSource',
                'financings.financingType',
            ])->findOrFail($projectId);

            return response()->json([
                'status' => 'success',
                'project' => $project,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب بيانات المشروع: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Finalize project (validate and save as final)
     */
    public function finalizeProject($projectId)
    {
        try {
            $project = Project::with([
                'specialObjectives',
                'preliminaryActivities.actions',
                'executiveActivities.actions',
            ])->findOrFail($projectId);

            // Validation checks
            $errors = [];

            // Check special objectives weights sum to 100
            $specialObjectivesWeight = $project->specialObjectives->sum('objective_weight');
            if ($specialObjectivesWeight != 100) {
                $errors[] = 'مجموع أوزان الأهداف الخاصة يجب أن يساوي 100 (الحالي: '.$specialObjectivesWeight.')';
            }

            // Check preliminary activities action weights
            foreach ($project->preliminaryActivities as $activity) {
                $actionsWeight = $activity->actions->sum('action_weight');
                if ($actionsWeight != 100) {
                    $errors[] = 'مجموع أوزان إجراءات النشاط التمهيدي "'.$activity->activity_name.'" يجب أن يساوي 100 (الحالي: '.$actionsWeight.')';
                }
            }

            // Check executive activities action weights
            foreach ($project->executiveActivities as $activity) {
                $actionsWeight = $activity->actions->sum('action_weight');
                if ($actionsWeight != 100) {
                    $errors[] = 'مجموع أوزان إجراءات النشاط التنفيذي "'.$activity->activity_name.'" يجب أن يساوي 100 (الحالي: '.$actionsWeight.')';
                }
            }

            if (! empty($errors)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'يجب تصحيح الأخطاء التالية قبل الحفظ النهائي:',
                    'errors' => $errors,
                ], 422);
            }

            // Update project status to final
            $project->update([
                'status' => 'final',
                'finalized_at' => Carbon::now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم حفظ المشروع نهائياً بنجاح',
                'project' => $project,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في الحفظ النهائي: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get project data for editing (by step)
     * This method ensures compatibility with projects created via traditional form
     */
    public function getProjectStep($projectId, $step)
    {
        try {
            $project = Project::findOrFail($projectId);

            // Ensure compatibility with traditional form data
            $project = $this->ensureWizardCompatibility($project);

            switch ($step) {
                case 1:
                    $project->load(['locations.governorate', 'locations.directorate', 'locations.subArea', 'locations.village']);
                    break;
                case 2:
                    $project->load(['detail', 'mainObjectives', 'specialObjectives']);
                    break;
                case 3:
                    $project->load(['entities.entity', 'risks']);
                    break;
                case 4:
                    $project->load(['preliminaryActivities.actions', 'executiveActivities.actions']);
                    break;
                case 5:
                    $project->load(['cost', 'financings.fundingSource', 'financings.financingType']);
                    break;
                default:
                    return response()->json([
                        'status' => 'error',
                        'message' => 'رقم الخطوة غير صحيح',
                    ], 400);
            }

            // Convert to wizard format if needed
            $project = $this->convertToWizardFormat($project, $step);

            return response()->json([
                'status' => 'success',
                'project' => $project,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب بيانات المشروع: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update existing project step
     */
    public function updateProjectStep(Request $request, $projectId, $step)
    {
        $project = Project::findOrFail($projectId);

        // Check if project is finalized
        if ($project->status === 'final') {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن تعديل مشروع تم حفظه نهائياً',
            ], 403);
        }

        switch ($step) {
            case 1:
                return $this->updateStep1($request, $project);
            case 2:
                return $this->updateStep2($request, $project);
            case 3:
                return $this->updateStep3($request, $project);
            case 4:
                return $this->updateStep4($request, $project);
            case 5:
                return $this->updateStep5($request, $project);
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'رقم الخطوة غير صحيح',
                ], 400);
        }
    }

    private function updateStep1(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'program_id' => 'required|exists:programs,id',
            'domain_id' => 'required|exists:domains,id',
            'subdomain_id' => 'required|exists:subdomains,id',
            'intervention_id' => 'required|exists:interventions,id',
            'start_date_gregorian' => 'required|date',
            'start_date_hijri' => 'required|string',
            'end_date_gregorian' => 'required|date|after_or_equal:start_date_gregorian',
            'end_date_hijri' => 'required|string',
            'number_of_beneficiaries' => 'required|integer|min:1',
            'locations' => 'required|array|min:1',
            'locations.*.governorate_id' => 'required|exists:governorates,id',
            'locations.*.directorate_id' => 'required|exists:directorates,id',
            'locations.*.sub_area_id' => 'required|exists:sub_areas,id',
            'locations.*.village_id' => 'required|exists:villages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $project->update($request->only([
                'project_name', 'program_id', 'domain_id', 'subdomain_id',
                'intervention_id', 'start_date_gregorian', 'start_date_hijri',
                'end_date_gregorian', 'end_date_hijri', 'number_of_beneficiaries',
            ]));

            // Update locations
            $project->locations()->delete();
            foreach ($request->locations as $location) {
                $project->locations()->create($location);
            }

            $project->update(['draft_saved_at' => Carbon::now()]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث الخطوة الأولى بنجاح',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في تحديث البيانات: '.$e->getMessage(),
            ], 500);
        }
    }

    private function updateStep2(Request $request, Project $project)
    {
        // Similar validation and update logic as saveStep2
        return $this->saveStep2($request, $project->id);
    }

    private function updateStep3(Request $request, Project $project)
    {
        // Similar validation and update logic as saveStep3
        return $this->saveStep3($request, $project->id);
    }

    private function updateStep4(Request $request, Project $project)
    {
        // Similar validation and update logic as saveStep4
        return $this->saveStep4($request, $project->id);
    }

    private function updateStep5(Request $request, Project $project)
    {
        // Similar validation and update logic as saveStep5
        return $this->saveStep5($request, $project->id);
    }

    /**
     * Export project to PDF
     */
    public function exportToPDF($projectId)
    {
        try {
            $project = Project::with([
                'program',
                'domain',
                'subdomain',
                'intervention',
                'detail',
                'locations.governorate',
                'locations.directorate',
                'locations.subArea',
                'locations.village',
                'mainObjectives',
                'specialObjectives',
                'entities.entity',
                'risks',
                'preliminaryActivities.actions',
                'executiveActivities.actions',
                'cost',
                'financings.fundingSource',
                'financings.financingType',
            ])->findOrFail($projectId);

            // Create PDF using DomPDF
            $pdf = Pdf::loadView('projects.pdf.project-report', compact('project'));

            // تحديد حجم الورقة والاتجاه
            $pdf->setPaper('A4', 'portrait');

            // تفعيل خيارات دعم العربية و HTML5
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true, // لتحليل HTML5 بشكل أفضل
                'isPhpEnabled' => true,        // للسماح بتنفيذ PHP داخل الـ View
                'defaultFont' => 'DejaVu Sans', // لدعم النصوص العربية
            ]);

            // اسم الملف النهائي
            $fileName = 'project_'.$projectId.'_'.date('Y-m-d').'.pdf';

            // تحميل الـ PDF مباشرة
            return $pdf->download($fileName);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في تصدير الملف: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if project data is compatible with wizard format
     * and convert if necessary
     */
    private function ensureWizardCompatibility(Project $project)
    {
        // Ensure project has draft_saved_at if it doesn't exist
        if (! $project->draft_saved_at && $project->status === 'draft') {
            $project->update(['draft_saved_at' => $project->created_at]);
        }

        // Ensure project has proper status
        if (! in_array($project->status, ['draft', 'final'])) {
            $project->update(['status' => 'draft']);
        }

        return $project;
    }

    /**
     * Convert traditional form data to wizard format
     */
    private function convertToWizardFormat($project, $step)
    {
        switch ($step) {
            case 1:
                // Step 1 data is already compatible
                return $project;

            case 2:
                // Ensure objectives have proper structure
                if ($project->mainObjectives->isEmpty()) {
                    $project->mainObjectives()->create([
                        'objective_description' => '',
                        'objective_weight' => 0,
                    ]);
                }
                if ($project->specialObjectives->isEmpty()) {
                    $project->specialObjectives()->create([
                        'objective_description' => '',
                        'objective_weight' => 0,
                    ]);
                }

                return $project;

            case 3:
                // Ensure entities and risks have proper structure
                if ($project->entities->isEmpty()) {
                    $project->entities()->create([
                        'entity_id' => null,
                        'role' => '',
                        'responsibilities' => '',
                    ]);
                }
                if ($project->risks->isEmpty()) {
                    $project->risks()->create([
                        'risk_description' => '',
                        'risk_level' => '',
                        'mitigation_strategy' => '',
                    ]);
                }

                return $project;

            case 4:
                // Ensure activities have proper structure
                if ($project->preliminaryActivities->isEmpty()) {
                    $activity = $project->preliminaryActivities()->create([
                        'activity_name' => '',
                        'activity_weight' => 0,
                    ]);
                    $activity->actions()->create([
                        'action' => '',
                        'action_weight' => 0,
                        'start_date' => '',
                        'end_date' => '',
                    ]);
                }
                if ($project->executiveActivities->isEmpty()) {
                    $activity = $project->executiveActivities()->create([
                        'activity_name' => '',
                        'activity_weight' => 0,
                    ]);
                    $activity->actions()->create([
                        'action' => '',
                        'action_weight' => 0,
                        'start_date' => '',
                        'end_date' => '',
                    ]);
                }

                return $project;

            case 5:
                // Ensure cost and financing have proper structure
                if (! $project->cost) {
                    $project->cost()->create([
                        'total_cost' => 0,
                        'currency' => 'USD',
                        'cost_breakdown' => '',
                    ]);
                }
                if ($project->financings->isEmpty()) {
                    $project->financings()->create([
                        'funding_source_id' => null,
                        'financing_type_id' => null,
                        'amount' => 0,
                        'percentage' => 0,
                        'notes' => '',
                    ]);
                }

                return $project;

            default:
                return $project;
        }
    }
}
