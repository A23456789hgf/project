<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id')->comment('Parent stage for tree structure');

            // إذا أردت علاقة FK
            $table->foreign('parent_id')->references('id')->on('stages')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
