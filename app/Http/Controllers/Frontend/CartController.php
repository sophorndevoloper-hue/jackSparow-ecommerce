<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the shopping cart.
     */
    public function index(): View
    {
        $cart = session()->get('cart', []);
        $totals = $this->calculateTotals($cart);

        return view('frontend.cart', array_merge(['cart' => $cart], $totals));
    }

    /**
     * Calculate cart subtotal, tax, shipping, and grand total.
     *
     * @param  array<int|string, array<string, mixed>>  $cart
     * @return array<string, mixed>
     */
    protected function calculateTotals(array $cart): array
    {
        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += ((float) $item['price']) * ((int) $item['quantity']);
        }

        $tax = round($subtotal * 0.08, 2);
        $shipping = $subtotal > 0 ? ($subtotal >= 150 ? 0.00 : 15.00) : 0.00;
        $total = $subtotal + $tax + $shipping;
        $count = array_sum(array_column($cart, 'quantity'));

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
            'cart_count' => $count,
            'formatted_subtotal' => '$'.number_format($subtotal, 2),
            'formatted_tax' => '$'.number_format($tax, 2),
            'formatted_shipping' => $shipping == 0 ? 'FREE (OVER $150)' : '$'.number_format($shipping, 2),
            'formatted_total' => '$'.number_format($total, 2),
        ];
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = (int) $request->input('quantity', 1);

        if ($product->stock_quantity <= 0) {
            $msg = 'Sorry, "'.$product->name.'" is out of stock.';
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('error', $msg);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $newQuantity = $cart[$product->id]['quantity'] + $quantity;
            if ($newQuantity > $product->stock_quantity) {
                $msg = 'Only '.$product->stock_quantity.' units available in stock.';
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 422);
                }

                return back()->with('error', $msg);
            }
            $cart[$product->id]['quantity'] = $newQuantity;
        } else {
            if ($quantity > $product->stock_quantity) {
                $msg = 'Only '.$product->stock_quantity.' units available in stock.';
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 422);
                }

                return back()->with('error', $msg);
            }

            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => (float) $product->effective_price,
                'image' => $product->primaryImage?->image_path,
                'image_url' => $product->primary_image_url,
                'quantity' => $quantity,
                'stock_quantity' => $product->stock_quantity,
                'category_name' => $product->category->name,
                'brand_name' => $product->brand?->name,
            ];
        }

        session()->put('cart', $cart);

        $cartCount = array_sum(array_column($cart, 'quantity'));
        $successMessage = 'Added "'.$product->name.'" to your cart!';

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'cart_count' => $cartCount,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->effective_price,
                ],
            ]);
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Update item quantity in the cart.
     */
    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quantity = (int) $request->input('quantity');
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            if ($quantity > $product->stock_quantity) {
                $errorMessage = 'Maximum available stock is '.$product->stock_quantity.' units.';
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'stock_quantity' => $product->stock_quantity,
                        'current_quantity' => $cart[$product->id]['quantity'],
                    ], 422);
                }

                return back()->with('error', $errorMessage);
            }

            $cart[$product->id]['quantity'] = $quantity;
            session()->put('cart', $cart);

            $totals = $this->calculateTotals($cart);
            $itemTotal = ((float) $cart[$product->id]['price']) * $quantity;
            $successMessage = 'Updated quantity to '.$quantity.' for '.$product->name.'.';

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json(array_merge([
                    'success' => true,
                    'message' => $successMessage,
                    'item_id' => $product->id,
                    'quantity' => $quantity,
                    'item_total' => $itemTotal,
                    'formatted_item_total' => '$'.number_format($itemTotal, 2),
                ], $totals));
            }

            return back()->with('success', 'Cart updated successfully.');
        }

        $notFoundMessage = 'Item not found in cart.';
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $notFoundMessage,
            ], 404);
        }

        return back()->with('error', $notFoundMessage);
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session()->put('cart', $cart);

            $totals = $this->calculateTotals($cart);
            $successMessage = 'Removed "'.$product->name.'" from your cart.';

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json(array_merge([
                    'success' => true,
                    'message' => $successMessage,
                    'removed_id' => $product->id,
                    'is_empty' => empty($cart),
                ], $totals));
            }

            return back()->with('success', $successMessage);
        }

        $notFoundMessage = 'Item not found in cart.';
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $notFoundMessage,
            ], 404);
        }

        return back()->with('error', $notFoundMessage);
    }

    /**
     * Clear all items from cart.
     */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        session()->forget('cart');

        $message = 'Your shopping cart has been emptied.';

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => 0,
                'is_empty' => true,
            ]);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }
}
