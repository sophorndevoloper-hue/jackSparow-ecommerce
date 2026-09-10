<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 1500);
        $tax = round($subtotal * 0.08, 2);
        $shipping = 15.00;
        $total = $subtotal + $tax + $shipping;

        return [
            'order_number' => 'ORD-'.strtoupper(fake()->unique()->bothify('####-????')),
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered']),
            'payment_status' => fake()->randomElement(['pending', 'paid']),
            'payment_method' => fake()->randomElement(['cash_on_delivery', 'credit_card', 'bank_transfer']),
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'shipping_fee' => $shipping,
            'discount_amount' => 0.00,
            'total_amount' => $total,
            'shipping_address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => 'United States',
            ],
            'billing_address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => 'United States',
            ],
            'customer_notes' => fake()->optional()->sentence(),
            'admin_notes' => null,
        ];
    }
}
