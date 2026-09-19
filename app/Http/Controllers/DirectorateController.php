<?php

// app/Http/Controllers/DirectorateController.php

namespace App\Http\Controllers;

use App\Exports\DirectoratesExport;
use App\Models\Directorate;
use App\Models\Governorate;
use App\Scopes\DomainScope;
use App\Services\FileImportService;
use App\Services\ImportTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DirectorateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Directorate::class);
        $query = Directorate::with('governorate');

        // البحث
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // الفلترة بالمحافظة (اختيار المستخدم فقط)
        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }

        // الترتيب
        if ($sort = $request->query('sort')) {
            $query->orderBy($sort, $request->query('direction', 'asc'));
        } else {
            $query->latest();
        }

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $directorates = $query->paginate($perPage);

        $governorates = Governorate::getCachedAll();

        return view('configuration.directorates.index', compact('directorates', 'governorates'))
            ->with('search', $request->search)
            ->with('governorate_id', $request->governorate_id)
            ->with('sort', $request->sort)
            ->with('direction', $request->direction);
    }

    public function create()
    {
        $this->authorize('create', Directorate::class);
        $governorates = Governorate::getCachedAll();

        return view('configuration.directorates.create', compact('governorates'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Directorate::class);
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:255|unique:directorates,name,NULL,id,governorate_id,'.$request->governorate_id,
        ]);

        Directorate::create($request->only('governorate_id', 'name'));

        return redirect()->route('directorates.index')->with('success', 'تمت الإضافة بنجاح');
    }

    public function edit(Directorate $directorate)
    {
        $this->authorize('update', Directorate::class);
        $governorates = Governorate::getCachedAll();

        return view('configuration.directorates.edit', compact('directorate', 'governorates'));
    }

    public function update(Request $request, Directorate $directorate)
    {
        $this->authorize('update', Directorate::class);
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:255|unique:directorates,name,'.$directorate->id.',id,governorate_id,'.$request->governorate_id,
        ]);

        $directorate->update($request->only('governorate_id', 'name'));

        return redirect()->route('directorates.index')->with('success', 'تم التحديث بنجاح');
    }

    public function destroy(Directorate $directorate)
    {
        $this->authorize('delete', Directorate::class);
        $directorate->delete();

        return redirect()->route('directorates.index')->with('success', 'تم الحذف بنجاح');
    }

    /**
     * Get directorates by governorate for API
     */
    public function getByGovernorate($governorateId)
    {
        $query = Directorate::withoutGlobalScope(DomainScope::class)->where('is_active', true);

        if (! empty($governorateId) && $governorateId !== '0') {
            $query->where('governorate_id', $governorateId);
        }

        $directorates = $query->select('id', 'name')
            ->orderBy('name')
            ->get();

        $directorates->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($directorates);
    }

    public function showImportForm()
    {
        return view('configuration.directorates.import');
    }

    public function downloadTemplate(Request $request)
    {
        $this->authorize('import', Directorate::class);
        $format = $request->get('format', 'csv');

        $templateData = [
            'headers' => ['id', 'governorate_name', 'name', 'is_active'],
            'sample_data' => [
                [1, 'صنعاء', 'مديرية الثورة', 1],
                [2, 'عدن', 'مديرية المعلا', 1],
                [3, 'تعز', 'مديرية الوازعية', 1],
            ],
        ];

        $fileImportService = new FileImportService;
        $filePath = $fileImportService->createTemplate($templateData, $format);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request)
    {
        $this->authorize('import', Directorate::class);
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $fileName = 'import_preview_'.time().'.'.$extension;
        $path = $file->storeAs('temp', $fileName);

        $fileImportService = new FileImportService;
        $fileData = $fileImportService->readFile($path, true);

        $headers = $fileData['headers'];
        $rows = array_slice($fileData['data'], 0, 10);

        return view('configuration.directorates.import_preview', [
            'headers' => $headers,
            'rows' => $rows,
            'filePath' => $path,
            'mappingFields' => ['id', 'governorate_name', 'name', 'is_active'],
        ]);
    }

    public function processImport(Request $request)
    {
        $this->authorize('import', Directorate::class);
        $request->validate([
            'file_path' => 'required',
            'operation' => 'required|in:insert,update,both',
            'mapping' => 'required|array',
        ]);

        $filePath = $request->input('file_path');
        $operation = $request->input('operation');
        $mapping = $request->input('mapping');

        $fileImportService = new FileImportService;
        $fileData = $fileImportService->readFile($filePath, true);
        $records = $fileData['data'];

        $trackingService = new ImportTrackingService;
        $importLog = $trackingService->startImport('المديريات', Directorate::class, basename($filePath));

        $totalRows = 0;
        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($records as $index => $row) {
            $totalRows++;

            $mappedData = [];
            foreach ($mapping as $field => $header) {
                if ($header && isset($row[$header])) {
                    $mappedData[$field] = $row[$header];
                }
            }

            if (isset($mappedData['governorate_name'])) {
                $governorate = Governorate::where('name', $mappedData['governorate_name'])->first();
                if ($governorate) {
                    $mappedData['governorate_id'] = $governorate->id;
                } else {
                    $failed++;
                    $errors[$index + 2] = ['المحافظة غير موجودة: '.$mappedData['governorate_name']];

                    continue;
                }
                unset($mappedData['governorate_name']);
            }

            $rules = [
                'governorate_id' => 'required|exists:governorates,id',
                'name' => 'required|string|max:255',
            ];

            if ($operation === 'insert') {
                $rules['name'] .= '|unique:directorates,name,NULL,id,governorate_id,'.($mappedData['governorate_id'] ?? 'NULL');
            } elseif ($operation === 'update' && isset($mappedData['id'])) {
                $rules['name'] .= '|unique:directorates,name,'.$mappedData['id'].',id,governorate_id,'.($mappedData['governorate_id'] ?? 'NULL');
            }

            $validator = Validator::make($mappedData, $rules);

            if ($validator->fails()) {
                $failed++;
                $errors[$index + 2] = $validator->errors()->all();

                continue;
            }

            try {
                switch ($operation) {
                    case 'insert':
                        $record = Directorate::create($mappedData);
                        $trackingService->recordSuccess($importLog, $record, 'created');
                        break;
                    case 'update':
                        if (isset($mappedData['id'])) {
                            $directorate = Directorate::find($mappedData['id']);
                            if ($directorate) {
                                $directorate->update($mappedData);
                                $trackingService->recordSuccess($importLog, $directorate, 'updated');
                            }
                        }
                        break;
                    case 'both':
                        $record = Directorate::updateOrCreate(
                            [
                                'name' => $mappedData['name'],
                                'governorate_id' => $mappedData['governorate_id'],
                            ],
                            $mappedData
                        );
                        $action = $record->wasRecentlyCreated ? 'created' : 'updated';
                        $trackingService->recordSuccess($importLog, $record, $action);
                        break;
                }
                $successful++;
            } catch (\Exception $e) {
                $failed++;
                $errors[$index + 2] = [$e->getMessage()];
            }
        }

        $importedFileName = 'imported_'.Str::uuid().'.csv';
        $importedPath = 'imports/directorates/'.$importedFileName;

        if (! Storage::exists('imports/directorates/')) {
            Storage::makeDirectory('imports/directorates/');
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

        return view('configuration.directorates.import_report', [
            'report' => $report,
            'errors' => $errors,
            'importedFile' => $importedFileName,
        ]);
    }

    public function downloadErrorReport(Request $request)
    {
        $errors = $request->session()->get('import_errors', []);
        $format = $request->get('format', 'csv');

        $fileImportService = new FileImportService;
        $filePath = $fileImportService->createErrorReport($errors, $format);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function undoImport($fileName)
    {
        $this->authorize('import', Directorate::class);
        $importedRecords = Directorate::where('import_batch', $fileName)->get();

        DB::transaction(function () use ($importedRecords) {
            foreach ($importedRecords as $record) {
                $record->delete();
            }
        });

        return redirect()->back()->with('success', 'تم التراجع عن الاستيراد بنجاح');
    }

    public function showExport()
    {
        return view('configuration.directorates.export');
    }

    public function exportExcel()
    {
        $this->authorize('export', Directorate::class);
        $startTime = microtime(true);
        $fileName = 'directorates_'.date('Y-m-d_H-i-s').'.xlsx';

        try {
            Log::info('Excel Export Started', [
                'module' => 'Directorates',
                'user_id' => auth()->id() ?? 'guest',
                'user_ip' => request()->ip(),
                'file_name' => $fileName,
                'timestamp' => now()->toDateTimeString(),
            ]);

            $directoratesCount = Directorate::count();

            if ($directoratesCount === 0) {
                Log::warning('Excel Export Warning', [
                    'module' => 'Directorates',
                    'reason' => 'No directorates found in database',
                    'user_id' => auth()->id() ?? 'guest',
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }

            $export = new DirectoratesExport;
            $spreadsheet = $export->export();

            $tempFile = tempnam(sys_get_temp_dir(), 'directorates_export');

            if (! $tempFile) {
                throw new \Exception('Failed to create temporary file for export');
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            if (! file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('Export file was not created or is empty');
            }

            $fileSize = filesize($tempFile);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Excel Export Success', [
                'module' => 'Directorates',
                'user_id' => auth()->id() ?? 'guest',
                'user_ip' => request()->ip(),
                'file_name' => $fileName,
                'file_size' => $fileSize.' bytes',
                'records_exported' => $directoratesCount,
                'execution_time' => $executionTime.' ms',
                'temp_file' => $tempFile,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Excel Export Failed - PhpSpreadsheet Error', [
                'module' => 'Directorates',
                'user_id' => auth()->id() ?? 'guest',
                'user_ip' => request()->ip(),
                'file_name' => $fileName,
                'error_type' => 'PhpSpreadsheet Exception',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return back()->with('error', 'فشل في تصدير الملف: خطأ في معالجة البيانات - '.$e->getMessage());

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Excel Export Failed - General Error', [
                'module' => 'Directorates',
                'user_id' => auth()->id() ?? 'guest',
                'user_ip' => request()->ip(),
                'file_name' => $fileName,
                'error_type' => 'General Exception',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'stack_trace' => $e->getTraceAsString(),
                'memory_usage' => memory_get_usage(true).' bytes',
                'memory_peak' => memory_get_peak_usage(true).' bytes',
                'timestamp' => now()->toDateTimeString(),
            ]);

            return back()->with('error', 'فشل في تصدير الملف: '.$e->getMessage());

        } catch (\Throwable $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::critical('Excel Export Critical Error', [
                'module' => 'Directorates',
                'user_id' => auth()->id() ?? 'guest',
                'user_ip' => request()->ip(),
                'file_name' => $fileName,
                'error_type' => 'Critical Error',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'stack_trace' => $e->getTraceAsString(),
                'memory_usage' => memory_get_usage(true).' bytes',
                'memory_peak' => memory_get_peak_usage(true).' bytes',
                'timestamp' => now()->toDateTimeString(),
            ]);

            return back()->with('error', 'حدث خطأ خطير أثناء التصدير. يرجى المحاولة مرة أخرى أو الاتصال بالدعم الفني.');
        }
    }
}
