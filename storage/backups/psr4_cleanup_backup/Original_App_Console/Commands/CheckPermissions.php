<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class CheckPermissions extends Command
{
    protected $signature = 'permissions:check {--user= : User ID to check scoping for} {--test : Run test scenarios for scoping assurance}';

    protected $description = 'Check permissions and data scoping for a user';

    public function handle()
    {
        $userId = $this->option('user');
        $runTest = $this->option('test');

        if ($runTest) {
            $this->runScopingScenarios();

            return;
        }

        if ($userId) {
            $user = User::with('role')->find($userId);
            if (! $user) {
                $this->error('User not found!');

                return;
            }

            $this->info("Assurance check for User: {$user->name} (Role: ".optional($user->role)->name.')');
            $this->info('User Type: '.($user->isCentralUser() ? 'CENTRALIZED' : 'GEOGRAPHIC'));

            $adminScope = $user->getModuleAdminScope('projects');
            $geoScope = $user->getModuleGeoScope('projects');

            $this->line(" - Admin Scope (Projects): <comment>{$adminScope}</comment>");
            $this->line(" - Geo Scope (Projects): <comment>{$geoScope}</comment>");

            // Simulate Project Query
            \Auth::login($user);
            $query = Project::query();
            $sql = $query->toSql();
            $bindings = $query->getBindings();

            $this->info("\nGenerated SQL for Project Filtering:");
            $this->line($this->interpolateQuery($sql, $bindings));

            $count = Project::count();
            $this->info("Total visible projects: {$count}");

        } else {
            $roles = Role::with('permissions')->get();
            foreach ($roles as $role) {
                $this->info('Role: '.$role->name);
                foreach ($role->permissions as $perm) {
                    $status = $perm->pivot->is_active ? 'VISIBLE' : 'HIDDEN';
                    $this->line(" - Permission: {$perm->name} => {$status}");
                }
                $this->line('');
            }
        }

        $this->info('Check complete!');
    }

    protected function runScopingScenarios()
    {
        $this->info('=== SCOPING ASSURANCE TEST SCENARIOS ===');

        $scenarios = [
            [
                'name' => 'Centralized User - Creator Only',
                'type' => 'central',
                'admin' => 'user',
                'geo' => 'none',
            ],
            [
                'name' => 'Centralized User - Own Entity',
                'type' => 'central',
                'admin' => 'own',
                'geo' => 'none',
            ],
            [
                'name' => 'Geographic User - Same Governorate',
                'type' => 'geo',
                'admin' => 'all',
                'geo' => 'same_governorate',
            ],
            [
                'name' => 'Geographic User - Same Directorate',
                'type' => 'geo',
                'admin' => 'all',
                'geo' => 'same_directorate',
            ],
            [
                'name' => 'Geographic User - No restrictions',
                'type' => 'geo',
                'admin' => 'all',
                'geo' => 'all',
            ],
        ];

        // Create a mock user
        $user = new User([
            'id' => 999,
            'name' => 'Test Scout',
            'entity_id' => 5,
            'governorate_id' => 2,
            'directorate_id' => 10,
        ]);

        foreach ($scenarios as $s) {
            $this->line("\nScenario: <info>{$s['name']}</info>");

            // Mock permissions
            $role = new Role([
                'module_scopes' => ['projects' => $s['admin']],
                'module_geo_scopes' => ['projects' => $s['geo']],
            ]);
            $user->setRelation('role', $role);

            // Adjust user central/geo status
            if ($s['type'] === 'central') {
                $user->governorate_id = null;
                $user->directorate_id = null;
            } else {
                $user->governorate_id = 2;
                $user->directorate_id = 10;
            }

            \Auth::login($user);
            $query = Project::query();
            $sql = $query->toSql();
            $bindings = $query->getBindings();

            $this->line(' - SQL: '.$this->interpolateQuery($sql, $bindings));
        }
    }

    protected function interpolateQuery($query, $params)
    {
        $keys = [];
        $values = $params;

        // build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:'.$key.'/';
            } else {
                $keys[] = '/[?]/';
            }

            if (is_array($value)) {
                $values[$key] = implode(',', $value);
            }

            if (is_null($value)) {
                $values[$key] = 'NULL';
            }
        }

        $query = preg_replace($keys, $values, $query, 1, $count);

        return $query;
    }
}
