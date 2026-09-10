<?php

namespace Database\Seeders;

use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;

class CustomerGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'Wholesale Tier 1',
                'slug' => 'wholesale-tier-1',
                'code' => 'WHOLESALE_1',
                'description' => 'Standard high-volume hardware distributor tier.',
                'default_discount_percentage' => 15.00,
                'min_order_amount' => 1000.00,
                'credit_limit' => 25000.00,
                'payment_terms_days' => 30,
                'tax_exempt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'System Integrators & VARs',
                'slug' => 'system-integrators',
                'code' => 'SYSTEM_INTEGRATOR',
                'description' => 'Custom PC OEM builders and Value Added Resellers.',
                'default_discount_percentage' => 12.50,
                'min_order_amount' => 500.00,
                'credit_limit' => 15000.00,
                'payment_terms_days' => 30,
                'tax_exempt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise Direct',
                'slug' => 'enterprise-direct',
                'code' => 'ENTERPRISE',
                'description' => 'Corporate IT and data center infrastructure accounts.',
                'default_discount_percentage' => 18.00,
                'min_order_amount' => 5000.00,
                'credit_limit' => 100000.00,
                'payment_terms_days' => 60,
                'tax_exempt' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Retail / Small Business',
                'slug' => 'retail-smb',
                'code' => 'RETAIL_SMB',
                'description' => 'Small computer shops and verified repair businesses.',
                'default_discount_percentage' => 5.00,
                'min_order_amount' => 0.00,
                'credit_limit' => 2000.00,
                'payment_terms_days' => 0,
                'tax_exempt' => false,
                'is_active' => true,
            ],
        ];

        foreach ($groups as $group) {
            CustomerGroup::updateOrCreate(['code' => $group['code']], $group);
        }
    }
}
