<?php

namespace Database\Seeders;

use App\Models\Make;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $makes = [
            [
                'name' => 'Dell Technologies',
                'slug' => 'dell',
                'website' => 'https://www.dell.com',
                'description' => 'Global provider of enterprise workstations, PowerEdge servers, commercial laptops, and datacenter hardware solutions.',
                'is_active' => true,
            ],
            [
                'name' => 'HP Inc.',
                'slug' => 'hp',
                'website' => 'https://www.hp.com',
                'description' => 'Manufacturer of enterprise PCs, ProLiant & Z Workstation hardware, commercial displays, and printing systems.',
                'is_active' => true,
            ],
            [
                'name' => 'Lenovo',
                'slug' => 'lenovo',
                'website' => 'https://www.lenovo.com',
                'description' => 'Leading multinational technology company producing ThinkPad systems, ThinkStation workstations, and ThinkSystem servers.',
                'is_active' => true,
            ],
            [
                'name' => 'Supermicro',
                'slug' => 'supermicro',
                'website' => 'https://www.supermicro.com',
                'description' => 'Global leader in high-performance, high-efficiency server technology, AI clusters, and green computing solutions.',
                'is_active' => true,
            ],
            [
                'name' => 'Cisco Systems',
                'slug' => 'cisco',
                'website' => 'https://www.cisco.com',
                'description' => 'Worldwide leader in networking hardware, unified computing system (UCS) servers, and enterprise infrastructure.',
                'is_active' => true,
            ],
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'website' => 'https://www.apple.com',
                'description' => 'Pioneer of high-performance Apple Silicon Mac workstations, Mac Studio, and consumer computing devices.',
                'is_active' => true,
            ],
            [
                'name' => 'ASUS OEM',
                'slug' => 'asus',
                'website' => 'https://www.asus.com',
                'description' => 'Original equipment manufacturer of commercial motherboards, server systems, and ROG high-performance hardware.',
                'is_active' => true,
            ],
            [
                'name' => 'Intel Corporation',
                'slug' => 'intel',
                'website' => 'https://www.intel.com',
                'description' => 'Global semiconductor manufacturer producing Xeon server processors, NUC compute elements, and client silicon.',
                'is_active' => true,
            ],
            [
                'name' => 'Acer',
                'slug' => 'acer',
                'website' => 'https://www.acer.com',
                'description' => 'Hardware and electronics manufacturer specializing in commercial desktop PCs, Predator systems, and displays.',
                'is_active' => true,
            ],
            [
                'name' => 'Alienware',
                'slug' => 'alienware',
                'website' => 'https://www.alienware.com',
                'description' => 'Premier high-performance workstation and enthusiast gaming PC hardware subsidiary of Dell Technologies.',
                'is_active' => true,
            ],
        ];

        foreach ($makes as $makeData) {
            Make::updateOrCreate(
                ['slug' => $makeData['slug']],
                $makeData
            );
        }

        // Associate existing sample products with corresponding makes if applicable
        $intelMake = Make::where('slug', 'intel')->first();
        if ($intelMake) {
            Product::where('slug', 'like', '%intel%')->whereNull('make_id')->update(['make_id' => $intelMake->id]);
        }

        $asusMake = Make::where('slug', 'asus')->first();
        if ($asusMake) {
            Product::where('slug', 'like', '%asus%')->whereNull('make_id')->update(['make_id' => $asusMake->id]);
        }
    }
}
