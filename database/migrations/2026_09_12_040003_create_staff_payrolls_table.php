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
        Schema::create('staff_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('payroll_code', 50)->unique();
            $table->string('period_start', 10); // YYYY-MM-DD
            $table->string('period_end', 10);   // YYYY-MM-DD
            $table->decimal('base_salary', 12, 2)->default(0.00);
            $table->unsignedInteger('worked_days')->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0.00);
            $table->decimal('bonus_amount', 12, 2)->default(0.00);
            $table->decimal('deductions_amount', 12, 2)->default(0.00);
            $table->decimal('net_salary', 12, 2)->default(0.00);
            $table->string('payment_method', 50)->default('bank_transfer'); // bank_transfer, cash, check
            $table->string('payment_date', 10)->nullable(); // YYYY-MM-DD
            $table->string('status', 50)->default('draft'); // draft, approved, paid
            $table->text('reference_note')->nullable();

            $table->timestamps();

            $table->index(['period_start', 'period_end', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_payrolls');
    }
};
