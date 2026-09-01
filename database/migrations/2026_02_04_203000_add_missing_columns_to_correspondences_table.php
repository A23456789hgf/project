<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('correspondences', function (Blueprint $table) {
            if (! Schema::hasColumn('correspondences', 'confidential')) {
                $table->boolean('confidential')->default(false)->after('priority');
            }
            if (! Schema::hasColumn('correspondences', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('correspondences', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('sent_at');
            }
            if (! Schema::hasColumn('correspondences', 'closed_by_user_id')) {
                $table->unsignedBigInteger('closed_by_user_id')->nullable()->after('closed_at');
                $table->foreign('closed_by_user_id')->references('id')->on('users')->onDelete('set null');
            }
            if (! Schema::hasColumn('correspondences', 'close_reason')) {
                $table->text('close_reason')->nullable()->after('closed_by_user_id');
            }
            if (! Schema::hasColumn('correspondences', 'last_action_at')) {
                $table->timestamp('last_action_at')->nullable()->after('close_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('correspondences', function (Blueprint $table) {
            $table->dropForeign(['closed_by_user_id']);
            $table->dropColumn([
                'confidential',
                'sent_at',
                'closed_at',
                'closed_by_user_id',
                'close_reason',
                'last_action_at',
            ]);
        });
    }
};
