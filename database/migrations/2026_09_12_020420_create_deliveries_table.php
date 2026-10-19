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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete();
            $table->string('tracking_number')->nullable()->index();
            $table->string('carrier')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('recipient_name');
            $table->string('recipient_phone')->nullable();
            $table->string('recipient_email')->nullable();
            $table->json('shipping_address')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
