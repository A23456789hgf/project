<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectInfoSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'بيانات المشروع';
    }

    public function headings(): array
    {
        return [
            'رقم المشروع',
            'اسم المشروع',
            'نوع المشروع',
            'البرنامج',
            'المجال',
            'المجال الفرعي',
            'التدخل',
            'الأولوية',
            'حالة المشروع',
            'حالة الاعتماد',
            'رقم الاستمارة',
            'تاريخ البداية (م)',
            'تاريخ البداية (هـ)',
            'تاريخ النهاية (م)',
            'تاريخ النهاية (هـ)',
            'عدد المستفيدين',
            'إجمالي التكلفة',
            'المبلغ المصروف',
            'المبلغ المتبقي',
            'السنة الهجرية',
            'اسم الجهة المنشئة',
            'التوجهات الرئيسية',
            'التوجهات الفرعية',
            'الفئات المستهدفة',
            'فئة المستفيد',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            return $this->projects->map(function ($project) {
                return [
                    $project->form_number,
                    $project->project_name,
                    $project->project_type,
                    $project->program_id,
                    $project->domain_id,
                    $project->subdomain_id,
                    $project->intervention_id,
                    $project->priority_id,
                    $project->status,
                    $project->approval_status,
                    $project->form_number,
                    $project->start_date_gregorian,
                    $project->start_date_hijri,
                    $project->end_date_gregorian,
                    $project->end_date_hijri,
                    $project->number_of_beneficiaries,
                    $project->cost->total_cost ?? '',
                    $project->cost->spent_amount ?? '',
                    $project->cost->remaining_amount ?? '',
                    $project->cost->hijri_year ?? '',
                    $project->creatorEntityName,
                    $project->main_directives,
                    $project->subdirectives,
                    $project->target_categories,
                    $project->target_category_id,
                ];
            })->toArray();
        }

        return [
            [
                'PRO14470001',        // رقم المشروع (اختياري)
                'مشروع تجريبي',       // اسم المشروع
                'new',                 // نوع المشروع
                '1',                   // البرنامج
                '1',                   // المجال
                '1',                   // المجال الفرعي
                '1',                   // التدخل
                '1',                   // الأولوية
                'draft',               // حالة المشروع
                'pending',             // حالة الاعتماد
                '',                    // رقم الاستمارة
                '2026-01-01',          // تاريخ البداية (م)
                '1447-07-01',          // تاريخ البداية (هـ)
                '2026-12-31',          // تاريخ النهاية (م)
                '1448-06-30',          // تاريخ النهاية (هـ)
                '500',                 // عدد المستفيدين
                '100000',              // إجمالي التكلفة
                '20000',               // المبلغ المصروف
                '80000',               // المبلغ المتبقي
                '1445',                // السنة الهجرية
                'الجهة المانحة',         // اسم الجهة المنشئة
                '',                    // التوجهات الرئيسية
                '',                    // التوجهات الفرعية
                '',                    // الفئات المستهدفة
                '',                    // فئة المستفيد
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F4E79'],
                ],
            ],
        ];
    }
}
