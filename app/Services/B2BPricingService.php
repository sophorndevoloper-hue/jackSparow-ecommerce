<?php

namespace App\Services;

use App\Models\CustomerGroup;
use App\Models\FrontendUser;
use App\Models\Product;
use App\Models\ProductPriceTier;
use InvalidArgumentException;

class B2BPricingService
{
    /**
     * Calculate comprehensive B2B line item pricing, quantity validation, and margin safeguards.
     *
     * @return array{
     *     product_id: int,
     *     sku: string,
     *     quantity: int,
     *     is_moq_satisfied: bool,
     *     is_case_pack_satisfied: bool,
     *     base_price: float,
     *     effective_unit_price: float,
     *     subtotal: float,
     *     total_savings: float,
     *     savings_percentage: float,
     *     tier_applied: string|null,
     *     floor_price: float,
     *     is_floor_guarded: bool,
     *     customer_group: string|null
     * }
     */
    public function calculateLineItemPricing(Product $product, int $quantity, CustomerGroup|FrontendUser|null $customerOrGroup = null): array
    {
        $group = $customerOrGroup instanceof FrontendUser ? $customerOrGroup->customerGroup : $customerOrGroup;

        // 1. Validate MOQ & Case Pack
        $moq = max(1, (int) ($product->moq ?? 1));
        $casePack = max(1, (int) ($product->case_pack_multiple ?? 1));

        $isMoqSatisfied = $quantity >= $moq;
        $isCasePackSatisfied = ($quantity % $casePack) === 0;

        $basePrice = (float) ($product->sale_price ?: $product->price);
        $floorPrice = $product->getFloorPrice();

        $tierApplied = null;
        $unitPrice = $basePrice;

        // 2. Query matching tiers
        if ($group) {
            $groupTier = ProductPriceTier::where('product_id', $product->id)
                ->where('customer_group_id', $group->id)
                ->where('min_quantity', '<=', $quantity)
                ->where(function ($q) use ($quantity) {
                    $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
                })
                ->orderByDesc('min_quantity')
                ->first();

            if ($groupTier) {
                $unitPrice = (float) $groupTier->unit_price;
                $tierApplied = "Group ({$group->name}) Tier [{$groupTier->min_quantity}+]";
            }
        }

        if ($tierApplied === null) {
            $generalTier = ProductPriceTier::where('product_id', $product->id)
                ->whereNull('customer_group_id')
                ->where('min_quantity', '<=', $quantity)
                ->where(function ($q) use ($quantity) {
                    $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
                })
                ->orderByDesc('min_quantity')
                ->first();

            if ($generalTier) {
                $unitPrice = (float) $generalTier->unit_price;
                $tierApplied = "Volume Break [{$generalTier->min_quantity}+]";
            }
        }

        // 3. Apply Group default discount % if no custom tier found
        if ($tierApplied === null && $group && (float) $group->default_discount_percentage > 0) {
            $discountRate = (float) $group->default_discount_percentage / 100;
            $unitPrice = round($basePrice * (1 - $discountRate), 2);
            $tierApplied = "Group Discount ({$group->default_discount_percentage}%)";
        }

        // 4. Margin Floor Guard Protection
        $isFloorGuarded = false;
        if ($floorPrice > 0 && $unitPrice < $floorPrice) {
            $unitPrice = $floorPrice;
            $isFloorGuarded = true;
            $tierApplied = ($tierApplied ? $tierApplied.' + ' : '').'Margin Floor Protected';
        }

        $subtotal = round($unitPrice * $quantity, 2);
        $regularSubtotal = round($basePrice * $quantity, 2);
        $totalSavings = max(0.00, round($regularSubtotal - $subtotal, 2));
        $savingsPercentage = $regularSubtotal > 0 ? round(($totalSavings / $regularSubtotal) * 100, 2) : 0.00;

        return [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'quantity' => $quantity,
            'is_moq_satisfied' => $isMoqSatisfied,
            'is_case_pack_satisfied' => $isCasePackSatisfied,
            'base_price' => $basePrice,
            'effective_unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'total_savings' => $totalSavings,
            'savings_percentage' => $savingsPercentage,
            'tier_applied' => $tierApplied,
            'floor_price' => $floorPrice,
            'is_floor_guarded' => $isFloorGuarded,
            'customer_group' => $group?->name,
        ];
    }

    /**
     * Set up or update a pricing tier for a product.
     */
    public function upsertTier(Product $product, int $minQty, ?int $maxQty, float $unitPrice, ?int $customerGroupId = null, string $currency = 'USD'): ProductPriceTier
    {
        if ($minQty < 1) {
            throw new InvalidArgumentException('Minimum quantity must be at least 1.');
        }

        if ($maxQty !== null && $maxQty < $minQty) {
            throw new InvalidArgumentException('Maximum quantity cannot be less than minimum quantity.');
        }

        return ProductPriceTier::updateOrCreate(
            [
                'product_id' => $product->id,
                'customer_group_id' => $customerGroupId,
                'min_quantity' => $minQty,
            ],
            [
                'max_quantity' => $maxQty,
                'unit_price' => $unitPrice,
                'currency' => $currency,
            ]
        );
    }
}
