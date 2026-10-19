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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('staff_code', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->nullable()->unique();
            $table->string('phone', 50);
            $table->string('department', 100)->default('Warehouse');
            $table->string('role', 100)->default('Warehouse Staff');
            $table->string('employment_type', 50)->default('full_time'); // full_time, part_time, contract, intern
            $table->string('status', 50)->default('active'); // active, on_leave, suspended, terminated
            $table->date('join_date')->nullable();

            // Compensation & Salary Details
            $table->decimal('base_salary', 12, 2)->default(0.00);
            $table->string('salary_type', 50)->default('monthly'); // monthly, hourly, weekly
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();

            // Emergency & Personal Contacts
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['department', 'status']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
