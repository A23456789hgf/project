<?php

namespace App\Exports;

use App\Models\EntityFather;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EntityFathersExport implements FromCollection, WithHeadings
{
    /**
     * @return Collection
     */
    public function collection()
    {
        return EntityFather::all('entity_father');
    }

    public function headings(): array
    {
        return [
            'Entity Father', // Column heading in Excel
        ];
    }
}
