<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Support\Permissions\PermissionMatrixRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PermissionsMatrixSync extends Command
{
    protected $signature = 'permissions:matrix-sync {--write-matrix : Update _permissions_matrix.blade.php registry block} {--sync-db : Upsert permissions DB table from matrix} {--scan : Scan codebase for permission usage and add missing to matrix}';

    protected $description = 'Audit and sync permissions using _permissions_matrix.blade.php as source of truth';

    public function handle(): int
    {
        PermissionMatrixRegistry::clearCache();
        $matrixPath = base_path(PermissionMatrixRegistry::REGISTRY_PATH);
        if (! File::exists($matrixPath)) {
            $this->error("Matrix file not found at: {$matrixPath}");

            return self::FAILURE;
        }

        $registry = PermissionMatrixRegistry::read();
        $matrixSlugs = collect($registry['permissions'])->pluck('slug')->filter()->values()->all();

        $foundSlugs = [];
        if ($this->option('scan')) {
            $foundSlugs = $this->scanForPermissionSlugs();
            $missing = array_values(array_diff($foundSlugs, $matrixSlugs));

            if (! empty($missing)) {
                $this->warn('Missing permissions in matrix registry (will add if --write-matrix):');
                foreach ($missing as $s) {
                    $this->line(" - {$s}");
                }

                if ($this->option('write-matrix')) {
                    $added = $this->writeMatrixRegistry(array_merge($matrixSlugs, $missing));
                    $this->info("Matrix updated. Added: {$added}");

                    // refresh
                    PermissionMatrixRegistry::clearCache();
                    $registry = PermissionMatrixRegistry::read();
                    $matrixSlugs = collect($registry['permissions'])->pluck('slug')->filter()->values()->all();
                }
            } else {
                $this->info('No missing permissions found in scan.');
            }
        }

        if ($this->option('sync-db')) {
            $count = $this->syncDbFromMatrix($registry['permissions'] ?? []);
            $this->info("DB synced from matrix. Upserted: {$count}");
        }

        $this->outputReport($matrixSlugs, $foundSlugs);

        return self::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function scanForPermissionSlugs(): array
    {
        $paths = [
            base_path('routes'),
            base_path('app'),
            base_path('resources/views'),
            base_path('resources/js'),
        ];

        $patterns = [
            // Routes
            '/->middleware\\(\\s*[\'"]can:([^\'"]+)[\'"]\\s*\\)/',
            '/middleware\\(\\s*\\[\\s*[\'"]can:([^\'"]+)[\'"]\\s*\\]\\s*\\)/',
            // Blade directives
            '/@hasPermission\\(\\s*[\'"]([^\'"]+)[\'"]\\s*\\)/',
            '/@permission\\(\\s*[\'"]([^\'"]+)[\'"]\\s*\\)/',
            // PHP calls
            '/hasPermission\\(\\s*[\'"]([^\'"]+)[\'"]/',
            '/Gate::allows\\(\\s*[\'"]([^\'"]+)[\'"]/',
            '/Gate::authorize\\(\\s*[\'"]([^\'"]+)[\'"]/',
            '/\\$this->authorize\\(\\s*[\'"]([^\'"]+)[\'"]/',
        ];

        $slugs = [];
        foreach ($paths as $path) {
            if (! File::exists($path)) {
                continue;
            }
            foreach (File::allFiles($path) as $file) {
                // Ignore backup/duplicate controllers directory
                if (str_contains(str_replace('\\', '/', $file->getPathname()), 'app/Controllers/')) {
                    continue;
                }

                $ext = strtolower($file->getExtension());
                if (! in_array($ext, ['php', 'blade.php', 'js', 'ts', 'vue'], true)) {
                    continue;
                }
                $contents = File::get($file->getPathname());
                foreach ($patterns as $re) {
                    if (preg_match_all($re, $contents, $m)) {
                        foreach ($m[1] as $raw) {
                            $slug = trim((string) $raw);
                            if ($slug === '') {
                                continue;
                            }
                            // Strip parameters from middleware (e.g. 'can:resume,project' -> 'resume')
                            if (str_contains($slug, ',')) {
                                $slug = explode(',', $slug)[0];
                            }
                            // normalize accidental spaces
                            $slug = preg_replace('/\\s+/', '', $slug);

                            // Only accept structured module.action permissions (must have a dot)
                            // Skip dynamic PHP expressions or parameters (containing $, {, })
                            if (! str_contains($slug, '.') || str_contains($slug, '$') || str_contains($slug, '{') || str_contains($slug, '}')) {
                                continue;
                            }

                            $slugs[$slug] = true;
                        }
                    }
                }
            }
        }

        $out = array_keys($slugs);
        sort($out);

        return $out;
    }

    /**
     * @param  string[]  $slugs
     */
    private function writeMatrixRegistry(array $slugs): int
    {
        $slugs = array_values(array_unique(array_filter(array_map('trim', $slugs))));
        sort($slugs);

        $permissions = [];
        foreach ($slugs as $slug) {
            $permissions[] = [
                'slug' => $slug,
                'module' => PermissionMatrixRegistry::inferModule($slug),
                'type' => PermissionMatrixRegistry::inferType($slug),
                'name' => PermissionMatrixRegistry::inferName($slug),
            ];
        }

        $payload = [
            'version' => 1,
            'permissions' => $permissions,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $path = base_path(PermissionMatrixRegistry::REGISTRY_PATH);
        $contents = File::get($path);

        $start = PermissionMatrixRegistry::START_MARKER;
        $end = PermissionMatrixRegistry::END_MARKER;

        // Rebuild the full Blade comment block to keep things deterministic.
        $replacement = "{{--\n{$start}\n{$json}\n{$end}\n--}}";

        $startPos = strpos($contents, $start);
        $endPos = strpos($contents, $end);
        if ($startPos !== false && $endPos !== false && $endPos > $startPos) {
            // Replace the whole existing comment block that contains the markers.
            $before = substr($contents, 0, $startPos);
            $after = substr($contents, $endPos + strlen($end));

            // Try to trim to the start of the Blade comment opener and end of the comment closer.
            $commentOpenPos = strrpos($before, '{{--');
            $commentClosePos = strpos($after, '--}}');

            if ($commentOpenPos !== false && $commentClosePos !== false) {
                $before = substr($contents, 0, $commentOpenPos);
                $after = substr($after, $commentClosePos + strlen('--}}'));
                $contents = rtrim($before)."\n\n".$replacement."\n\n".ltrim($after);
            } else {
                // Fallback: inject replacement near top
                $contents = $replacement."\n\n".$contents;
            }
        } else {
            // Insert near top if markers missing.
            $contents = $replacement."\n\n".$contents;
        }

        File::put($path, $contents);

        return count($permissions);
    }

    /**
     * @param  array<int, array{slug:string,name?:string,module?:string,type?:string,description?:string}>  $permissions
     */
    private function syncDbFromMatrix(array $permissions): int
    {
        $records = [];
        $now = now()->toDateTimeString();

        foreach ($permissions as $p) {
            $slug = trim((string) ($p['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $module = (string) ($p['module'] ?? PermissionMatrixRegistry::inferModule($slug));
            $type = (string) ($p['type'] ?? PermissionMatrixRegistry::inferType($slug));
            $name = (string) ($p['name'] ?? PermissionMatrixRegistry::inferName($slug));
            $description = isset($p['description']) ? (string) $p['description'] : null;

            $records[] = [
                'slug' => $slug,
                'name' => $name,
                'module' => $module,
                'type' => $type,
                'description' => $description,
                'auto_registered' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($records)) {
            foreach (array_chunk($records, 500) as $chunk) {
                Permission::upsert(
                    $chunk,
                    ['slug'],
                    ['name', 'module', 'type', 'description', 'auto_registered', 'updated_at']
                );
            }
        }

        return count($records);
    }

    /**
     * @param  string[]  $matrixSlugs
     * @param  string[]  $foundSlugs
     */
    private function outputReport(array $matrixSlugs, array $foundSlugs): void
    {
        $reportPath = base_path('storage/logs/permissions_matrix_sync_report.txt');
        $lines = [];
        $lines[] = 'Permissions Matrix Sync Report';
        $lines[] = 'Generated: '.now()->toDateTimeString();
        $lines[] = '';
        $lines[] = 'Matrix permissions: '.count($matrixSlugs);
        if (! empty($foundSlugs)) {
            $lines[] = 'Scanned permissions: '.count($foundSlugs);
            $missing = array_values(array_diff($foundSlugs, $matrixSlugs));
            $extra = array_values(array_diff($matrixSlugs, $foundSlugs));
            $lines[] = 'Missing in matrix (used in code): '.count($missing);
            foreach ($missing as $m) {
                $lines[] = " - {$m}";
            }
            $lines[] = '';
            $lines[] = 'Present in matrix but not found in scan: '.count($extra);
            foreach (array_slice($extra, 0, 200) as $e) {
                $lines[] = " - {$e}";
            }
            if (count($extra) > 200) {
                $lines[] = ' - ... truncated ...';
            }
        }

        File::ensureDirectoryExists(dirname($reportPath));
        File::put($reportPath, implode("\n", $lines));
        $this->info("Report written to: {$reportPath}");
    }
}
