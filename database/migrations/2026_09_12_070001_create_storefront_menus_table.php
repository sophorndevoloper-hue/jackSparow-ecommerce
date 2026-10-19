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
        Schema::create('storefront_menus', function (Blueprint $table) {
            $table->id();
            $table->string('location')->index(); // 'top', 'main', 'footer'
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('storefront_menus')
                ->nullOnDelete();
            $table->string('title');
            $table->string('url')->default('#');
            $table->string('route_name')->nullable();
            $table->string('target')->default('_self'); // '_self', '_blank'
            $table->string('icon')->nullable(); // Bootstrap icon class or emoji
            $table->string('badge')->nullable(); // e.g. 'HOT', 'NEW', 'SALE'
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['location', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storefront_menus');
    }
};
