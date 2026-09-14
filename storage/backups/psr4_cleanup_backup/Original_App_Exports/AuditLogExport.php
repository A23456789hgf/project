<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditLogExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
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
