<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when one or more dropdown values in a project row cannot be resolved
 * to existing database records. Carries a full list of ALL missing values so
 * the caller can collect them before deciding to skip the project entirely.
 */
class MissingDropdownValuesException extends RuntimeException
{
    /**
     * @param  array  $missingValues  Each element:
     *                                [
     *                                'field_key'   => 'program_id',
     *                                'field_label' => 'البرنامج',
     *                                'table_name'  => 'programs',
     *                                'value'       => 'برنامج الأمن الغذائي',
     *                                ]
     */
    public function __construct(private readonly array $missingValues)
    {
        $labels = implode(', ', array_column($missingValues, 'field_label'));
        parent::__construct("قيم غير موجودة في النظام: {$labels}");
    }

    public function getMissingValues(): array
    {
        return $this->missingValues;
    }
}
