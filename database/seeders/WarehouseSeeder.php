<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainWarehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN'],
            [
                'name' => 'Main Central Depot',
                'phone' => '+1 (555) 019-2834',
                'email' => 'warehouse.main@jacksparrow.com',
                'address' => '100 Silicon Blvd, Suite 400',
                'city' => 'San Jose, CA',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        $eastWarehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-EAST'],
            [
                'name' => 'East Coast Distribution Hub',
                'phone' => '+1 (555) 018-7722',
                'email' => 'warehouse.east@jacksparrow.com',
                'address' => '750 Logistics Way',
                'city' => 'Newark, NJ',
                'is_active' => true,
                'is_default' => false,
            ]
        );

        $rmaCenter = Warehouse::firstOrCreate(
            ['code' => 'WH-RMA'],
            [
                'name' => 'RMA & Repair Center',
                'phone' => '+1 (555) 014-9911',
                'email' => 'warehouse.rma@jacksparrow.com',
                'address' => '22 Tech Parkway',
                'city' => 'Austin, TX',
                'is_active' => true,
                'is_default' => false,
            ]
        );

        // Seed existing products to main warehouse if not attached
        $products = Product::all();
        foreach ($products as $product) {
            if (! $mainWarehouse->products()->where('products.id', $product->id)->exists()) {
                $mainWarehouse->products()->attach($product->id, [
                    'quantity' => $product->stock_quantity,
                ]);
            }
        }
    }
}
