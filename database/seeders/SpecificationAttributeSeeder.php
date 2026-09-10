<?php

namespace Database\Seeders;

use App\Models\SpecificationAttribute;
use Illuminate\Database\Seeder;

class SpecificationAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Socket Standard',
                'slug' => 'socket-standard',
                'code' => 'socket',
                'data_type' => 'string',
                'unit' => null,
                'is_filterable' => true,
                'is_required' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Chipset',
                'slug' => 'chipset',
                'code' => 'chipset',
                'data_type' => 'string',
                'unit' => null,
                'is_filterable' => true,
                'is_required' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Form Factor',
                'slug' => 'form-factor',
                'code' => 'form_factor',
                'data_type' => 'string',
                'unit' => null,
                'is_filterable' => true,
                'is_required' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Thermal Design Power',
                'slug' => 'thermal-design-power',
                'code' => 'tdp_watts',
                'data_type' => 'number',
                'unit' => 'W',
                'is_filterable' => true,
                'is_required' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Memory Generation',
                'slug' => 'memory-generation',
                'code' => 'ram_generation',
                'data_type' => 'string',
                'unit' => null,
                'is_filterable' => true,
                'is_required' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'PCIe Generation',
                'slug' => 'pcie-generation',
                'code' => 'pcie_gen',
                'data_type' => 'string',
                'unit' => null,
                'is_filterable' => true,
                'is_required' => false,
                'sort_order' => 6,
            ],
        ];

        foreach ($attributes as $attr) {
            SpecificationAttribute::updateOrCreate(['code' => $attr['code']], $attr);
        }
    }
}
