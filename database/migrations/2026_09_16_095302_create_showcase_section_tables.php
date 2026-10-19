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
        // 1. Featured Components table
        Schema::create('featured_components', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Featured Hardware');
            $table->text('description')->nullable();
            $table->string('badge')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Flash Deals & Clearance table
        Schema::create('flash_deals', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Flash Deals & Clearance');
            $table->text('description')->nullable();
            $table->string('badge')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Best Sellers table
        Schema::create('best_sellers', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Best Sellers');
            $table->text('description')->nullable();
            $table->string('badge')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. New Arrivals table (with start_date and end_date)
        Schema::create('new_arrivals', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('New Arrivals');
            $table->text('description')->nullable();
            $table->string('badge')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Product Promotions table
        Schema::create('product_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('badge')->nullable(); // e.g. "MEGA DEAL", "LIMITED 30% OFF"
            $table->string('banner_image')->nullable();
            $table->string('cta_text')->nullable()->default('Shop Promotion');
            $table->string('cta_url')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 6. Update storefront_products to function as central conjunction table
        Schema::table('storefront_products', function (Blueprint $table) {
            $table->string('showcaseable_type', 100)->nullable()->after('product_id');
            $table->unsignedBigInteger('showcaseable_id')->nullable()->after('showcaseable_type');

            $table->index(['showcaseable_type', 'showcaseable_id'], 'idx_storefront_showcaseable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('storefront_products', function (Blueprint $table) {
            $table->dropIndex('idx_storefront_showcaseable');
            $table->dropColumn(['showcaseable_type', 'showcaseable_id']);
        });

        Schema::dropIfExists('product_promotions');
        Schema::dropIfExists('new_arrivals');
        Schema::dropIfExists('best_sellers');
        Schema::dropIfExists('flash_deals');
        Schema::dropIfExists('featured_components');
    }
};
