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
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('date', 10); // YYYY-MM-DD format
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->decimal('work_hours', 5, 2)->default(0.00);
            $table->decimal('overtime_hours', 5, 2)->default(0.00);
            $table->string('status', 50)->default('present'); // present, late, half_day, absent, on_leave
            $table->string('ip_address', 45)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['staff_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
