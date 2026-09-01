<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class ChatPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'المحادثة الداخلية', 'slug' => 'chat.view', 'module' => 'chat'],
            ['name' => 'أيقونة المحادثة في القائمة الجانبية', 'slug' => 'chat.sidebar', 'module' => 'chat'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                [
                    'name' => $perm['name'],
                    'module' => $perm['module'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
