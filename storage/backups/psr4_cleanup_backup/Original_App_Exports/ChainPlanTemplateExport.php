<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ChainPlanTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'صنعاء',
                'صنعاء القديمة',
                'سلسلة البن',
                'الزراعة',
                'اسم المشروع هنا',
                'اسم النشاط هنا',
                'مساحة مزروعة',
                '100',
                'منحة',
                'الوزارة',
                'مؤسسة أ',
                'مؤسسة ب',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'المحافظة',
            'المديرية',
            'السلسلة',
            'المجال',
            'اسم المشروع',
            'اسم النشاط',
            'المؤشر',
            'العدد',
            'نوع التمويل',
            'جهة التمويل',
            'الجهة المنفذة',
            'الجهة المشرفة',
        ];
    }
}
