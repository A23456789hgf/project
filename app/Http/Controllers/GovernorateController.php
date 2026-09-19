<?php

namespace App\Http\Controllers;

use App\Exports\GovernoratesExport;
use App\Models\Governorate;
// use App\Imports\GovernorateImport;
// use App\Imports\GovernoratesImport;
use App\Services\FileImportService;
use App\Services\ImportTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// use App\Http\Exports\GovernorateTemplateExport;
// use App\Http\Exports\ImportErrorsExport;
class GovernorateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Governorate::class);
        $query = Governorate::query();

        // البحث
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // الترتيب
        if ($sort = $request->query('sort')) {
            $query->orderBy($sort, $request->query('direction', 'asc'));
        } else {
            $query->latest();
        }

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $governorates = $query->paginate($perPage);

        return view('configuration.governorates.index', compact('governorates'));
    }

    public function create()
    {
        $this->authorize('create', Governorate::class);

        return view('configuration.governorates.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Governorate::class);
        $request->validate(['name' => 'required|unique:governorates,name']);
        Governorate::create($request->only('name'));

        return redirect()->route('governorates.index')->with('success', 'تمت الإضافة بنجاح');
    }

    public function edit(Governorate $governorate)
    {
        $this->authorize('update', Governorate::class);

        return view('configuration.governorates.edit', compact('governorate'));
    }

    public function update(Request $request, Governorate $governorate)
    {
        $this->authorize('update', Governorate::class);
        $request->validate([
            'name' => 'required|max:255|unique:governorates,name,'.$governorate->id,
        ]);
        $governorate->update($request->only('name'));

        return redirect()->route('governorates.index')->with('success', 'تم التحديث بنجاح');
    }

    public function destroy(Governorate $governorate)
    {
        $governorate->delete();

        return redirect()->route('governorates.index')->with('success', 'تم الحذف بنجاح');
    }

    public function show(Governorate $governorate)
    {
        $this->authorize('view', Governorate::class);

        // يمكنك تنفيذ المنطق الخاص بعرض تفاصيل المحافظة هنا
        return view('configuration.governorates.show', compact('governorate'));
    }

    public function showImportForm()
    {
        return view('configuration.governorates.import');
    }

    public function import(Request $request)
    {
        Excel::import(new GovernoratesImport, $request->file('file'));

        return back()->with('success', 'تم استيراد البيانات بنجاح!');
    }

    public function downloadTemplate(Request $request)
    {
        $this->authorize('import', Governorate::class);
        $format = $request->get('format', 'csv'); // Default to CSV

        $templateData = [
            'headers' => ['id', 'name'],
            'sample_data' => [
                [1, 'صنعاء الجديدة'],
                [2, 'عدن الجديدة'],
                [3, 'تعز الجديدة'],
            ],
        ];

        $fileImportService = new FileImportService;
        $filePath = $fileImportService->createTemplate($templateData, $format);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request)
    {
        $this->authorize('import', Governorate::class);
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $fileName = 'import_preview_'.time().'.'.$extension;
        $path = $file->storeAs('temp', $fileName);

        // Use FileImportService to handle both CSV and Excel
        $fileImportService = new FileImportService;
        $fileData = $fileImportService->readFile($path, true);

        $headers = $fileData['headers'];
        $rows = array_slice($fileData['data'], 0, 10); // أول 10 صفوف للمعاينة

        return view('configuration.governorates.import_preview', [
            'headers' => $headers,
            'rows' => $rows,
            'filePath' => $path,
            'mappingFields' => ['id', 'name'], // الحقول المتوقعة
        ]);
    }

    public function processImport(Request $request)
    {
        $this->authorize('import', Governorate::class);
        $request->validate([
            'file_path' => 'required',
            'operation' => 'required|in:insert,update,both',
            'mapping' => 'required|array',
        ]);

        $filePath = $request->input('file_path');
        $operation = $request->input('operation');
        $mapping = $request->input('mapping');

        // قراءة الملف ومعالجة البيانات أولاً (CSV أو Excel)
        $fileImportService = new FileImportService;

        try {
            $fileData = $fileImportService->readFile($filePath, true);
            $records = $fileData['data'];

            // Log file reading info for troubleshooting
            Log::info('File Import Started', [
                'file_path' => basename($filePath),
                'total_records' => count($records),
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'خطأ في قراءة الملف: '.$e->getMessage()]);
        }

        $trackingService = new ImportTrackingService;
        $importLog = $trackingService->startImport('المحافظات', Governorate::class, basename($filePath));

        $totalRows = 0;
        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($records as $index => $row) {
            $totalRows++;

            // تطبيق التطابق مع تنظيف البيانات
            $mappedData = [];
            foreach ($mapping as $field => $header) {
                if ($header && isset($row[$header])) {
                    $value = $row[$header];
                    // تنظيف البيانات
                    if (is_string($value)) {
                        $value = trim($value); // إزالة المسافات الزائدة
                    }
                    // تجاهل القيم الفارغة أو null
                    if ($value !== null && $value !== '') {
                        $mappedData[$field] = $value;
                    }
                }
            }

            // تجاهل الصفوف الفارغة تماماً
            if (empty($mappedData) || (isset($mappedData['name']) && empty(trim($mappedData['name'])))) {
                continue;
            }

            // التحقق من صحة البيانات
            $rules = ['name' => 'required|string|max:255'];

            // إضافة قاعدة التفرد حسب العملية
            if ($operation === 'insert') {
                $rules['name'] .= '|unique:governorates,name';
            } elseif ($operation === 'update' && isset($mappedData['id'])) {
                $rules['name'] .= '|unique:governorates,name,'.$mappedData['id'];
            }

            $validator = Validator::make($mappedData, $rules);

            if ($validator->fails()) {
                $failed++;
                $rowNumber = $index + 2; // +2 because index starts at 0 and we have header row
                $errors[$rowNumber] = [
                    'row_data' => $mappedData,
                    'errors' => $validator->errors()->all(),
                ];

                continue;
            }

            try {
                switch ($operation) {
                    case 'insert':
                        $record = Governorate::create($mappedData);
                        $trackingService->recordSuccess($importLog, $record, 'created');
                        break;
                    case 'update':
                        if (isset($mappedData['id'])) {
                            $governorate = Governorate::find($mappedData['id']);
                            if ($governorate) {
                                $governorate->update($mappedData);
                                $trackingService->recordSuccess($importLog, $governorate, 'updated');
                            }
                        }
                        break;
                    case 'both':
                        $record = Governorate::updateOrCreate(
                            ['name' => $mappedData['name']],
                            $mappedData
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
                    'errors' => ['Database Error: '.$e->getMessage()],
                ];
            }
        }

        // حفظ نسخة من الملف المستورد بعد المعالجة
        $importedFileName = 'imported_'.Str::uuid().'.csv';
        $importedPath = 'imports/governorates/'.$importedFileName;

        // إنشاء مجلد الاستيراد إذا لم يكن موجوداً
        if (! Storage::exists('imports/governorates/')) {
            Storage::makeDirectory('imports/governorates/');
        }

        try {
            Storage::move($filePath, $importedPath);
        } catch (\Exception $e) {
            // إذا فشل نقل الملف، استخدم اسم الملف الأصلي
            $importedFileName = basename($filePath);
        }

        $trackingService->finishImport($importLog, $totalRows, $successful, $failed, $errors);

        $report = [
            'total' => $totalRows,
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
        ];

        // حفظ الأخطاء في الجلسة لتقرير الأخطاء
        session(['import_errors' => $errors]);

        return view('configuration.governorates.import_report', [
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
        $this->authorize('import', Governorate::class);
        // البحث عن السجلات المستوردة بواسطة اسم الملف
        $importedRecords = Governorate::where('import_batch', $fileName)->get();

        DB::transaction(function () use ($importedRecords) {
            foreach ($importedRecords as $record) {
                $record->delete();
            }
        });

        return redirect()->back()->with('success', 'تم التراجع عن الاستيراد بنجاح');
    }

    /**
     * Debug method to test file reading
     */
    public function debugImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $fileName = 'debug_'.time().'.'.$extension;
        $path = $file->storeAs('temp', $fileName);

        $fileImportService = new FileImportService;

        try {
            $fileData = $fileImportService->readFile($path, true);

            return response()->json([
                'success' => true,
                'file_info' => [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'extension' => $extension,
                    'mime_type' => $file->getMimeType(),
                ],
                'headers' => $fileData['headers'],
                'total_rows' => count($fileData['data']),
                'sample_data' => array_slice($fileData['data'], 0, 5), // First 5 rows
                'all_data' => $fileData['data'], // All data for debugging
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Export governorates to Excel
     */
    public function exportExcel()
    {
        $this->authorize('export', Governorate::class);
        $startTime = microtime(true);
        $fileName = 'governorates_'.date('Y-m-d_H-i-s').'.xlsx';

        try {
            // Log export start
            Log::info('Excel Export Started', [
                'module' => 'Governorates',
                'user_id' => auth()->id() ?? 'guest',
                'user_ip' => request()->ip(),
                'file_name' => $fileName,
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Get governorates count for logging
            $governoratesCount = Governorate::count();

            if ($governoratesCount === 0) {
                Log::warning('Excel Export Warning', [
                    'module' => 'Governorates',
                    'reason' => 'No governorates found in database',
                    'user_id' => auth()->id() ?? 'guest',
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }

            // Create export
            $export = new GovernoratesExport;
            $spreadsheet = $export->export();

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'governorates_export');

            if (! $tempFile) {
                throw new \Exception('Failed to create temporary file for export');
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            // Check if file was created successfully
            if (! file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('Export file was not created or is empty');
            }

            $fileSize = filesize($tempFile);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds

            // Log successful export
            Log::info('Excel Export Success', [
                'module' => 'Governorates',
                'user_id' => auth()->id() ?? 'guest',
                'user_ip' => request()->ip(),
                'file_name' => $fileName,
                'file_size' => $fileSize.' bytes',
                'records_exported' => $governoratesCount,
                'execution_time' => $executionTime.' ms',
                'temp_file' => $tempFile,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Excel Export Failed - PhpSpreadsheet Error', [
                'module' => 'Governorates',
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
                'module' => 'Governorates',
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
                'module' => 'Governorates',
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
