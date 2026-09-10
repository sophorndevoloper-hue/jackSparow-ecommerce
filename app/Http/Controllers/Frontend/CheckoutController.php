<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FrontendUser;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /**
     * Display the checkout screen.
     */
    public function index(): View|RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your shopping cart is empty.');
        }

        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $tax = round($subtotal * 0.08, 2);
        $shipping = $subtotal >= 150 ? 0.00 : 15.00;
        $total = $subtotal + $tax + $shipping;

        $user = auth()->user();

        return view('frontend.checkout', compact('cart', 'subtotal', 'tax', 'shipping', 'total', 'user'));
    }

    /**
     * Process checkout, place order, and decrement product inventory.
     */
    public function process(Request $request): RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'in:cash_on_delivery,credit_card,bank_transfer'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($validated, $cart) {
            $subtotal = 0.0;

            // Verify stock availability
            foreach ($cart as $productId => $item) {
                $product = Product::lockForUpdate()->find($productId);
                if (! $product || $product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => 'Product "'.$item['name'].'" has insufficient stock available ('.$product?->stock_quantity.' remaining).',
                    ]);
                }
                $subtotal += $item['price'] * $item['quantity'];
            }

            $tax = round($subtotal * 0.08, 2);
            $shipping = $subtotal >= 150 ? 0.00 : 15.00;
            $total = $subtotal + $tax + $shipping;

            $shippingAddress = [
                'recipient' => $validated['customer_name'],
                'street' => $validated['street'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
            ];

            $frontendUser = auth('frontend')->user();
            if (! $frontendUser && ! empty($validated['customer_email'])) {
                $frontendUser = FrontendUser::where('email', $validated['customer_email'])->first();
            }

            $orderNumber = 'ORD-'.date('Ymd').'-'.strtoupper(str()->random(5));

            $order = Order::create([
                'order_number' => $orderNumber,
                'frontend_user_id' => $frontendUser?->id,
                'customer_id' => $frontendUser?->id,
                'user_id' => auth('backend')->check() ? auth('backend')->id() : null,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'status' => 'pending',
                'payment_status' => $validated['payment_method'] === 'credit_card' ? 'paid' : 'pending',
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'shipping_fee' => $shipping,
                'discount_amount' => 0.00,
                'total_amount' => $total,
                'shipping_address' => $shippingAddress,
                'billing_address' => $shippingAddress,
                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            if ($frontendUser) {
                $frontendUser->increment('orders_count');
                $frontendUser->increment('total_spent', $total);
                $frontendUser->checkAndPromote();
            }

            foreach ($cart as $productId => $item) {
                $product = Product::find($productId);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total_price' => round($item['price'] * $item['quantity'], 2),
                    'specifications_snapshot' => $product->specifications,
                ]);

                // Decrement stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // Clear session cart
            session()->forget('cart');

            return redirect()->route('order.success', $order->order_number)
                ->with('success', 'Thank you! Your computer parts order has been placed successfully.');
        });
    }
}
