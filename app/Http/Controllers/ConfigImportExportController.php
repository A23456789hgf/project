<?php

namespace App\Http\Controllers;

use App\Exports\BaseSpreadsheetExport;
use App\Models\Association;
use App\Models\Authority;
use App\Models\BeneficiaryGroup;
use App\Models\Directorate;
use App\Models\Domain;
use App\Models\Donor;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Executor;
use App\Models\FinancialItem;
use App\Models\FinancingType;
use App\Models\FormFinancing;
use App\Models\FundedEntity;
use App\Models\FundingSource;
use App\Models\Governorate;
use App\Models\InternalEntity;
use App\Models\Intervention;
use App\Models\MainGuide;
use App\Models\MainRouter;
use App\Models\Participant;
use App\Models\Priority;
use App\Models\Program;
use App\Models\SubArea;
use App\Models\Subdomain;
use App\Models\SubFinancingForm;
use App\Models\SubRouter;
use App\Models\Supervisor;
use App\Models\Unit;
use App\Models\Village;
use App\Services\FileImportService;
use App\Services\ImportTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ConfigImportExportController extends Controller
{
    // Map entity => config: model class, headers, columns, title, unique keys
    private array $configs = [
        'programs' => [
            'model' => Program::class,
            'headers' => ['المعرف', 'اسم البرنامج', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'البرامج',
            'unique' => ['name'],
        ],
        'domains' => [
            'model' => Domain::class,
            'headers' => ['المعرف', 'اسم المجال', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'المجالات',
            'unique' => ['name'],
        ],
        'subdomains' => [
            'model' => Subdomain::class,
            'headers' => ['المعرف', 'المجال', 'اسم المجال الفرعي', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'domain_id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'المجالات الفرعية',
            'unique' => ['domain_id', 'name'],
            'relations' => [
                'domain_id' => [Domain::class, 'name', 'domain_name'],
            ],
        ],
        'interventions' => [
            'model' => Intervention::class,
            'headers' => ['المعرف', 'اسم التدخل', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'أنواع التدخل',
            'unique' => ['name'],
        ],
        'entity-types' => [
            'model' => EntityType::class,
            'headers' => ['المعرف', 'نوع الجهة', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'أنواع الجهات',
            'unique' => ['name'],
        ],
        'entities' => [
            'model' => Entity::class,
            'headers' => ['المعرف', 'اسم الجهة', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الجهات',
            'unique' => ['name'],
        ],
        'funding-entities' => [
            'model' => FundedEntity::class,
            'headers' => ['المعرف', 'اسم الجهة الممولة', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الجهات الممولة',
            'unique' => ['name'],
        ],
        'funding-sources' => [
            'model' => FundingSource::class,
            'headers' => ['المعرف', 'اسم مصدر التمويل', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'مصادر التمويل',
            'unique' => ['name'],
        ],
        'executors' => [
            'model' => Executor::class,
            'headers' => ['المعرف', 'اسم الجهة المنفذة', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الجهات المنفذة',
            'unique' => ['name'],
        ],
        'supervisors' => [
            'model' => Supervisor::class,
            'headers' => ['المعرف', 'اسم الجهة المشرفة', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الجهات المشرفة',
            'unique' => ['name'],
        ],
        'participation' => [
            'model' => Participant::class,
            'headers' => ['المعرف', 'اسم الجهة المشاركة', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الجهات المشاركة',
            'unique' => ['name'],
        ],
        'donors' => [
            'model' => Donor::class,
            'headers' => ['المعرف', 'اسم الجهة المانحة', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الجهات المانحة',
            'unique' => ['name'],
        ],
        'associations' => [
            'model' => Association::class,
            'headers' => ['المعرف', 'اسم الجمعية', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الجمعيات',
            'unique' => ['name'],
        ],
        'financial-item' => [
            'model' => FinancialItem::class,
            'headers' => ['المعرف', 'اسم البند', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'البنود المالية',
            'unique' => ['name'],
        ],
        'financing-types' => [
            'model' => FinancingType::class,
            'headers' => ['المعرف', 'نوع التمويل', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'أنواع التمويل',
            'unique' => ['name'],
        ],
        'formfinancing' => [
            'model' => FormFinancing::class,
            'headers' => ['المعرف', 'شكل التمويل', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'أشكال التمويل',
            'unique' => ['name'],
        ],
        'subfinancing-forms' => [
            'model' => SubFinancingForm::class,
            'headers' => ['المعرف', 'شكل التمويل', 'الاسم الفرعي', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'financing_form_id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'أشكال التمويل الفرعية',
            'unique' => ['financing_form_id', 'name'],
            'relations' => [
                'financing_form_id' => [FormFinancing::class, 'name', 'financing_form_name'],
            ],
        ],
        'priorities' => [
            'model' => Priority::class,
            'headers' => ['المعرف', 'الأولوية', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'priority', 'is_enabled', 'created_at', 'updated_at'],
            'title' => 'الأولويات',
            'unique' => ['priority'],
        ],
        'main-routers' => [
            'model' => MainRouter::class,
            'headers' => ['المعرف', 'اسم الموجه الرئيسي', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'main_router', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الموجهات الرئيسية',
            'unique' => ['main_router'],
        ],
        'sub-routers' => [
            'model' => SubRouter::class,
            'headers' => ['المعرف', 'الموجه الرئيسي', 'اسم الموجه الفرعي', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'main_router_id', 'sub_router', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الموجهات الفرعية',
            'unique' => ['main_router_id', 'sub_router'],
            'relations' => [
                'main_router_id' => [MainRouter::class, 'main_router', 'main_router_name'],
            ],
        ],
        'main-guides' => [
            'model' => MainGuide::class,
            'headers' => ['المعرف', 'اسم الدليل الرئيسي', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الأدلة الرئيسية',
            'unique' => ['name'],
        ],
        'authorities' => [
            'model' => Authority::class,
            'headers' => ['المعرف', 'اسم الجهة', 'الجهة الأم', 'المحافظة', 'المديرية', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'agency_name', 'parent_id', 'governorate_id', 'directorate_id', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الجهات',
            'unique' => ['agency_name'],
            'relations' => [
                'parent_id' => [Authority::class, 'agency_name', 'parent_name'],
                'governorate_id' => [Governorate::class, 'name', 'governorate_name'],
                'directorate_id' => [Directorate::class, 'name', 'directorate_name'],
            ],
        ],
        'governorates' => [
            'model' => Governorate::class,
            'headers' => ['المعرف', 'اسم المحافظة', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'المحافظات',
            'unique' => ['name'],
        ],
        'directorates' => [
            'model' => Directorate::class,
            'headers' => ['المعرف', 'المحافظة', 'اسم المديرية', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'governorate_id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'المديريات',
            'unique' => ['governorate_id', 'name'],
            'relations' => [
                'governorate_id' => [Governorate::class, 'name', 'governorate_name'],
            ],
        ],
        'sub-areas' => [
            'model' => SubArea::class,
            'headers' => ['المعرف', 'المحافظة', 'المديرية', 'اسم العزلة/المنطقة', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'governorate_id', 'directorate_id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'العزل والمناطق',
            'unique' => ['directorate_id', 'name'],
            'relations' => [
                'governorate_id' => [Governorate::class, 'name', 'governorate_name'],
                'directorate_id' => [Directorate::class, 'name', 'directorate_name'],
            ],
        ],
        'villages' => [
            'model' => Village::class,
            'headers' => ['المعرف', 'المحافظة', 'المديرية', 'العزلة', 'اسم القرية', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'governorate_id', 'directorate_id', 'sub_area_id', 'name', 'is_active', 'created_at', 'updated_at'],
            'title' => 'القرى',
            'unique' => ['sub_area_id', 'name'],
            'relations' => [
                'governorate_id' => [Governorate::class, 'name', 'governorate_name'],
                'directorate_id' => [Directorate::class, 'name', 'directorate_name'],
                'sub_area_id' => [SubArea::class, 'name', 'sub_area_name'],
            ],
        ],
        'units' => [
            'model' => Unit::class,
            'headers' => ['المعرف', 'اسم الوحدة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'unit_name', 'created_at', 'updated_at'],
            'title' => 'الوحدات',
            'unique' => ['unit_name'],
        ],
        'beneficiary-groups' => [
            'model' => BeneficiaryGroup::class,
            'headers' => ['المعرف', 'اسم الفئة المستفيدة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'created_at', 'updated_at'],
            'title' => 'الفئات المستفيدة',
            'unique' => ['name'],
        ],
        'internal-entities' => [
            'model' => InternalEntity::class,
            'headers' => ['المعرف', 'اسم الجهة', 'كود الجهة', 'الجهة الأم', 'الجهة المشرفة', 'المحافظة', 'المديرية', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'],
            'columns' => ['id', 'name', 'entity_code', 'parent_id', 'authority_id', 'governorate_id', 'directorate_id', 'is_active', 'created_at', 'updated_at'],
            'title' => 'الجهات الداخلية',
            'unique' => ['name'],
            'relations' => [
                'parent_id' => [InternalEntity::class, 'name', 'parent_name'],
                'authority_id' => [Authority::class, 'agency_name', 'authority_name'],
                'governorate_id' => [Governorate::class, 'name', 'governorate_name'],
                'directorate_id' => [Directorate::class, 'name', 'directorate_name'],
            ],
        ],
    ];

    public function index()
    {
        $entities = [];
        foreach ($this->configs as $key => $cfg) {
            $entities[] = [
                'key' => $key,
                'title' => $cfg['title'],
                'count' => $cfg['model']::count(),
            ];
        }

        return view('configuration.import-export', compact('entities'));
    }

    public function showImport(string $entity)
    {
        abort_unless(isset($this->configs[$entity]), 404);
        $config = $this->configs[$entity];
        $this->authorize('import', $config['model']);

        $authorities = [];
        if ($entity === 'internal-entities') {
            $authorities = Authority::where('is_active', true)->visibleToUser()->orderBy('agency_name')->get();
        }

        return view('configuration.import', [
            'entity' => $entity,
            'title' => $config['title'],
            'config' => $config,
            'authorities' => $authorities,
        ]);
    }

    public function export(Request $request, string $entity)
    {
        abort_unless(isset($this->configs[$entity]), 404);
        $cfg = $this->configs[$entity];
        $this->authorize('export', $cfg['model']);

        $model = $cfg['model'];
        $query = $model::query();
        if (method_exists($model, 'scopeVisibleToUser')) {
            $query->visibleToUser();
        }

        // Use the first unique column if it exists as sort key, fallback to ID
        $sortKey = isset($cfg['unique'][0]) ? $cfg['unique'][0] : 'id';

        // Eager load all configured relations to prevent lazy loading errors
        if (! empty($cfg['relations'])) {
            $relationNames = array_map(
                fn ($col) => str_replace('_id', '', $col),
                array_keys($cfg['relations'])
            );
            $query->with($relationNames);
        }

        $rows = $query->orderBy($sortKey)->get();

        $dataRows = [];
        foreach ($rows as $row) {
            $record = [];
            foreach ($cfg['columns'] as $col) {
                $value = $row->$col;
                // Replace foreign keys with display names if mapping provided
                if (isset($cfg['relations'][$col])) {
                    [$relModel, $relDisplay, $exportHeader] = $cfg['relations'][$col];
                    // Try to resolve via relation if exists
                    $value = method_exists($row, str_replace('_id', '', $col)) && $row->{str_replace('_id', '', $col)}
                        ? $row->{str_replace('_id', '', $col)}->$relDisplay
                        : $value;
                }
                if ($value instanceof Carbon) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                if ($col === 'is_active') {
                    $value = $row->is_active ? 'نشط' : 'غير نشط';
                }
                $record[] = $value;
            }
            $dataRows[] = $record;
        }

        $sheetTitle = $cfg['title'];
        $reportTitle = 'تقرير '.$cfg['title'];
        $headers = $cfg['headers'];

        // If relations exist, swap header labels accordingly
        if (! empty($cfg['relations'])) {
            foreach ($cfg['relations'] as $keyCol => $relCfg) {
                [$relModel, $relDisplay, $exportHeader] = $relCfg;
                // replace in header array
                $idx = array_search($keyCol, $cfg['columns']);
                if ($idx !== false) {
                    $headers[$idx] = $exportHeader;
                }
            }
        }

        $spreadsheet = BaseSpreadsheetExport::build($sheetTitle, $reportTitle, $headers, $dataRows);

        $fileName = $entity.'_'.date('Y-m-d_H-i-s').'.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $entity.'_export_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        // Clear any output buffers to prevent file corruption
        if (ob_get_length()) {
            ob_end_clean();
        }

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function downloadTemplate(Request $request, string $entity)
    {
        abort_unless(isset($this->configs[$entity]), 404);
        $cfg = $this->configs[$entity];
        $this->authorize('import', $cfg['model']);

        // Build headers using column keys, but output as raw keys for import mapping
        // We'll include readable relation names where defined
        $headers = $cfg['columns'];
        if (! empty($cfg['relations'])) {
            foreach ($cfg['relations'] as $keyCol => $relCfg) {
                [$relModel, $relDisplay, $exportHeader] = $relCfg;
                $idx = array_search($keyCol, $headers);
                if ($idx !== false) {
                    $headers[$idx] = str_replace('_id', '_name', $keyCol);
                }
            }
        }

        $sample = [];
        // generate simple sample rows
        $sample[] = array_map(function ($h) {
            return $h === 'is_active' ? 1 : ($h === 'id' ? 1 : 'مثال');
        }, $headers);
        $sample[] = array_map(function ($h) {
            return $h === 'is_active' ? 1 : ($h === 'id' ? 2 : 'مثال 2');
        }, $headers);

        $service = new FileImportService;
        $filePath = $service->createTemplate([
            'headers' => $headers,
            'sample_data' => $sample,
        ], $request->get('format', 'xlsx'));

        // Clear any output buffers to prevent file corruption
        if (ob_get_length()) {
            ob_end_clean();
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request, string $entity)
    {
        abort_unless(isset($this->configs[$entity]), 404);
        $cfg = $this->configs[$entity];
        $this->authorize('import', $cfg['model']);

        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:5120']);

        $file = $request->file('file');
        $fileName = 'import_preview_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('temp', $fileName);

        $service = new FileImportService;
        $fileData = $service->readFile($path, true);

        $headers = $fileData['headers'];
        $rows = array_slice($fileData['data'], 0, 10);

        return response()->json([
            'headers' => $headers,
            'rows' => $rows,
            'filePath' => $path,
        ]);
    }

    public function processImport(Request $request, string $entity)
    {
        abort_unless(isset($this->configs[$entity]), 404);
        $cfg = $this->configs[$entity];
        $this->authorize('import', $cfg['model']);

        $request->validate([
            'file_path' => 'required|string',
            'operation' => 'required|in:insert,update,both',
            'mapping' => 'required|array',
            'authority_id' => 'nullable|exists:authorities,id',
        ]);

        $filePath = $request->input('file_path');
        $operation = $request->input('operation');
        $mapping = $request->input('mapping');

        $service = new FileImportService;
        $fileData = $service->readFile($filePath, true);
        $records = $fileData['data'];

        $modelClass = $cfg['model'];
        $uniqueKeys = $cfg['unique'];

        $trackingService = new ImportTrackingService;
        // Fallback to $entity if title is missing
        $title = $cfg['title'] ?? $entity;
        $importLog = $trackingService->startImport($title, $modelClass, basename($filePath));

        $total = 0;
        $ok = 0;
        $failed = 0;
        $errors = [];

        foreach ($records as $i => $row) {
            $total++;
            $mapped = [];
            foreach ($mapping as $field => $header) {
                if ($header !== null && isset($row[$header])) {
                    $val = $row[$header];
                    if (is_string($val)) {
                        $val = trim($val);
                    }
                    if ($val !== '' && $val !== null) {
                        $mapped[$field] = $val;
                    }
                }
            }

            // Resolve relation names to IDs if defined
            if (! empty($cfg['relations'])) {
                foreach ($cfg['relations'] as $keyCol => $relCfg) {
                    if (isset($mapped[str_replace('_id', '_name', $keyCol)])) {
                        [$relModel, $relDisplay, $exportHeader] = $relCfg;
                        $name = $mapped[str_replace('_id', '_name', $keyCol)];
                        $relRecord = $relModel::where($relDisplay, $name)->first();
                        if (! $relRecord) {
                            $failed++;
                            $errors[$i + 2] = ["القيمة غير موجودة: {$name}"];

                            continue 2; // skip row
                        }
                        $mapped[$keyCol] = $relRecord->id;
                        unset($mapped[str_replace('_id', '_name', $keyCol)]);
                    }
                }
            }

            // Apply default authority if not provided in row
            if ($entity === 'internal-entities' && ! isset($mapped['authority_id']) && $request->filled('authority_id')) {
                $mapped['authority_id'] = $request->input('authority_id');
            }

            // Basic validation: require name if exists
            if (isset($mapped['name']) && $mapped['name'] === '') {
                $failed++;
                $errors[$i + 2] = ['حقل الاسم فارغ'];

                continue;
            }

            // Normalize is_active
            if (isset($mapped['is_active'])) {
                $mapped['is_active'] = (bool) $mapped['is_active'];
            }

            try {
                if ($operation === 'insert') {
                    $newRecord = $modelClass::create($mapped);
                    $trackingService->recordSuccess($importLog, $newRecord, 'created');
                } elseif ($operation === 'update') {
                    $query = $modelClass::query();
                    foreach ($uniqueKeys as $key) {
                        $query->where($key, $mapped[$key] ?? null);
                    }
                    if ($existing = $query->first()) {
                        $existing->update($mapped);
                        $trackingService->recordSuccess($importLog, $existing, 'updated');
                    }
                } else { // both
                    $query = [];
                    foreach ($uniqueKeys as $key) {
                        if (! array_key_exists($key, $mapped)) {
                            throw new \Exception('مفاتيح التمييز غير مكتملة للتحديث');
                        }
                        $query[$key] = $mapped[$key];
                    }
                    $record = $modelClass::updateOrCreate($query, $mapped);
                    $action = $record->wasRecentlyCreated ? 'created' : 'updated';
                    $trackingService->recordSuccess($importLog, $record, $action);
                }
                $ok++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[$i + 2] = [$e->getMessage()];
            }
        }

        $trackingService->finishImport($importLog, $total, $ok, $failed, $errors);

        return response()->json([
            'total' => $total,
            'successful' => $ok,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }
}
