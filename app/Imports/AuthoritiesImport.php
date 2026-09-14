<?php

namespace App\Imports;

use App\Models\Authority;
use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\TypeEntity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AuthoritiesImport implements ToCollection, WithHeadingRow
{
    protected $importMode;

    protected $results = [
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'failed' => 0,
        'errors' => [],
        'details' => [],
    ];

    public function __construct($importMode = 'add')
    {
        $this->importMode = $importMode;
    }

    public function collection(Collection $collection)
    {
        Log::info("AuthoritiesImport collection called with mode: {$this->importMode}, rows: ".$collection->count());

        foreach ($collection as $index => $row) {
            $agencyName = trim($row['agency_name'] ?? $row['asm_algh'] ?? $row['asm_aljhh'] ?? $row['اسم_الجهة'] ?? '');

            // Try to find agency name in other common headings if empty
            if (empty($agencyName)) {
                foreach ($row as $key => $val) {
                    if (str_contains($key, 'agency') || str_contains($key, 'asm') || str_contains($key, 'name') || str_contains($key, 'جهة')) {
                        if (! empty(trim($val))) {
                            $agencyName = trim($val);
                            break;
                        }
                    }
                }
            }

            $activeVal = $row['is_active'] ?? $row['alhal'] ?? $row['الحالة'] ?? null;
            if ($activeVal === null) {
                foreach ($row as $key => $val) {
                    if (str_contains($key, 'active') || str_contains($key, 'hal') || str_contains($key, 'حال')) {
                        if (trim($val) !== '') {
                            $activeVal = $val;
                            break;
                        }
                    }
                }
            }
            $isActive = $this->parseActiveStatus($activeVal ?? 'نشط');

            $fundedVal = $row['is_funded'] ?? $row['funded'] ?? $row['algh_almmwlh'] ?? $row['جهة_ممولة'] ?? $row['ممولة'] ?? null;
            if ($fundedVal === null) {
                foreach ($row as $key => $val) {
                    if (str_contains($key, 'funded') || str_contains($key, 'mmwlh') || str_contains($key, 'تمويل') || str_contains($key, 'ممولة')) {
                        if (trim($val) !== '') {
                            $fundedVal = $val;
                            break;
                        }
                    }
                }
            }
            $isFunded = $this->parseFundedStatus($fundedVal ?? 'غير ممولة');

            if (empty($agencyName) || str_starts_with($agencyName, '=')) {
                $hasOtherData = false;
                foreach ($row as $val) {
                    if (! empty(trim((string) $val))) {
                        $hasOtherData = true;
                        break;
                    }
                }
                if ($hasOtherData && ! str_starts_with($agencyName, '=')) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $index + 2,
                        'agency_name' => 'غير محدد',
                        'errors' => ['agency_name' => ['اسم الجهة مطلوب ولا يمكن أن يكون فارغاً']],
                    ];
                    $this->results['details'][] = [
                        'agency_name' => 'سطر '.($index + 2).' (بدون اسم)',
                        'status' => 'failed',
                        'message' => 'تعذر الاستيراد: اسم الجهة مطلوب',
                    ];
                } else {
                    $this->results['skipped']++;
                }

                continue;
            }

            $parentName = trim($row['parent_name'] ?? $row['parent_id'] ?? $row['algh_alab'] ?? $row['الجهة_الأب'] ?? $row['parent_agency'] ?? '');
            if (empty($parentName)) {
                foreach ($row as $key => $val) {
                    if (str_contains($key, 'parent') || str_contains($key, 'alab') || str_contains($key, 'أب')) {
                        if (! empty(trim($val))) {
                            $parentName = trim($val);
                            break;
                        }
                    }
                }
            }
            $parentId = null;

            if (! empty($parentName) && $parentName !== '-') {
                $parent = Authority::where('agency_name', $parentName)->first();
                if ($parent) {
                    $parentId = $parent->id;
                }
            }

            $govName = trim($row['governorate_name'] ?? $row['governorate_id'] ?? $row['almhafth'] ?? $row['المحافظة'] ?? '');
            $dirName = trim($row['directorate_name'] ?? $row['directorate_id'] ?? $row['almdyryh'] ?? $row['almdyry'] ?? $row['المديرية'] ?? '');
            $govId = null;
            $dirId = null;

            if (! empty($govName) && $govName !== '-') {
                $gov = Governorate::withoutGlobalScopes()->where('name', $govName)->first();
                if ($gov) {
                    $govId = $gov->id;
                    if (! empty($dirName) && $dirName !== '-') {
                        $dir = Directorate::withoutGlobalScopes()
                            ->where('governorate_id', $govId)
                            ->where('name', $dirName)
                            ->first();
                        if ($dir) {
                            $dirId = $dir->id;
                        }
                    }
                }
            }

            $scopeRaw = trim($row['entity_scope'] ?? $row['ntaq_algh'] ?? $row['نطاق_الجهة'] ?? $row['النطاق'] ?? '');
            if (empty($scopeRaw)) {
                foreach ($row as $key => $val) {
                    if (str_contains($key, 'scope') || str_contains($key, 'ntaq') || str_contains($key, 'نطاق')) {
                        if (! empty(trim($val))) {
                            $scopeRaw = trim($val);
                            break;
                        }
                    }
                }
            }
            $entityScope = null;
            if (! empty($scopeRaw) && $scopeRaw !== '-') {
                $lowScope = strtolower($scopeRaw);
                if ($lowScope === 'internal' || $lowScope === 'داخلي') {
                    $entityScope = 'internal';
                } elseif ($lowScope === 'external' || $lowScope === 'خارجي') {
                    $entityScope = 'external';
                }
            }

            $typeName = trim($row['type_entity_id'] ?? $row['type_entity_name'] ?? $row['noa_algh'] ?? $row['nw_algh'] ?? $row['نوع_الجهة'] ?? '');
            if (empty($typeName)) {
                foreach ($row as $key => $val) {
                    if (str_contains($key, 'type') || str_contains($key, 'نوع')) {
                        if (! empty(trim($val))) {
                            $typeName = trim($val);
                            break;
                        }
                    }
                }
            }
            $typeEntityId = null;
            if (! empty($typeName) && $typeName !== '-') {
                if (is_numeric($typeName)) {
                    $typeEntityId = (int) $typeName;
                } else {
                    $typeObj = TypeEntity::where('name', $typeName)->first();
                    if ($typeObj) {
                        $typeEntityId = $typeObj->id;
                    }
                }
            }

            try {
                if ($this->importMode === 'add') {
                    // Only create if doesn't exist
                    $exists = Authority::where('agency_name', $agencyName)->exists();
                    if ($exists) {
                        $this->results['skipped']++;
                        $this->results['details'][] = [
                            'agency_name' => $agencyName,
                            'status' => 'skipped',
                            'message' => 'الجهة موجودة مسبقاً (تم التجاهل)',
                        ];
                    } else {
                        Authority::create([
                            'agency_name' => $agencyName,
                            'is_active' => $isActive,
                            'is_funded' => $isFunded,
                            'parent_id' => $parentId,
                            'governorate_id' => $govId,
                            'directorate_id' => $dirId,
                            'type_entity_id' => $typeEntityId,
                        ]);
                        $this->results['imported']++;
                        $this->results['details'][] = [
                            'agency_name' => $agencyName,
                            'status' => 'success',
                            'message' => 'تم استيراد الجهة بنجاح'.($parentId ? ' (مع الجهة الأب)' : ''),
                        ];
                    }
                } elseif ($this->importMode === 'update') {
                    // Update ONLY if exists, skip if doesn't
                    $authority = Authority::where('agency_name', $agencyName)->first();
                    if ($authority) {
                        $updateData = [
                            'is_active' => $isActive,
                            'governorate_id' => $govId,
                            'directorate_id' => $dirId,
                        ];
                        if ($fundedVal !== null) {
                            $updateData['is_funded'] = $isFunded;
                        }
                        if ($parentId !== null) {
                            $updateData['parent_id'] = $parentId;
                        }
                        if ($typeEntityId !== null) {
                            $updateData['type_entity_id'] = $typeEntityId;
                        }
                        $authority->update($updateData);
                        $this->results['updated']++;
                        $this->results['details'][] = [
                            'agency_name' => $agencyName,
                            'status' => 'updated',
                            'message' => 'تم تحديث بيانات الجهة'.($parentId ? ' (وتحديث الجهة الأب)' : ''),
                        ];
                    } else {
                        $this->results['skipped']++;
                        $this->results['details'][] = [
                            'agency_name' => $agencyName,
                            'status' => 'skipped',
                            'message' => 'الجهة غير موجودة مسبقاً (تم التجاهل في وضع التحديث فقط)',
                        ];
                    }
                } elseif ($this->importMode === 'add_update') {
                    // Update if exists, create if doesn't
                    $authority = Authority::where('agency_name', $agencyName)->first();
                    if ($authority) {
                        $updateData = [
                            'is_active' => $isActive,
                            'governorate_id' => $govId,
                            'directorate_id' => $dirId,
                        ];
                        if ($fundedVal !== null) {
                            $updateData['is_funded'] = $isFunded;
                        }
                        if ($parentId !== null) {
                            $updateData['parent_id'] = $parentId;
                        }
                        if ($typeEntityId !== null) {
                            $updateData['type_entity_id'] = $typeEntityId;
                        }
                        $authority->update($updateData);
                        $this->results['updated']++;
                        $this->results['details'][] = [
                            'agency_name' => $agencyName,
                            'status' => 'updated',
                            'message' => 'تم تحديث بيانات الجهة'.($parentId ? ' (وتحديث الجهة الأب)' : ''),
                        ];
                    } else {
                        Authority::create([
                            'agency_name' => $agencyName,
                            'is_active' => $isActive,
                            'is_funded' => $isFunded,
                            'parent_id' => $parentId,
                            'governorate_id' => $govId,
                            'directorate_id' => $dirId,
                            'type_entity_id' => $typeEntityId,
                        ]);
                        $this->results['imported']++;
                        $this->results['details'][] = [
                            'agency_name' => $agencyName,
                            'status' => 'success',
                            'message' => 'تم إضافة جهة جديدة'.($parentId ? ' (مع الجهة الأب)' : ''),
                        ];
                    }
                }
            } catch (\Exception $e) {
                $this->results['failed']++;
                $errorInfo = [
                    'row' => $index + 2,
                    'agency_name' => $agencyName,
                    'errors' => ['system' => [$e->getMessage()]],
                ];
                $this->results['errors'][] = $errorInfo;
                $this->results['details'][] = [
                    'agency_name' => $agencyName,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];
            }
        }
    }

    public function getResults()
    {
        return $this->results;
    }

    /**
     * Parse active status from various formats
     */
    protected function parseActiveStatus($value)
    {
        $value = trim(strtolower($value));

        // Check for Arabic and English variations
        if ($value === 'نشط' || $value === 'active' || $value === '1' || $value === 'true') {
            return true;
        }

        if ($value === 'غير نشط' || $value === 'inactive' || $value === '0' || $value === 'false') {
            return false;
        }

        // Default to active
        return true;
    }

    /**
     * Parse funded status from various formats
     */
    protected function parseFundedStatus($value)
    {
        $value = trim(strtolower((string) $value));

        if ($value === 'ممولة' || $value === 'ممولة' || $value === 'funded' || $value === '1' || $value === 'true' || $value === 'نعم' || $value === 'yes') {
            return true;
        }

        if ($value === 'غير ممولة' || $value === 'غير ممول' || $value === 'unfunded' || $value === '0' || $value === 'false' || $value === 'لا' || $value === 'no') {
            return false;
        }

        return false;
    }
}
