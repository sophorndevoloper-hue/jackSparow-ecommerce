<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Pricing\DynamicPricingEngine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingEngineController extends Controller
{
    public function __construct(
        protected DynamicPricingEngine $pricingEngine
    ) {}

    /**
     * Display the Dynamic Pricing Engine dashboard & matrix.
     */
    public function index(Request $request): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'sku', 'price', 'cost_price', 'stock_quantity'])
            ->orderBy('name')
            ->get();

        $selectedProduct = null;
        $calculation = null;

        if ($request->filled('product_id')) {
            $selectedProduct = $products->firstWhere('id', (int) $request->input('product_id'));
        } elseif ($products->isNotEmpty()) {
            $selectedProduct = $products->first();
        }

        $tier = $request->input('tier', 'Gold');
        $quantity = (int) $request->input('quantity', 50);
        $customMargin = $request->filled('custom_margin') ? (float) $request->input('custom_margin') : null;

        if ($selectedProduct) {
            $calculation = $this->pricingEngine->calculate($selectedProduct, $tier, $quantity, $customMargin);
        }

        $tiers = DynamicPricingEngine::TIER_DISCOUNTS;
        $breaks = DynamicPricingEngine::QUANTITY_BREAKS;

        return view('backend.pricing.index', compact(
            'products',
            'selectedProduct',
            'calculation',
            'tier',
            'quantity',
            'customMargin',
            'tiers',
            'breaks'
        ));
    }

    /**
     * JSON calculation API for real-time calculator & quotes.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'tier' => ['required', 'in:Standard,Gold,OEM'],
            'quantity' => ['required', 'integer', 'min:1'],
            'custom_margin' => ['nullable', 'numeric', 'min:1', 'max:90'],
        ]);

        if (! empty($validated['product_id'])) {
            $product = Product::findOrFail($validated['product_id']);
        } else {
            $product = [
                'name' => 'Custom Item',
                'sku' => 'CUSTOM',
                'price' => (float) ($validated['base_price'] ?? 100.0),
                'cost_price' => (float) ($validated['cost_price'] ?? 70.0),
            ];
        }

        $customMargin = isset($validated['custom_margin']) && $validated['custom_margin'] !== null
            ? (float) $validated['custom_margin']
            : null;

        $result = $this->pricingEngine->calculate(
            $product,
            $validated['tier'],
            (int) $validated['quantity'],
            $customMargin
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
