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

        if (! Schema::hasColumn($permissionsTable, 'action_route')) {
            Schema::table($permissionsTable, function (Blueprint $table) {
                $table->string('action_route')->nullable()->after('guard_name');
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

        if (Schema::hasColumn($permissionsTable, 'action_route')) {
            Schema::table($permissionsTable, function (Blueprint $table) {
                $table->dropColumn('action_route');
            });
        }
    }
};
