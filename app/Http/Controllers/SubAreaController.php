<?php

namespace App\Http\Controllers;

use App\Exports\SubAreasExport;
use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\SubArea;
use App\Services\FileImportService;
use App\Services\ImportTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SubAreaController extends Controller
{
    public function index(Request $request)
    {
        $query = SubArea::with('governorate', 'directorate');

        // 1. فلترة نصية متعددة الحقول
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhereHas('governorate', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('directorate', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        // 2. فلترة بالمحافظة
        if ($request->governorate_id) {
            $query->where('governorate_id', $request->governorate_id);
        }

        // 3. فلترة بالمديرية
        if ($request->directorate_id) {
            $query->where('directorate_id', $request->directorate_id);
        }

        // 4. نظام الترتيب
        if ($sort = $request->query('sort')) {
            $direction = $request->query('direction', 'asc');
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $subAreas = $query->paginate($perPage);
        $governorates = Governorate::getCachedAll();

        // Fetch directorates based on selected governorate if exists
        $directorates = $request->governorate_id
            ? Directorate::where('governorate_id', $request->governorate_id)->get()
            : collect();

        // إرسال معايير الفلترة للواجهة
        return view('configuration.subareas.index', compact('subAreas', 'governorates', 'directorates'))
            ->with('search', $request->search)
            ->with('governorate_id', $request->governorate_id)
            ->with('directorate_id', $request->directorate_id)
            ->with('sort', $request->sort)
            ->with('direction', $request->direction);
    }

    public function create()
    {
        $governorates = Governorate::getCachedAll();
        $directorates = collect(); // قائمة فارغة للمديريات

        return view('configuration.subareas.create', compact('governorates', 'directorates'))
            ->with('selected_governorate', old('governorate_id'))
            ->with('selected_directorate', old('directorate_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'name' => 'required|string|max:255|unique:sub_areas,name,NULL,id,directorate_id,'.$request->directorate_id,
        ]);

        SubArea::create($request->only('governorate_id', 'directorate_id', 'name'));

        return redirect()->route('sub-areas.index')->with('success', 'تمت إضافة العزلة / المنطقة');
    }

    public function show(SubArea $subArea)
    {
        $subArea->load(['governorate', 'directorate']);

        return view('configuration.subareas.show', compact('subArea'));
    }

    public function edit(SubArea $subArea)
    {
        $governorates = Governorate::getCachedAll();
        $directorates = Directorate::where('governorate_id', $subArea->governorate_id)->get();

        return view('configuration.subareas.edit', compact('subArea', 'governorates', 'directorates'))
            ->with('selected_governorate', $subArea->governorate_id)
            ->with('selected_directorate', $subArea->directorate_id);
    }

    public function update(Request $request, SubArea $subArea)
    {
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'name' => 'required|string|max:255|unique:sub_areas,name,'.$subArea->id.',id,directorate_id,'.$request->directorate_id,
        ]);

        $subArea->update($request->only('governorate_id', 'directorate_id', 'name'));

        return redirect()->route('sub-areas.index')->with('success', 'تم تحديث البيانات');
    }

    public function destroy(SubArea $subArea)
    {
        $subArea->delete();

        return redirect()->route('sub-areas.index')->with('success', 'تم حذف السجل');
    }

    // AJAX لجلب المديريات حسب المحافظة
    public function getDirectorates($governorate_id)
    {
        $query = Directorate::where('is_active', true);

        if (! empty($governorate_id) && $governorate_id !== '0') {
            $query->where('governorate_id', $governorate_id);
        }

        $directorates = $query->get(['id', 'name']);

        // Add text field for Select2 compatibility if needed
        $directorates->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($directorates);
    }

    /**
     * Get sub areas by directorate for API
     */
    public function getByDirectorate($governorateId, $directorateId)
    {
        $subAreas = SubArea::where('governorate_id', $governorateId)
            ->where('directorate_id', $directorateId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($subAreas);
    }

    public function showImportForm()
    {
        return view('configuration.subareas.import');
    }

    public function downloadTemplate(Request $request)
    {
        $format = $request->get('format', 'csv'); // Default to CSV

        $templateData = [
            'headers' => ['id', 'governorate_name', 'directorate_name', 'name', 'is_active'],
            'sample_data' => [
                [1, 'صنعاء', 'مديرية الثورة', 'حي الحصبة', 1],
                [2, 'عدن', 'مديرية المعلا', 'حي المعلا الجديد', 1],
                [3, 'تعز', 'مديرية الوازعية', 'منطقة الوازعية المركز', 1],
            ],
        ];

        $fileImportService = new FileImportService;
        $filePath = $fileImportService->createTemplate($templateData, $format);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request)
    {
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

        return view('configuration.subareas.import_preview', [
            'headers' => $headers,
            'rows' => $rows,
            'filePath' => $path,
            'mappingFields' => ['id', 'governorate_name', 'directorate_name', 'name', 'is_active'],
        ]);
    }

    public function processImport(Request $request)
    {
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
            Log::info('SubArea Import Started', [
                'file_path' => basename($filePath),
                'total_records' => count($records),
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'خطأ في قراءة الملف: '.$e->getMessage()]);
        }

        $trackingService = new ImportTrackingService;
        $importLog = $trackingService->startImport('المناطق والعزل', SubArea::class, basename($filePath));

        $totalRows = 0;
        $successful = 0;
        $failed = 0;
        $errors = [];

        // Generate import batch filename for tracking
        $importedFileName = 'imported_'.Str::uuid().'.'.pathinfo($filePath, PATHINFO_EXTENSION);

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
            $rules = [
                'name' => 'required|string|max:255',
                'governorate_name' => 'required|string',
                'directorate_name' => 'required|string',
            ];

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
                // Find governorate
                $governorate = Governorate::where('name', trim($mappedData['governorate_name']))->first();
                if (! $governorate) {
                    throw new \Exception("المحافظة '{$mappedData['governorate_name']}' غير موجودة");
                }

                // Find directorate
                $directorate = Directorate::where('name', trim($mappedData['directorate_name']))
                    ->where('governorate_id', $governorate->id)
                    ->first();
                if (! $directorate) {
                    throw new \Exception("المديرية '{$mappedData['directorate_name']}' غير موجودة في محافظة '{$mappedData['governorate_name']}'");
                }

                $subAreaData = [
                    'governorate_id' => $governorate->id,
                    'directorate_id' => $directorate->id,
                    'name' => trim($mappedData['name']),
                    'is_active' => isset($mappedData['is_active']) ? (bool) $mappedData['is_active'] : true,
                    'import_batch' => $importedFileName,
                ];

                switch ($operation) {
                    case 'insert':
                        // Check if record already exists
                        $existing = SubArea::where('name', $subAreaData['name'])
                            ->where('directorate_id', $directorate->id)
                            ->first();
                        if (! $existing) {
                            $record = SubArea::create($subAreaData);
                            $trackingService->recordSuccess($importLog, $record, 'created');
                        } else {
                            throw new \Exception("العزلة/المنطقة '{$subAreaData['name']}' موجودة مسبقاً في مديرية '{$mappedData['directorate_name']}'");
                        }
                        break;
                    case 'update':
                        if (isset($mappedData['id'])) {
                            $subArea = SubArea::find($mappedData['id']);
                            if ($subArea) {
                                $subArea->update($subAreaData);
                                $trackingService->recordSuccess($importLog, $subArea, 'updated');
                            } else {
                                throw new \Exception('لم يتم العثور على العزلة/المنطقة للتحديث');
                            }
                        } else {
                            $subArea = SubArea::where('name', $subAreaData['name'])
                                ->where('directorate_id', $directorate->id)
                                ->first();
                            if ($subArea) {
                                $subArea->update($subAreaData);
                                $trackingService->recordSuccess($importLog, $subArea, 'updated');
                            } else {
                                throw new \Exception('لم يتم العثور على العزلة/المنطقة للتحديث');
                            }
                        }
                        break;
                    case 'both':
                        $record = SubArea::updateOrCreate(
                            [
                                'name' => $subAreaData['name'],
                                'directorate_id' => $directorate->id,
                            ],
                            $subAreaData
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

        // حفظ نسخة من الملف المستورد بعد المعالجة
        $importedPath = 'imports/sub_areas/'.$importedFileName;

        // إنشاء مجلد الاستيراد إذا لم يكن موجوداً
        if (! Storage::exists('imports/sub_areas/')) {
            Storage::makeDirectory('imports/sub_areas/');
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

        return view('configuration.subareas.import_report', [
            'report' => $report,
            'errors' => $errors,
            'importedFile' => $importedFileName,
        ]);
    }

    public function downloadErrorReport(Request $request)
    {
        $errors = session('import_errors', []);

        if (empty($errors)) {
            return redirect()->back()->with('error', 'لا توجد أخطاء للتصدير');
        }

        $format = $request->get('format', 'xlsx');
        $fileImportService = new FileImportService;
        $filePath = $fileImportService->createErrorReport($errors, $format);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function undoImport($fileName)
    {
        // البحث عن السجلات المستوردة بواسطة اسم الملف
        $importedRecords = SubArea::where('import_batch', $fileName)->get();

        DB::transaction(function () use ($importedRecords) {
            foreach ($importedRecords as $record) {
                $record->delete();
            }
        });

        return redirect()->back()->with('success', 'تم التراجع عن الاستيراد بنجاح');
    }

    /**
     * Export sub areas to Excel
     */
    public function exportExcel()
    {
        $startTime = microtime(true);

        try {
            // Log export start
            Log::info('Excel Export Started', [
                'module' => 'SubAreas',
                'user_id' => auth()->id() ?? 'guest',
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Check if there are any sub areas to export
            $subAreasCount = SubArea::count();

            if ($subAreasCount === 0) {
                Log::warning('Excel Export Warning', [
                    'module' => 'SubAreas',
                    'reason' => 'No sub areas found in database',
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }

            // Create export
            $export = new SubAreasExport;
            $spreadsheet = $export->export();

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'sub_areas_export');

            if (! $tempFile) {
                throw new \Exception('Failed to create temporary file for export');
            }

            // Write to temporary file
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            // Check if file was created successfully
            if (! file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('Export file was not created or is empty');
            }

            $fileName = 'sub_areas_export_'.date('Y-m-d_H-i-s').'.xlsx';
            $fileSize = filesize($tempFile);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds

            // Log successful export
            Log::info('Excel Export Success', [
                'module' => 'SubAreas',
                'user_id' => auth()->id() ?? 'guest',
                'file_name' => $fileName,
                'file_size' => $fileSize.' bytes',
                'records_exported' => $subAreasCount,
                'execution_time' => $executionTime.' ms',
                'temp_file' => $tempFile,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Excel Export Failed - PhpSpreadsheet Error', [
                'module' => 'SubAreas',
                'user_id' => auth()->id() ?? 'guest',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'timestamp' => now()->toDateTimeString(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير البيانات: '.$e->getMessage());

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Excel Export Failed - General Error', [
                'module' => 'SubAreas',
                'user_id' => auth()->id() ?? 'guest',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير البيانات');

        } catch (\Throwable $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::critical('Excel Export Critical Error', [
                'module' => 'SubAreas',
                'user_id' => auth()->id() ?? 'guest',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ خطير أثناء تصدير البيانات');
        }
    }
}
