<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Cash on Delivery (COD)',
                'code' => 'cash_on_delivery',
                'description' => 'Pay in cash directly to the courier upon receiving and inspecting your package.',
                'instructions' => 'Please have the exact amount ready upon delivery. Our couriers carry receipt slips.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Credit / Debit Card',
                'code' => 'credit_card',
                'description' => 'Instant secure online payment via Visa, Mastercard, or American Express with 256-bit SSL encryption.',
                'instructions' => 'Your card will be charged immediately upon order confirmation. 3D-Secure authentication may be required.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Direct Bank Wire Transfer',
                'code' => 'bank_transfer',
                'description' => 'Direct corporate bank transfer for large purchases and business workstations.',
                'instructions' => "Bank: Silicon Valley Commerce Bank\nAccount Name: JackSparow Hardware Corp\nAccount Number: 9872-3498-1102\nRouting Number: 121000358\nReference: Use your Order Number as payment reference.",
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'ABA PAY / KHQR',
                'code' => 'aba_pay',
                'description' => 'Fast mobile payment via ABA PAY or any bank app supporting KHQR standard.',
                'instructions' => 'Scan the KHQR code presented during checkout with your banking app to authorize payment instantly.',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $data) {
            PaymentMethod::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
