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
        Schema::create('storefront_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('section', 50)->default('featured')->index(); // featured, deals, bestseller, new_arrivals
            $table->string('badge', 50)->nullable(); // HOT, NEW, SALE, TOP PICK, SAVE $50
            $table->string('custom_title')->nullable(); // Optional marketing override title
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'section'], 'uniq_storefront_product_section');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storefront_products');
    }
};
