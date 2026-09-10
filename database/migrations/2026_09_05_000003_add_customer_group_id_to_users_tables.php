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
                $table->foreignId('customer_group_id')->nullable()->after('id')->constrained('customer_groups')->nullOnDelete();
                $table->string('company_name')->nullable()->after('name');
                $table->string('tax_vat_number')->nullable()->after('company_name');
                $table->decimal('credit_limit', 12, 2)->default(0.00)->after('total_spent');
                $table->decimal('credit_balance', 12, 2)->default(0.00)->after('credit_limit');
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('customer_group_id')->nullable()->after('id')->constrained('customer_groups')->nullOnDelete();
                $table->string('company_name')->nullable()->after('name');
                $table->string('tax_vat_number')->nullable()->after('company_name');
                $table->decimal('credit_limit', 12, 2)->default(0.00)->after('total_spent');
                $table->decimal('credit_balance', 12, 2)->default(0.00)->after('credit_limit');
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
                $table->dropForeign(['customer_group_id']);
                $table->dropColumn(['customer_group_id', 'company_name', 'tax_vat_number', 'credit_limit', 'credit_balance']);
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['customer_group_id']);
                $table->dropColumn(['customer_group_id', 'company_name', 'tax_vat_number', 'credit_limit', 'credit_balance']);
            });
        }
    }
};
