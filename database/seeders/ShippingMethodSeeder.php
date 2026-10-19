<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Standard Ground Delivery',
                'code' => 'standard',
                'carrier' => 'FedEx Ground',
                'base_cost' => 15.00,
                'min_order_amount' => 150.00,
                'estimated_days' => '3 - 5 business days',
                'description' => 'Reliable ground shipping for hardware components. Free on orders above $150.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Express Priority Air',
                'code' => 'express',
                'carrier' => 'DHL Express',
                'base_cost' => 35.00,
                'min_order_amount' => null,
                'estimated_days' => '1 - 2 business days',
                'description' => 'Expedited air freight with signature delivery and priority handling.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Overnight Next-Day Air',
                'code' => 'overnight',
                'carrier' => 'UPS Next Day Air',
                'base_cost' => 55.00,
                'min_order_amount' => null,
                'estimated_days' => 'Next business day',
                'description' => 'Fastest delivery option for critical PC components and custom build hardware.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Local Warehouse Pickup',
                'code' => 'pickup',
                'carrier' => 'Store Pickup',
                'base_cost' => 0.00,
                'min_order_amount' => null,
                'estimated_days' => 'Ready in 2 hours',
                'description' => 'Collect your hardware directly at our main facility during working hours.',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $data) {
            ShippingMethod::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        // Seed deliveries for sample orders
        $standardMethod = ShippingMethod::where('code', 'standard')->first();
        $expressMethod = ShippingMethod::where('code', 'express')->first();

        $orders = Order::take(5)->get();
        foreach ($orders as $index => $order) {
            if ($order->deliveries()->count() === 0) {
                $statusList = ['delivered', 'dispatched', 'in_transit', 'preparing', 'pending'];
                $status = $statusList[$index % count($statusList)];
                $method = $index % 2 === 0 ? $standardMethod : $expressMethod;

                $order->update(['shipping_method_id' => $method?->id]);

                Delivery::create([
                    'delivery_number' => 'DEL-'.date('Y').'-'.str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'order_id' => $order->id,
                    'shipping_method_id' => $method?->id,
                    'tracking_number' => 'TRK-'.strtoupper($method?->carrier ? substr($method->carrier, 0, 3) : 'SHP').'-'.rand(100000, 999999),
                    'carrier' => $method?->carrier ?? 'FedEx Ground',
                    'status' => $status,
                    'recipient_name' => $order->customer_name ?: 'Valued Customer',
                    'recipient_phone' => $order->customer_phone ?: '+1 (555) 019-2834',
                    'recipient_email' => $order->customer_email ?: 'customer@store.test',
                    'shipping_address' => $order->shipping_address ?: [
                        'recipient' => $order->customer_name,
                        'street' => '100 Silicon Blvd',
                        'city' => 'San Jose',
                        'state' => 'CA',
                        'postal_code' => '95134',
                        'country' => 'United States',
                    ],
                    'estimated_delivery_date' => now()->addDays(3)->toDateString(),
                    'dispatched_at' => in_array($status, ['dispatched', 'in_transit', 'delivered']) ? now()->subDays(2) : null,
                    'delivered_at' => $status === 'delivered' ? now()->subDay() : null,
                    'notes' => 'Handle with care - fragile gaming hardware and sensitive electronics.',
                ]);
            }
        }
    }
}
