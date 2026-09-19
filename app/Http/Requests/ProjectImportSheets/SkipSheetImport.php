<?php

namespace App\Imports\ProjectImportSheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SkipSheetImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Intentionally skip — this is the lookup reference sheet
    }
}
