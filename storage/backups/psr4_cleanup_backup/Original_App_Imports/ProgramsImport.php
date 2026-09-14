<?php

namespace App\Imports;

use App\Models\Program;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProgramsImport implements SkipsOnFailure, ToCollection, WithHeadingRow
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
                if (isset($row[$column])) {
                    $mappedData[$field] = trim($row[$column]);
                } else {
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
                $programData = [
                    'name' => trim($data['name']),
                    'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
                    'import_batch' => $this->importBatch,
                ];

                if ($this->operation === 'update' || $this->operation === 'both') {
                    $existingProgram = null;

                    if (isset($data['id']) && ! empty($data['id'])) {
                        $existingProgram = Program::find($data['id']);
                    } else {
                        $existingProgram = Program::where('name', $programData['name'])->first();
                    }

                    if ($existingProgram) {
                        $existingProgram->update($programData);
                        $this->report['successful_updates']++;

                        return;
                    }
                }

                if ($this->operation === 'insert' || $this->operation === 'both') {
                    $duplicate = Program::where('name', $programData['name'])->first();

                    if ($duplicate) {
                        throw new \Exception("البرنامج '{$data['name']}' موجود مسبقاً");
                    }

                    Program::create($programData);
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
