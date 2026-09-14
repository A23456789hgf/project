<?php

namespace App\Imports;

use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\SubArea;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SubAreasImport implements SkipsOnFailure, ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;

    private $operation;

    private $mapping;

    private $importBatch;

    private $failures = [];

    private $report = [
        'total_rows' => 0,
        'successful_inserts' => 0,
        'successful_updates' => 0,
        'failed_rows' => 0,
        'skipped_rows' => 0,
    ];

    public function __construct($operation = 'insert', $mapping = [], $importBatch = null)
    {
        $this->operation = $operation;
        $this->mapping = $mapping;
        $this->importBatch = $importBatch ?? 'batch_'.time();
    }

    public function collection(Collection $rows)
    {
        $this->report['total_rows'] = $rows->count();

        foreach ($rows as $index => $row) {
            try {
                $mappedData = $this->mapRowData($row->toArray());

                if ($this->isEmptyRow($mappedData)) {
                    $this->report['skipped_rows']++;

                    continue;
                }

                $validatedData = $this->validateRowData($mappedData, $index + 2);

                if (! $validatedData) {
                    $this->report['failed_rows']++;

                    continue;
                }

                $this->processRow($validatedData);

            } catch (\Exception $e) {
                $this->report['failed_rows']++;
                $this->failures[] = [
                    'row' => $index + 2,
                    'attribute' => 'general',
                    'errors' => [$e->getMessage()],
                    'values' => $row->toArray(),
                ];
            }
        }
    }

    private function mapRowData($row)
    {
        $mappedData = [];

        foreach ($this->mapping as $field => $column) {
            if (! empty($column)) {
                // Try exact match first
                if (isset($row[$column])) {
                    $mappedData[$field] = trim($row[$column]);
                } else {
                    // Try case-insensitive match
                    $lowerColumn = strtolower($column);
                    foreach ($row as $key => $value) {
                        if (strtolower($key) === $lowerColumn) {
                            $mappedData[$field] = trim($value);
                            break;
                        }
                    }
                }
            }
        }

        return $mappedData;
    }

    private function isEmptyRow($data)
    {
        foreach ($data as $value) {
            if (! empty(trim($value))) {
                return false;
            }
        }

        return true;
    }

    private function validateRowData($data, $rowNumber)
    {
        $rules = [
            'governorate_name' => 'required|string',
            'directorate_name' => 'required|string',
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $this->failures[] = [
                'row' => $rowNumber,
                'attribute' => 'validation',
                'errors' => $validator->errors()->all(),
                'values' => $data,
            ];

            return false;
        }

        return $data;
    }

    private function processRow($data)
    {
        try {
            DB::transaction(function () use ($data) {
                // Find governorate
                $governorate = Governorate::where('name', trim($data['governorate_name']))->first();
                if (! $governorate) {
                    throw new \Exception("المحافظة '{$data['governorate_name']}' غير موجودة");
                }

                // Find directorate
                $directorate = Directorate::where('name', trim($data['directorate_name']))
                    ->where('governorate_id', $governorate->id)
                    ->first();

                if (! $directorate) {
                    throw new \Exception("المديرية '{$data['directorate_name']}' غير موجودة في محافظة '{$data['governorate_name']}'");
                }

                $subAreaData = [
                    'governorate_id' => $governorate->id,
                    'directorate_id' => $directorate->id,
                    'name' => trim($data['name']),
                    'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
                    'import_batch' => $this->importBatch,
                ];

                if ($this->operation === 'update' || $this->operation === 'both') {
                    // Try to find existing record
                    $existingSubArea = null;

                    if (isset($data['id']) && ! empty($data['id'])) {
                        $existingSubArea = SubArea::find($data['id']);
                    } else {
                        $existingSubArea = SubArea::where('name', $subAreaData['name'])
                            ->where('directorate_id', $directorate->id)
                            ->first();
                    }

                    if ($existingSubArea) {
                        $existingSubArea->update($subAreaData);
                        $this->report['successful_updates']++;

                        return;
                    }
                }

                if ($this->operation === 'insert' || $this->operation === 'both') {
                    // Check for duplicates
                    $duplicate = SubArea::where('name', $subAreaData['name'])
                        ->where('directorate_id', $directorate->id)
                        ->first();

                    if ($duplicate) {
                        throw new \Exception("العزلة/المنطقة '{$data['name']}' موجودة مسبقاً في مديرية '{$data['directorate_name']}'");
                    }

                    SubArea::create($subAreaData);
                    $this->report['successful_inserts']++;
                }
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getReport()
    {
        return $this->report;
    }

    public function failures()
    {
        return $this->failures;
    }
}
