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
            $table->foreignId('payment_term_id')
                ->nullable()
                ->after('payment_due_date')
                ->constrained('customer_group_payment_terms')
                ->nullOnDelete();
            $table->decimal('deposit_amount', 12, 2)->default(0.00)->after('payment_term_id');
            $table->boolean('deposit_paid')->default(false)->after('deposit_amount');
            $table->timestamp('deposit_paid_at')->nullable()->after('deposit_paid');
            $table->decimal('early_payment_discount_amount', 12, 2)->default(0.00)->after('deposit_paid_at');
            $table->date('early_payment_discount_deadline')->nullable()->after('early_payment_discount_amount');
            $table->decimal('late_fee_amount', 12, 2)->default(0.00)->after('early_payment_discount_deadline');
            $table->timestamp('late_fee_applied_at')->nullable()->after('late_fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_term_id']);
            $table->dropColumn([
                'payment_term_id',
                'deposit_amount',
                'deposit_paid',
                'deposit_paid_at',
                'early_payment_discount_amount',
                'early_payment_discount_deadline',
                'late_fee_amount',
                'late_fee_applied_at',
            ]);
        });
    }
};
