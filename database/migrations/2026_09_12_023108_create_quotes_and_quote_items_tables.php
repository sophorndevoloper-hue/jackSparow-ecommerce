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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->string('client_name');
            $table->string('company_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('company_tier')->default('Standard');
            $table->string('status')->default('pending_review');
            $table->date('target_delivery_date')->nullable();
            $table->text('client_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('target_margin_percent', 5, 2)->default(15.00);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_quoted_price', 12, 2)->default(0);
            $table->timestamp('quoted_at')->nullable();
            $table->date('valid_until')->nullable();
            $table->foreignId('converted_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name');
            $table->string('item_sku')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('target_unit_price', 12, 2)->nullable();
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('margin_percent', 5, 2)->default(0);
            $table->decimal('quoted_unit_price', 12, 2)->default(0);
            $table->decimal('total_quoted_price', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
