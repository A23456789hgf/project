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
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->string('notifiable_type')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->unsignedBigInteger('notifiable_id')->nullable();
            }

            // Add index if not exists (checking manually is hard in migration without raw SQL,
            // but usually we can try adding it with a catch or check constraint name if possible.
            // However, explicit check for columns is usually enough for the structure).
            // Let's just add the index if both were missing or added.
        });

        // Add index in a separate schema call or check
        try {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['notifiable_type', 'notifiable_id']);
            });
        } catch (Exception $e) {
            // Index likely already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['notifiable_type', 'notifiable_id']);
        });
    }
};
