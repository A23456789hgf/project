<?php

namespace App\Http\Controllers;

use App\Exports\ProgramsExport;
use App\Models\Program;
use App\Services\FileImportService;
use App\Services\ImportTrackingService;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProgramController extends Controller
{
    use HasApprovalWorkflow;

    public function index(Request $request)
    {
        $query = Program::query();

        // Apply entity visibility filter
        $query->visibleToUser('programs');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $query = $this->applyStatusFilter($query, $request);

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $programs = $query->paginate($perPage)->withQueryString();

        return view('configuration.programs.index', compact('programs'));
    }

    public function approve(Program $program)
    {
        return $this->approveModel($program, 'programs.index');
    }

    public function reject(Program $program)
    {
        return $this->rejectModel($program, 'programs.index');
    }

    public function create()
    {
        return view('configuration.programs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:programs,name',
        ]);

        $user = auth()->user();
        $program = Program::create([
            'name' => $request->name,
            'is_active' => true,
            'created_by_entity' => $user->department ?? $user->entity_id,
            'created_by_user_id' => $user->id,
        ]);

        if ($request->ajax()) {
            return response()->json($program);
        }

        return redirect()->route('programs.index')->with('success', 'تمت الإضافة بنجاح');
    }

    public function edit($id)
    {
        $program = Program::findOrFail($id);

        return view('configuration.programs.edit', compact('program'));
    }

    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:programs,name,'.$program->id,
        ]);

        $program->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('programs.index')->with('success', 'تم التحديث بنجاح');
    }

    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        $program->delete();

        return redirect()->route('programs.index')->with('success', 'تم الحذف بنجاح');
    }

    public function showImportForm()
    {
        return view('configuration.programs.import');
    }

    public function downloadTemplate(Request $request)
    {
        $format = $request->get('format', 'xls');

        $templateData = [
            'headers' => ['id', 'name', 'is_active'],
            'sample_data' => [
                [1, 'البرنامج الأول', 1],
                [2, 'البرنامج الثاني', 1],
                [3, 'البرنامج الثالث', 1],
            ],
        ];

        $fileImportService = new FileImportService;
        $filePath = $fileImportService->createTemplate($templateData, $format);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:150000000',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $fileName = 'import_preview_'.time().'.'.$extension;
        $path = $file->storeAs('temp', $fileName);

        $fileImportService = new FileImportService;
        $fileData = $fileImportService->readFile($path, true);

        $headers = $fileData['headers'];
        $rows = array_slice($fileData['data'], 0, 10);

        return view('configuration.programs.import_preview', [
            'headers' => $headers,
            'rows' => $rows,
            'filePath' => $path,
            'mappingFields' => ['id', 'name', 'is_active'],
            'dbColumns' => [
                'id' => 'المعرف',
                'name' => 'اسم البرنامج',
                'is_active' => 'الحالة',
            ],
        ]);
    }

    public function processImport(Request $request)
    {
        set_time_limit(900);
        ini_set('memory_limit', '1024M');

        $request->validate([
            'file_path' => 'required',
            'operation' => 'required|in:insert,update,both',
            'mapping' => 'required|array',
        ]);

        $filePath = $request->input('file_path');
        $operation = $request->input('operation');
        $mapping = $request->input('mapping');

        $fileImportService = new FileImportService;

        try {
            $fileData = $fileImportService->readFile($filePath, true);
            $records = $fileData['data'];

            Log::info('Programs Import Started', [
                'file_path' => basename($filePath),
                'total_records' => count($records),
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'خطأ في قراءة الملف: '.$e->getMessage()]);
        }

        $totalRows = 0;
        $successful = 0;
        $failed = 0;
        $errors = [];

        $trackingService = new ImportTrackingService;
        $importLog = $trackingService->startImport('البرامج', Program::class, basename($filePath));

        $importedFileName = 'imported_'.Str::uuid().'.'.pathinfo($filePath, PATHINFO_EXTENSION);

        $recordCount = count($records);
        $batchSize = $recordCount > 10000 ? 500 : 100;
        $batchedRecords = array_chunk($records, $batchSize, true);

        foreach ($batchedRecords as $batch) {
            DB::transaction(function () use ($batch, $mapping, $operation, $importedFileName,
                &$totalRows, &$successful, &$failed, &$errors, $trackingService, $importLog) {

                foreach ($batch as $index => $row) {
                    $totalRows++;

                    $mappedData = [];
                    foreach ($mapping as $field => $header) {
                        if ($header && isset($row[$header])) {
                            $value = $row[$header];
                            if (is_string($value)) {
                                $value = trim($value);
                            }
                            if ($value !== null && $value !== '') {
                                $mappedData[$field] = $value;
                            }
                        }
                    }

                    if (empty($mappedData) || (isset($mappedData['name']) && empty(trim($mappedData['name'])))) {
                        continue;
                    }

                    $rules = [
                        'name' => 'required|string|max:255',
                    ];

                    $validator = Validator::make($mappedData, $rules);

                    if ($validator->fails()) {
                        $failed++;
                        $rowNumber = $index + 2;
                        $errors[$rowNumber] = [
                            'row_data' => $mappedData,
                            'errors' => $validator->errors()->all(),
                        ];

                        continue;
                    }

                    try {
                        $programData = [
                            'name' => trim($mappedData['name']),
                            'is_active' => isset($mappedData['is_active']) ? (bool) $mappedData['is_active'] : true,
                            'import_batch' => $importedFileName,
                        ];

                        switch ($operation) {
                            case 'insert':
                                $existing = Program::where('name', $programData['name'])->exists();
                                if (! $existing) {
                                    $record = Program::create($programData);
                                    $trackingService->recordSuccess($importLog, $record, 'created');
                                } else {
                                    throw new \Exception("البرنامج '{$programData['name']}' موجود مسبقاً");
                                }
                                break;
                            case 'update':
                                if (isset($mappedData['id'])) {
                                    $program = Program::find($mappedData['id']);
                                    if ($program) {
                                        $program->update($programData);
                                        $trackingService->recordSuccess($importLog, $program, 'updated');
                                    }
                                } else {
                                    $program = Program::where('name', $programData['name'])->first();
                                    if ($program) {
                                        $program->update($programData);
                                        $trackingService->recordSuccess($importLog, $program, 'updated');
                                    }
                                }
                                break;
                            case 'both':
                                $record = Program::updateOrCreate(
                                    ['name' => $programData['name']],
                                    $programData
                                );
                                $action = $record->wasRecentlyCreated ? 'created' : 'updated';
                                $trackingService->recordSuccess($importLog, $record, $action);
                                break;
                        }
                        $successful++;
                    } catch (\Exception $e) {
                        $failed++;
                        $rowNumber = $index + 2;
                        $errors[$rowNumber] = [
                            'row_data' => $mappedData,
                            'errors' => [$e->getMessage()],
                        ];
                    }
                }
            });
        }

        $importedPath = 'imports/programs/'.$importedFileName;

        if (! Storage::exists('imports/programs/')) {
            Storage::makeDirectory('imports/programs/');
        }

        try {
            Storage::move($filePath, $importedPath);
        } catch (\Exception $e) {
            $importedFileName = basename($filePath);
        }

        $trackingService->finishImport($importLog, $totalRows, $successful, $failed, $errors);

        $report = [
            'total' => $totalRows,
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
        ];

        session(['import_errors' => $errors]);

        return view('configuration.programs.import.import_report', [
            'report' => $report,
            'errors' => $errors,
            'importedFile' => $importedFileName,
        ]);
    }

    public function undoImport($fileName)
    {
        $importedRecords = Program::where('import_batch', $fileName)->get();

        DB::transaction(function () use ($importedRecords) {
            foreach ($importedRecords as $record) {
                $record->delete();
            }
        });

        return redirect()->back()->with('success', 'تم التراجع عن الاستيراد بنجاح');
    }

    public function showExportForm()
    {
        return view('configuration.programs.export');
    }

    public function downloadExport(Request $request)
    {
        $startTime = microtime(true);

        try {
            $scope = $request->get('scope', 'all');
            $format = $request->get('format', 'xlsx');
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $fields = $request->get('fields', ['id', 'name', 'is_active']);

            $allowedSorts = ['name', 'is_active', 'created_at', 'id'];
            if (! in_array($sortBy, $allowedSorts)) {
                $sortBy = 'name';
            }
            if (! in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = Program::query();

            if ($scope === 'active') {
                $query->where('is_active', true);
            } elseif ($scope === 'inactive') {
                $query->where('is_active', false);
            }

            $programs = $query->orderBy($sortBy, $sortOrder)->get();
            $programsCount = $programs->count();

            Log::info('Export Started', [
                'module' => 'Programs',
                'scope' => $scope,
                'format' => $format,
                'user_id' => auth()->id() ?? 'guest',
                'total_records' => $programsCount,
                'timestamp' => now()->toDateTimeString(),
            ]);

            if ($programsCount === 0) {
                Log::warning('Export Warning', [
                    'module' => 'Programs',
                    'reason' => 'No programs found with selected scope',
                    'scope' => $scope,
                ]);
            }

            $dataRows = [];
            foreach ($programs as $program) {
                $row = [];
                foreach ($fields as $field) {
                    if ($field === 'id') {
                        $row[] = $program->id;
                    } elseif ($field === 'name') {
                        $row[] = $program->name;
                    } elseif ($field === 'is_active') {
                        $row[] = $program->is_active ? 'نشط' : 'غير نشط';
                    } elseif ($field === 'created_at') {
                        $row[] = $program->created_at->format('Y-m-d H:i:s');
                    }
                }
                $dataRows[] = $row;
            }

            $headers = [];
            foreach ($fields as $field) {
                if ($field === 'id') {
                    $headers[] = 'المعرف';
                } elseif ($field === 'name') {
                    $headers[] = 'اسم البرنامج';
                } elseif ($field === 'is_active') {
                    $headers[] = 'الحالة';
                } elseif ($field === 'created_at') {
                    $headers[] = 'تاريخ الإنشاء';
                }
            }

            if ($format === 'csv') {
                return $this->exportToCsv($headers, $dataRows);
            } else {
                return $this->exportToExcel($headers, $dataRows);
            }

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Export Failed', [
                'module' => 'Programs',
                'user_id' => auth()->id() ?? 'guest',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير البيانات: '.$e->getMessage());
        }
    }

    private function exportToExcel($headers, $dataRows)
    {
        $fileName = 'programs_export_'.date('Y-m-d_H-i-s').'.xlsx';

        try {
            $export = new ProgramsExport($headers, $dataRows);
            $spreadsheet = $export->export();

            $tempFile = tempnam(sys_get_temp_dir(), 'programs_export');

            if (! $tempFile) {
                throw new \Exception('Failed to create temporary file for export');
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            if (! file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('Export file was not created or is empty');
            }

            Log::info('Excel Export Success', [
                'module' => 'Programs',
                'file_name' => $fileName,
                'file_size' => filesize($tempFile).' bytes',
                'records_exported' => count($dataRows),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Excel Export Failed', [
                'module' => 'Programs',
                'error_message' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            throw $e;
        }
    }

    private function exportToCsv($headers, $dataRows)
    {
        $fileName = 'programs_export_'.date('Y-m-d_H-i-s').'.csv';

        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'programs_csv_export');
            $resource = fopen($tempFile, 'w');

            if (! $resource) {
                throw new \Exception('Failed to create temporary CSV file');
            }

            fputcsv($resource, $headers, ',', '"');

            foreach ($dataRows as $row) {
                fputcsv($resource, $row, ',', '"');
            }

            fclose($resource);

            if (! file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('CSV file was not created or is empty');
            }

            Log::info('CSV Export Success', [
                'module' => 'Programs',
                'file_name' => $fileName,
                'file_size' => filesize($tempFile).' bytes',
                'records_exported' => count($dataRows),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->download($tempFile, $fileName, ['Content-Type' => 'text/csv; charset=utf-8'])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('CSV Export Failed', [
                'module' => 'Programs',
                'error_message' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            throw $e;
        }
    }

    public function exportExcel()
    {
        $startTime = microtime(true);

        try {
            Log::info('Excel Export Started', [
                'module' => 'Programs',
                'user_id' => auth()->id() ?? 'guest',
                'timestamp' => now()->toDateTimeString(),
            ]);

            $programsCount = Program::count();

            if ($programsCount === 0) {
                Log::warning('Excel Export Warning', [
                    'module' => 'Programs',
                    'reason' => 'No programs found in database',
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }

            $export = new ProgramsExport;
            $spreadsheet = $export->export();

            $tempFile = tempnam(sys_get_temp_dir(), 'programs_export');

            if (! $tempFile) {
                throw new \Exception('Failed to create temporary file for export');
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            if (! file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('Export file was not created or is empty');
            }

            $fileName = 'programs_export_'.date('Y-m-d_H-i-s').'.xlsx';
            $fileSize = filesize($tempFile);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Excel Export Success', [
                'module' => 'Programs',
                'user_id' => auth()->id() ?? 'guest',
                'file_name' => $fileName,
                'file_size' => $fileSize.' bytes',
                'records_exported' => $programsCount,
                'execution_time' => $executionTime.' ms',
                'temp_file' => $tempFile,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Excel Export Failed', [
                'module' => 'Programs',
                'user_id' => auth()->id() ?? 'guest',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير البيانات: '.$e->getMessage());
        }
    }
}
