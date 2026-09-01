<?php

namespace App\Imports;

use App\Models\Authority;
use Illuminate\Support\Collection;
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
        foreach ($collection as $index => $row) {
            $agencyName = '';

            // Try all possible headers
            $possibleHeaders = [
                'agency_name', 'agency name', 'agencyname',
                'اسم الجهة', 'اسم_الجهة', 'اسم-الجهة', 'الجهة',
                'asm_aljh', 'asm-aljh', 'asm aljh',
            ];

            foreach ($possibleHeaders as $header) {
                if (isset($row[$header])) {
                    $agencyName = trim($row[$header]);
                    break;
                }

                // Also try slugified versions that Maatwebsite Excel might generate
                $slugified = str_replace(' ', '_', strtolower($header));
                if (isset($row[$slugified])) {
                    $agencyName = trim($row[$slugified]);
                    break;
                }
            }

            // If still empty, check all keys for keywords
            if (empty($agencyName)) {
                foreach ($row as $key => $val) {
                    $lowKey = strtolower((string) $key);
                    if (str_contains($lowKey, 'agency_name') ||
                        str_contains($lowKey, 'asm_aljh') ||
                        str_contains($lowKey, 'asm_aljh') ||
                        str_contains($lowKey, 'اسم') ||
                        str_contains($lowKey, 'جهة')) {
                        $agencyName = trim((string) $val);
                        if (! empty($agencyName)) {
                            break;
                        }
                    }
                }
            }

            $isActiveValue = $row['is_active'] ?? $row['الحالة'] ?? $row['نشط'] ?? $row['حالة'] ?? 'نشط';
            $isActive = $this->parseActiveStatus($isActiveValue);

            $parentName = trim($row['parent_name'] ?? $row['الجهة_الأم'] ?? $row['الجهة_الأب'] ?? $row['اسم_الجهة_الأم'] ?? '');
            $parentId = null;
            if (! empty($parentName)) {
                $parent = Authority::where('agency_name', $parentName)->first();
                if ($parent) {
                    $parentId = $parent->id;
                }
            }

            if (empty($agencyName)) {
                $this->results['skipped']++;

                continue; // Skip empty rows
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
                            'parent_id' => $parentId,
                        ]);
                        $this->results['imported']++;
                        $this->results['details'][] = [
                            'agency_name' => $agencyName,
                            'status' => 'success',
                            'message' => 'تم استيراد الجهة بنجاح',
                        ];
                    }
                } elseif ($this->importMode === 'update') {
                    // Update if exists, create if doesn't
                    $authority = Authority::where('agency_name', $agencyName)->first();
                    if ($authority) {
                        $authority->update([
                            'is_active' => $isActive,
                            'parent_id' => $parentId,
                        ]);
                        $this->results['updated']++;
                        $this->results['details'][] = [
                            'agency_name' => $agencyName,
                            'status' => 'updated',
                            'message' => 'تم تحديث بيانات الجهة',
                        ];
                    } else {
                        Authority::create([
                            'agency_name' => $agencyName,
                            'is_active' => $isActive,
                            'parent_id' => $parentId,
                        ]);
                        $this->results['imported']++;
                        $this->results['details'][] = [
                            'agency_name' => $agencyName,
                            'status' => 'success',
                            'message' => 'تم إضافة جهة جديدة',
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
}
