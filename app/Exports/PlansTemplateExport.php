<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class PlansTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new PlansTemplateSheetExport('الخطط', ['رقم الخطة', 'الأولوية', 'الجهة المقدمة']),
            new PlansTemplateSheetExport('المشاريع', ['رقم الخطة', 'اسم المشروع', 'الأهمية', 'حالة المشروع', 'خط الأساس', 'نوع التكلفة', 'التكلفة', 'توفر التمويل', 'مصدر التمويل', 'الجهة المشاركة']),
            new PlansTemplateSheetExport('الأهداف', ['اسم المشروع', 'الهدف المحدد', 'الوزن %', 'قيمة المؤشر', 'وحدة القياس']),
            new PlansTemplateSheetExport('الأنشطة', ['اسم المشروع', 'اسم النشاط', 'وزن النشاط']),
            new PlansTemplateSheetExport('الإجراءات', ['اسم النشاط', 'اسم الإجراء', 'وزن الإجراء', 'تاريخ البداية', 'تاريخ النهاية', 'المدة']),
        ];
    }
}

class PlansTemplateSheetExport implements WithHeadings, WithTitle
{
    private $title;

    private $headings;

    public function __construct(string $title, array $headings)
    {
        $this->title = $title;
        $this->headings = $headings;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
