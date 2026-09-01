<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_entities', function (Blueprint $table) {
            $table->unsignedBigInteger('authority_id')->nullable()->after('parent_id');

            $table->foreign('authority_id')
                ->references('id')
                ->on('authorities')
                ->onDelete('set null');

            $table->index('authority_id');
        });
    }

    public function down(): void
    {
        Schema::table('internal_entities', function (Blueprint $table) {
            $table->dropForeign(['authority_id']);
            $table->dropIndex(['authority_id']);
            $table->dropColumn('authority_id');
        });
    }
};
