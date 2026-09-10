<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
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

        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $tax = round($subtotal * 0.08, 2);
        $shipping = $subtotal > 0 ? ($subtotal >= 150 ? 0.00 : 15.00) : 0.00;
        $total = $subtotal + $tax + $shipping;

        return view('frontend.cart', compact('cart', 'subtotal', 'tax', 'shipping', 'total'));
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = (int) $request->input('quantity', 1);

        if ($product->stock_quantity <= 0) {
            return back()->with('error', 'Sorry, "'.$product->name.'" is out of stock.');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $newQuantity = $cart[$product->id]['quantity'] + $quantity;
            if ($newQuantity > $product->stock_quantity) {
                return back()->with('error', 'Only '.$product->stock_quantity.' units available in stock.');
            }
            $cart[$product->id]['quantity'] = $newQuantity;
        } else {
            if ($quantity > $product->stock_quantity) {
                return back()->with('error', 'Only '.$product->stock_quantity.' units available in stock.');
            }

            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => (float) $product->effective_price,
                'image' => $product->primaryImage?->image_path,
                'quantity' => $quantity,
                'stock_quantity' => $product->stock_quantity,
                'category_name' => $product->category->name,
                'brand_name' => $product->brand?->name,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Added "'.$product->name.'" to your cart!');
    }

    /**
     * Update item quantity in the cart.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quantity = (int) $request->input('quantity');
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            if ($quantity > $product->stock_quantity) {
                return back()->with('error', 'Maximum available stock is '.$product->stock_quantity.' units.');
            }

            $cart[$product->id]['quantity'] = $quantity;
            session()->put('cart', $cart);

            return back()->with('success', 'Cart updated successfully.');
        }

        return back()->with('error', 'Item not found in cart.');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Product $product): RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session()->put('cart', $cart);

            return back()->with('success', 'Item removed from your cart.');
        }

        return back()->with('error', 'Item not found in cart.');
    }

    /**
     * Clear all items from cart.
     */
    public function clear(): RedirectResponse
    {
        session()->forget('cart');

        return redirect()->route('cart.index')->with('success', 'Cart emptied.');
    }
}
