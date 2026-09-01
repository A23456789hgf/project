<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if correspondence_replies table exists
        if (Schema::hasTable('correspondence_replies')) {
            Schema::table('correspondence_replies', function (Blueprint $table) {
                // Rename attachment_path to attachments if it exists
                if (Schema::hasColumn('correspondence_replies', 'attachment_path')) {
                    $table->renameColumn('attachment_path', 'attachments');
                }

                // Rename return_info to return_date if it exists
                if (Schema::hasColumn('correspondence_replies', 'return_info')) {
                    $table->renameColumn('return_info', 'return_date');
                }

                // Ensure attachments column is JSON type (if we renamed it, we might need to change its type)
                // Note: renameColumn doesn't change type. We'll handle type conversion in a separate raw statement if needed
            });

            // Convert column types if necessary
            $dbType = DB::getDriverName();
            if ($dbType === 'mysql') {
                DB::statement('ALTER TABLE correspondence_replies MODIFY attachments JSON NULL');
                DB::statement('ALTER TABLE correspondence_replies MODIFY return_date DATE NULL');
            }

            Schema::table('correspondence_replies', function (Blueprint $table) {
                // Add status column if it doesn't exist
                if (! Schema::hasColumn('correspondence_replies', 'status')) {
                    $table->string('status')->default('sent')->after('return_date');
                }

                // Add confidential column if it doesn't exist
                if (! Schema::hasColumn('correspondence_replies', 'confidential')) {
                    $table->boolean('confidential')->default(false)->after('status');
                }

                // Add forwarding_id column if it doesn't exist
                if (! Schema::hasColumn('correspondence_replies', 'forwarding_id')) {
                    $table->unsignedBigInteger('forwarding_id')->nullable()->after('referral_id');
                    $table->foreign('forwarding_id')->references('id')->on('correspondence_forwardings')->onDelete('set null');
                }

                // Add acknowledged_at and acknowledged_by_user_id if they don't exist
                if (! Schema::hasColumn('correspondence_replies', 'acknowledged_at')) {
                    $table->timestamp('acknowledged_at')->nullable()->after('replied_at');
                }
                if (! Schema::hasColumn('correspondence_replies', 'acknowledged_by_user_id')) {
                    $table->unsignedBigInteger('acknowledged_by_user_id')->nullable()->after('acknowledged_at');
                    $table->foreign('acknowledged_by_user_id')->references('id')->on('users')->onDelete('set null');
                }

                // Add notes column if it doesn't exist
                if (! Schema::hasColumn('correspondence_replies', 'notes')) {
                    $table->text('notes')->nullable()->after('confidential');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('correspondence_replies', function (Blueprint $table) {
            $table->dropForeign(['forwarding_id']);
            $table->dropForeign(['acknowledged_by_user_id']);
            $table->dropColumn([
                'forwarding_id',
                'status',
                'confidential',
                'notes',
                'acknowledged_at',
                'acknowledged_by_user_id',
            ]);

            // Reverting renames is complex if types were changed, usually down() for sync migrations is best-effort
            if (Schema::hasColumn('correspondence_replies', 'attachments')) {
                $table->renameColumn('attachments', 'attachment_path');
            }
            if (Schema::hasColumn('correspondence_replies', 'return_date')) {
                $table->renameColumn('return_date', 'return_info');
            }
        });
    }
};
