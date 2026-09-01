<?php

namespace App\Imports\PlanImportSheets;

use App\Imports\PlansImport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class GenericPlanSheetImport implements ToCollection, WithHeadingRow
{
    private PlansImport $parent;

    private string $type;

    public function __construct(PlansImport $parent, string $type)
    {
        $this->parent = $parent;
        $this->type = $type;
        HeadingRowFormatter::default('none');
    }

    public function collection(Collection $rows)
    {
        // Convert to array and pass to parent
        $this->parent->addRows($this->type, $rows->toArray());
    }
}
