<?php

namespace App\Http\Controllers\Project\Traits;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait ProjectValidationTrait
{
    protected $projectBasicRelations = [
        'program', 'domain', 'subdomain', 'intervention',
        'locations.governorate', 'mainObjectives', 'specialObjectives',
        'risks', 'cost', 'detail', 'implementingEntities',
        'participatingEntities', 'financings',
        'preliminaryActivities.procedures.costs',
        'preliminaryFinancialSummaries',
        'executiveActivities.actions.assignedEntities',
        'executiveActivities.actions.costs',
    ];

    protected $projectDetailedRelations = [
        'program', 'domain', 'subdomain', 'intervention', 'priority',
        'detail', 'locations.governorate', 'locations.directorate',
        'locations.subArea', 'locations.village', 'mainObjectives',
        'specialObjectives.results.outputs', 'objectiveResults', 'resultOutputs',
        'risks', 'cost', 'financings.fundingSource', 'financings.authority',
        'financings.financingType', 'financings.financingForm',
        'financings.subFinancingForm', 'supervisingAuthorities.authority',
        'supervisingAuthorities.parent', 'implementingEntities.authority',
        'implementingEntities.parent', 'participatingEntities.authority',
        'participatingEntities.parent',
        'preliminaryActivities.procedures.costs',
        'preliminaryFinancialSummaries.financialItem',
        'preliminaryFinancialSummaries.activity',
        'preliminaryFinancialSummaries.procedure',
        'preliminaryFinancialSummaries.cost',
        'executiveActivities.actions.assignedEntities',
        'executiveActivities.actions.costs', 'executiveFinancialSummaries',
    ];

    protected $projectEditRelations = [
        'detail', 'locations', 'mainObjectives', 'specialObjectives',
        'risks', 'cost', 'financings', 'supervisingAuthorities',
        'implementingEntities', 'participatingEntities',
        'preliminaryActivities.procedures.costs',
        'preliminaryFinancialSummaries',
        'executiveActivities.actions.assignedEntities',
        'executiveActivities.actions.costs',
    ];

    protected function getStoreValidationRules($request = null)
    {
        $isDraft = ($request ? $request->input('status') : request()->input('status')) === 'draft';

        return array_merge(
            $this->getBasicValidationRules($isDraft),
            $this->getProjectDetailsValidationRules($isDraft),
            $this->getProjectLocationsValidationRules($isDraft),
            $this->getProjectObjectivesValidationRules($isDraft),
            $this->getProjectRisksValidationRules($isDraft),
            $this->getProjectCostsValidationRules($isDraft),
            $this->getProjectFinancingsValidationRules($isDraft),
            $this->getProjectSupervisingAuthoritiesValidationRules($isDraft),
            $this->getImplementingEntitiesValidationRules($isDraft),
            $this->getParticipatingEntitiesValidationRules($isDraft),
            $this->getPreliminaryActivitiesValidationRules($isDraft),
            $this->getExecutiveActivitiesValidationRules($isDraft)
        );
    }

    protected function getUpdateValidationRules($request = null)
    {
        $isDraft = ($request ? $request->input('status') : request()->input('status')) === 'draft';

        return array_merge(
            $this->getBasicValidationRules($isDraft),
            $this->getProjectDetailsValidationRules($isDraft),
            $this->getProjectLocationsValidationRules($isDraft),
            $this->getProjectObjectivesValidationRules($isDraft),
            $this->getProjectRisksValidationRules($isDraft),
            $this->getProjectCostsValidationRules($isDraft),
            $this->getProjectFinancingsValidationRules($isDraft),
            $this->getProjectSupervisingAuthoritiesValidationRules($isDraft),
            $this->getImplementingEntitiesValidationRules($isDraft),
            $this->getParticipatingEntitiesValidationRules($isDraft),
            $this->getPreliminaryActivitiesValidationRules($isDraft),
            $this->getExecutiveActivitiesValidationRules($isDraft)
        );
    }

    protected function getAutoSaveValidationRules($request = null)
    {
        // Auto-save uses MINIMAL validation - only basic structure validation
        // User is still filling out the form step-by-step, so we accept partial data
        return [
            // Approval Phase - minimal validation
            'assembly_approval_notes' => 'nullable|string|max:1000',

            // Basic fields - minimal validation
            'status' => 'nullable|in:draft,final',
            'project_name' => 'nullable|string|max:255',
            'program_id' => 'nullable|exists:programs,id',
            'domain_id' => 'nullable|exists:domains,id',
            'subdomain_id' => 'nullable|exists:subdomains,id',
            'intervention_id' => 'nullable|exists:interventions,id',
            'priority_id' => 'nullable|exists:priorities,id',

            // Dates - minimal validation
            'start_date_gregorian' => 'nullable|date',
            'start_date_hijri' => 'nullable|string',
            'end_date_gregorian' => 'nullable|date',
            'end_date_hijri' => 'nullable|string',

            // Details - nullable
            'number_of_beneficiaries' => 'nullable|integer|min:1',
            'is_part_of_plan' => 'nullable|boolean',
            'project_summary' => 'nullable|string',
            'project_introduction' => 'nullable|string',
            'problem_and_justification' => 'nullable|string',
            'project_components' => 'nullable|string',
            'expected_impact' => 'nullable|string',

            // Objectives - only structure validation
            'main_objective' => 'nullable|string',
            'main_indicator' => 'nullable|string',
            'main_indicator_unit' => 'nullable|string',
            'special_objectives' => 'nullable|array',
            'objective_results' => 'nullable|array',
            'result_outputs' => 'nullable|array',

            // Locations - only structure validation
            'locations' => 'nullable|array',

            // Risks - only structure validation (no min:1 requirement)
            'risks' => 'nullable|array',
            'risks.*.risk' => 'nullable|string|max:500',
            'risks.*.risk_rate' => 'nullable|integer|min:1|max:10',
            'risks.*.proposed_solution' => 'nullable|string|max:500',

            // Entities - only structure validation (no min:1 requirement)
            'supervising_authorities' => 'nullable|array',
            'implementing_entities' => 'nullable|array',
            'participating_entities' => 'nullable|array',

            // Activities - only structure validation (no min:1 requirement)
            'preliminary_activities' => 'nullable|array',
            'executive_activities' => 'nullable|array',

            // Financings - only structure validation (no min:1 requirement)
            'financings' => 'nullable|array',
            'financings.*.funding_source_id' => 'nullable|exists:funding_sources,id',
            'financings.*.authority_id' => 'nullable|exists:authorities,id',
            'financings.*.financing_type_id' => 'nullable|exists:financing_types,id',
            'financings.*.financing_form_id' => 'nullable|exists:financing_forms,id',
            'financings.*.sub_financing_form_id' => 'nullable|exists:sub_financing_forms,id',
            'financings.*.financing_amount' => 'nullable|numeric|min:0',
            'financings.*.financing_percentage' => 'nullable|numeric|min:0|max:100',

            // Costs - nullable
            'project_cost.total_cost' => 'nullable|numeric|min:0',
            'project_cost.year_type' => 'nullable|in:gregorian,hijri',
            'project_cost.approval_date_hijri' => 'nullable|string',
            'project_cost.approval_year_gregorian' => 'nullable|integer',
        ];
    }

    // ==================== STEP-BY-STEP VALIDATION RULES ====================

    protected function getStep1Rules($isDraft = true)
    {
        return $this->getBasicValidationRules($isDraft);
    }

    protected function getStep2Rules($isDraft = true)
    {
        return array_merge(
            $this->getProjectDetailsValidationRules($isDraft),
            $this->getProjectLocationsValidationRules($isDraft),
            $this->getProjectObjectivesValidationRules($isDraft)
        );
    }

    protected function getStep3Rules($isDraft = true)
    {
        return array_merge(
            $this->getProjectRisksValidationRules($isDraft),
            $this->getProjectSupervisingAuthoritiesValidationRules($isDraft),
            $this->getImplementingEntitiesValidationRules($isDraft),
            $this->getParticipatingEntitiesValidationRules($isDraft),
            $this->getBeneficiaryEntitiesValidationRules($isDraft),
            $this->getProjectEntitiesValidationRules($isDraft)
        );
    }

    protected function getStep4Rules($isDraft = true)
    {
        return array_merge(
            $this->getPreliminaryActivitiesValidationRules($isDraft),
            // Preliminary financial summary validation if needed
            [
                'preliminary_financial_summaries' => 'nullable|array',
            ]
        );
    }

    protected function getStep5Rules($isDraft = true)
    {
        return array_merge(
            $this->getExecutiveActivitiesValidationRules($isDraft),
            // Executive financial summary validation if needed
            [
                'executive_financial_summaries' => 'nullable|array',
            ]
        );
    }

    protected function getStep6Rules($isDraft = true)
    {
        return array_merge(
            $this->getProjectCostsValidationRules($isDraft),
            $this->getProjectFinancingsValidationRules($isDraft)
        );
    }

    protected function getStep7Rules($isDraft = true)
    {
        // Step 7 is the review step. Data integrity was already validated in steps 1-6.
        // Running full validation here is expensive and causes severe delays.
        // We only validate the status field and optional notes.
        // The finalizeProject() logic enforces deeper business rules (weight totals, etc.) via validateFinalProjectRequirements.
        return [
            'status' => 'required|in:draft,final',
            'assembly_approval_notes' => 'nullable|string|max:1000',
        ];
    }

    private function getBasicValidationRules(bool $isDraft): array
    {
        return [
            'assembly_approval_notes' => 'nullable|string|max:1000',
            'project_name' => 'nullable|string|max:255',
            'program_id' => $isDraft ? 'nullable|exists:programs,id' : 'required|exists:programs,id',
            'domain_id' => $isDraft ? 'nullable|exists:domains,id' : 'required|exists:domains,id',
            'subdomain_id' => $isDraft ? 'nullable|exists:subdomains,id' : 'required|exists:subdomains,id',
            'intervention_id' => $isDraft ? 'nullable|exists:interventions,id' : 'required|exists:interventions,id',
            'start_date_gregorian' => 'nullable|date',
            'start_date_hijri' => 'nullable|string',
            'end_date_gregorian' => 'nullable|date|after:start_date_gregorian',
            'end_date_hijri' => 'nullable|string',
            'number_of_beneficiaries' => 'nullable|integer|min:0',
            'main_directives' => 'nullable|string',
            'subdirectives' => 'nullable|string',
            'priority' => 'nullable|string',
            'target_categories' => 'nullable|string',
            'priority_id' => 'nullable|exists:priorities,id',
            'status' => 'required|in:draft,final',
        ];
    }

    private function getProjectDetailsValidationRules(bool $isDraft): array
    {
        return [
            'is_part_of_plan' => 'nullable|boolean',
            'project_summary' => 'nullable|string',
            'project_introduction' => 'nullable|string',
            'problem_and_justification' => 'nullable|string',
            'project_components' => 'nullable|string',
            'expected_impact' => 'nullable|string',
        ];
    }

    private function getProjectLocationsValidationRules(bool $isDraft): array
    {
        return [
            'locations' => 'nullable|array',
            'locations.*.governorate_id' => 'nullable',
            'locations.*.directorate_id' => 'nullable',
            'locations.*.sub_area_id' => 'nullable',
            'locations.*.village_id' => 'nullable',
        ];
    }

    private function getProjectObjectivesValidationRules(bool $isDraft): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'main_objectives' => 'nullable|array',
            'main_objectives.*.objective' => $requiredRule.'|string|max:500',
            'main_objectives.*.indicator' => 'nullable|string|max:255',
            'main_objectives.*.indicator_unit' => 'nullable|string|max:100',
            'special_objectives' => 'nullable|array',
            'special_objectives.*.objective' => $requiredRule.'|string|max:500',
            'special_objectives.*.objective_weight' => $requiredRule.'|numeric|min:0|max:100',
            'special_objectives.*.target_value' => 'nullable|numeric',
            'special_objectives.*.measurement_unit' => 'nullable|string|max:100',
            'objective_results' => 'nullable|array',
            'objective_results.*.special_objective_id' => 'nullable|integer',
            'objective_results.*.result_name' => $requiredRule.'|string|max:500',
            'objective_results.*.target_value' => 'nullable|numeric',
            'objective_results.*.indicator_type' => 'nullable|in:quantitative,relative,qualitative',
            'objective_results.*.indicator_unit' => 'nullable|string|max:100',
            'result_outputs' => 'nullable|array',
            'result_outputs.*.special_objective_id' => 'nullable|integer',
            'result_outputs.*.objective_result_id' => 'nullable|integer',
            'result_outputs.*.output' => $requiredRule.'|string|max:500',
            'result_outputs.*.target_value' => 'nullable|numeric',
            'result_outputs.*.indicator_type' => 'nullable|in:quantitative,relative,qualitative',
            'result_outputs.*.indicator_unit' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom validation messages for project objectives
     */
    protected function getProjectObjectivesValidationMessages(): array
    {
        return [
            'special_objectives.*.objective.required' => 'نص الهدف الخاص مطلوب',
            'special_objectives.*.objective_weight.required' => 'وزن الهدف الخاص مطلوب',
            'special_objectives.*.measurement_unit.required' => 'وحدة القياس مطلوبة لكل هدف خاص',
            'objective_results.*.result_name.required' => 'اسم النتيجة مطلوب',
            'result_outputs.*.output.required' => 'نص المخرج مطلوب',
        ];
    }

    private function getProjectRisksValidationRules(bool $isDraft): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'risks' => 'nullable|array',
            'risks.*.risk' => $requiredRule.'|string|max:500',
            'risks.*.risk_rate' => $requiredRule.'|integer|min:1|max:10',
            'risks.*.proposed_solution' => 'nullable|string|max:500',
        ];
    }

    private function getProjectCostsValidationRules(bool $isDraft): array
    {
        return [
            'project_cost.total_cost' => 'nullable|numeric|min:0',
            'project_cost.year_type' => 'nullable|in:gregorian,hijri',
            'project_cost.approval_date_hijri' => 'nullable|string',
            'project_cost.approval_year_gregorian' => 'nullable|integer',
        ];
    }

    private function getProjectFinancingsValidationRules(bool $isDraft): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';
        $financingsRule = 'nullable|array';

        return [
            'financings' => $financingsRule,
            'financings.*.funding_source_id' => $requiredRule.'|exists:funding_sources,id',
            'financings.*.authority_id' => $requiredRule.'|exists:authorities,id',
            'financings.*.financing_type_id' => $requiredRule.'|exists:financing_types,id',
            'financings.*.financing_form_id' => $requiredRule.'|exists:financing_forms,id',
            'financings.*.sub_financing_form_id' => 'nullable|exists:sub_financing_forms,id',
            'financings.*.financing_amount' => $requiredRule.'|numeric|min:0',
            'financings.*.financing_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }

    private function getProjectSupervisingAuthoritiesValidationRules(bool $isDraft): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'supervising_authorities' => 'nullable|array',
            'supervising_authorities.*.authority_type' => $requiredRule.'|in:internal,external',
            'supervising_authorities.*.authority_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $typeAttribute = str_replace('.authority_id', '.authority_type', $attribute);
                    // Use request() helper as we are inside a trait used by controller
                    $type = request()->input($typeAttribute);

                    if ($type === 'external' && $value) {
                        if (! DB::table('authorities')->where('id', $value)->exists()) {
                            $fail('The selected supervising authority is invalid.');
                        }
                    }
                },
            ],
            'supervising_authorities.*.entity_id' => 'nullable|string',
            'supervising_authorities.*.internal_entity_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $typeAttribute = str_replace('.internal_entity_id', '.authority_type', $attribute);
                    $type = request()->input($typeAttribute);

                    if ($type === 'internal' && $value) {
                        if (! DB::table('internal_entities')->where('id', $value)->exists()) {
                            $fail('The selected internal supervising authority is invalid.');
                        }
                    }
                },
            ],
            'supervising_authorities.*.parent_id' => 'nullable',
        ];
    }

    private function getImplementingEntitiesValidationRules(bool $isDraft): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'implementing_entities' => 'nullable|array',
            'implementing_entities.*.authority_type' => $requiredRule.'|in:internal,external',
            'implementing_entities.*.authority_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $typeAttribute = str_replace('.authority_id', '.authority_type', $attribute);
                    $type = request()->input($typeAttribute) ?? request()->input(str_replace('.authority_type', '.entity_type', $typeAttribute));

                    if ($type === 'external' && $value) {
                        if (! DB::table('authorities')->where('id', $value)->exists()) {
                            $fail('The selected implementing entity is invalid.');
                        }
                    }
                },
            ],
            'implementing_entities.*.entity_id' => 'nullable|string',
            'implementing_entities.*.internal_entity_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $typeAttribute = str_replace('.internal_entity_id', '.authority_type', $attribute);
                    $type = request()->input($typeAttribute) ?? request()->input(str_replace('.authority_type', '.entity_type', $typeAttribute));

                    if ($type === 'internal' && $value) {
                        if (! DB::table('internal_entities')->where('id', $value)->exists()) {
                            $fail('The selected internal implementing entity is invalid.');
                        }
                    }
                },
            ],
            'implementing_entities.*.parent_id' => 'nullable',
        ];
    }

    private function getParticipatingEntitiesValidationRules(bool $isDraft): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'participating_entities' => 'nullable|array',
            'participating_entities.*.authority_type' => $requiredRule.'|in:internal,external',
            'participating_entities.*.authority_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $typeAttribute = str_replace('.authority_id', '.authority_type', $attribute);
                    $type = request()->input($typeAttribute) ?? request()->input(str_replace('.authority_type', '.entity_type', $typeAttribute));

                    if ($type === 'external' && $value) {
                        if (! DB::table('authorities')->where('id', $value)->exists()) {
                            $fail('The selected participating entity is invalid.');
                        }
                    }
                },
            ],
            'participating_entities.*.entity_id' => 'nullable|string',
            'participating_entities.*.internal_entity_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $typeAttribute = str_replace('.internal_entity_id', '.authority_type', $attribute);
                    $type = request()->input($typeAttribute) ?? request()->input(str_replace('.authority_type', '.entity_type', $typeAttribute));

                    if ($type === 'internal' && $value) {
                        if (! DB::table('internal_entities')->where('id', $value)->exists()) {
                            $fail('The selected internal participating entity is invalid.');
                        }
                    }
                },
            ],
            'participating_entities.*.parent_id' => 'nullable',
        ];
    }

    private function getBeneficiaryEntitiesValidationRules(bool $isDraft): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'beneficiary_entities' => 'nullable|array',
            'beneficiary_entities.*.authority_type' => $requiredRule.'|in:internal,external',
            'beneficiary_entities.*.authority_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $typeAttribute = str_replace('.authority_id', '.authority_type', $attribute);
                    $type = request()->input($typeAttribute);

                    if ($type === 'external' && $value) {
                        if (! DB::table('authorities')->where('id', $value)->exists()) {
                            $fail('The selected beneficiary entity is invalid.');
                        }
                    }
                },
            ],
            'beneficiary_entities.*.entity_id' => 'nullable|string',
            'beneficiary_entities.*.internal_entity_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $typeAttribute = str_replace('.internal_entity_id', '.authority_type', $attribute);
                    $type = request()->input($typeAttribute);

                    if ($type === 'internal' && $value) {
                        if (! DB::table('internal_entities')->where('id', $value)->exists()) {
                            $fail('The selected internal beneficiary entity is invalid.');
                        }
                    }
                },
            ],
            'beneficiary_entities.*.parent_id' => 'nullable',
        ];
    }

    private function getProjectEntitiesValidationRules(bool $isDraft): array
    {
        return [
            'project_entities' => 'nullable|array',
            'project_entities.*.entity_name' => 'nullable|string',
        ];
    }

    private function getPreliminaryActivitiesValidationRules(bool $isDraft): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'preliminary_activities' => 'nullable|array',
            'preliminary_activities.*.name' => $requiredRule.'|string|max:255',
            'preliminary_activities.*.weight' => $requiredRule.'|numeric|min:0|max:100',
            'preliminary_activities.*.procedures' => 'nullable|array',
            'preliminary_activities.*.procedures.*.procedure_name' => $requiredRule.'|string|max:255',
            'preliminary_activities.*.procedures.*.weight' => $requiredRule.'|numeric|min:0|max:100',
            'preliminary_activities.*.procedures.*.start_date' => 'nullable|date',
            'preliminary_activities.*.procedures.*.end_date' => 'nullable|date|after_or_equal:preliminary_activities.*.procedures.*.start_date',
            'preliminary_activities.*.procedures.*.verification_means' => 'nullable|string|max:500',
            'preliminary_activities.*.procedures.*.costs' => 'nullable|array',
            'preliminary_activities.*.procedures.*.costs.*.financial_item_id' => $requiredRule.'|exists:financial_items,id',
            'preliminary_activities.*.procedures.*.costs.*.unit_id' => 'nullable|exists:units,id',
            'preliminary_activities.*.procedures.*.costs.*.amount' => $requiredRule.'|numeric|min:0',
            'preliminary_activities.*.procedures.*.costs.*.quantity' => $requiredRule.'|integer|min:1',
        ];
    }

    private function getExecutiveActivitiesValidationRules(bool $isDraft): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'executive_activities' => 'nullable|array',
            'executive_activities.*.name' => $requiredRule.'|string|max:255',
            'executive_activities.*.weight' => $requiredRule.'|numeric|min:0|max:100',
            'executive_activities.*.output' => 'nullable|string|max:500',
            'executive_activities.*.risk' => 'nullable|string|max:500',
            'executive_activities.*.actions' => 'nullable|array',
            'executive_activities.*.actions.*.action' => $requiredRule.'|string|max:500',
            'executive_activities.*.actions.*.weight' => $requiredRule.'|numeric|min:0|max:100',
            'executive_activities.*.actions.*.start_date' => 'nullable|date',
            'executive_activities.*.actions.*.end_date' => 'nullable|date|after_or_equal:executive_activities.*.actions.*.start_date',
            'executive_activities.*.actions.*.verification_means' => 'nullable|string|max:500',
            'executive_activities.*.actions.*.assigned_entities' => 'nullable|array',
            'executive_activities.*.actions.*.assigned_entities.*.entity' => $requiredRule.'|string|max:255',
            'executive_activities.*.actions.*.assigned_entities.*.name' => $requiredRule.'|string|max:255',
            'executive_activities.*.actions.*.assigned_entities.*.task' => $requiredRule.'|string|max:500',
            'executive_activities.*.actions.*.costs' => 'nullable|array',
            'executive_activities.*.actions.*.costs.*.financial_item_id' => $requiredRule.'|exists:financial_items,id',
            'executive_activities.*.actions.*.costs.*.unit_id' => 'nullable|exists:units,id',
            'executive_activities.*.actions.*.costs.*.amount' => $requiredRule.'|numeric|min:0',
            'executive_activities.*.actions.*.costs.*.quantity' => $requiredRule.'|integer|min:1',
        ];
    }

    protected function validateRisksOnRequest($validator, Request $request): void
    {
        if ($request->has('risks')) {
            $risks = $request->input('risks', []);
            foreach ($risks as $index => $risk) {
                if (empty($risk['risk']) || empty($risk['risk_rate'])) {
                    $validator->errors()->add("risks.{$index}.risk", 'Risk description and rate are required');
                }
            }
        }
    }

    protected function validateSupervisingAuthoritiesOnRequest($validator, Request $request, ?Project $project = null): void
    {
        $isDraft = $request->input('status') === 'draft';
        $supervisingAuthorities = $request->input('supervising_authorities', []);

        if ($isDraft) {
            return;
        }

        // If request is empty, check database
        if (empty($supervisingAuthorities) && $project) {
            $dbCount = $project->supervisingAuthorities()->count();
            if ($dbCount > 0) {
                return; // Valid, found in DB
            }
        }

        // Log for debugging
        Log::debug('Validating supervising authorities', [
            'raw_count' => count($supervisingAuthorities),
            'data' => $supervisingAuthorities,
        ]);

        if (empty($supervisingAuthorities)) {
            $validator->errors()->add(
                'supervising_authorities',
                'At least one supervising authority is required to save the project as final.'
            );

            return;
        }

        foreach ($supervisingAuthorities as $index => $authority) {
            $type = $authority['authority_type'] ?? null;
            if (empty($type)) {
                $validator->errors()->add(
                    "supervising_authorities.{$index}.authority_type",
                    'Authority type is required for each authority.'
                );
            }

            if ($type === 'internal' && empty($authority['internal_entity_id'])) {
                $validator->errors()->add(
                    "supervising_authorities.{$index}.internal_entity_id",
                    'Entity name is required for each internal authority.'
                );
            } elseif ($type === 'external' && empty($authority['authority_id'])) {
                $validator->errors()->add(
                    "supervising_authorities.{$index}.authority_id",
                    'Authority name is required for each external authority.'
                );
            } elseif (empty($type) && empty($authority['authority_id']) && empty($authority['internal_entity_id'])) {
                $validator->errors()->add(
                    "supervising_authorities.{$index}.authority_id",
                    'Authority or Entity name is required.'
                );
            }
        }
    }

    protected function validateImplementingEntitiesOnRequest($validator, Request $request, ?Project $project = null): void
    {
        $isDraft = $request->input('status') === 'draft';
        $implementingEntities = $request->input('implementing_entities', []);

        if ($isDraft) {
            return;
        }

        // If request is empty, check database
        if (empty($implementingEntities) && $project) {
            $dbCount = $project->implementingEntities()->count();
            if ($dbCount > 0) {
                return; // Valid, found in DB
            }
        }

        // Log for debugging
        Log::debug('Validating implementing entities', [
            'raw_count' => count($implementingEntities),
            'data' => $implementingEntities,
        ]);

        if (empty($implementingEntities)) {
            $validator->errors()->add(
                'implementing_entities',
                'At least one implementing entity is required to save the project as final.'
            );

            return;
        }

        foreach ($implementingEntities as $index => $entity) {
            $type = $entity['authority_type'] ?? $entity['entity_type'] ?? null;
            if (empty($type)) {
                $validator->errors()->add(
                    "implementing_entities.{$index}.authority_type",
                    'Entity type is required for each entity.'
                );
            }

            if ($type === 'internal' && empty($entity['internal_entity_id'])) {
                $validator->errors()->add(
                    "implementing_entities.{$index}.internal_entity_id",
                    'Entity name is required for each internal entity.'
                );
            } elseif ($type === 'external' && empty($entity['authority_id'])) {
                $validator->errors()->add(
                    "implementing_entities.{$index}.authority_id",
                    'Authority name is required for each external entity.'
                );
            } elseif (empty($type) && empty($entity['authority_id']) && empty($entity['internal_entity_id'])) {
                $validator->errors()->add(
                    "implementing_entities.{$index}.authority_id",
                    'Authority or Entity name is required.'
                );
            }
        }
    }

    protected function validateParticipatingEntitiesOnRequest($validator, Request $request, ?Project $project = null): void
    {
        $isDraft = $request->input('status') === 'draft';
        $participatingEntities = $request->input('participating_entities', []);

        if ($isDraft) {
            return;
        }

        // If request is empty, check database
        if (empty($participatingEntities) && $project) {
            $dbCount = $project->participatingEntities()->count();
            if ($dbCount > 0) {
                return; // Valid, found in DB
            }
        }

        // Log for debugging
        Log::debug('Validating participating entities', [
            'raw_count' => count($participatingEntities),
            'data' => $participatingEntities,
        ]);

        if (empty($participatingEntities)) {
            $validator->errors()->add(
                'participating_entities',
                'At least one participating entity is required to save the project as final.'
            );

            return;
        }

        foreach ($participatingEntities as $index => $entity) {
            $type = $entity['authority_type'] ?? $entity['entity_type'] ?? null;
            if (empty($type)) {
                $validator->errors()->add(
                    "participating_entities.{$index}.authority_type",
                    'Entity type is required for each entity.'
                );
            }

            if ($type === 'internal' && empty($entity['internal_entity_id'])) {
                $validator->errors()->add(
                    "participating_entities.{$index}.internal_entity_id",
                    'Entity name is required for each internal entity.'
                );
            } elseif ($type === 'external' && empty($entity['authority_id'])) {
                $validator->errors()->add(
                    "participating_entities.{$index}.authority_id",
                    'Authority name is required for each external entity.'
                );
            } elseif (empty($type) && empty($entity['authority_id']) && empty($entity['internal_entity_id'])) {
                $validator->errors()->add(
                    "participating_entities.{$index}.authority_id",
                    'Authority or Entity name is required.'
                );
            }
        }
    }

    protected function validateBeneficiaryEntitiesOnRequest($validator, Request $request, ?Project $project = null): void
    {
        $isDraft = $request->input('status') === 'draft';
        $beneficiaryEntities = $request->input('beneficiary_entities', []);

        if ($isDraft) {
            return;
        }

        // If request is empty, check database
        if (empty($beneficiaryEntities) && $project) {
            $dbCount = $project->beneficiaryEntities()->count();
            if ($dbCount > 0) {
                return; // Valid, found in DB
            }
        }

        foreach ($beneficiaryEntities as $index => $entity) {
            $type = $entity['authority_type'] ?? $entity['entity_type'] ?? null;
            if (empty($type)) {
                $validator->errors()->add(
                    "beneficiary_entities.{$index}.authority_type",
                    'Entity type is required for each beneficiary entity.'
                );
            }

            if ($type === 'internal' && empty($entity['internal_entity_id'])) {
                $validator->errors()->add(
                    "beneficiary_entities.{$index}.internal_entity_id",
                    'Entity name is required for each internal beneficiary entity.'
                );
            } elseif ($type === 'external' && empty($entity['authority_id'])) {
                $validator->errors()->add(
                    "beneficiary_entities.{$index}.authority_id",
                    'Authority name is required for each external beneficiary entity.'
                );
            } elseif (empty($type) && empty($entity['authority_id']) && empty($entity['internal_entity_id'])) {
                $validator->errors()->add(
                    "beneficiary_entities.{$index}.authority_id",
                    'Authority or Entity name is required.'
                );
            }
        }
    }

    protected function validatePreliminaryActivitiesOnRequest($validator, Request $request): void
    {
        if (! $request->has('preliminary_activities')) {
            return;
        }

        $preliminaryActivities = $request->input('preliminary_activities', []);

        // Validate minimum 1 activity (for final submission only)
        $isDraft = $request->input('status') === 'draft';
        if (! $isDraft) {
            $activityCount = count(array_filter($preliminaryActivities, function ($act) {
                return ! empty($act['name']);
            }));

            if ($activityCount < 1) {
                $validator->errors()->add(
                    'preliminary_activities',
                    'At least one preliminary activity is required.'
                );
            }
        }

        foreach ($preliminaryActivities as $activityIndex => $activity) {
            $activityWeight = floatval($activity['weight'] ?? 0);
            if ($activityWeight < 0 || $activityWeight > 100) {
                $validator->errors()->add(
                    "preliminary_activities.{$activityIndex}.weight",
                    'Activity weight must be between 0 and 100.'
                );
            }

            // Validate minimum 1 procedure per activity (for final submission only)
            $isDraft = $request->input('status') === 'draft';
            if (! $isDraft) {
                $procedures = isset($activity['procedures']) ? $activity['procedures'] : [];
                $procedureCount = count(array_filter($procedures, function ($proc) {
                    return ! empty($proc['procedure_name']);
                }));

                if ($procedureCount < 1) {
                    $activityName = $activity['name'] ?? 'Activity '.($activityIndex + 1);
                    $validator->errors()->add(
                        "preliminary_activities.{$activityIndex}.procedures",
                        "{$activityName} must contain at least one procedure."
                    );
                }
            }

            if (isset($activity['procedures'])) {
                foreach ($activity['procedures'] as $procedureIndex => $procedure) {
                    $procedureWeight = floatval($procedure['weight'] ?? 0);
                    if ($procedureWeight < 0 || $procedureWeight > 100) {
                        $validator->errors()->add(
                            "preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.weight",
                            'Procedure weight must be between 0 and 100.'
                        );
                    }

                    if (isset($procedure['costs'])) {
                        foreach ($procedure['costs'] as $costIndex => $cost) {
                            if (empty($cost['financial_item_id'])) {
                                $validator->errors()->add(
                                    "preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.costs.{$costIndex}.financial_item_id",
                                    'Financial item is required for each cost.'
                                );
                            }

                            $amount = floatval($cost['amount'] ?? 0);
                            $quantity = intval($cost['quantity'] ?? 0);

                            if ($amount < 0) {
                                $validator->errors()->add(
                                    "preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.costs.{$costIndex}.amount",
                                    'Cost amount cannot be negative.'
                                );
                            }

                            if ($quantity < 1) {
                                $validator->errors()->add(
                                    "preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.costs.{$costIndex}.quantity",
                                    'Cost quantity must be at least 1.'
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    protected function validateExecutiveActivitiesOnRequest($validator, Request $request): void
    {
        if (! $request->has('executive_activities')) {
            return;
        }

        $executiveActivities = $request->input('executive_activities', []);

        // Validate minimum 1 activity (for final submission only)
        $isDraft = $request->input('status') === 'draft';
        if (! $isDraft) {
            $activityCount = count(array_filter($executiveActivities, function ($act) {
                return ! empty($act['name']);
            }));

            if ($activityCount < 1) {
                $validator->errors()->add(
                    'executive_activities',
                    'At least one executive activity is required.'
                );
            }
        }

        foreach ($executiveActivities as $activityIndex => $activity) {
            $activityWeight = floatval($activity['weight'] ?? 0);
            if ($activityWeight < 0 || $activityWeight > 100) {
                $validator->errors()->add(
                    "executive_activities.{$activityIndex}.weight",
                    'Activity weight must be between 0 and 100.'
                );
            }

            // Validate minimum 1 action per activity (for final submission only)
            if (! $isDraft) {
                $actions = isset($activity['actions']) ? $activity['actions'] : [];
                $actionCount = count(array_filter($actions, function ($actn) {
                    return ! empty($actn['action_name']) || ! empty($actn['name']);
                }));

                if ($actionCount < 1) {
                    $activityName = $activity['name'] ?? 'Activity '.($activityIndex + 1);
                    $validator->errors()->add(
                        "executive_activities.{$activityIndex}.actions",
                        "{$activityName} must contain at least one action."
                    );
                }
            }

            if (isset($activity['actions'])) {
                foreach ($activity['actions'] as $actionIndex => $action) {
                    $actionWeight = floatval($action['weight'] ?? 0);
                    if ($actionWeight < 0 || $actionWeight > 100) {
                        $validator->errors()->add(
                            "executive_activities.{$activityIndex}.actions.{$actionIndex}.weight",
                            'Action weight must be between 0 and 100.'
                        );
                    }

                    if (isset($action['assigned_entities'])) {
                        foreach ($action['assigned_entities'] as $assignedIndex => $assigned) {
                            if (empty($assigned['entity'])) {
                                $validator->errors()->add(
                                    "executive_activities.{$activityIndex}.actions.{$actionIndex}.assigned_entities.{$assignedIndex}.entity",
                                    'Entity is required for each assigned entity.'
                                );
                            }

                            if (empty($assigned['name'])) {
                                $validator->errors()->add(
                                    "executive_activities.{$activityIndex}.actions.{$actionIndex}.assigned_entities.{$assignedIndex}.name",
                                    'Name is required for each assigned entity.'
                                );
                            }
                        }
                    }

                    if (isset($action['costs'])) {
                        foreach ($action['costs'] as $costIndex => $cost) {
                            if (empty($cost['financial_item_id'])) {
                                $validator->errors()->add(
                                    "executive_activities.{$activityIndex}.actions.{$actionIndex}.costs.{$costIndex}.financial_item_id",
                                    'Financial item is required for each cost.'
                                );
                            }

                            $amount = floatval($cost['amount'] ?? 0);
                            $quantity = intval($cost['quantity'] ?? 0);

                            if ($amount < 0) {
                                $validator->errors()->add(
                                    "executive_activities.{$activityIndex}.actions.{$actionIndex}.costs.{$costIndex}.amount",
                                    'Cost amount cannot be negative.'
                                );
                            }

                            if ($quantity < 1) {
                                $validator->errors()->add(
                                    "executive_activities.{$activityIndex}.actions.{$actionIndex}.costs.{$costIndex}.quantity",
                                    'Cost quantity must be at least 1.'
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    protected function validateFinancingsOnRequest($validator, Request $request, ?Project $project = null): void
    {
        $isDraft = $request->input('status') === 'draft';
        $financings = $request->input('financings', []);

        if ($isDraft) {
            return;
        }

        // If request is empty, check database
        if (empty($financings) && $project) {
            $dbCount = $project->financings()->count();
            if ($dbCount > 0) {
                return; // Valid, found in DB
            }
        }

        // Log for debugging
        Log::debug('Validating financings', [
            'raw_count' => count($financings),
            'data' => $financings,
        ]);

        if (empty($financings)) {
            $validator->errors()->add(
                'financings',
                'At least one financing source is required to save the project as final.'
            );

            return;
        }

        $totalAmount = 0;
        foreach ($financings as $index => $financing) {
            if (empty($financing['funding_source_id'])) {
                $validator->errors()->add(
                    "financings.{$index}.funding_source_id",
                    'Funding source is required for each financing.'
                );
            }

            if (empty($financing['authority_id'])) {
                $validator->errors()->add(
                    "financings.{$index}.authority_id",
                    'Authority is required for each financing.'
                );
            }

            if (empty($financing['financing_type_id'])) {
                $validator->errors()->add(
                    "financings.{$index}.financing_type_id",
                    'Financing type is required for each financing.'
                );
            }

            if (empty($financing['financing_form_id'])) {
                $validator->errors()->add(
                    "financings.{$index}.financing_form_id",
                    'Financing form is required for each financing.'
                );
            }

            if (empty($financing['financing_amount']) || $financing['financing_amount'] <= 0) {
                $validator->errors()->add(
                    "financings.{$index}.financing_amount",
                    'Financing amount must be greater than 0.'
                );
            } else {
                $totalAmount += floatval($financing['financing_amount']);
            }
        }

        // Skip financing amount validation - users can have partial financing
        // or other funding sources not listed in this field
    }

    protected function validateFinalProjectRequirements($validator, Request $request, ?Project $project = null): void
    {
        // Log all validation data for debugging
        Log::debug('=== Starting Final Project Validation ===', [
            'status' => $request->input('status'),
            'project_name' => $request->input('project_name'),
            'program_id' => $request->input('program_id'),
            'priority_id' => $request->input('priority_id'),
            'has_supervising_authorities' => $request->has('supervising_authorities'),
            'has_implementing_entities' => $request->has('implementing_entities'),
            'has_participating_entities' => $request->has('participating_entities'),
            'has_financings' => $request->has('financings'),
            'has_preliminary_activities' => $request->has('preliminary_activities'),
            'has_executive_activities' => $request->has('executive_activities'),
            'project_id' => $project ? $project->id : 'null',
        ]);

        if ($request->has('special_objectives')) {
            $specialObjectives = $request->input('special_objectives', []);
            $totalWeight = 0;

            foreach ($specialObjectives as $objective) {
                $totalWeight += floatval($objective['objective_weight'] ?? 0);
            }

            if ($totalWeight != 100) {
                $validator->errors()->add('special_objectives', 'The sum of special objectives weights must equal 100% (current: '.$totalWeight.'%)');
            }
        }

        $this->validateResultOutputsOnRequest($validator, $request);
        $this->validateActivitiesOnRequest($validator, $request, $project);
        $this->validateSupervisingAuthoritiesOnRequest($validator, $request, $project);
        $this->validateImplementingEntitiesOnRequest($validator, $request, $project);
        $this->validateParticipatingEntitiesOnRequest($validator, $request, $project);
        $this->validateBeneficiaryEntitiesOnRequest($validator, $request, $project);
        $this->validateFinancingsOnRequest($validator, $request, $project);

        Log::debug('=== Final Project Validation Complete ===', [
            'has_errors' => $validator->errors()->isNotEmpty(),
            'error_count' => $validator->errors()->count(),
            'errors' => $validator->errors()->toArray(),
        ]);
    }

    protected function validateActivitiesOnRequest($validator, Request $request, ?Project $project = null): void
    {
        $isDraft = $request->input('status') === 'draft';
        if ($isDraft) {
            return;
        }

        $preliminaryActivities = $request->input('preliminary_activities', []);
        $executiveActivities = $request->input('executive_activities', []);

        // Log for debugging
        Log::debug('Validating activities', [
            'preliminary_raw_count' => count($preliminaryActivities),
            'executive_raw_count' => count($executiveActivities),
        ]);

        // Enforce minimum 1 preliminary activity
        $prelimCount = count(array_filter($preliminaryActivities, function ($act) {
            return ! empty(trim($act['name'] ?? ''));
        }));

        // If request is empty, check database
        if ($prelimCount < 1 && $project) {
            $prelimCount = $project->preliminaryActivities()->count();
        }

        Log::debug('Preliminary activities count after filtering/DB check', ['count' => $prelimCount]);

        if ($prelimCount < 1) {
            $validator->errors()->add('preliminary_activities', "يجب إضافة نشاط تمهيدي واحد على الأقل. (العدد الحالي: {$prelimCount})");
        }

        // Validate preliminary procedures
        foreach ($preliminaryActivities as $index => $activity) {
            if (empty(trim($activity['name'] ?? ''))) {
                continue; // Skip empty activities
            }

            $procedures = $activity['procedures'] ?? [];
            $procedureCount = count(array_filter($procedures, function ($p) {
                return ! empty(trim($p['procedure_name'] ?? ''));
            }));

            if ($procedureCount < 1) {
                $activityName = $activity['name'] ?? 'النشاط رقم '.($index + 1);
                $validator->errors()->add("preliminary_activities.{$index}.procedures", "النشاط التمهيدي '{$activityName}' يجب أن يحتوي على إجراء واحد على الأقل. (العدد الحالي: {$procedureCount})");
            }
        }

        // Enforce minimum 1 executive activity
        $executiveCount = count(array_filter($executiveActivities, function ($act) {
            return ! empty(trim($act['name'] ?? ''));
        }));

        // If request is empty, check database
        if ($executiveCount < 1 && $project) {
            $executiveCount = $project->executiveActivities()->count();
        }

        Log::debug('Executive activities count after filtering/DB check', ['count' => $executiveCount]);

        if ($executiveCount < 1) {
            $validator->errors()->add('executive_activities', "يجب إضافة نشاط تنفيذي واحد على الأقل. (العدد الحالي: {$executiveCount})");
        }

        // Validate executive actions
        foreach ($executiveActivities as $index => $activity) {
            if (empty(trim($activity['name'] ?? ''))) {
                continue; // Skip empty activities
            }

            $actions = $activity['actions'] ?? [];
            $actionCount = count(array_filter($actions, function ($a) {
                return ! empty(trim($a['action'] ?? '')) || ! empty(trim($a['action_name'] ?? '')) || ! empty(trim($a['name'] ?? ''));
            }));

            if ($actionCount < 1) {
                $activityName = $activity['name'] ?? 'النشاط رقم '.($index + 1);
                $validator->errors()->add("executive_activities.{$index}.actions", "النشاط التنفيذي '{$activityName}' يجب أن يحتوي على إجراء واحد (Action) على الأقل. (العدد الحالي: {$actionCount})");
            }
        }
    }

    protected function validateResultOutputsOnRequest($validator, Request $request): void
    {
        if (! $request->has('result_outputs')) {
            return;
        }

        $outputs = $request->input('result_outputs', []);

        foreach ($outputs as $index => $output) {
            // Fields are optional now, only the output name is required
        }
    }
}
