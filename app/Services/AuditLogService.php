<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log a general action.
     */
    public static function log(string $action, ?string $module = null, ?string $description = null, ?string $modelType = null, $modelId = null, ?array $oldValues = null, ?array $newValues = null)
    {
        $user = Auth::user();

        static $userEntityNames = [];
        $userId = $user?->id ?? 0;
        if ($userId && ! array_key_exists($userId, $userEntityNames)) {
            $userEntityNames[$userId] = $user->internalEntity?->name;
        }
        $entityName = $userId ? $userEntityNames[$userId] : null;

        try {
            AuditLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? $user?->username ?? 'Guest',
                'entity_name' => $entityName, // Capture user's entity name correctly
                'action' => $action,
                'module' => $module ?? self::guessModuleFromUrl(),
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'model_type' => $modelType,
                'model_id' => $modelId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'description' => $description,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Never let audit logging failure crash the main request
            \Log::error('Audit Log Failure: '.$e->getMessage());
        }
    }

    /**
     * Guess the module name from the current URL.
     */
    private static function guessModuleFromUrl(): ?string
    {
        $path = Request::path();
        $segments = explode('/', $path);

        // Logical guessing based on URL structure
        if (isset($segments[0])) {
            return match ($segments[0]) {
                'projects' => 'Projects',
                'correspondence' => 'Correspondence',
                'users' => 'User Management',
                'roles' => 'Permissions',
                'entities' => 'Settings',
                'admin' => 'Administration',
                default => ucfirst($segments[0])
            };
        }

        return 'General';
    }
}
