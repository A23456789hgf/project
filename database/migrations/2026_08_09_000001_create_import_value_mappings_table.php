<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_value_mappings', function (Blueprint $table) {
            $table->id();

            // Session identifier — ties all mappings to one import run
            $table->string('import_session', 100)->index();

            // The specific dropdown field being mapped
            $table->string('field_key', 100);      // e.g. program_id
            $table->string('field_label', 255);     // e.g. البرنامج
            $table->string('table_name', 100);      // e.g. programs

            // The original text value from Excel (full length for storage/display)
            $table->string('original_value', 500);

            // SHA-256 hash of (import_session + ':::' + field_key + ':::' + original_value)
            // Used as the unique key to avoid MySQL utf8mb4 key-length limits.
            $table->char('mapping_hash', 64)->nullable();

            // User's chosen action
            $table->enum('action', ['replace', 'add', 'edit_add'])->nullable();

            // The final resolved record
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_value', 500)->nullable();

            // Audit
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Unique constraint via hash — avoids key-length error on mysql utf8mb4
            $table->unique('mapping_hash', 'uq_mapping_hash');

            $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
            $table->index('field_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_value_mappings');
    }
};
