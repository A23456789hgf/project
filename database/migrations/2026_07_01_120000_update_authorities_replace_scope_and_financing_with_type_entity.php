<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - حذف عمود entity_scope من جدول authorities
     * - حذف عمود financing_type_id من جدول authorities
     * - إضافة عمود type_entity_id يرتبط بجدول type_entities
     */
    public function up(): void
    {
        Schema::table('authorities', function (Blueprint $table) {
            // حذف entity_scope
            if (Schema::hasColumn('authorities', 'entity_scope')) {
                $table->dropColumn('entity_scope');
            }

            // حذف financing_form_id إن وُجد
            if (Schema::hasColumn('authorities', 'financing_form_id')) {
                try {
                    $table->dropForeign(['financing_form_id']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('financing_form_id');
            }
        });

        // حذف financing_type_id مع foreign key بطريقة آمنة
        if (Schema::hasColumn('authorities', 'financing_type_id')) {
            Schema::table('authorities', function (Blueprint $table) {
                try {
                    $table->dropForeign(['financing_type_id']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('financing_type_id');
            });
        }

        // إضافة type_entity_id
        Schema::table('authorities', function (Blueprint $table) {
            if (! Schema::hasColumn('authorities', 'type_entity_id')) {
                $table->unsignedBigInteger('type_entity_id')->nullable()->after('directorate_id');
                $table->foreign('type_entity_id')->references('id')->on('type_entities')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authorities', function (Blueprint $table) {
            // حذف type_entity_id
            if (Schema::hasColumn('authorities', 'type_entity_id')) {
                $table->dropForeign(['type_entity_id']);
                $table->dropColumn('type_entity_id');
            }

            // إعادة entity_scope
            if (! Schema::hasColumn('authorities', 'entity_scope')) {
                $table->string('entity_scope')->nullable()->after('directorate_id');
            }

            // إعادة financing_type_id
            if (! Schema::hasColumn('authorities', 'financing_type_id')) {
                $table->unsignedBigInteger('financing_type_id')->nullable()->after('entity_scope');
            }
        });
    }
};
