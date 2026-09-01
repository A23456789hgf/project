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
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['task_id', 'user_id']);
        });

        // Migrate existing assignments
        $tasks = DB::table('tasks')->whereNotNull('assigned_to')->get();
        $inserts = [];
        foreach ($tasks as $task) {
            $inserts[] = [
                'task_id' => $task->id,
                'user_id' => $task->assigned_to,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($inserts)) {
            // chunk inserts
            foreach (array_chunk($inserts, 500) as $chunk) {
                DB::table('task_user')->insert($chunk);
            }
        }

        Schema::table('tasks', function (Blueprint $table) {
            // Check if foreign key exists before dropping
            $conn = Schema::getConnection();
            $dbName = $conn->getDatabaseName();
            $hasForeignKey = collect($conn->select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ? 
                  AND TABLE_NAME = 'tasks' 
                  AND COLUMN_NAME = 'assigned_to' 
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$dbName]))->count() > 0;

            if ($hasForeignKey) {
                $table->dropForeign(['assigned_to']);
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });

        // Migrate back
        $taskUsers = DB::table('task_user')->get();
        foreach ($taskUsers as $tu) {
            DB::table('tasks')->where('id', $tu->task_id)->update(['assigned_to' => $tu->user_id]);
        }

        Schema::dropIfExists('task_user');
    }
};
