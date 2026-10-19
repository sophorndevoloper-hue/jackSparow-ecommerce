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
        Schema::create('customer_group_payment_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->index();
            $table->integer('days')->default(0)->comment('Payment window in days: 0 for immediate/COD, 30 for Net 30, etc.');
            $table->text('description')->nullable();
            $table->decimal('early_payment_discount_percentage', 5, 2)->default(0.00)->comment('Discount percentage if paid early');
            $table->integer('early_payment_days')->default(0)->comment('Days threshold to qualify for early payment discount');
            $table->decimal('late_fee_percentage', 5, 2)->default(0.00)->comment('Monthly late payment penalty fee percentage');
            $table->boolean('require_deposit')->default(false);
            $table->decimal('deposit_percentage', 5, 2)->default(0.00);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_group_payment_terms');
    }
};
