<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'geographic_scope_id')) {
                $table->bigInteger('geographic_scope_id')->nullable()->after('created_by');
            }
            if (! Schema::hasColumn('plans', 'administrative_scope_id')) {
                $table->bigInteger('administrative_scope_id')->nullable()->after('geographic_scope_id');
            }
        });

        // Update existing data based on creator's profile
        DB::table('plans')->get()->each(function ($plan) {
            $user = DB::table('users')->where('id', $plan->created_by)->first();
            if ($user) {
                DB::table('plans')->where('id', $plan->id)->update([
                    'geographic_scope_id' => $user->governorate_id,
                    'administrative_scope_id' => $user->entity_id,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['geographic_scope_id', 'administrative_scope_id']);
        });
    }
};
