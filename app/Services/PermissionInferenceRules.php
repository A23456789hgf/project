<?php

namespace App\Services;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class PermissionInferenceRules
{
    /**
     * Infer permission operation from route.
     *
     * @param  Route  $route
     */
    public function inferOperation($route): string
    {
        $uri = $route->uri();
        $name = $route->getName() ?? '';
        $methods = $route->methods();

        // Check route patterns first (more specific)
        $patterns = config('permissions.inference_rules.route_patterns', []);
        foreach ($patterns as $pattern => $operation) {
            if (preg_match($pattern, $uri) || preg_match($pattern, $name)) {
                return $operation;
            }
        }

        // Check route name parts
        if ($name) {
            $parts = explode('.', $name);
            $lastPart = end($parts);

            // Common operation keywords in route names
            $operationMap = config('permissions.operation_translations', []);
            if (isset($operationMap[$lastPart])) {
                return $lastPart;
            }
        }

        // Fall back to HTTP method mapping
        $httpMethods = config('permissions.inference_rules.http_methods', []);
        $method = $methods[0] ?? 'GET';

        return $httpMethods[$method] ?? 'view';
    }

    /**
     * Infer module name from route.
     *
     * @param  Route  $route
     */
    public function inferModule($route): string
    {
        $name = $route->getName();

        if ($name && str_contains($name, '.')) {
            // Extract module from route name (e.g., 'projects.view' -> 'projects')
            $parts = explode('.', $name);

            return $parts[0];
        }

        // Fall back to URI prefix
        $uri = trim($route->uri(), '/');
        $segments = explode('/', $uri);

        return $segments[0] ?? 'general';
    }

    /**
     * Generate permission slug from route.
     *
     * @param  Route  $route
     */
    public function generateSlug($route): string
    {
        $name = $route->getName();

        if ($name) {
            // Use route name as base for slug
            return $name;
        }

        // Generate from URI and method
        $module = $this->inferModule($route);
        $operation = $this->inferOperation($route);

        return "{$module}.{$operation}";
    }

    /**
     * Generate Arabic name for permission.
     */
    public function generateArabicName(string $module, string $operation): string
    {
        $moduleTranslation = config("permissions.module_translations.{$module}", $module);
        $operationTranslation = config("permissions.operation_translations.{$operation}", $operation);

        // Special case for sidebar permissions
        if ($operation === 'sidebar') {
            return $operationTranslation;
        }

        return "{$operationTranslation} {$moduleTranslation}";
    }

    /**
     * Generate Arabic description for permission.
     */
    public function generateArabicDescription(string $module, string $operation): string
    {
        $moduleTranslation = config("permissions.module_translations.{$module}", $module);
        $operationTranslation = config("permissions.operation_translations.{$operation}", $operation);

        $descriptionMap = [
            'view' => "عرض وتصفح {$moduleTranslation}",
            'create' => "إنشاء {$moduleTranslation} جديدة",
            'edit' => "تعديل وتحديث {$moduleTranslation}",
            'delete' => "حذف {$moduleTranslation}",
            'show' => "عرض تفاصيل {$moduleTranslation}",
            'export' => "تصدير {$moduleTranslation} إلى ملفات خارجية",
            'print' => "طباعة {$moduleTranslation}",
            'import' => "استيراد {$moduleTranslation} من ملفات خارجية",
            'approve' => "الموافقة على {$moduleTranslation}",
            'reject' => "رفض {$moduleTranslation}",
            'sidebar' => "إظهار {$moduleTranslation} في القائمة الجانبية",
            'manage' => "إدارة {$moduleTranslation}",
        ];

        return $descriptionMap[$operation] ?? "{$operationTranslation} {$moduleTranslation}";
    }

    /**
     * Categorize permission type (sidebar, page, or action).
     */
    public function categorizePermission(string $slug): string
    {
        if (str_contains($slug, 'sidebar')) {
            return 'sidebar';
        }

        if (str_contains($slug, 'view') || str_contains($slug, 'index')) {
            return 'page';
        }

        return 'action';
    }

    /**
     * Check if route should be excluded from permission discovery.
     *
     * @param  Route  $route
     */
    public function shouldExcludeRoute($route): bool
    {
        $name = $route->getName();

        if (! $name) {
            return true; // Exclude unnamed routes
        }

        $excludedPatterns = config('permissions.excluded_routes', []);

        foreach ($excludedPatterns as $pattern) {
            if (Str::is($pattern, $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract controller class and method from route.
     *
     * @param  Route  $route
     * @return array{class: string|null, method: string|null}
     */
    public function extractControllerInfo($route): array
    {
        $action = $route->getAction();

        if (isset($action['controller'])) {
            $controller = $action['controller'];

            if (is_string($controller) && str_contains($controller, '@')) {
                [$class, $method] = explode('@', $controller);

                return ['class' => $class, 'method' => $method];
            }

            if (is_string($controller)) {
                // Invokable controller
                return ['class' => $controller, 'method' => '__invoke'];
            }
        }

        return ['class' => null, 'method' => null];
    }
}
