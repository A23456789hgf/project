<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectsFixedStructureExport implements WithMultipleSheets
{
    protected $projects;

    public function __construct(Collection $projects)
    {
        $this->projects = $projects;
    }

    /**
     * Create a sheet for each project
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->projects as $index => $project) {
            $sheetTitle = $this->sanitizeSheetTitle($project->form_number ?? 'Project_'.($index + 1));
            $sheets[] = new ProjectFixedStructureSheet($project, $sheetTitle);
        }

        return $sheets;
    }

    /**
     * Sanitize sheet title to comply with Excel naming rules
     * - Max 31 characters
     * - No special characters: : \ / ? * [ ]
     */
    private function sanitizeSheetTitle($title)
    {
        // Remove invalid characters
        $title = str_replace([':', '\\', '/', '?', '*', '[', ']'], '_', $title);

        // Limit to 31 characters
        if (mb_strlen($title) > 31) {
            $title = mb_substr($title, 0, 31);
        }

        return $title;
    }
}
