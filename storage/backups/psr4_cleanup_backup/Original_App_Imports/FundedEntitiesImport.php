<?php

namespace App\Imports;

use App\Models\FundedEntity;
use App\Models\FundingSource;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FundedEntitiesImport implements SkipsOnError, SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    private $results = [
        'success' => 0,
        'errors' => 0,
        'skipped' => 0,
        'details' => [],
    ];

    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        try {
            // Find funding source by name
            $fundingSource = FundingSource::where('name', trim($row['مصدر_التمويل'] ?? $row['funding_source'] ?? ''))->first();
            if (! $fundingSource) {
                $this->results['errors']++;
                $this->results['details'][] = "مصدر التمويل '{$row['مصدر_التمويل']}' غير موجود";

                return null;
            }

            // Get entity text
            $entityText = trim($row['الجهة'] ?? $row['entity'] ?? '');
            if (empty($entityText)) {
                $this->results['errors']++;
                $this->results['details'][] = 'حقل الجهة مطلوب';

                return null;
            }

            // Check if combination already exists
            $exists = FundedEntity::where('funding_source_id', $fundingSource->id)
                ->where('entity', $entityText)
                ->exists();

            if ($exists) {
                $this->results['skipped']++;
                $this->results['details'][] = "المزيج من '{$fundingSource->name}' و '{$entityText}' موجود مسبقاً";

                return null;
            }

            $this->results['success']++;

            return new FundedEntity([
                'funding_source_id' => $fundingSource->id,
                'entity' => $entityText,
            ]);

        } catch (\Exception $e) {
            $this->results['errors']++;
            $this->results['details'][] = 'خطأ في الصف: '.$e->getMessage();

            return null;
        }
    }

    public function rules(): array
    {
        return [
            'مصدر_التمويل' => 'required|string',
            'الجهة' => 'required|string',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'مصدر_التمويل.required' => 'مصدر التمويل مطلوب',
            'الجهة.required' => 'الجهة مطلوبة',
        ];
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
