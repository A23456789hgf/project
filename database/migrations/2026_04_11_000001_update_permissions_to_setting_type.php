<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Permission::whereIn('slug', ['users.view-profiles', 'users.edit-passwords'])
            ->update(['type' => Permission::TYPE_SETTING]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('slug', 'users.view-profiles')->update(['type' => Permission::TYPE_PAGE]);
        Permission::where('slug', 'users.edit-passwords')->update(['type' => Permission::TYPE_ACTION]);
    }
};
