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
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_due_date')) {
                $table->date('payment_due_date')->nullable()->after('payment_method')->index();
            }
        });

        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->index(['status', 'product_id', 'order_item_id'], 'idx_serials_status_prod_item');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->index(['status', 'company_tier', 'created_at'], 'idx_quotes_status_tier_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex('idx_quotes_status_tier_created');
        });

        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->dropIndex('idx_serials_status_prod_item');
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_due_date')) {
                $table->dropColumn('payment_due_date');
            }
        });
    }
};
