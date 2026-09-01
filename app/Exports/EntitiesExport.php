<?php

namespace App\Exports;

use App\Models\Entity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EntitiesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Entity::with('father')->get()->map(function ($entity) {
            return [
                'الاسم' => $entity->name,
                'الجهة الأب' => $entity->father?->name ?? $entity->father?->entity_father ?? '-',
                'الحالة' => $entity->is_active ? 'نشط' : 'غير نشط',
            ];
        });
    }

    public function headings(): array
    {
        return ['الاسم', 'الجهة الأب', 'الحالة'];
    }
}
