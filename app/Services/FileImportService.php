<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Writer;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FileImportService
{
    /**
     * Read data from CSV or Excel file
     */
    public function readFile($filePath, $hasHeaders = true)
    {
        $fullPath = Storage::path($filePath);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->readCsvFile($fullPath, $hasHeaders);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            return $this->readExcelFileSimple($fullPath, $hasHeaders);
        }

        throw new \InvalidArgumentException('Unsupported file format: '.$extension);
    }

    /**
     * Read CSV file
     */
    private function readCsvFile($filePath, $hasHeaders = true)
    {
        $reader = Reader::createFromPath($filePath, 'r');

        if ($hasHeaders) {
            $reader->setHeaderOffset(0);
            $records = $reader->getRecords();
            $headers = $reader->getHeader();
        } else {
            $records = $reader->getRecords();
            $headers = [];
        }

        return [
            'headers' => $headers,
            'data' => iterator_to_array($records),
        ];
    }

    /**
     * Read Excel file using PhpSpreadsheet
     */
    private function readExcelFileSimple($filePath, $hasHeaders = true)
    {
        try {
            // Use read-only mode and disable calculation for faster processing of large files
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);

            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            $data = [];
            $headers = [];

            $startRow = $hasHeaders ? 2 : 1;

            // Get headers if they exist
            if ($hasHeaders && $highestRow >= 1) {
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    try {
                        $headerValue = $worksheet->getCellByColumnAndRow($col, 1)->getCalculatedValue();
                        if ($headerValue !== null && is_string($headerValue)) {
                            $headerValue = trim($headerValue);
                        }
                        $headers[] = $headerValue;
                    } catch (\Exception $e) {
                        $headers[] = 'Column_'.$col; // Fallback header name
                    }
                }
            }

            // Get data rows
            for ($row = $startRow; $row <= $highestRow; $row++) {
                $rowData = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    try {
                        $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();

                        // Clean and normalize the value
                        if ($cellValue !== null) {
                            if (is_array($cellValue) || is_object($cellValue)) {
                                $cellValue = json_encode($cellValue);
                            } elseif (is_string($cellValue)) {
                                $cellValue = trim($cellValue);
                            }
                            // Convert numeric values to strings if needed
                            if (is_numeric($cellValue)) {
                                $cellValue = (string) $cellValue;
                            }
                        }

                        if ($hasHeaders && isset($headers[$col - 1])) {
                            $headerKey = trim($headers[$col - 1]);
                            $rowData[$headerKey] = $cellValue;
                        } else {
                            $rowData[] = $cellValue;
                        }
                    } catch (\Exception $e) {
                        // If there's an error reading a cell, set it as null
                        if ($hasHeaders && isset($headers[$col - 1])) {
                            $headerKey = trim($headers[$col - 1]);
                            $rowData[$headerKey] = null;
                        } else {
                            $rowData[] = null;
                        }
                    }
                }

                // Skip completely empty rows
                $hasData = false;
                foreach ($rowData as $value) {
                    if ($value !== null && $value !== '') {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $data[] = $rowData;
                }
            }

            return [
                'headers' => $headers,
                'data' => $data,
            ];

        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Error reading Excel file: '.$e->getMessage());
        }
    }

    /**
     * Create template file in specified format
     */
    public function createTemplate($templateData, $format = 'xls')
    {
        $fileName = 'template_'.time().'.'.$format;
        $filePath = storage_path('app/public/templates/'.$fileName);

        // Ensure directory exists
        if (! file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        if ($format === 'csv') {
            $this->createCsvTemplate($templateData, $filePath);
        } elseif ($format === 'xlsx') {
            $this->createExcelTemplate($templateData, $filePath, 'xlsx');
        } elseif ($format === 'xls') {
            $this->createExcelTemplate($templateData, $filePath, 'xls');
        }

        return $filePath;
    }

    /**
     * Create CSV template
     */
    private function createCsvTemplate($templateData, $filePath)
    {
        $writer = Writer::createFromPath($filePath, 'w+');

        // Write headers
        if (isset($templateData['headers'])) {
            $writer->insertOne($templateData['headers']);
        }

        // Write sample data
        if (isset($templateData['sample_data'])) {
            $writer->insertAll($templateData['sample_data']);
        }
    }

    /**
     * Create Excel template
     */
    private function createExcelTemplate($templateData, $filePath, $writerType = 'xls')
    {
        $spreadsheet = new Spreadsheet;
        $worksheet = $spreadsheet->getActiveSheet();

        $row = 1;

        // Write headers
        if (isset($templateData['headers'])) {
            $col = 1;
            foreach ($templateData['headers'] as $header) {
                $worksheet->setCellValueByColumnAndRow($col, $row, $header);
                $col++;
            }
            $row++;
        }

        // Write sample data
        if (isset($templateData['sample_data'])) {
            foreach ($templateData['sample_data'] as $dataRow) {
                $col = 1;
                foreach ($dataRow as $value) {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    } elseif ($value === null) {
                        $value = '';
                    } else {
                        $value = (string) $value;
                    }
                    $worksheet->setCellValueByColumnAndRow($col, $row, $value);
                    $col++;
                }
                $row++;
            }
        }

        // Style headers
        if (isset($templateData['headers'])) {
            $headerRange = 'A1:'.chr(64 + count($templateData['headers'])).'1';
            $worksheet->getStyle($headerRange)->getFont()->setBold(true);
            $worksheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E3F2FD');
        }

        // Auto-size columns
        foreach (range('A', chr(64 + count($templateData['headers'] ?? []))) as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Use appropriate writer based on type
        if ($writerType === 'xls') {
            $writer = new Xls($spreadsheet);
        } else {
            $writer = new Xlsx($spreadsheet);
        }
        $writer->save($filePath);
    }

    /**
     * Create error report file
     */
    public function createErrorReport($errors, $format = 'xls')
    {
        $fileName = 'import_errors_'.time().'.'.$format;
        $filePath = storage_path('app/temp/'.$fileName);

        // Ensure directory exists
        if (! file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $templateData = [
            'headers' => ['Row', 'Error'],
            'sample_data' => [],
        ];

        foreach ($errors as $row => $errorList) {
            foreach ($errorList as $error) {
                $templateData['sample_data'][] = [$row, $error];
            }
        }

        if ($format === 'csv') {
            $this->createCsvTemplate($templateData, $filePath);
        } elseif ($format === 'xlsx') {
            $this->createExcelTemplate($templateData, $filePath, 'xlsx');
        } elseif ($format === 'xls') {
            $this->createExcelTemplate($templateData, $filePath, 'xls');
        }

        return $filePath;
    }
}
