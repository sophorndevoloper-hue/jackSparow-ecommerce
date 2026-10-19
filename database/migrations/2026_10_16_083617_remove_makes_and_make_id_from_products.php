<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'make_id')) {
            Schema::table('products', function (Blueprint $table) {
                if (DB::getDriverName() === 'sqlite') {
                    $table->dropColumn('make_id');
                } else {
                    $table->dropConstrainedForeignId('make_id');
                }
            });
        }

        Schema::dropIfExists('makes');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('makes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('make_id')->nullable()->after('brand_id')->constrained('makes')->nullOnDelete();
        });
    }
};
