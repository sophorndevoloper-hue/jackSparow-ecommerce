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
        $tableNames = config('permission.table_names');
        $permissionsTable = $tableNames['permissions'] ?? 'permissions';

        if (! Schema::hasColumn($permissionsTable, 'menu_id')) {
            Schema::table($permissionsTable, function (Blueprint $table) {
                $table->foreignId('menu_id')->nullable()->after('guard_name')->constrained('admin_menus')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $permissionsTable = $tableNames['permissions'] ?? 'permissions';

        if (Schema::hasColumn($permissionsTable, 'menu_id')) {
            Schema::table($permissionsTable, function (Blueprint $table) {
                $table->dropConstrainedForeignId('menu_id');
            });
        }
    }
};
