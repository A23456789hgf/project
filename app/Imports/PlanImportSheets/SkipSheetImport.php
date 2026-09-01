<?php

namespace App\Imports\PlanImportSheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SkipSheetImport implements ToCollection
{
    public function collection(Collection $collection)
    {
        // Do nothing, skip this sheet
    }
}
