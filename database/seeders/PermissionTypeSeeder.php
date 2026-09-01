<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionTypeSeeder extends Seeder
{
    public function run()
    {
        Permission::all()->each(function ($p) {
            $slug = $p->slug;
            if (str_contains($slug, 'sidebar')) {
                $p->type = 'sidebar';
            } elseif (str_contains($slug, 'view-own') || str_contains($slug, 'view-all') || str_contains($slug, 'scope-') || str_contains($slug, '.scope')) {
                $p->type = 'scope';
            } elseif (str_contains($slug, '.view')) {
                $p->type = 'page';
            } else {
                $p->type = 'action';
            }
            $p->save();
        });
    }
}
