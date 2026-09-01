<?php

namespace App\Traits;

trait MapsImportHeadings
{
    /**
     * Map Arabic headings (slugified) to English keys.
     */
    public function mapArabicHeadings(array $row): array
    {
        $mapping = [
            // Project Info
            'رقم المشروع' => 'form_number',
            'اسم المشروع' => 'project_name',
            'نوع المشروع' => 'project_type',
            'اسم الجهة المنشئة' => 'created_by_entity',
            'إجمالي التكلفة' => 'total_cost',
            'المبلغ المصروف' => 'spent_amount',
            'المبلغ المتبقي' => 'remaining_amount',
            'السنة الهجرية' => 'hijri_year',
            'البرنامج' => 'program_id',
            'المجال' => 'domain_id',
            'المجال الفرعي' => 'subdomain_id',
            'التدخل' => 'intervention_id',
            'الأولوية' => 'priority_id',
            'حالة المشروع' => 'status',
            'حالة الاعتماد' => 'approval_status',
            'رقم الاستمارة' => 'form_number',
            'تاريخ البداية (م)' => 'start_date_gregorian',
            'تاريخ البداية (هـ)' => 'start_date_hijri',
            'تاريخ النهاية (م)' => 'end_date_gregorian',
            'تاريخ النهاية (هـ)' => 'end_date_hijri',
            'عدد المستفيدين' => 'number_of_beneficiaries',
            'التوجهات الرئيسية' => 'main_directives',
            'التوجهات الفرعية' => 'subdirectives',
            'الفئات المستهدفة' => 'target_categories',
            'فئة المستفيد' => 'target_category_id',

            // Project Details
            'ضمن الخطة' => 'is_part_of_plan',
            'ملخص المشروع' => 'project_summary',
            'مقدمة المشروع' => 'project_introduction',
            'المبرر والمشكلة' => 'problem_and_justification',
            'مكونات المشروع' => 'project_components',
            'الأثر المتوقع' => 'expected_impact',

            // Locations
            'المحافظة' => 'governorate_id',
            'المديرية' => 'directorate_id',
            'العزلة' => 'sub_area_id',
            'القرية' => 'village_id',

            // Objectives
            'الهدف الرئيسي' => 'objective',
            'الهدف الخاص' => 'objective',
            'وزن الهدف' => 'objective_weight',
            'القيمة المستهدفة' => 'target_value',
            'وحدة القياس' => 'measurement_unit',

            // Results & Outputs
            'النتيجة' => 'result_name',
            'نوع المؤشر' => 'indicator_type',
            'وحدة المؤشر' => 'indicator_unit',
            'المخرج' => 'output',

            // Preliminary Activities
            'النشاط' => 'activity_name',
            'وزن النشاط' => 'activity_weight',
            'الإجراء' => 'procedure_name',
            'البند المالي' => 'financial_item_id',
            'المبلغ' => 'cost_amount',

            // Executive Activities
            'النشاط التنفيذي' => 'activity_name',
            'الإجراء التنفيذي' => 'action',
            'وزن الإجراء' => 'action_weight',
            'تاريخ البداية' => 'start_date',
            'تاريخ النهاية' => 'end_date',
            'وسيلة التحقق' => 'verification_means',
            'الجهة المسؤولة' => 'assigned_entity_id',
            'التكلفة' => 'cost_amount',

            // Financings
            'مصدر التمويل' => 'funding_source_id',
            'الجهة' => 'authority_id',
            'نوع التمويل' => 'financing_type_id',
            'شكل التمويل' => 'financing_form_id',
            'شكل التمويل الفرعي' => 'sub_financing_form_id',
            'مبلغ التمويل' => 'financing_amount',

            // Entities
            'دور الجهة' => 'entity_role',
            'نوع الجهة' => 'authority_type',
            'المرجع' => 'parent_id',

            // Arabic entity role values (accepted in the entity_role column)
            'مشرفة' => 'entity_role_supervising',
            'منفذة' => 'entity_role_implementing',
            'مشاركة' => 'entity_role_participating',
            'مستفيدة' => 'entity_role_beneficiary',
        ];

        $mappedRow = [];
        foreach ($row as $key => $value) {
            $key = trim((string) $key);
            if (is_string($value)) {
                $value = trim($value);
            }

            // Try exact match first
            if (isset($mapping[$key])) {
                $targetKey = $mapping[$key];
                if (! isset($mappedRow[$targetKey]) || ($mappedRow[$targetKey] === null || trim((string) $mappedRow[$targetKey]) === '')) {
                    $mappedRow[$targetKey] = $value;
                } elseif ($value !== null && trim((string) $value) !== '') {
                    $mappedRow[$targetKey] = $value;
                }

                continue;
            }

            // Try matching normalized/slugified key
            $found = false;
            foreach ($mapping as $arabic => $english) {
                // Simplified check: if English key is already present in row or if slugified Arabic matches
                if ($key === $english) {
                    if (! isset($mappedRow[$english]) || ($mappedRow[$english] === null || trim((string) $mappedRow[$english]) === '')) {
                        $mappedRow[$english] = $value;
                    } elseif ($value !== null && trim((string) $value) !== '') {
                        $mappedRow[$english] = $value;
                    }
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                // Keep original key if no mapping found
                if (! isset($mappedRow[$key]) || ($mappedRow[$key] === null || trim((string) $mappedRow[$key]) === '')) {
                    $mappedRow[$key] = $value;
                } elseif ($value !== null && trim((string) $value) !== '') {
                    $mappedRow[$key] = $value;
                }
            }
        }

        return $mappedRow;
    }
}
