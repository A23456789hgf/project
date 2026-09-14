<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditLogExport implements FromCollection, WithHeadings, WithMapping
{
    protected $logs;

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            'المستخدم',
            'الجهة',
            'الإجراء',
            'القسم (Module)',
            'الوصف',
            'رابط الصفحة (URL)',
            'عنوان IP',
            'التاريخ والوقت',
        ];
    }

    public function map($log): array
    {
        return [
            $log->user_name ?? ($log->user->name ?? 'النظام'),
            $log->entity_name ?? '-',
            $log->translated_action,
            $log->translated_module,
            $log->translated_description,
            $log->url,
            $log->ip_address,
            $log->arabic_date,
        ];
    }
}
