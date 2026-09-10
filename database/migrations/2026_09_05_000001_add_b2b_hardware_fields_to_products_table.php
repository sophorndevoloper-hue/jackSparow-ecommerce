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
        Schema::table('products', function (Blueprint $table) {
            $table->string('mpn')->nullable()->after('sku')->comment('Manufacturer Part Number');
            $table->string('upc_ean')->nullable()->after('mpn')->comment('Universal Product Code / EAN barcode');
            $table->string('hs_code')->nullable()->after('upc_ean')->comment('Harmonized System tariff code');
            $table->string('unspsc_code')->nullable()->after('hs_code')->comment('UN Standard Products & Services Code');
            $table->decimal('map_price', 10, 2)->nullable()->after('cost_price')->comment('Minimum Advertised Price');
            $table->decimal('min_margin_percentage', 5, 2)->default(10.00)->after('map_price')->comment('Safety margin floor protection %');
            $table->integer('moq')->default(1)->after('min_margin_percentage')->comment('Minimum Order Quantity');
            $table->integer('case_pack_multiple')->default(1)->after('moq')->comment('Wholesale order multiple/batch size');
            $table->integer('max_order_quantity')->nullable()->after('case_pack_multiple')->comment('Max order qty per B2B order');
            $table->boolean('requires_serial_tracking')->default(false)->after('max_order_quantity')->comment('Flag for serialized tracking');
            $table->integer('manufacturer_warranty_months')->default(36)->after('warranty_period')->comment('Manufacturer warranty duration');
            $table->integer('distributor_warranty_months')->default(12)->after('manufacturer_warranty_months')->comment('Direct distributor warranty');
            $table->string('form_factor')->nullable()->after('distributor_warranty_months')->comment('e.g. ATX, Micro-ATX, M.2 2280');
            $table->string('socket')->nullable()->after('form_factor')->comment('e.g. AM5, LGA1700');
            $table->string('chipset')->nullable()->after('socket')->comment('e.g. X670E, Z790, B650');
            $table->integer('tdp_watts')->nullable()->after('chipset')->comment('Thermal Design Power in Watts');
            $table->integer('power_requirement_watts')->nullable()->after('tdp_watts')->comment('Recommended or required PSU wattage');
            $table->decimal('weight_kg', 8, 3)->nullable()->after('power_requirement_watts')->comment('Product weight in kg');
            $table->decimal('length_cm', 8, 2)->nullable()->after('weight_kg');
            $table->decimal('width_cm', 8, 2)->nullable()->after('length_cm');
            $table->decimal('height_cm', 8, 2)->nullable()->after('width_cm');
            $table->boolean('is_hazmat')->default(false)->after('height_cm')->comment('Hazardous materials flag (e.g. lithium batteries)');
            $table->jsonb('specs')->nullable()->after('specifications')->comment('Normalized structured specs JSON');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'mpn',
                'upc_ean',
                'hs_code',
                'unspsc_code',
                'map_price',
                'min_margin_percentage',
                'moq',
                'case_pack_multiple',
                'max_order_quantity',
                'requires_serial_tracking',
                'manufacturer_warranty_months',
                'distributor_warranty_months',
                'form_factor',
                'socket',
                'chipset',
                'tdp_watts',
                'power_requirement_watts',
                'weight_kg',
                'length_cm',
                'width_cm',
                'height_cm',
                'is_hazmat',
                'specs',
            ]);
        });
    }
};
