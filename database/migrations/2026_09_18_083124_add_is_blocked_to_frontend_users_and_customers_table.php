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
        if (Schema::hasTable('frontend_users')) {
            Schema::table('frontend_users', function (Blueprint $table) {
                $table->boolean('is_blocked')->default(false)->after('customer_type');
                $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->boolean('is_blocked')->default(false)->after('customer_type');
                $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('frontend_users')) {
            Schema::table('frontend_users', function (Blueprint $table) {
                $table->dropColumn(['is_blocked', 'blocked_at']);
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn(['is_blocked', 'blocked_at']);
            });
        }
    }
};
