<?php

namespace App\Services;

use App\Attributes\PermissionAttribute;
use Illuminate\Support\Facades\Route;
use ReflectionClass;

class PermissionDiscoveryService
{
    protected PermissionInferenceRules $inferenceRules;

    public function __construct(PermissionInferenceRules $inferenceRules)
    {
        $this->inferenceRules = $inferenceRules;
    }

    /**
     * Scan all routes and discover permissions.
     */
    public function discoverPermissions(): array
    {
        $routes = Route::getRoutes();
        $discovered = [];

        foreach ($routes as $route) {
            // Skip excluded routes
            if ($this->inferenceRules->shouldExcludeRoute($route)) {
                continue;
            }

            $permission = $this->extractPermissionFromRoute($route);

            if ($permission) {
                $slug = $permission['slug'];

                // Avoid duplicates (first one wins)
                if (! isset($discovered[$slug])) {
                    $discovered[$slug] = $permission;
                }
            }
        }

        return array_values($discovered);
    }

    /**
     * Extract permission metadata from a route.
     *
     * @param  \Illuminate\Routing\Route  $route
     */
    protected function extractPermissionFromRoute($route): ?array
    {
        // First, check if controller method has PermissionAttribute
        $attributePermission = $this->getPermissionFromAttribute($route);

        if ($attributePermission) {
            return $attributePermission;
        }

        // Fall back to inference
        return $this->inferPermissionFromRoute($route);
    }

    /**
     * Get permission from controller method attribute.
     *
     * @param  \Illuminate\Routing\Route  $route
     */
    protected function getPermissionFromAttribute($route): ?array
    {
        $controllerInfo = $this->inferenceRules->extractControllerInfo($route);

        if (! $controllerInfo['class'] || ! $controllerInfo['method']) {
            return null;
        }

        try {
            $reflection = new ReflectionClass($controllerInfo['class']);

            if (! $reflection->hasMethod($controllerInfo['method'])) {
                return null;
            }

            $method = $reflection->getMethod($controllerInfo['method']);
            $attributes = $method->getAttributes(PermissionAttribute::class);

            if (empty($attributes)) {
                return null;
            }

            $attribute = $attributes[0]->newInstance();

            return [
                'slug' => $attribute->slug,
                'name' => $attribute->name,
                'description' => $attribute->description,
                'module' => $attribute->module,
                'operation' => $attribute->operation,
                'controller_class' => $controllerInfo['class'],
                'controller_method' => $controllerInfo['method'],
                'auto_registered' => true,
                'source' => 'attribute',
            ];
        } catch (\Exception $e) {
            // If reflection fails, fall back to inference
            return null;
        }
    }

    /**
     * Infer permission from route using rules.
     *
     * @param  \Illuminate\Routing\Route  $route
     */
    protected function inferPermissionFromRoute($route): ?array
    {
        $slug = $this->inferenceRules->generateSlug($route);
        $module = $this->inferenceRules->inferModule($route);
        $operation = $this->inferenceRules->inferOperation($route);
        $controllerInfo = $this->inferenceRules->extractControllerInfo($route);

        return [
            'slug' => $slug,
            'name' => $this->inferenceRules->generateArabicName($module, $operation),
            'description' => $this->inferenceRules->generateArabicDescription($module, $operation),
            'module' => $module,
            'operation' => $operation,
            'controller_class' => $controllerInfo['class'],
            'controller_method' => $controllerInfo['method'],
            'auto_registered' => true,
            'source' => 'inference',
        ];
    }

    /**
     * Get permissions for a specific module.
     */
    public function discoverPermissionsForModule(string $module): array
    {
        $allPermissions = $this->discoverPermissions();

        return array_filter($allPermissions, function ($permission) use ($module) {
            return $permission['module'] === $module;
        });
    }

    /**
     * Get permissions for a specific controller.
     */
    public function discoverPermissionsForController(string $controllerClass): array
    {
        $allPermissions = $this->discoverPermissions();

        return array_filter($allPermissions, function ($permission) use ($controllerClass) {
            return $permission['controller_class'] === $controllerClass;
        });
    }

    /**
     * Get statistics about discovered permissions.
     */
    public function getDiscoveryStatistics(): array
    {
        $permissions = $this->discoverPermissions();

        $stats = [
            'total' => count($permissions),
            'by_source' => [
                'attribute' => 0,
                'inference' => 0,
            ],
            'by_module' => [],
            'by_operation' => [],
        ];

        foreach ($permissions as $permission) {
            // Count by source
            $stats['by_source'][$permission['source']]++;

            // Count by module
            $module = $permission['module'];
            $stats['by_module'][$module] = ($stats['by_module'][$module] ?? 0) + 1;

            // Count by operation
            $operation = $permission['operation'];
            $stats['by_operation'][$operation] = ($stats['by_operation'][$operation] ?? 0) + 1;
        }

        // Sort modules by count
        arsort($stats['by_module']);
        arsort($stats['by_operation']);

        return $stats;
    }

    /**
     * Check if auto-discovery is enabled.
     */
    public function isEnabled(): bool
    {
        return config('permissions.auto_discovery.enabled', true);
    }
}
