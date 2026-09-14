<?php

namespace App\Imports;

use App\Models\ChainPlan;
use App\Models\Directorate;
use App\Models\Governorate;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class ChainPlanImport implements OnEachRow, WithStartRow
{
    /**
     * قائمة الحقول المسموح باستيرادها (تستخدم للتحقق).
     */
    public const ALL_FIELDS = [
        'governorate_id',
        'directorate_id',
        'value_chain_id',
        'domain_id',
        'project_name',
        'activity_name',
        'indicator',
        'number',
        'value_chain_financing_type_id',
        'funding_source_id',
        'authority_id',
        'implementing_entity_id',
    ];

    /**
     * خريطة تربط اسم الحقل بالمفتاح الذي سيظهر في المعاينة (اختياري).
     */
    public const PREVIEW_KEYS = [
        'governorate_id' => 'governorate',
        'directorate_id' => 'directorate',
        'value_chain_id' => 'value_chain',
        'domain_id' => 'domain',
        'project_name' => 'project_name',
        'activity_name' => 'activity_name',
        'indicator' => 'indicator',
        'number' => 'number',
        'value_chain_financing_type_id' => 'financing_type',
        'funding_source_id' => 'funding_source',
        'authority_id' => 'authority',
        'implementing_entity_id' => 'implementing_entity',
    ];

    /**
     * مصفوفة المطابقة النهائية: [field => column_index].
     * يتم بناؤها من المطابقة التي يمررها المستخدم.
     */
    protected array $columnMap = [];

    /**
     * قائمة الحقول الفعلية التي سيتم استيرادها (المطابقة غير null).
     */
    protected array $fields = [];

    public $trackingService;

    public $importLog;

    public $importedCount = 0;

    public $rowCount = 0;

    /**
     * المُنشئ يستقبل مصفوفة المطابقة من المتحكم.
     * المصفوفة تكون على شكل: [0 => 'governorate_id', 1 => null, 2 => 'project_name', ...]
     */
    public function __construct(array $mapping, $trackingService = null, $importLog = null)
    {
        $this->trackingService = $trackingService;
        $this->importLog = $importLog;
        // فلترة القيم غير الفارغة (غير null) وبناء columnMap و fields
        foreach ($mapping as $columnIndex => $fieldName) {
            if ($fieldName !== null && in_array($fieldName, self::ALL_FIELDS, true)) {
                $this->columnMap[$fieldName] = (int) $columnIndex;
                $this->fields[] = $fieldName;
            }
        }
    }

    /**
     * تحديد صف البداية (تجاهل صف الرؤوس).
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * تحويل كل صف من الملف إلى نموذج ChainPlan.
     */
    public function onRow(Row $excelRow)
    {
        $row = $excelRow->toArray();
        $this->rowCount++;
        $data = [];

        foreach ($this->fields as $field) {
            // الحصول على قيمة العمود بناءً على المطابقة
            $value = $this->getValueFromRow($row, $field);

            // معالجة الحقول الخاصة (المفاتيح الأجنبية)
            if ($field === 'governorate_id') {
                $data[$field] = $this->resolveGovernorateId($value);

                continue;
            }

            if ($field === 'directorate_id') {
                $data[$field] = $this->resolveDirectorateId($value);

                continue;
            }

            // باقي الح fields توضع كما هي
            $data[$field] = $value;
        }

        // إنشاء النموذج فقط إذا كان هناك بيانات
        if (! empty($data)) {
            $chainPlan = ChainPlan::create($data);
            if ($this->trackingService && $this->importLog) {
                $this->trackingService->recordSuccess($this->importLog, $chainPlan, 'created');
            }
            $this->importedCount++;
        }
    }

    /**
     * استرجاع قيمة من الصف بناءً على اسم الحقل.
     */
    private function getValueFromRow(array $row, string $field): ?string
    {
        if (! isset($this->columnMap[$field])) {
            return null;
        }

        $index = $this->columnMap[$field];
        $value = $row[$index] ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * تحويل اسم المحافظة إلى ID.
     */
    private function resolveGovernorateId(?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        return Governorate::where('name', $name)->value('id');
    }

    /**
     * تحويل اسم المديرية إلى ID.
     */
    private function resolveDirectorateId(?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        return Directorate::where('name', $name)->value('id');
    }

    /**
     * (اختياري) دالة لإعداد بيانات المعاينة – يمكن استدعاؤها من المتحكم إن أردت.
     * ولكن المتحكم الحالي يستخدم معاينة مباشرة من Excel، لذا قد لا تحتاجها.
     */
    public function previewRow(array $row, int $rowNumber): array
    {
        $preview = ['row_number' => $rowNumber];

        foreach ($this->fields as $field) {
            $previewKey = self::PREVIEW_KEYS[$field] ?? $field;
            $preview[$previewKey] = $this->getValueFromRow($row, $field) ?? '-';
        }

        return $preview;
    }
}
