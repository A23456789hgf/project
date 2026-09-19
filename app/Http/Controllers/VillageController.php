<?php

namespace App\Http\Controllers;

use App\Exports\VillagesExport;
use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\SubArea;
use App\Models\Village;
use App\Services\FileImportService;
use App\Services\ImportTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class VillageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Village::class);
        $query = Village::with('governorate', 'directorate', 'subArea');

        // فلترة متعددة المستويات
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhereHas('governorate', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('directorate', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('subArea', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        // فلترة بالمحافظة
        if ($request->governorate_id) {
            $query->where('governorate_id', $request->governorate_id);
        }

        // فلترة بالمديرية
        if ($request->directorate_id) {
            $query->where('directorate_id', $request->directorate_id);
        }

        // فلترة بالعزلة
        if ($request->sub_area_id) {
            $query->where('sub_area_id', $request->sub_area_id);
        }

        // الترتيب
        if ($sort = $request->query('sort')) {
            $direction = $request->query('direction', 'asc');
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $villages = $query->paginate($perPage);
        $governorates = Governorate::getCachedAll();

        // Fetch directorates based on selected governorate
        $directorates = $request->governorate_id
            ? Directorate::where('governorate_id', $request->governorate_id)->get()
            : collect();

        // Fetch sub-areas based on selected directorate
        $subAreas = $request->directorate_id
            ? SubArea::where('directorate_id', $request->directorate_id)->get()
            : collect();

        return view('configuration.villages.index', compact('villages', 'governorates', 'directorates', 'subAreas'))
            ->with('search', $request->search)
            ->with('governorate_id', $request->governorate_id)
            ->with('directorate_id', $request->directorate_id)
            ->with('sub_area_id', $request->sub_area_id)
            ->with('sort', $request->sort)
            ->with('direction', $request->direction);
    }

    public function create()
    {
        $this->authorize('create', Village::class);
        $governorates = Governorate::getCachedAll();
        $directorates = collect();
        $subAreas = collect();

        return view('configuration.villages.create', compact('governorates', 'directorates', 'subAreas'))
            ->with('selected_governorate', old('governorate_id'))
            ->with('selected_directorate', old('directorate_id'))
            ->with('selected_sub_area', old('sub_area_id'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Village::class);
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'sub_area_id' => 'required|exists:sub_areas,id',
            'name' => 'required|string|max:255|unique:villages,name,NULL,id,sub_area_id,'.$request->sub_area_id,
            'is_active' => 'boolean',
        ]);

        Village::create([
            'governorate_id' => $request->governorate_id,
            'directorate_id' => $request->directorate_id,
            'sub_area_id' => $request->sub_area_id,
            'name' => $request->name,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('villages.index')->with('success', 'تمت إضافة القرية بنجاح');
    }

    public function show(Village $village)
    {
        $this->authorize('view', Village::class);
        $village->load(['governorate', 'directorate', 'subArea']);

        return view('configuration.villages.show', compact('village'));
    }

    public function edit(Village $village)
    {
        $this->authorize('update', Village::class);
        $governorates = Governorate::getCachedAll();
        $directorates = Directorate::where('governorate_id', $village->governorate_id)->get();
        $subAreas = SubArea::where('directorate_id', $village->directorate_id)->get();

        return view('configuration.villages.edit', compact('village', 'governorates', 'directorates', 'subAreas'))
            ->with('selected_governorate', $village->governorate_id)
            ->with('selected_directorate', $village->directorate_id)
            ->with('selected_sub_area', $village->sub_area_id);
    }

    public function update(Request $request, Village $village)
    {
        $this->authorize('update', Village::class);
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'sub_area_id' => 'required|exists:sub_areas,id',
            'name' => 'required|string|max:255|unique:villages,name,'.$village->id.',id,sub_area_id,'.$request->sub_area_id,
            'is_active' => 'boolean',
        ]);

        $village->update([
            'governorate_id' => $request->governorate_id,
            'directorate_id' => $request->directorate_id,
            'sub_area_id' => $request->sub_area_id,
            'name' => $request->name,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('villages.index')->with('success', 'تم تحديث بيانات القرية بنجاح');
    }

    public function destroy(Village $village)
    {
        $this->authorize('delete', Village::class);
        $village->delete();

        return redirect()->route('villages.index')->with('success', 'تم حذف القرية بنجاح');
    }

    // AJAX: جلب المديريات حسب المحافظة
    public function getDirectorates($governorate_id)
    {
        $query = Directorate::where('is_active', true);

        if (! empty($governorate_id) && $governorate_id !== '0') {
            $query->where('governorate_id', $governorate_id);
        }

        $directorates = $query->get(['id', 'name']);

        $directorates->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($directorates);
    }

    // AJAX: جلب العزل حسب المديرية
    public function getSubAreas($directorate_id)
    {
        $query = SubArea::where('is_active', true);

        if (! empty($directorate_id) && $directorate_id !== '0') {
            $query->where('directorate_id', $directorate_id);
        }

        $subAreas = $query->get(['id', 'name']);

        $subAreas->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($subAreas);
    }

    /**
     * Get villages by sub area for API
     */
    public function getBySubArea($governorateId, $directorateId, $subAreaId)
    {
        $villages = Village::where('governorate_id', $governorateId)
            ->where('directorate_id', $directorateId)
            ->where('sub_area_id', $subAreaId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($villages);
    }

    public function showImportForm()
    {
        return view('configuration.villages.import');
    }

    public function downloadTemplate(Request $request)
    {
        $this->authorize('import', Village::class);
        $format = $request->get('format', 'xls'); // Default to XLS

        $templateData = [
            'headers' => ['id', 'governorate_name', 'directorate_name', 'sub_area_name', 'name', 'is_active'],
            'sample_data' => [
                [1, 'صنعاء', 'مديرية الثورة', 'حي الحصبة', 'قرية الحصبة الشرقية', 1],
                [2, 'عدن', 'مديرية المعلا', 'حي المعلا الجديد', 'قرية المعلا الغربية', 1],
                [3, 'تعز', 'مديرية الوازعية', 'منطقة الوازعية المركز', 'قرية الوازعية الجنوبية', 1],
            ],
        ];

        $fileImportService = new FileImportService;
        $filePath = $fileImportService->createTemplate($templateData, $format);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request)
    {
        $this->authorize('import', Village::class);
        // Increase limits for reading large files
        set_time_limit(600); // 10 minutes
        ini_set('memory_limit', '512M'); // 512MB memory

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:150000000',
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

        return view('configuration.villages.import_preview', [
            'headers' => $headers,
            'rows' => $rows,
            'filePath' => $path,
            'mappingFields' => ['id', 'governorate_name', 'directorate_name', 'sub_area_name', 'name', 'is_active'],
        ]);
    }

    public function processImport(Request $request)
    {
        $this->authorize('import', Village::class);
        // Increase execution time and memory for very large imports (150MB+)
        set_time_limit(900); // 15 minutes
        ini_set('memory_limit', '1024M'); // 1GB memory

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
            Log::info('Village Import Started', [
                'file_path' => basename($filePath),
                'total_records' => count($records),
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'خطأ في قراءة الملف: '.$e->getMessage()]);
        }

        // ===== OPTIMIZATION: Cache all location lookups =====
        // Load all governorates, directorates, and sub-areas into memory once
        $governoratesCache = Governorate::getCachedAll()->keyBy('name');
        $directoratesCache = Directorate::getCachedAll()->keyBy(function ($item) {
            return $item->name.'|'.$item->governorate_id;
        });
        $subAreasCache = SubArea::getCachedAll()->keyBy(function ($item) {
            return $item->name.'|'.$item->directorate_id;
        });

        $totalRows = 0;
        $successful = 0;
        $failed = 0;
        $errors = [];

        $trackingService = new ImportTrackingService;
        $importLog = $trackingService->startImport('القرى', Village::class, basename($filePath));

        // Generate import batch filename for tracking
        $importedFileName = 'imported_'.Str::uuid().'.'.pathinfo($filePath, PATHINFO_EXTENSION);

        // ===== OPTIMIZATION: Process in batches for memory efficiency =====
        // Larger batch size for faster processing of very large files
        $recordCount = count($records);
        $batchSize = $recordCount > 10000 ? 500 : 100; // Use larger batches for huge files
        $batchedRecords = array_chunk($records, $batchSize, true);

        foreach ($batchedRecords as $batch) {
            DB::transaction(function () use ($batch, $mapping, $operation, $importedFileName,
                $governoratesCache, $directoratesCache, $subAreasCache,
                &$totalRows, &$successful, &$failed, &$errors, $trackingService, $importLog) {

                foreach ($batch as $index => $row) {
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
                        'sub_area_name' => 'required|string',
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
                        // ===== OPTIMIZATION: Use cached lookups instead of queries =====
                        $govName = trim($mappedData['governorate_name']);
                        $governorate = $governoratesCache[$govName] ?? null;
                        if (! $governorate) {
                            throw new \Exception("المحافظة '{$govName}' غير موجودة");
                        }

                        $dirName = trim($mappedData['directorate_name']);
                        $directorate = $directoratesCache[$dirName.'|'.$governorate->id] ?? null;
                        if (! $directorate) {
                            throw new \Exception("المديرية '{$dirName}' غير موجودة في محافظة '{$govName}'");
                        }

                        $subAreaName = trim($mappedData['sub_area_name']);
                        $subArea = $subAreasCache[$subAreaName.'|'.$directorate->id] ?? null;
                        if (! $subArea) {
                            throw new \Exception("العزلة/المنطقة '{$subAreaName}' غير موجودة في مديرية '{$dirName}'");
                        }

                        $villageData = [
                            'governorate_id' => $governorate->id,
                            'directorate_id' => $directorate->id,
                            'sub_area_id' => $subArea->id,
                            'name' => trim($mappedData['name']),
                            'is_active' => isset($mappedData['is_active']) ? (bool) $mappedData['is_active'] : true,
                            'import_batch' => $importedFileName,
                        ];

                        switch ($operation) {
                            case 'insert':
                                $existing = Village::where('name', $villageData['name'])
                                    ->where('sub_area_id', $subArea->id)
                                    ->exists();
                                if (! $existing) {
                                    $record = Village::create($villageData);
                                    $trackingService->recordSuccess($importLog, $record, 'created');
                                } else {
                                    throw new \Exception("القرية '{$villageData['name']}' موجودة مسبقاً");
                                }
                                break;
                            case 'update':
                                if (isset($mappedData['id'])) {
                                    $village = Village::find($mappedData['id']);
                                    if ($village) {
                                        $village->update($villageData);
                                        $trackingService->recordSuccess($importLog, $village, 'updated');
                                    }
                                } else {
                                    $village = Village::where('name', $villageData['name'])
                                        ->where('sub_area_id', $subArea->id)
                                        ->first();
                                    if ($village) {
                                        $village->update($villageData);
                                        $trackingService->recordSuccess($importLog, $village, 'updated');
                                    }
                                }
                                break;
                            case 'both':
                                $record = Village::updateOrCreate(
                                    [
                                        'name' => $villageData['name'],
                                        'sub_area_id' => $subArea->id,
                                    ],
                                    $villageData
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

        // حفظ نسخة من الملف المستورد بعد المعالجة
        $importedPath = 'imports/villages/'.$importedFileName;

        // إنشاء مجلد الاستيراد إذا لم يكن موجوداً
        if (! Storage::exists('imports/villages/')) {
            Storage::makeDirectory('imports/villages/');
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

        return view('configuration.villages.import_report', [
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
        $this->authorize('import', Village::class);
        // البحث عن السجلات المستوردة بواسطة اسم الملف
        $importedRecords = Village::where('import_batch', $fileName)->get();

        DB::transaction(function () use ($importedRecords) {
            foreach ($importedRecords as $record) {
                $record->delete();
            }
        });

        return redirect()->back()->with('success', 'تم التراجع عن الاستيراد بنجاح');
    }

    /**
     * Export villages to Excel
     */
    public function exportExcel()
    {
        $this->authorize('export', Village::class);
        $startTime = microtime(true);

        try {
            // Log export start
            Log::info('Excel Export Started', [
                'module' => 'Villages',
                'user_id' => auth()->id() ?? 'guest',
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Check if there are any villages to export
            $villagesCount = Village::count();

            if ($villagesCount === 0) {
                Log::warning('Excel Export Warning', [
                    'module' => 'Villages',
                    'reason' => 'No villages found in database',
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }

            // Create export
            $export = new VillagesExport;
            $spreadsheet = $export->export();

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'villages_export');

            if (! $tempFile) {
                throw new \Exception('Failed to create temporary file for export');
            }

            // Write to temporary file using XLS format
            $writer = new Xls($spreadsheet);
            $writer->save($tempFile);

            // Check if file was created successfully
            if (! file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('Export file was not created or is empty');
            }

            $fileName = 'villages_export_'.date('Y-m-d_H-i-s').'.xls';
            $fileSize = filesize($tempFile);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds

            // Log successful export
            Log::info('Excel Export Success', [
                'module' => 'Villages',
                'user_id' => auth()->id() ?? 'guest',
                'file_name' => $fileName,
                'file_size' => $fileSize.' bytes',
                'records_exported' => $villagesCount,
                'execution_time' => $executionTime.' ms',
                'temp_file' => $tempFile,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Excel Export Failed - PhpSpreadsheet Error', [
                'module' => 'Villages',
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
                'module' => 'Villages',
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
                'module' => 'Villages',
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
