<?php

namespace App\Services\Pricing;

use App\Models\Product;

class DynamicPricingEngine
{
    /**
     * Tier discount definitions (percentage off base price).
     */
    public const TIER_DISCOUNTS = [
        'Standard' => 0.00,
        'Gold' => 10.00,
        'OEM' => 20.00,
    ];

    /**
     * Quantity break volume discounts (percentage off tier unit price).
     */
    public const QUANTITY_BREAKS = [
        [
            'min' => 200,
            'max' => null,
            'label' => '200+ units (Bulk Pallet)',
            'discount_percent' => 12.00,
        ],
        [
            'min' => 50,
            'max' => 199,
            'label' => '50 - 199 units (Case Pack)',
            'discount_percent' => 5.00,
        ],
        [
            'min' => 1,
            'max' => 49,
            'label' => '1 - 49 units (Standard Order)',
            'discount_percent' => 0.00,
        ],
    ];

    /**
     * Calculate dynamic unit price and totals for a product.
     *
     * @param  Product|array{price: float, cost_price?: float, name?: string, sku?: string}  $product
     * @param  string  $tier  Standard|Gold|OEM
     * @param  int  $quantity  Quantity ordered
     * @param  float|null  $customMarginPercent  Optional target margin override
     * @return array<string, mixed>
     */
    public function calculate(Product|array $product, string $tier = 'Standard', int $quantity = 1, ?float $customMarginPercent = null): array
    {
        $basePrice = $product instanceof Product ? (float) $product->price : (float) ($product['price'] ?? 0.0);
        $costPrice = $product instanceof Product ? (float) ($product->cost_price ?? ($basePrice * 0.70)) : (float) ($product['cost_price'] ?? ($basePrice * 0.70));
        $name = $product instanceof Product ? $product->name : ($product['name'] ?? 'Product');
        $sku = $product instanceof Product ? $product->sku : ($product['sku'] ?? 'N/A');

        $quantity = max(1, $quantity);
        $tier = array_key_exists($tier, self::TIER_DISCOUNTS) ? $tier : 'Standard';

        // 1. If custom margin override is requested (BOM RFQ custom pricing mode)
        if ($customMarginPercent !== null && $costPrice > 0) {
            $marginFraction = min(0.95, max(0.01, $customMarginPercent / 100));
            $unitPrice = round($costPrice / (1 - $marginFraction), 2);

            // Apply volume discount to custom margin if applicable
            $break = $this->getQuantityBreak($quantity);
            $volumeDiscountPercent = $break['discount_percent'];
            if ($volumeDiscountPercent > 0) {
                $unitPrice = round($unitPrice * (1 - ($volumeDiscountPercent / 100)), 2);
            }

            $totalPrice = round($unitPrice * $quantity, 2);
            $totalCost = round($costPrice * $quantity, 2);
            $profit = max(0, $totalPrice - $totalCost);
            $effectiveMargin = $totalPrice > 0 ? round(($profit / $totalPrice) * 100, 2) : 0.0;

            return [
                'product_name' => $name,
                'sku' => $sku,
                'base_price' => $basePrice,
                'cost_price' => $costPrice,
                'tier' => $tier,
                'tier_discount_percent' => 0.0,
                'quantity' => $quantity,
                'volume_bracket' => $break['label'],
                'volume_discount_percent' => $volumeDiscountPercent,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'total_cost' => $totalCost,
                'profit' => $profit,
                'margin_percent' => $effectiveMargin,
                'savings_amount' => round(max(0, ($basePrice * $quantity) - $totalPrice), 2),
            ];
        }

        // 2. Standard Dynamic Tier & Quantity Break Pricing
        $tierDiscountPercent = self::TIER_DISCOUNTS[$tier] ?? 0.0;
        $tierUnitPrice = $basePrice * (1 - ($tierDiscountPercent / 100));

        $break = $this->getQuantityBreak($quantity);
        $volumeDiscountPercent = $break['discount_percent'];

        $finalUnitPrice = round($tierUnitPrice * (1 - ($volumeDiscountPercent / 100)), 2);
        $totalPrice = round($finalUnitPrice * $quantity, 2);
        $totalCost = round($costPrice * $quantity, 2);
        $profit = max(0, $totalPrice - $totalCost);
        $marginPercent = $totalPrice > 0 ? round(($profit / $totalPrice) * 100, 2) : 0.0;
        $regularTotal = round($basePrice * $quantity, 2);
        $savingsAmount = max(0, round($regularTotal - $totalPrice, 2));

        return [
            'product_name' => $name,
            'sku' => $sku,
            'base_price' => $basePrice,
            'cost_price' => $costPrice,
            'tier' => $tier,
            'tier_discount_percent' => $tierDiscountPercent,
            'tier_unit_price' => round($tierUnitPrice, 2),
            'quantity' => $quantity,
            'volume_bracket' => $break['label'],
            'volume_discount_percent' => $volumeDiscountPercent,
            'unit_price' => $finalUnitPrice,
            'total_price' => $totalPrice,
            'total_cost' => $totalCost,
            'profit' => $profit,
            'margin_percent' => $marginPercent,
            'savings_amount' => $savingsAmount,
        ];
    }

    /**
     * Get the quantity break rule for a quantity.
     *
     * @return array{min: int, max: int|null, label: string, discount_percent: float}
     */
    public function getQuantityBreak(int $quantity): array
    {
        foreach (self::QUANTITY_BREAKS as $break) {
            if ($quantity >= $break['min']) {
                return $break;
            }
        }

        return self::QUANTITY_BREAKS[count(self::QUANTITY_BREAKS) - 1];
    }
}
