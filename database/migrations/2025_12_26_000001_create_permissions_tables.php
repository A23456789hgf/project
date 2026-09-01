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
        // Create permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Human-readable name (e.g., "Create Projects")
            $table->string('slug')->unique(); // Unique identifier (e.g., "projects.create")
            $table->text('description')->nullable(); // Permission description
            $table->string('module'); // Module grouping (e.g., "projects", "approvals")
            $table->timestamps();

            $table->index('module'); // Index for faster queries by module
        });

        // Create role_permission pivot table
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // Role name/slug (e.g., "Admin", "Manager")
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->timestamps();

            // Ensure unique role-permission combinations
            $table->unique(['role', 'permission_id']);
            $table->index('role'); // Index for faster queries by role
        });

        // Create user_roles table for multi-role support
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role'); // Role name/slug
            $table->timestamps();

            // Ensure unique user-role combinations
            $table->unique(['user_id', 'role']);
            $table->index('user_id'); // Index for faster queries by user
            $table->index('role'); // Index for faster queries by role
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
    }
};
