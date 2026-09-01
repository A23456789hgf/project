<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class ValueChainsPermissionsSeeder extends Seeder
{
    public function run()
    {
        $config = config('permissions.value_chains', []);

        foreach ($config as $p) {
            $slug = $p['slug'];
            $name = $p['name'] ?? $slug;
            $type = $p['type'] ?? 'action';

            // Primary slug (as defined)
            Permission::updateOrCreate([
                'slug' => $slug,
            ], [
                'name' => $name,
                'module' => explode('.', $slug)[0] ?? 'value_chains',
                'type' => $type,
                'auto_registered' => false,
            ]);

            // Also create a hyphenated module variant if module uses underscores (for compatibility with views)
            $parts = explode('.', $slug, 2);
            if (count($parts) === 2) {
                $module = $parts[0];
                if (str_contains($module, '_')) {
                    $hyphenModule = str_replace('_', '-', $module);
                    $altSlug = $hyphenModule.'.'.$parts[1];
                    // Only create if different
                    if ($altSlug !== $slug) {
                        Permission::updateOrCreate([
                            'slug' => $altSlug,
                        ], [
                            'name' => $name,
                            'module' => $hyphenModule,
                            'type' => $type,
                            'auto_registered' => false,
                        ]);
                    }
                }
            }
        }
    }
}
