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
        Schema::create('specification_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique()->comment('e.g. socket, chipset, pcie_gen, form_factor, tdp, clock_speed');
            $table->string('data_type')->default('string')->comment('string, number, boolean, select');
            $table->string('unit')->nullable()->comment('e.g. W, GHz, GB, mm');
            $table->jsonb('options')->nullable()->comment('For select data types');
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('specification_attribute_id')->constrained('specification_attributes')->cascadeOnDelete();
            $table->text('value_string')->nullable();
            $table->decimal('value_number', 12, 4)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->jsonb('value_json')->nullable();
            $table->string('formatted_value')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'specification_attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
        Schema::dropIfExists('specification_attributes');
    }
};
