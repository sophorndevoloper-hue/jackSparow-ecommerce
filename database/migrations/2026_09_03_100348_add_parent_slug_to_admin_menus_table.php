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
        if (! Schema::hasColumn('admin_menus', 'parent_slug')) {
            Schema::table('admin_menus', function (Blueprint $table) {
                $table->string('parent_slug')->nullable()->after('slug');
                $table->string('route_name')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('admin_menus', 'parent_slug')) {
            Schema::table('admin_menus', function (Blueprint $table) {
                $table->dropColumn('parent_slug');
            });
        }
    }
};
