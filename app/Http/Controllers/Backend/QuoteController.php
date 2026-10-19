<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\Pricing\DynamicPricingEngine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    public function __construct(
        protected DynamicPricingEngine $pricingEngine
    ) {}

    /**
     * Display a listing of RFQs and Quotes.
     */
    public function index(Request $request): View
    {
        $query = Quote::query()->with(['items', 'convertedOrder']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tier')) {
            $query->where('company_tier', $request->input('tier'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('client_email', 'like', "%{$search}%");
            });
        }

        $quotes = $query->latest('id')->paginate(15)->withQueryString();

        // Pipeline Metrics
        $totalQuotes = Quote::count();
        $pendingReview = Quote::where('status', 'pending_review')->count();
        $quotedCount = Quote::where('status', 'quoted')->count();
        $convertedCount = Quote::where('status', 'converted_to_order')->count();
        $totalPipelineValue = Quote::whereIn('status', ['pending_review', 'quoted', 'accepted', 'converted_to_order'])->sum('total_quoted_price');

        return view('backend.quotes.index', compact(
            'quotes',
            'totalQuotes',
            'pendingReview',
            'quotedCount',
            'convertedCount',
            'totalPipelineValue'
        ));
    }

    /**
     * Show form to submit / create a new Bill of Materials (BOM) RFQ.
     */
    public function create(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'sku', 'price', 'cost_price', 'stock_quantity'])
            ->orderBy('name')
            ->get();

        $tiers = array_keys(DynamicPricingEngine::TIER_DISCOUNTS);

        return view('backend.quotes.create', compact('products', 'tiers'));
    }

    /**
     * Store a newly created RFQ with its custom Bill of Materials.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'company_tier' => ['required', 'in:Standard,Gold,OEM'],
            'target_delivery_date' => ['nullable', 'date'],
            'client_notes' => ['nullable', 'string', 'max:2000'],
            'target_margin_percent' => ['nullable', 'numeric', 'min:1', 'max:90'],
            // BOM Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.item_sku' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.target_unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $quoteNumber = 'RFQ-'.date('Y').'-'.strtoupper(Str::random(6));
        $targetMargin = (float) ($validated['target_margin_percent'] ?? 15.00);

        $quote = Quote::create([
            'quote_number' => $quoteNumber,
            'client_name' => $validated['client_name'],
            'company_name' => $validated['company_name'],
            'client_email' => $validated['client_email'] ?? null,
            'client_phone' => $validated['client_phone'] ?? null,
            'company_tier' => $validated['company_tier'],
            'status' => 'pending_review',
            'target_delivery_date' => $validated['target_delivery_date'] ?? null,
            'client_notes' => $validated['client_notes'] ?? null,
            'target_margin_percent' => $targetMargin,
            'total_cost' => 0,
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_quoted_price' => 0,
        ]);

        $totalCost = 0;
        $totalQuoted = 0;

        foreach ($validated['items'] as $itemData) {
            $product = ! empty($itemData['product_id']) ? Product::find($itemData['product_id']) : null;
            $qty = (int) $itemData['quantity'];
            $cost = $product ? (float) ($product->cost_price ?? ($product->price * 0.70)) : 50.0;

            // Auto-calculate suggested quoted unit price using dynamic pricing engine
            if ($product) {
                $calc = $this->pricingEngine->calculate($product, $validated['company_tier'], $qty, $targetMargin);
                $unitPrice = $calc['unit_price'];
                $itemMargin = $calc['margin_percent'];
            } else {
                $unitPrice = round($cost / (1 - ($targetMargin / 100)), 2);
                $itemMargin = $targetMargin;
            }

            $lineTotal = round($unitPrice * $qty, 2);
            $totalCost += ($cost * $qty);
            $totalQuoted += $lineTotal;

            $quote->items()->create([
                'product_id' => $product?->id,
                'item_name' => $itemData['item_name'],
                'item_sku' => $itemData['item_sku'] ?? $product?->sku,
                'description' => $itemData['description'] ?? null,
                'quantity' => $qty,
                'target_unit_price' => isset($itemData['target_unit_price']) ? (float) $itemData['target_unit_price'] : null,
                'unit_cost' => $cost,
                'margin_percent' => $itemMargin,
                'quoted_unit_price' => $unitPrice,
                'total_quoted_price' => $lineTotal,
            ]);
        }

        $quote->update([
            'total_cost' => $totalCost,
            'subtotal' => $totalQuoted,
            'total_quoted_price' => $totalQuoted,
        ]);

        return redirect()->route('admin.quotes.show', $quote->id)
            ->with('success', "RFQ '{$quote->quote_number}' created successfully. You can now review and adjust custom margins.");
    }

    /**
     * Display the RFQ review & custom margin pricing dashboard.
     */
    public function show(Quote $quote): View
    {
        $quote->load(['items.product', 'convertedOrder']);

        return view('backend.quotes.show', compact('quote'));
    }

    /**
     * Update custom margin pricing on the BOM items.
     */
    public function updatePricing(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'target_margin_percent' => ['nullable', 'numeric', 'min:1', 'max:90'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array'],
            'items.*.quoted_unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.margin_percent' => ['nullable', 'numeric', 'min:0', 'max:95'],
        ]);

        $subtotal = 0;
        $totalCost = 0;

        foreach ($validated['items'] as $itemId => $itemData) {
            $item = QuoteItem::where('quote_id', $quote->id)->findOrFail($itemId);
            $unitPrice = (float) $itemData['quoted_unit_price'];
            $margin = isset($itemData['margin_percent']) ? (float) $itemData['margin_percent'] : $item->margin_percent;
            $lineTotal = round($unitPrice * $item->quantity, 2);

            $item->update([
                'quoted_unit_price' => $unitPrice,
                'margin_percent' => $margin,
                'total_quoted_price' => $lineTotal,
            ]);

            $subtotal += $lineTotal;
            $totalCost += ($item->unit_cost * $item->quantity);
        }

        $quote->update([
            'target_margin_percent' => $validated['target_margin_percent'] ?? $quote->target_margin_percent,
            'admin_notes' => $validated['admin_notes'] ?? $quote->admin_notes,
            'total_cost' => $totalCost,
            'subtotal' => $subtotal,
            'total_quoted_price' => $subtotal,
        ]);

        return back()->with('success', 'Quote pricing and margins updated successfully. Total: $'.number_format($subtotal, 2));
    }

    /**
     * Send quote back to client and lock with validity date.
     */
    public function sendQuote(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'valid_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $days = (int) ($validated['valid_days'] ?? 14);

        $quote->update([
            'status' => 'quoted',
            'quoted_at' => now(),
            'valid_until' => now()->addDays($days),
            'admin_notes' => $validated['admin_notes'] ?? $quote->admin_notes,
        ]);

        return back()->with('success', "Quote '{$quote->quote_number}' has been finalized and sent to {$quote->company_name}. Valid until ".$quote->valid_until->format('M d, Y').'.');
    }

    /**
     * Convert an accepted quote into an active customer Order.
     */
    public function convertToOrder(Quote $quote): RedirectResponse
    {
        if ($quote->converted_order_id) {
            return redirect()->route('admin.orders.show', $quote->converted_order_id)
                ->with('info', 'Quote was already converted to Order.');
        }

        $orderNumber = 'ORD-'.date('Y').'-'.strtoupper(Str::random(6));

        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_name' => $quote->client_name,
            'customer_email' => $quote->client_email ?? 'wholesale@'.Str::slug($quote->company_name).'.local',
            'customer_phone' => $quote->client_phone,
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'payment_method' => 'Bank Wire / Terms',
            'subtotal' => $quote->subtotal,
            'tax_amount' => $quote->tax_amount,
            'shipping_fee' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => $quote->total_quoted_price,
            'shipping_address' => [
                'recipient' => $quote->client_name,
                'company' => $quote->company_name,
                'street' => 'Commercial Delivery Address',
                'city' => 'Phnom Penh',
                'country' => 'Cambodia',
            ],
            'customer_notes' => "Converted from Quote {$quote->quote_number}. {$quote->client_notes}",
            'admin_notes' => "Approved B2B Quote with {$quote->target_margin_percent}% custom margin.",
        ]);

        foreach ($quote->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->item_name,
                'product_sku' => $item->item_sku ?? 'SKU-GEN',
                'unit_price' => $item->quoted_unit_price,
                'quantity' => $item->quantity,
                'total_price' => $item->total_quoted_price,
            ]);
        }

        $quote->update([
            'status' => 'converted_to_order',
            'converted_order_id' => $order->id,
        ]);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', "Quote '{$quote->quote_number}' successfully converted to Order #{$order->order_number}!");
    }
}
