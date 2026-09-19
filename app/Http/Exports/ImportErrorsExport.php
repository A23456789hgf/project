<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportErrorsExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    protected $errors;

    public function __construct($errors)
    {
        $this->errors = $errors;
    }

    public function array(): array
    {
        $data = [];

        if (is_array($this->errors)) {
            foreach ($this->errors as $error) {
                $data[] = [
                    'row' => $error['row'] ?? '',
                    'attribute' => $error['attribute'] ?? '',
                    'errors' => is_array($error['errors']) ? implode(', ', $error['errors']) : $error['errors'],
                    'values' => is_array($error['values']) ? json_encode($error['values'], JSON_UNESCAPED_UNICODE) : $error['values'],
                ];
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'رقم الصف',
            'الحقل',
            'الأخطاء',
            'القيم',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
