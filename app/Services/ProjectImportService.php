<?php

namespace App\Services;

use App\Exceptions\MissingDropdownValuesException;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectImportService
{
    // -----------------------------------------------------------------------
    // Explicit dropdown field registry
    // -----------------------------------------------------------------------
    // IMPORTANT: Only fields listed here are subject to dropdown validation.
    // We do NOT auto-detect by _id suffix to avoid touching internal FKs.
    // -----------------------------------------------------------------------

    /**
     * Top-level project fields that map to reference tables.
     * 'col' is the column used for name-based lookup.
     * 'parent' is used for hierarchical filtering (governorate → directorate → sub_area).
     */
    private const DROPDOWN_FIELDS = [
        'program_id' => [
            'table' => 'programs',
            'label' => 'البرنامج',
            'col' => 'name',
            'parent' => null,
        ],
        'domain_id' => [
            'table' => 'domains',
            'label' => 'المجال',
            'col' => 'name',
            'parent' => null,
        ],
        'subdomain_id' => [
            'table' => 'subdomains',
            'label' => 'المجال الفرعي',
            'col' => 'name',
            'parent' => null,
        ],
        'intervention_id' => [
            'table' => 'interventions',
            'label' => 'التدخل',
            'col' => 'name',
            'parent' => null,
        ],
        'priority_id' => [
            'table' => 'priorities',
            'label' => 'الأولوية',
            'col' => 'priority',   // priorities.priority column
            'parent' => null,
        ],
        'target_category_id' => [
            'table' => 'target_categories',
            'label' => 'فئة المستفيد',
            'col' => 'name',
            'parent' => null,
        ],
        'governorate_id' => [
            'table' => 'governorates',
            'label' => 'المحافظة',
            'col' => 'name',
            'parent' => null,
        ],
    ];

    /**
     * Fields inside each location entry (sub-sheet 'locations').
     * These are validated per-location row, not at the project root.
     */
    private const LOCATION_DROPDOWN_FIELDS = [
        'governorate_id' => [
            'table' => 'governorates',
            'label' => 'المحافظة',
            'col' => 'name',
            'parent' => null,
        ],
        'directorate_id' => [
            'table' => 'directorates',
            'label' => 'المديرية',
            'col' => 'name',
            'parent' => 'governorate_id',  // must belong to the resolved governorate
            'parent_fk' => 'governorate_id',
        ],
        'sub_area_id' => [
            'table' => 'sub_areas',
            'label' => 'العزلة',
            'col' => 'name',
            'parent' => 'directorate_id',
            'parent_fk' => 'directorate_id',
        ],
    ];

    /**
     * Fields inside each financing entry (sub-sheet 'financings').
     */
    private const FINANCING_DROPDOWN_FIELDS = [
        'funding_source_id' => [
            'table' => 'funding_sources',
            'label' => 'مصدر التمويل',
            'col' => 'name',
            'parent' => null,
        ],
        'financing_type_id' => [
            'table' => 'financing_types',
            'label' => 'نوع التمويل',
            'col' => 'name',
            'parent' => null,
        ],
        'financing_form_id' => [
            'table' => 'financing_forms',
            'label' => 'شكل التمويل',
            'col' => 'name',
            'parent' => null,
        ],
        'sub_financing_form_id' => [
            'table' => 'sub_financing_forms',
            'label' => 'شكل التمويل الفرعي',
            'col' => 'name',
            'parent' => null,
        ],
    ];

    /**
     * Fields inside each entity entry (sub-sheet 'entities').
     */
    private const ENTITY_DROPDOWN_FIELDS = [
        'authority_id' => [
            'table' => 'authorities',
            'label' => 'الجهة',
            'col' => 'agency_name',
            'parent' => null,
        ],
    ];

    /**
     * Fields inside preliminary/executive activities.
     */
    private const ACTIVITY_DROPDOWN_FIELDS = [
        'financial_item_id' => [
            'table' => 'financial_items',
            'label' => 'البند المالي',
            'col' => 'name',
            'parent' => null,
        ],
    ];

    // -----------------------------------------------------------------------
    // In-memory lookup cache (table___value → id|null)
    // Keyed per table to guarantee no cross-table contamination.
    // -----------------------------------------------------------------------
    private array $lookupCache = [];

    // -----------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------

    /**
     * Validate all dropdown values in $data, resolve them to IDs, then persist.
     *
     * Throws MissingDropdownValuesException if ANY reference value is missing.
     * Never creates new records automatically.
     * All sub-operations run inside a single DB transaction (Atomic).
     *
     * @throws MissingDropdownValuesException
     * @throws \Exception
     */
    public function import(array $data): Project
    {
        // Step 1 — Resolve and validate. Collect ALL missing values before deciding.
        [$resolvedData, $missingValues] = $this->resolveAndValidate($data);

        if (! empty($missingValues)) {
            throw new MissingDropdownValuesException($missingValues);
        }

        // Step 2 — All values are present → persist atomically.
        return DB::transaction(function () use ($resolvedData) {
            return $this->persist($resolvedData);
        });
    }

    // -----------------------------------------------------------------------
    // Resolution
    // -----------------------------------------------------------------------

    /**
     * Walk through $data and resolve all explicit dropdown fields to IDs.
     * Returns [$resolvedData, $missingValues].
     * NEVER creates new records.
     */
    private function resolveAndValidate(array $data): array
    {
        $missing = [];

        // ── Top-level project fields ─────────────────────────────────────
        foreach (self::DROPDOWN_FIELDS as $fieldKey => $meta) {
            if (! array_key_exists($fieldKey, $data)) {
                continue;
            }
            $raw = trim((string) ($data[$fieldKey] ?? ''));
            if ($raw === '' || is_numeric($raw)) {
                // Empty or already an ID → leave as-is
                continue;
            }
            $resolved = $this->lookupId($meta['table'], $meta['col'], $raw);
            if ($resolved === null) {
                $missing[] = [
                    'field_key' => $fieldKey,
                    'field_label' => $meta['label'],
                    'table_name' => $meta['table'],
                    'value' => $raw,
                    'parent_fk' => $meta['parent_fk'] ?? null,
                    'parent_id' => null,
                ];
            } else {
                $data[$fieldKey] = $resolved;
            }
        }

        // ── Locations ────────────────────────────────────────────────────
        foreach ($data['locations'] ?? [] as $locIdx => &$loc) {
            $resolvedGovId = null;
            $resolvedDirId = null;

            foreach (self::LOCATION_DROPDOWN_FIELDS as $fieldKey => $meta) {
                if (! array_key_exists($fieldKey, $loc)) {
                    continue;
                }
                $raw = trim((string) ($loc[$fieldKey] ?? ''));
                if ($raw === '' || is_numeric($raw)) {
                    if ($fieldKey === 'governorate_id' && is_numeric($raw)) {
                        $resolvedGovId = (int) $raw;
                    }
                    if ($fieldKey === 'directorate_id' && is_numeric($raw)) {
                        $resolvedDirId = (int) $raw;
                    }

                    continue;
                }

                // Hierarchical constraint
                $parentId = null;
                if ($meta['parent'] === 'governorate_id') {
                    $parentId = $resolvedGovId;
                } elseif ($meta['parent'] === 'directorate_id') {
                    $parentId = $resolvedDirId;
                }

                $resolved = $this->lookupId($meta['table'], $meta['col'], $raw, $meta['parent_fk'] ?? null, $parentId);
                if ($resolved === null) {
                    $missing[] = [
                        'field_key' => $fieldKey,
                        'field_label' => $meta['label'].' (موقع '.($locIdx + 1).')',
                        'table_name' => $meta['table'],
                        'value' => $raw,
                        'parent_fk' => $meta['parent_fk'] ?? null,
                        'parent_id' => $parentId,
                    ];
                } else {
                    $loc[$fieldKey] = $resolved;
                    if ($fieldKey === 'governorate_id') {
                        $resolvedGovId = $resolved;
                    }
                    if ($fieldKey === 'directorate_id') {
                        $resolvedDirId = $resolved;
                    }
                }
            }
        }
        unset($loc);

        // ── Financings ───────────────────────────────────────────────────
        foreach ($data['financings'] ?? [] as $finIdx => &$fin) {
            foreach (self::FINANCING_DROPDOWN_FIELDS as $fieldKey => $meta) {
                if (! array_key_exists($fieldKey, $fin)) {
                    continue;
                }
                $raw = trim((string) ($fin[$fieldKey] ?? ''));
                if ($raw === '' || is_numeric($raw)) {
                    continue;
                }
                $resolved = $this->lookupId($meta['table'], $meta['col'], $raw);
                if ($resolved === null) {
                    $missing[] = [
                        'field_key' => $fieldKey,
                        'field_label' => $meta['label'].' (تمويل '.($finIdx + 1).')',
                        'table_name' => $meta['table'],
                        'value' => $raw,
                        'parent_fk' => $meta['parent_fk'] ?? null,
                        'parent_id' => null,
                    ];
                } else {
                    $fin[$fieldKey] = $resolved;
                }
            }
        }
        unset($fin);

        // ── Entities ─────────────────────────────────────────────────────
        foreach (['supervising_authorities', 'implementing_entities', 'participating_entities', 'beneficiary_entities'] as $entityGroup) {
            foreach ($data[$entityGroup] ?? [] as $entIdx => &$ent) {
                foreach (self::ENTITY_DROPDOWN_FIELDS as $fieldKey => $meta) {
                    if (! array_key_exists($fieldKey, $ent)) {
                        continue;
                    }
                    $raw = trim((string) ($ent[$fieldKey] ?? ''));
                    if ($raw === '' || is_numeric($raw)) {
                        continue;
                    }
                    $resolved = $this->lookupId($meta['table'], $meta['col'], $raw);
                    if ($resolved === null) {
                        $missing[] = [
                            'field_key' => $fieldKey,
                            'field_label' => $meta['label']." ({$entityGroup} ".($entIdx + 1).')',
                            'table_name' => $meta['table'],
                            'value' => $raw,
                            'parent_fk' => $meta['parent_fk'] ?? null,
                            'parent_id' => null,
                        ];
                    } else {
                        $ent[$fieldKey] = $resolved;
                    }
                }
            }
            unset($ent);
        }

        // ── Activities (preliminary) — financial_item_id ─────────────────
        foreach ($data['preliminary_activities'] ?? [] as &$activity) {
            foreach ($activity['procedures'] ?? [] as &$procedure) {
                foreach ($procedure['costs'] ?? [] as &$cost) {
                    foreach (self::ACTIVITY_DROPDOWN_FIELDS as $fieldKey => $meta) {
                        if (! array_key_exists($fieldKey, $cost)) {
                            continue;
                        }
                        $raw = trim((string) ($cost[$fieldKey] ?? ''));
                        if ($raw === '' || is_numeric($raw)) {
                            continue;
                        }
                        $resolved = $this->lookupId($meta['table'], $meta['col'], $raw);
                        if ($resolved === null) {
                            $missing[] = [
                                'field_key' => $fieldKey,
                                'field_label' => $meta['label'].' (نشاط تمهيدي)',
                                'table_name' => $meta['table'],
                                'value' => $raw,
                                'parent_fk' => $meta['parent_fk'] ?? null,
                                'parent_id' => null,
                            ];
                        } else {
                            $cost[$fieldKey] = $resolved;
                        }
                    }
                }
                unset($cost);
            }
            unset($procedure);
        }
        unset($activity);

        // ── Activities (executive) — financial_item_id ───────────────────
        foreach ($data['executive_activities'] ?? [] as &$activity) {
            foreach ($activity['actions'] ?? [] as &$action) {
                foreach ($action['costs'] ?? [] as &$cost) {
                    foreach (self::ACTIVITY_DROPDOWN_FIELDS as $fieldKey => $meta) {
                        if (! array_key_exists($fieldKey, $cost)) {
                            continue;
                        }
                        $raw = trim((string) ($cost[$fieldKey] ?? ''));
                        if ($raw === '' || is_numeric($raw)) {
                            continue;
                        }
                        $resolved = $this->lookupId($meta['table'], $meta['col'], $raw);
                        if ($resolved === null) {
                            $missing[] = [
                                'field_key' => $fieldKey,
                                'field_label' => $meta['label'].' (نشاط تنفيذي)',
                                'table_name' => $meta['table'],
                                'value' => $raw,
                                'parent_fk' => $meta['parent_fk'] ?? null,
                                'parent_id' => null,
                            ];
                        } else {
                            $cost[$fieldKey] = $resolved;
                        }
                    }
                }
                unset($cost);
            }
            unset($action);
        }
        unset($activity);

        return [$data, $missing];
    }

    /**
     * Look up an ID in a reference table by name.
     * Uses in-memory cache (keyed by table___lowervalue[___parentId]) to avoid
     * repeated queries and guarantee no cross-table confusion.
     *
     * @param  string  $table  Target table
     * @param  string  $col  Name column to search
     * @param  string  $value  Raw text value from Excel
     * @param  string|null  $parentFk  Foreign-key column for hierarchical filter (e.g. 'governorate_id')
     * @param  int|null  $parentId  Resolved parent ID
     */
    private function lookupId(string $table, string $col, string $value, ?string $parentFk = null, ?int $parentId = null): ?int
    {
        $cacheKey = $table.'___'.mb_strtolower($value).'___'.($parentId ?? '');

        if (array_key_exists($cacheKey, $this->lookupCache)) {
            return $this->lookupCache[$cacheKey];
        }

        try {
            $query = DB::table($table)->where($col, 'LIKE', '%'.$value.'%');

            if ($parentFk !== null && $parentId !== null) {
                $query->where($parentFk, $parentId);
            }

            $record = $query->select('id')->first();
            $id = $record ? (int) $record->id : null;
        } catch (\Exception) {
            $id = null;
        }

        $this->lookupCache[$cacheKey] = $id;

        return $id;
    }

    /**
     * Expose lookup for use by DropdownValueMappingService (re-import).
     */
    public function resolveId(string $table, string $col, string $value, ?string $parentFk = null, ?int $parentId = null): ?int
    {
        return $this->lookupId($table, $col, $value, $parentFk, $parentId);
    }

    /**
     * Expose the explicit field registry for use in MappingService / Controller.
     */
    public static function getDropdownFieldMeta(string $fieldKey): ?array
    {
        return
            self::DROPDOWN_FIELDS[$fieldKey]
            ?? self::LOCATION_DROPDOWN_FIELDS[$fieldKey]
            ?? self::FINANCING_DROPDOWN_FIELDS[$fieldKey]
            ?? self::ENTITY_DROPDOWN_FIELDS[$fieldKey]
            ?? self::ACTIVITY_DROPDOWN_FIELDS[$fieldKey]
            ?? null;
    }

    /**
     * All distinct field keys across all registries.
     */
    public static function getAllDropdownFields(): array
    {
        return array_merge(
            self::DROPDOWN_FIELDS,
            self::LOCATION_DROPDOWN_FIELDS,
            self::FINANCING_DROPDOWN_FIELDS,
            self::ENTITY_DROPDOWN_FIELDS,
            self::ACTIVITY_DROPDOWN_FIELDS
        );
    }

    // -----------------------------------------------------------------------
    // Persistence (unchanged logic, only called after successful validation)
    // -----------------------------------------------------------------------

    private function persist(array $data): Project
    {
        $formNumber = $data['form_number'] ?? null;
        if (empty($formNumber)) {
            $hijriYear = $data['hijri_year'] ?? null;
            if (empty($hijriYear) && ! empty($data['start_date_hijri'])) {
                $parts = preg_split('/[\/\-]/', $data['start_date_hijri']);
                foreach ($parts as $part) {
                    if (strlen($part) == 4 && str_starts_with($part, '14')) {
                        $hijriYear = $part;
                        break;
                    }
                }
            }
            $existingProject = Project::where('project_name', $data['project_name'])->first();
            if ($existingProject && $existingProject->form_number) {
                $formNumber = $existingProject->form_number;
            } else {
                $formNumber = ProjectNumberGenerator::getNextProjectNumber($hijriYear);
            }
        }

        $project = Project::updateOrCreate(
            ['project_name' => $data['project_name'] ?? null],
            [
                'program_id' => $data['program_id'] ?? null,
                'project_type' => $data['project_type'] ?? 'new',
                'domain_id' => $data['domain_id'] ?? null,
                'subdomain_id' => $data['subdomain_id'] ?? null,
                'intervention_id' => $data['intervention_id'] ?? null,
                'priority_id' => $data['priority_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'approval_status' => $data['approval_status'] ?? 'pending',
                'current_approval_stage_id' => $data['current_approval_stage_id'] ?? null,
                'current_stage' => $data['current_stage'] ?? null,
                'form_number' => $formNumber,
                'qr_code' => $data['qr_code'] ?? null,
                'start_date_gregorian' => $data['start_date_gregorian'] ?? null,
                'start_date_hijri' => $data['start_date_hijri'] ?? null,
                'end_date_gregorian' => $data['end_date_gregorian'] ?? null,
                'end_date_hijri' => $data['end_date_hijri'] ?? null,
                'number_of_beneficiaries' => $data['number_of_beneficiaries'] ?? 0,
                'main_directives' => $data['main_directives'] ?? null,
                'subdirectives' => $data['subdirectives'] ?? null,
                'target_categories' => $data['target_categories'] ?? null,
                'target_category_id' => $data['target_category_id'] ?? null,
                'draft_saved_at' => $data['draft_saved_at'] ?? null,
                'finalized_at' => $data['finalized_at'] ?? null,
                'execution_started_at' => $data['execution_started_at'] ?? null,
                'completed_at' => $data['completed_at'] ?? null,
                'last_saved_step' => $data['last_saved_step'] ?? null,
                'created_by_user_id' => $data['created_by_user_id'] ?? null,
                'created_by_entity' => $data['created_by_entity'] ?? null,
                'updated_by_user_id' => $data['updated_by_user_id'] ?? null,
                'updated_by_entity' => $data['updated_by_entity'] ?? null,
                'frappe_synced' => $data['frappe_synced'] ?? false,
                'frappe_sync_at' => $data['frappe_sync_at'] ?? null,
                'frappe_sync_attempts' => $data['frappe_sync_attempts'] ?? 0,
                'frappe_project_id' => $data['frappe_project_id'] ?? null,
                'frappe_sync_error' => $data['frappe_sync_error'] ?? null,
                'frappe_last_sync_attempt' => $data['frappe_last_sync_attempt'] ?? null,
                'erpnext_project_id' => $data['erpnext_project_id'] ?? null,
                'sync_status' => $data['sync_status'] ?? 'pending',
                'synced_to_erpnext_at' => $data['synced_to_erpnext_at'] ?? null,
                'frappe_project_name' => $data['frappe_project_name'] ?? null,
                'frappe_sync_status' => $data['frappe_sync_status'] ?? 'pending',
                'frappe_synced_at' => $data['frappe_synced_at'] ?? null,
                'sync_error' => $data['sync_error'] ?? null,
            ]
        );

        // Detail
        if (! empty($data['detail'])) {
            $project->detail()->updateOrCreate(['project_id' => $project->id], $data['detail']);
        }

        // Cost
        if (isset($data['total_cost']) || isset($data['hijri_year']) || isset($data['spent_amount']) || isset($data['remaining_amount'])) {
            $totalCost = isset($data['total_cost']) && $data['total_cost'] !== '' ? floatval($data['total_cost']) : null;
            $spentAmount = isset($data['spent_amount']) && $data['spent_amount'] !== '' ? floatval($data['spent_amount']) : null;
            $remainingAmount = isset($data['remaining_amount']) && $data['remaining_amount'] !== '' ? floatval($data['remaining_amount']) : null;
            if ($totalCost !== null && $spentAmount !== null && $remainingAmount === null) {
                $remainingAmount = $totalCost - $spentAmount;
            }
            $project->cost()->updateOrCreate(
                ['project_id' => $project->id],
                ['total_cost' => $totalCost, 'spent_amount' => $spentAmount, 'remaining_amount' => $remainingAmount, 'hijri_year' => $data['hijri_year'] ?? null]
            );
        }

        // Locations
        foreach ($data['locations'] ?? [] as $loc) {
            if (empty($loc['governorate_id']) && empty($loc['directorate_id'])) {
                continue;
            }
            $project->locations()->updateOrCreate(
                ['project_id' => $project->id, 'governorate_id' => $loc['governorate_id'] ?? null, 'directorate_id' => $loc['directorate_id'] ?? null],
                $loc
            );
        }

        // Main Objectives
        foreach ($data['main_objectives'] ?? [] as $obj) {
            if (empty($obj['objective'])) {
                continue;
            }
            $project->mainObjectives()->updateOrCreate(['project_id' => $project->id, 'objective' => $obj['objective']], $obj);
        }

        // Special Objectives
        foreach ($data['special_objectives'] ?? [] as $obj) {
            if (empty($obj['objective'])) {
                continue;
            }
            $project->specialObjectives()->updateOrCreate(['project_id' => $project->id, 'objective' => $obj['objective']], $obj);
        }

        // Results & Outputs
        foreach ($data['objective_results'] ?? [] as $res) {
            if (empty($res['result_name'])) {
                continue;
            }
            $result = $project->objectiveResults()->updateOrCreate(['project_id' => $project->id, 'result_name' => $res['result_name']], $res);
            foreach ($res['outputs'] ?? [] as $out) {
                if (empty($out['output'])) {
                    continue;
                }
                $result->outputs()->updateOrCreate(['objective_result_id' => $result->id, 'output' => $out['output']], $out);
            }
        }

        // Preliminary Activities
        foreach ($data['preliminary_activities'] ?? [] as $activity) {
            if (empty($activity['name'])) {
                continue;
            }
            $act = $project->preliminaryActivities()->updateOrCreate(['project_id' => $project->id, 'name' => $activity['name']], $activity);
            foreach ($activity['procedures'] ?? [] as $procedure) {
                if (empty($procedure['procedure_name'])) {
                    continue;
                }
                $proc = $act->procedures()->updateOrCreate(['activity_id' => $act->id, 'procedure_name' => $procedure['procedure_name']], $procedure);
                foreach ($procedure['costs'] ?? [] as $cost) {
                    if (empty($cost['financial_item_id'])) {
                        continue;
                    }
                    $proc->costs()->updateOrCreate(['procedure_id' => $proc->id, 'financial_item_id' => $cost['financial_item_id']], $cost);
                }
            }
        }

        // Executive Activities
        foreach ($data['executive_activities'] ?? [] as $activity) {
            if (empty($activity['name'])) {
                continue;
            }
            $act = $project->executiveActivities()->updateOrCreate(['project_id' => $project->id, 'name' => $activity['name']], $activity);
            foreach ($activity['actions'] ?? [] as $action) {
                if (empty($action['action'])) {
                    continue;
                }
                $ac = $act->actions()->updateOrCreate(['executive_activity_id' => $act->id, 'action' => $action['action']], $action);
                foreach ($action['assigned_entities'] ?? [] as $ent) {
                    if (! empty($ent['entity_id'])) {
                        $ac->assignedEntities()->syncWithoutDetaching([$ent['entity_id']]);
                    }
                }
                foreach ($action['costs'] ?? [] as $cost) {
                    if (empty($cost['financial_item_id'])) {
                        continue;
                    }
                    $ac->costs()->updateOrCreate(['executive_activity_action_id' => $ac->id, 'financial_item_id' => $cost['financial_item_id']], $cost);
                }
            }
        }

        // Financings
        foreach ($data['financings'] ?? [] as $fin) {
            if (empty($fin['funding_source_id'])) {
                continue;
            }
            $project->financings()->updateOrCreate(['project_id' => $project->id, 'funding_source_id' => $fin['funding_source_id']], $fin);
        }

        // Supervising Authorities
        foreach ($data['supervising_authorities'] ?? [] as $authData) {
            if (empty($authData['authority_id'])) {
                continue;
            }
            unset($authData['entity_name']);
            $project->supervisingAuthorities()->updateOrCreate(['project_id' => $project->id, 'authority_id' => $authData['authority_id']], $authData);
        }
        foreach ($data['implementing_entities'] ?? [] as $implData) {
            if (empty($implData['authority_id'])) {
                continue;
            }
            unset($implData['entity_name']);
            $project->implementingEntities()->updateOrCreate(['project_id' => $project->id, 'authority_id' => $implData['authority_id']], $implData);
        }
        foreach ($data['participating_entities'] ?? [] as $partData) {
            if (empty($partData['authority_id'])) {
                continue;
            }
            unset($partData['entity_name']);
            $project->participatingEntities()->updateOrCreate(['project_id' => $project->id, 'authority_id' => $partData['authority_id']], $partData);
        }
        foreach ($data['beneficiary_entities'] ?? [] as $benData) {
            if (empty($benData['authority_id'])) {
                continue;
            }
            unset($benData['entity_name']);
            $project->beneficiaryEntities()->updateOrCreate(['project_id' => $project->id, 'authority_id' => $benData['authority_id']], $benData);
        }

        return $project;
    }
}
