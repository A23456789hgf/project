<?php

namespace App\Imports;

use App\Models\Entity;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EntitiesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // افترض أن الأعمدة هي 'name', 'father_id', 'is_active'
        return new Entity([
            'name' => $row['name'],
            'entity_father_id' => $row['father_id'] ?? null,
            'is_active' => isset($row['is_active']) ? ($row['is_active'] == 'نشط') : true,
        ]);
    }
}
