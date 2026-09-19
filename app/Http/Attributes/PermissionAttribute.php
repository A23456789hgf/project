<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PermissionAttribute
{
    /**
     * Create a new permission attribute instance.
     *
     * @param  string  $slug  The unique permission slug (e.g., 'projects.view')
     * @param  string  $name  The Arabic name of the permission
     * @param  string  $description  The Arabic description of the permission
     * @param  string  $module  The module this permission belongs to
     * @param  string  $operation  The operation type (view, create, edit, delete, etc.)
     */
    public function __construct(
        public string $slug,
        public string $name,
        public string $description,
        public string $module,
        public string $operation = 'view'
    ) {}

    /**
     * Convert the attribute to an array.
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'module' => $this->module,
            'operation' => $this->operation,
        ];
    }
}
