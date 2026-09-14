<?php

namespace App\Exports;

use App\Models\FundedEntity;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FundedEntitiesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return Collection
     */
    public function collection()
    {
        return FundedEntity::with(['fundingSource'])->get();
    }

    public function headings(): array
    {
        return [
            'الرقم',
            'مصدر التمويل',
            'الجهة',
            'تاريخ الإنشاء',
            'تاريخ التحديث',
        ];
    }

    /**
     * @param  mixed  $fundedEntity
     */
    public function map($fundedEntity): array
    {
        return [
            $fundedEntity->id,
            $fundedEntity->fundingSource->name ?? 'غير محدد',
            $fundedEntity->entity ?? 'غير محدد',
            $fundedEntity->created_at->format('Y-m-d H:i:s'),
            $fundedEntity->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
