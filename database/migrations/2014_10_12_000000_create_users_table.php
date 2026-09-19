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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('user_id')->unique(); // رقم المستخدم الفريد لتسجيل الدخول
            $table->string('name'); // الاسم الكامل
            $table->string('email')->unique()->nullable();
            $table->string('password'); // كلمة المرور المشفرة
            $table->enum('role', ['Creator', 'Financer', 'Manager', 'Funder', 'Admin']); // الدور
            $table->string('phone')->nullable(); // رقم الهاتف اختياري
            $table->string('department')->nullable(); // الجهة أو الإدارة اختياري
            $table->enum('status', ['Active', 'Disabled'])->default('Active'); // حالة الحساب
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->boolean('is_admin')->default(false)->comment('Admin flag');
            $table->timestamps();
            $table->boolean('is_active')->default(true)->comment('User active status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
