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
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique()->comment('e.g. WHOLESALE, RESELLER, SYSTEM_INTEGRATOR, ENTERPRISE, RETAIL');
            $table->text('description')->nullable();
            $table->decimal('default_discount_percentage', 5, 2)->default(0.00);
            $table->decimal('min_order_amount', 12, 2)->default(0.00);
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->integer('payment_terms_days')->default(0)->comment('Net D (e.g. Net 30, Net 60)');
            $table->boolean('tax_exempt')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_groups');
    }
};
