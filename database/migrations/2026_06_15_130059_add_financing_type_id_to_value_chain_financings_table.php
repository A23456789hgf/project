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
        Schema::table('value_chain_financings', function (Blueprint $table) {
            $table->foreignId('value_chain_financing_type_id')->nullable()->constrained('value_chain_financing_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('value_chain_financings', function (Blueprint $table) {
            $table->dropForeign(['value_chain_financing_type_id']);
            $table->dropColumn('value_chain_financing_type_id');
        });
    }
};
