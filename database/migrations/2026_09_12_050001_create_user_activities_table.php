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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('action', 50); // LOGIN, LOGOUT, CREATE, UPDATE, DELETE, CHECK_IN, CHECK_OUT, STATUS_CHANGE, PAYROLL_GENERATED
            $table->string('module', 50); // Staff, StaffAttendance, StaffPayroll, Users, Roles, Orders, Products, etc.
            $table->string('entity_id', 50)->nullable();
            $table->text('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'action']);
            $table->index(['module', 'entity_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
