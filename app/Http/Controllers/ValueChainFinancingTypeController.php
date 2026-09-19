<?php

namespace App\Http\Controllers;

use App\Exports\ValueChainFinancingTypesExport;
use App\Models\ValueChainFinancingType;
use App\Services\FileImportService;
use App\Services\ImportTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ValueChainFinancingTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:value-chain-financing-types.view')->only([
            'index',
            'activeTypes',
        ]);

        $this->middleware('permission:value-chain-financing-types.create')->only([
            'create',
            'store',
            'showImportForm',
            'downloadTemplate',
            'previewImport',
            'processImport',
        ]);

        $this->middleware('permission:value-chain-financing-types.edit')->only([
            'edit',
            'update',
            'undoImport',
        ]);

        $this->middleware('permission:value-chain-financing-types.delete')->only([
            'destroy',
        ]);

        $this->middleware('permission:value-chain-financing-types.export')->only([
            'showExportForm',
            'downloadExport',
            'exportExcel',
        ]);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ValueChainFinancingType::class);
        $query = ValueChainFinancingType::withInactive()
            ->visibleToUser('value-chain-financing-types')
            ->with(['creatorEntity']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $sort_by = $request->get('sort_by', 'name');
        $sort_order = $request->get('sort_order', 'asc');

        $allowed_sorts = ['name', 'is_active', 'created_at'];

        if (! in_array($sort_by, $allowed_sorts)) {
            $sort_by = 'name';
        }

        if (! in_array($sort_order, ['asc', 'desc'])) {
            $sort_order = 'asc';
        }

        $perPage = $request->query('per_page', 15);
        $perPage = in_array($perPage, [15, 50, 100, 500]) ? $perPage : 15;

        $types = $query
            ->orderBy($sort_by, $sort_order)
            ->paginate($perPage)
            ->withQueryString();

        return view('configuration.value_chain_financing_types.index', compact(
            'types',
            'sort_by',
            'sort_order'
        ));
    }

    public function create()
    {
        $this->authorize('create', ValueChainFinancingType::class);

        return view('configuration.value_chain_financing_types.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', ValueChainFinancingType::class);
        $request->validate([
            'name' => 'required|string|max:255|unique:value_chain_financing_types,name',
        ]);

        $user = auth()->user();

        $type = ValueChainFinancingType::create([
            'name' => $request->name,
            'is_active' => true,
            'created_by_entity' => $user->department ?? $user->entity_id,
            'created_by_user_id' => $user->id,
        ]);

        if ($request->ajax()) {
            return response()->json($type);
        }

        session()->flash('success', 'تمت الإضافة بنجاح');

        return redirect()
            ->route('value-chain-financing-types.index');
    }

    public function edit($id)
    {
        $this->authorize('update', ValueChainFinancingType::class);
        $type = ValueChainFinancingType::visibleToUser('value-chain-financing-types')
            ->findOrFail($id);

        return view('configuration.value_chain_financing_types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', ValueChainFinancingType::class);
        $type = ValueChainFinancingType::visibleToUser('value-chain-financing-types')
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:value_chain_financing_types,name,'.$type->id,
        ]);

        $type->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
        ]);

        session()->flash('success', 'تم التحديث بنجاح');

        return redirect()
            ->route('value-chain-financing-types.index');
    }

    public function destroy($id)
    {
        $this->authorize('delete', ValueChainFinancingType::class);
        $type = ValueChainFinancingType::visibleToUser('value-chain-financing-types')
            ->findOrFail($id);

        $type->delete();

        session()->flash('success', 'تم الحذف بنجاح');

        return redirect()
            ->route('value-chain-financing-types.index');
    }

    /**
     * الأنواع النشطة للقوائم المنسدلة
     */
    public function activeTypes()
    {
        $activeTypes = ValueChainFinancingType::where('is_active', true)
            ->visibleToUser('value-chain-financing-types')
            ->orderBy('name')
            ->get(['id', 'name']);

        if (request()->wantsJson()) {
            return response()->json($activeTypes);
        }

        return $activeTypes;
    }

    public function showImportForm()
    {
        return view('configuration.value_chain_financing_types.import');
    }

    public function downloadTemplate(Request $request)
    {
        $this->authorize('import', ValueChainFinancingType::class);
        $format = $request->get('format', 'xls');

        $templateData = [
            'headers' => ['id', 'name', 'is_active'],
            'sample_data' => [
                [1, 'النوع الأول', 1],
                [2, 'النوع الثاني', 1],
                [3, 'النوع الثالث', 1],
            ],
        ];

        $fileImportService = new FileImportService;

        $filePath = $fileImportService->createTemplate(
            $templateData,
            $format
        );

        return response()
            ->download($filePath)
            ->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request)
    {
        $this->authorize('import', ValueChainFinancingType::class);
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

        return view('configuration.value_chain_financing_types.import_preview', [
            'headers' => $headers,
            'rows' => $rows,
            'filePath' => $path,
            'mappingFields' => ['id', 'name', 'is_active'],
            'dbColumns' => [
                'id' => 'المعرف',
                'name' => 'اسم النوع',
                'is_active' => 'الحالة',
            ],
        ]);
    }

    public function processImport(Request $request)
    {
        $this->authorize('import', ValueChainFinancingType::class);
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

            Log::info('Value Chain Financing Types Import Started', [
                'file_path' => basename($filePath),
                'total_records' => count($records),
            ]);

        } catch (\Exception $e) {

            return back()->withErrors([
                'file' => 'خطأ في قراءة الملف: '.$e->getMessage(),
            ]);
        }

        $user = auth()->user();

        $totalRows = 0;
        $successful = 0;
        $failed = 0;
        $errors = [];

        $trackingService = new ImportTrackingService;
        $importLog = $trackingService->startImport('أنواع تمويل سلاسل القيمة', ValueChainFinancingType::class, basename($filePath));

        $importedFileName = 'imported_'.Str::uuid().'.'.pathinfo($filePath, PATHINFO_EXTENSION);

        $recordCount = count($records);

        $batchSize = $recordCount > 10000 ? 500 : 100;

        $batchedRecords = array_chunk($records, $batchSize, true);

        foreach ($batchedRecords as $batch) {

            DB::transaction(function () use (
                $batch,
                $mapping,
                $operation,
                $importedFileName,
                $user,
                &$totalRows,
                &$successful,
                &$failed,
                &$errors,
                $trackingService,
                $importLog
            ) {

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

                    if (
                        empty($mappedData) ||
                        (
                            isset($mappedData['name']) &&
                            empty(trim($mappedData['name']))
                        )
                    ) {
                        continue;
                    }

                    $validator = Validator::make($mappedData, [
                        'name' => 'required|string|max:255',
                    ]);

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

                        $typeData = [
                            'name' => trim($mappedData['name']),
                            'is_active' => isset($mappedData['is_active'])
                                ? (bool) $mappedData['is_active']
                                : true,
                            'import_batch' => $importedFileName,
                            'created_by_entity' => $user->department ?? $user->entity_id,
                            'created_by_user_id' => $user->id,
                        ];

                        switch ($operation) {

                            case 'insert':

                                $existing = ValueChainFinancingType::where(
                                    'name',
                                    $typeData['name']
                                )->exists();

                                if (! $existing) {

                                    $record = ValueChainFinancingType::create($typeData);
                                    $trackingService->recordSuccess($importLog, $record, 'created');

                                } else {

                                    throw new \Exception(
                                        "النوع '{$typeData['name']}' موجود مسبقاً"
                                    );
                                }

                                break;

                            case 'update':

                                if (isset($mappedData['id'])) {
                                    $type = ValueChainFinancingType::visibleToUser('value-chain-financing-types')
                                        ->where('id', $mappedData['id'])
                                        ->first();
                                    if ($type) {
                                        $type->update($typeData);
                                        $trackingService->recordSuccess($importLog, $type, 'updated');
                                    }
                                } else {
                                    $type = ValueChainFinancingType::visibleToUser('value-chain-financing-types')
                                        ->where('name', $typeData['name'])
                                        ->first();
                                    if ($type) {
                                        $type->update($typeData);
                                        $trackingService->recordSuccess($importLog, $type, 'updated');
                                    }
                                }

                                break;

                            case 'both':

                                $record = ValueChainFinancingType::updateOrCreate(
                                    ['name' => $typeData['name']],
                                    $typeData
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

        $importedPath = 'imports/value_chain_financing_types/'.$importedFileName;

        if (! Storage::exists('imports/value_chain_financing_types/')) {
            Storage::makeDirectory('imports/value_chain_financing_types/');
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

        session([
            'import_errors' => $errors,
        ]);

        return view('configuration.value_chain_financing_types.import.import_report', [
            'report' => $report,
            'errors' => $errors,
            'importedFile' => $importedFileName,
        ]);
    }

    public function undoImport($fileName)
    {
        $this->authorize('import', ValueChainFinancingType::class);
        $importedRecords = ValueChainFinancingType::visibleToUser('value-chain-financing-types')
            ->where('import_batch', $fileName)
            ->get();

        DB::transaction(function () use ($importedRecords) {

            foreach ($importedRecords as $record) {
                $record->delete();
            }
        });

        session()->flash('success', 'تم التراجع عن الاستيراد بنجاح');

        return redirect()
            ->back();
    }

    public function showExportForm()
    {
        return view('configuration.value_chain_financing_types.export');
    }

    public function downloadExport(Request $request)
    {
        $startTime = microtime(true);

        try {

            $scope = $request->get('scope', 'all');

            $format = $request->get('format', 'xlsx');

            $sortBy = $request->get('sort_by', 'name');

            $sortOrder = $request->get('sort_order', 'asc');

            $fields = $request->get('fields', [
                'id',
                'name',
                'is_active',
            ]);

            $allowedSorts = [
                'name',
                'is_active',
                'created_at',
                'id',
            ];

            if (! in_array($sortBy, $allowedSorts)) {
                $sortBy = 'name';
            }

            if (! in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = ValueChainFinancingType::visibleToUser('value-chain-financing-types');

            if ($scope === 'active') {
                $query->where('is_active', true);
            } elseif ($scope === 'inactive') {
                $query->where('is_active', false);
            }

            $types = $query
                ->orderBy($sortBy, $sortOrder)
                ->get();

            $dataRows = [];

            foreach ($types as $type) {

                $row = [];

                foreach ($fields as $field) {

                    if ($field === 'id') {
                        $row[] = $type->id;
                    } elseif ($field === 'name') {
                        $row[] = $type->name;
                    } elseif ($field === 'is_active') {
                        $row[] = $type->is_active
                            ? 'نشط'
                            : 'غير نشط';
                    } elseif ($field === 'created_at') {
                        $row[] = $type->created_at
                            ->format('Y-m-d H:i:s');
                    }
                }

                $dataRows[] = $row;
            }

            $headers = [];

            foreach ($fields as $field) {

                if ($field === 'id') {
                    $headers[] = 'المعرف';
                } elseif ($field === 'name') {
                    $headers[] = 'اسم النوع';
                } elseif ($field === 'is_active') {
                    $headers[] = 'الحالة';
                } elseif ($field === 'created_at') {
                    $headers[] = 'تاريخ الإنشاء';
                }
            }

            if ($format === 'csv') {
                return $this->exportToCsv($headers, $dataRows);
            }

            return $this->exportToExcel($headers, $dataRows);

        } catch (\Exception $e) {

            Log::error('Export Failed', [
                'module' => 'Value Chain Financing Types',
                'error_message' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            session()->flash('error', 'حدث خطأ أثناء التصدير');

            return redirect()
                ->back();
        }
    }

    private function exportToExcel($headers, $dataRows)
    {
        $fileName = 'vc_financing_types_export_'.date('Y-m-d_H-i-s').'.xlsx';

        $export = new ValueChainFinancingTypesExport($headers, $dataRows);

        $spreadsheet = $export->export();

        $tempFile = tempnam(sys_get_temp_dir(), 'vc_fin_types_export');

        $writer = new Xlsx($spreadsheet);

        $writer->save($tempFile);

        return response()
            ->download($tempFile, $fileName)
            ->deleteFileAfterSend(true);
    }

    private function exportToCsv($headers, $dataRows)
    {
        $fileName = 'vc_financing_types_export_'.date('Y-m-d_H-i-s').'.csv';

        $tempFile = tempnam(sys_get_temp_dir(), 'vc_fin_types_csv_export');

        $resource = fopen($tempFile, 'w');

        fputcsv($resource, $headers);

        foreach ($dataRows as $row) {
            fputcsv($resource, $row);
        }

        fclose($resource);

        return response()
            ->download(
                $tempFile,
                $fileName,
                ['Content-Type' => 'text/csv; charset=utf-8']
            )
            ->deleteFileAfterSend(true);
    }

    public function exportExcel()
    {
        $this->authorize('export', ValueChainFinancingType::class);
        $typesCount = ValueChainFinancingType::visibleToUser('value-chain-financing-types')->count();

        Log::info('Excel Export Started', [
            'module' => 'Value Chain Financing Types',
            'records_exported' => $typesCount,
            'user_id' => auth()->id(),
        ]);

        $export = new ValueChainFinancingTypesExport;

        $spreadsheet = $export->export();

        $tempFile = tempnam(sys_get_temp_dir(), 'vc_fin_types_export');

        $writer = new Xlsx($spreadsheet);

        $writer->save($tempFile);

        $fileName = 'vc_financing_types_export_'.date('Y-m-d_H-i-s').'.xlsx';

        return response()
            ->download($tempFile, $fileName)
            ->deleteFileAfterSend(true);
    }
}
