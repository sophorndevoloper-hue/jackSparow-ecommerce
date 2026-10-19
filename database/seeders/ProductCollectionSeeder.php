<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCollection;
use Illuminate\Database\Seeder;

class ProductCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::query()->where('is_active', true)->get();

        if ($products->isEmpty()) {
            return;
        }

        $collectionsData = [
            [
                'name' => 'Enthusiast 4K Gaming Rigs',
                'slug' => 'enthusiast-4k-gaming-rigs',
                'description' => 'Flagship graphics cards, unlocked multi-threaded CPUs, and ultra-high bandwidth DDR5 memory assembled for max frame rates.',
                'image' => null,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                // Assign GPUs, CPUs, High-end motherboards
                'filter' => fn ($p) => str_contains(strtolower($p->name), 'rtx') || str_contains(strtolower($p->name), 'ryzen') || str_contains(strtolower($p->name), 'core i9'),
            ],
            [
                'name' => '3D Rendering & Creator Workstations',
                'slug' => 'creator-workstations',
                'description' => 'Heavy compute silicon, high-capacity ECC & DDR5 memory kits, and multi-drive NVMe storage arrays for CAD, 3D modeling, and VFX.',
                'image' => null,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'filter' => fn ($p) => str_contains(strtolower($p->name), 'pro') || str_contains(strtolower($p->name), 'threadripper') || str_contains(strtolower($p->name), '64gb') || str_contains(strtolower($p->name), 'ssd'),
            ],
            [
                'name' => 'Ultra-Quiet Liquid Cooling Ecosystem',
                'slug' => 'liquid-cooling-ecosystem',
                'description' => 'Closed-loop AIO liquid coolers, magnetic levitation fans, high-airflow mesh chassis, and 80+ Platinum titanium power supplies.',
                'image' => null,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
                'filter' => fn ($p) => str_contains(strtolower($p->name), 'cooling') || str_contains(strtolower($p->name), 'liquid') || str_contains(strtolower($p->name), 'case') || str_contains(strtolower($p->name), 'corsair'),
            ],
            [
                'name' => 'Next-Gen Storage & Memory Bundles',
                'slug' => 'storage-memory-bundles',
                'description' => 'Blazing fast PCIe 4.0 & 5.0 NVMe solid state drives matched with low-latency RGB memory for instant OS and game loading.',
                'image' => null,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 4,
                'filter' => fn ($p) => str_contains(strtolower($p->name), 'ssd') || str_contains(strtolower($p->name), 'ram') || str_contains(strtolower($p->name), 'samsung') || str_contains(strtolower($p->name), 'wd_black'),
            ],
        ];

        foreach ($collectionsData as $data) {
            $filter = $data['filter'];
            unset($data['filter']);

            $collection = ProductCollection::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            $matchedProducts = $products->filter($filter);
            if ($matchedProducts->isEmpty()) {
                $matchedProducts = $products->random(min(4, $products->count()));
            }

            $syncData = [];
            $order = 1;
            foreach ($matchedProducts as $prod) {
                $syncData[$prod->id] = ['sort_order' => $order++];
            }

            $collection->products()->sync($syncData);
        }
    }
}
