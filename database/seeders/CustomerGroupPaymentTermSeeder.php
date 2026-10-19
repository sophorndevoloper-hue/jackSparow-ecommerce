<?php

namespace Database\Seeders;

use App\Models\CustomerGroup;
use App\Models\CustomerGroupPaymentTerm;
use Illuminate\Database\Seeder;

class CustomerGroupPaymentTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultTerms = [
            [
                'customer_group_id' => null,
                'name' => 'Immediate / Cash on Delivery (COD)',
                'code' => 'COD',
                'days' => 0,
                'description' => 'Payment due immediately upon delivery or at time of purchase.',
                'early_payment_discount_percentage' => 0.00,
                'early_payment_days' => 0,
                'late_fee_percentage' => 0.00,
                'require_deposit' => false,
                'deposit_percentage' => 0.00,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'customer_group_id' => null,
                'name' => 'Net 15 Days',
                'code' => 'NET_15',
                'days' => 15,
                'description' => 'Full payment is due within 15 calendar days from invoice date.',
                'early_payment_discount_percentage' => 0.00,
                'early_payment_days' => 0,
                'late_fee_percentage' => 1.50,
                'require_deposit' => false,
                'deposit_percentage' => 0.00,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'customer_group_id' => null,
                'name' => 'Net 30 Days',
                'code' => 'NET_30',
                'days' => 30,
                'description' => 'Standard wholesale B2B payment term. Due within 30 days of invoice.',
                'early_payment_discount_percentage' => 0.00,
                'early_payment_days' => 0,
                'late_fee_percentage' => 2.00,
                'require_deposit' => false,
                'deposit_percentage' => 0.00,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'customer_group_id' => null,
                'name' => '2/10 Net 30',
                'code' => '2_10_NET_30',
                'days' => 30,
                'description' => '2% cash discount if payment is settled within 10 days; full balance due in 30 days.',
                'early_payment_discount_percentage' => 2.00,
                'early_payment_days' => 10,
                'late_fee_percentage' => 2.00,
                'require_deposit' => false,
                'deposit_percentage' => 0.00,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'customer_group_id' => null,
                'name' => 'Net 60 Days (Enterprise)',
                'code' => 'NET_60',
                'days' => 60,
                'description' => 'Extended terms for qualified enterprise customer accounts.',
                'early_payment_discount_percentage' => 1.00,
                'early_payment_days' => 15,
                'late_fee_percentage' => 2.50,
                'require_deposit' => false,
                'deposit_percentage' => 0.00,
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($defaultTerms as $term) {
            CustomerGroupPaymentTerm::firstOrCreate(
                ['code' => $term['code'], 'customer_group_id' => $term['customer_group_id']],
                $term
            );
        }

        // Also associate terms with existing groups if available
        $groups = CustomerGroup::all();
        foreach ($groups as $group) {
            if ($group->payment_terms_days > 0) {
                CustomerGroupPaymentTerm::firstOrCreate(
                    [
                        'code' => strtoupper($group->code).'_NET_'.$group->payment_terms_days,
                        'customer_group_id' => $group->id,
                    ],
                    [
                        'name' => "{$group->name} - Net {$group->payment_terms_days} Days",
                        'days' => $group->payment_terms_days,
                        'description' => "Dedicated payment terms configured for {$group->name}.",
                        'early_payment_discount_percentage' => $group->default_discount_percentage > 5 ? 1.50 : 0.00,
                        'early_payment_days' => 10,
                        'late_fee_percentage' => 1.50,
                        'require_deposit' => false,
                        'deposit_percentage' => 0.00,
                        'is_default' => true,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
