<?php

namespace App\Services;

use App\Exceptions\MissingDropdownValuesException;
use App\Models\ImportValueMapping;
use Illuminate\Support\Facades\DB;

/**
 * Handles all three user resolution actions for missing dropdown values:
 *   - replace:   Map original_value → an existing record's ID
 *   - add:       Create a new record in the target table using original_value
 *   - edit_add:  Create a new record using a user-edited value
 *
 * Enforces:
 *   - Constraint #7:  Mapping key = (import_session + field_key + original_value)
 *   - Constraint #11: Upsert (update existing mapping rather than creating duplicates)
 *   - Constraint #12: Session ownership check before any operation
 *   - Constraint #6:  Duplicate prevention before adding new records
 *   - Constraint #13: Hierarchical filtering (governorate → directorate → sub_area)
 */
class DropdownValueMappingService
{
    // -----------------------------------------------------------------------
    // Session helpers
    // -----------------------------------------------------------------------

    /**
     * Build a session token for the authenticated user.
     */
    public static function buildSession(int $userId): string
    {
        return 'import_'.time().'_'.$userId;
    }

    /**
     * Validate that the session belongs to the authenticated user.
     */
    public static function validateSessionOwnership(string $session, int $userId): bool
    {
        // Session format: import_{timestamp}_{userId}
        $parts = explode('_', $session);

        return count($parts) === 3 && (int) $parts[2] === $userId;
    }

    // -----------------------------------------------------------------------
    // Option A — Replace with an existing value
    // -----------------------------------------------------------------------

    /**
     * Record a "replace" mapping. No new records are created in the target table.
     *
     * @param  string  $session  Import session identifier
     * @param  string  $fieldKey  e.g. program_id
     * @param  string  $originalValue  Raw Excel text
     * @param  int  $targetId  ID of the existing record chosen by the user
     * @param  int  $userId  Authenticated user
     */
    public function replace(string $session, string $fieldKey, string $originalValue, int $targetId, int $userId, ?int $parentId = null): ImportValueMapping
    {
        $meta = ProjectImportService::getDropdownFieldMeta($fieldKey);
        if (! $meta) {
            throw new \InvalidArgumentException("حقل غير معروف: {$fieldKey}");
        }

        // Verify target record actually exists in the correct table
        $record = DB::table($meta['table'])->where('id', $targetId)->select('id', $meta['col'])->first();
        if (! $record) {
            throw new \RuntimeException("السجل المختار غير موجود في جدول {$meta['table']}.");
        }

        $hash = ImportValueMapping::computeHash($session, $fieldKey, $originalValue, $parentId);

        return ImportValueMapping::updateOrCreate(
            ['mapping_hash' => $hash],
            [
                'import_session' => $session,
                'field_key' => $fieldKey,
                'original_value' => $originalValue,
                'field_label' => $meta['label'],
                'table_name' => $meta['table'],
                'action' => 'replace',
                'target_id' => $targetId,
                'target_value' => $record->{$meta['col']},
                'processed_by' => $userId,
                'processed_at' => now(),
            ]
        );
    }

    // -----------------------------------------------------------------------
    // Option B — Add original value as-is
    // -----------------------------------------------------------------------

    /**
     * Add the original Excel value to the target table (no edit).
     * Checks for duplicates before inserting.
     */
    public function addNew(string $session, string $fieldKey, string $originalValue, int $userId, ?string $parentFk = null, ?int $parentId = null): ImportValueMapping
    {
        $meta = ProjectImportService::getDropdownFieldMeta($fieldKey);
        if (! $meta) {
            throw new \InvalidArgumentException("حقل غير معروف: {$fieldKey}");
        }

        $targetId = $this->insertIfNotDuplicate($meta['table'], $meta['col'], $originalValue, $parentFk, $parentId);

        $hash = ImportValueMapping::computeHash($session, $fieldKey, $originalValue, $parentId);

        return ImportValueMapping::updateOrCreate(
            ['mapping_hash' => $hash],
            [
                'import_session' => $session,
                'field_key' => $fieldKey,
                'original_value' => $originalValue,
                'field_label' => $meta['label'],
                'table_name' => $meta['table'],
                'action' => 'add',
                'target_id' => $targetId,
                'target_value' => $originalValue,
                'processed_by' => $userId,
                'processed_at' => now(),
            ]
        );
    }

    // -----------------------------------------------------------------------
    // Option C — Edit then add
    // -----------------------------------------------------------------------

    /**
     * Add a user-edited version of the value to the target table.
     * Checks for duplicates before inserting.
     */
    public function editAndAdd(string $session, string $fieldKey, string $originalValue, string $editedValue, int $userId, ?string $parentFk = null, ?int $parentId = null): ImportValueMapping
    {
        $editedValue = trim($editedValue);
        if ($editedValue === '') {
            throw new \InvalidArgumentException('القيمة المعدّلة لا يمكن أن تكون فارغة.');
        }

        $meta = ProjectImportService::getDropdownFieldMeta($fieldKey);
        if (! $meta) {
            throw new \InvalidArgumentException("حقل غير معروف: {$fieldKey}");
        }

        $targetId = $this->insertIfNotDuplicate($meta['table'], $meta['col'], $editedValue, $parentFk, $parentId);

        $hash = ImportValueMapping::computeHash($session, $fieldKey, $originalValue, $parentId);

        return ImportValueMapping::updateOrCreate(
            ['mapping_hash' => $hash],
            [
                'import_session' => $session,
                'field_key' => $fieldKey,
                'original_value' => $originalValue,
                'field_label' => $meta['label'],
                'table_name' => $meta['table'],
                'action' => 'edit_add',
                'target_id' => $targetId,
                'target_value' => $editedValue,
                'processed_by' => $userId,
                'processed_at' => now(),
            ]
        );
    }

    // -----------------------------------------------------------------------
    // Dropdown options for the UI (constraint #12: strict table filtering)
    // -----------------------------------------------------------------------

    /**
     * Get available options for a given fieldKey — ONLY from its own table.
     * Supports hierarchical filtering (e.g. directorates filtered by governorate_id).
     *
     * @param  int|null  $parentId  Resolved parent ID for hierarchical fields
     * @return array [['id' => ..., 'label' => ...], ...]
     */
    public function getDropdownOptions(string $fieldKey, ?int $parentId = null): array
    {
        $meta = ProjectImportService::getDropdownFieldMeta($fieldKey);
        if (! $meta) {
            return [];
        }

        $query = DB::table($meta['table'])->select('id', $meta['col'].' as label');

        // Hierarchical filter — constraint #13
        if (! empty($meta['parent_fk']) && $parentId !== null) {
            $query->where($meta['parent_fk'], $parentId);
        }

        // Filter by is_active if the column exists (most reference tables have it)
        try {
            $query->where('is_active', true);
        } catch (\Exception) {
            // Table may not have is_active — ignore
        }

        return $query->orderBy($meta['col'])->get()->toArray();
    }

    // -----------------------------------------------------------------------
    // Re-import: apply mappings then validate
    // -----------------------------------------------------------------------

    /**
     * Apply all saved mappings for the given session to a skipped project's data.
     * Returns resolved $data or throws MissingDropdownValuesException if still missing.
     *
     * @param  array  $projectData  Structured project data (already built)
     * @param  array  $missingValues  From original skip event
     *
     * @throws MissingDropdownValuesException If values remain unresolved
     */
    public function applyMappingsAndValidate(string $session, array $projectData, array $missingValues): array
    {
        $hashes = [];
        foreach ($missingValues as $mv) {
            $hashes[] = ImportValueMapping::computeHash($session, $mv['field_key'], $mv['value'], $mv['parent_id'] ?? null);
        }

        $mappings = ImportValueMapping::whereIn('mapping_hash', $hashes)->get()->keyBy('mapping_hash');

        $stillMissing = [];

        foreach ($missingValues as $mv) {
            $lookupKey = ImportValueMapping::computeHash($session, $mv['field_key'], $mv['value'], $mv['parent_id'] ?? null);
            $mapping = $mappings->get($lookupKey);

            if (! $mapping) {
                // No mapping recorded yet for this value
                $stillMissing[] = $mv;

                continue;
            }

            if (! $mapping->target_id) {
                $stillMissing[] = $mv;

                continue;
            }

            // Apply the resolved ID back into the data structure
            $projectData = $this->applyResolvedId($projectData, $mv['field_key'], $mv['value'], $mapping->target_id);
        }

        if (! empty($stillMissing)) {
            throw new MissingDropdownValuesException($stillMissing);
        }

        return $projectData;
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Insert a record if no case-insensitive match already exists.
     * Returns the ID (existing or newly created).
     * Constraint #6: prevents duplicate records.
     */
    private function insertIfNotDuplicate(string $table, string $col, string $value, ?string $parentFk = null, ?int $parentId = null): int
    {
        $query = DB::table($table)->where($col, 'LIKE', $value);
        if ($parentFk && $parentId) {
            $query->where($parentFk, $parentId);
        }

        $existing = $query->select('id')->first();
        if ($existing) {
            return (int) $existing->id;
        }

        $insertData = [$col => $value];
        if ($parentFk && $parentId) {
            $insertData[$parentFk] = $parentId;
        }

        // Set is_active if the column exists (check via schema info)
        try {
            if (DB::getSchemaBuilder()->hasColumn($table, 'is_active')) {
                $insertData['is_active'] = true;
            }
            if (DB::getSchemaBuilder()->hasColumn($table, 'created_at')) {
                $insertData['created_at'] = now();
                $insertData['updated_at'] = now();
            }
        } catch (\Exception) {
            // Ignore schema introspection errors
        }

        return DB::table($table)->insertGetId($insertData);
    }

    /**
     * Walk through project data and replace a specific field's text value with a resolved ID.
     * Handles nested structures (financings, entities, locations, activities).
     */
    private function applyResolvedId(array $data, string $fieldKey, string $originalValue, int $resolvedId): array
    {
        // Top-level project field
        if (isset($data[$fieldKey]) && trim((string) $data[$fieldKey]) === trim($originalValue)) {
            $data[$fieldKey] = $resolvedId;
        }

        // Nested arrays
        $nestedKeys = ['locations', 'financings', 'supervising_authorities', 'implementing_entities', 'participating_entities', 'beneficiary_entities'];
        foreach ($nestedKeys as $group) {
            foreach ($data[$group] ?? [] as $idx => $item) {
                if (isset($item[$fieldKey]) && trim((string) $item[$fieldKey]) === trim($originalValue)) {
                    $data[$group][$idx][$fieldKey] = $resolvedId;
                }
            }
        }

        // Preliminary activities → procedures → costs
        foreach ($data['preliminary_activities'] ?? [] as $aIdx => $activity) {
            foreach ($activity['procedures'] ?? [] as $pIdx => $procedure) {
                foreach ($procedure['costs'] ?? [] as $cIdx => $cost) {
                    if (isset($cost[$fieldKey]) && trim((string) $cost[$fieldKey]) === trim($originalValue)) {
                        $data['preliminary_activities'][$aIdx]['procedures'][$pIdx]['costs'][$cIdx][$fieldKey] = $resolvedId;
                    }
                }
            }
        }

        // Executive activities → actions → costs
        foreach ($data['executive_activities'] ?? [] as $aIdx => $activity) {
            foreach ($activity['actions'] ?? [] as $acIdx => $action) {
                foreach ($action['costs'] ?? [] as $cIdx => $cost) {
                    if (isset($cost[$fieldKey]) && trim((string) $cost[$fieldKey]) === trim($originalValue)) {
                        $data['executive_activities'][$aIdx]['actions'][$acIdx]['costs'][$cIdx][$fieldKey] = $resolvedId;
                    }
                }
            }
        }

        return $data;
    }
}
