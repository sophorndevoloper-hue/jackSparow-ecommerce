<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Backend\CustomerGroupPaymentTerm;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Services\SerialTrackingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /**
     * Display the checkout screen.
     */
    public function index(): View|RedirectResponse
    {
        if (! auth('frontend')->check()) {
            return redirect()->guest(route('login'))->with('info', 'Please sign in to proceed with checkout.');
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            if ($lastOrder = session()->get('last_order_number')) {
                return redirect()->route('order.success', $lastOrder);
            }

            return redirect()->route('cart.index');
        }

        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $tax = round($subtotal * 0.08, 2);
        $shipping = $subtotal >= 150 ? 0.00 : 15.00;
        $total = $subtotal + $tax + $shipping;

        $user = auth('frontend')->user();
        $customerGroup = $user?->customerGroup;

        $eligiblePaymentTerms = collect();
        $availableCredit = 0.00;
        $creditLimit = 0.00;
        $outstandingCredit = 0.00;

        if ($user && $customerGroup) {
            $eligiblePaymentTerms = CustomerGroupPaymentTerm::query()
                ->where('is_active', true)
                ->where(function ($q) use ($customerGroup) {
                    $q->where('customer_group_id', $customerGroup->id)
                        ->orWhereNull('customer_group_id');
                })
                ->orderByRaw('customer_group_id IS NOT NULL DESC')
                ->orderBy('days')
                ->get();

            $creditLimit = (float) ($user->credit_limit > 0 ? $user->credit_limit : ($customerGroup->credit_limit ?? 0));
            $outstandingCredit = (float) Order::where('frontend_user_id', $user->id)
                ->whereIn('payment_status', ['pending', 'partially_paid'])
                ->sum('total_amount');

            $availableCredit = max(0.00, round($creditLimit - $outstandingCredit, 2));
        }

        $paymentMethods = PaymentMethod::query()
            ->active()
            ->ordered()
            ->get();

        return view('frontend.checkout', compact(
            'cart',
            'subtotal',
            'tax',
            'shipping',
            'total',
            'user',
            'customerGroup',
            'paymentMethods',
            'eligiblePaymentTerms',
            'availableCredit',
            'creditLimit',
            'outstandingCredit'
        ));
    }

    /**
     * Process checkout, place order, and decrement product inventory.
     */
    public function process(Request $request): RedirectResponse
    {
        if (! auth('frontend')->check()) {
            return redirect()->guest(route('login'))->with('info', 'Please sign in to complete your checkout.');
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            if ($lastOrder = session()->get('last_order_number')) {
                return redirect()->route('order.success', $lastOrder);
            }

            return redirect()->route('cart.index');
        }

        $activeCodes = PaymentMethod::query()->active()->pluck('code')->all();
        if (empty($activeCodes)) {
            $activeCodes = ['cash_on_delivery', 'credit_card', 'bank_transfer', 'aba_pay'];
        }

        $frontendUser = auth('frontend')->user();
        $isTermPayment = $request->input('payment_method') === 'payment_term';

        $rules = [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'string'],
            'payment_term_id' => [
                'nullable',
                Rule::requiredIf($isTermPayment),
                'exists:customer_group_payment_terms,id',
            ],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ];

        if (! $isTermPayment) {
            $rules['payment_method'][] = Rule::in($activeCodes);
        }

        $validated = $request->validate($rules);

        $selectedPaymentTerm = null;
        if ($isTermPayment) {
            if (! $frontendUser || ! $frontendUser->customer_group_id) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Net credit settlement terms are reserved for verified B2B customer accounts.',
                ]);
            }

            $selectedPaymentTerm = CustomerGroupPaymentTerm::query()
                ->where('id', $validated['payment_term_id'])
                ->where('is_active', true)
                ->where(function ($q) use ($frontendUser) {
                    $q->where('customer_group_id', $frontendUser->customer_group_id)
                        ->orWhereNull('customer_group_id');
                })
                ->first();

            if (! $selectedPaymentTerm) {
                throw ValidationException::withMessages([
                    'payment_term_id' => 'The selected payment term is not valid or not permitted for your customer tier.',
                ]);
            }
        }

        return DB::transaction(function () use ($validated, $cart, $frontendUser, $selectedPaymentTerm, $isTermPayment) {
            $subtotal = 0.0;

            // Verify stock availability
            foreach ($cart as $productId => $item) {
                $product = Product::lockForUpdate()->find($productId);
                if (! $product || $product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => 'Product "'.$item['name'].'" has insufficient stock available ('.$product?->stock_quantity.' remaining).',
                    ]);
                }

                $hasSerials = $product->serialNumbers()->exists();
                if ($product->requires_serial_tracking || $hasSerials) {
                    $inStockCount = $product->serialNumbers()->where('status', SerialNumber::STATUS_IN_STOCK)->count();
                    if ($inStockCount < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'cart' => 'Product "'.$item['name'].'" has insufficient serialized stock available ('.$inStockCount.' remaining).',
                        ]);
                    }
                }

                $subtotal += $item['price'] * $item['quantity'];
            }

            $tax = round($subtotal * 0.08, 2);
            $shipping = $subtotal >= 150 ? 0.00 : 15.00;
            $total = $subtotal + $tax + $shipping;

            // Enforce credit limit for B2B Net Terms
            if ($isTermPayment && $frontendUser) {
                $customerGroup = $frontendUser->customerGroup;
                $creditLimit = (float) ($frontendUser->credit_limit > 0 ? $frontendUser->credit_limit : ($customerGroup?->credit_limit ?? 0));

                if ($creditLimit > 0) {
                    $outstanding = (float) Order::where('frontend_user_id', $frontendUser->id)
                        ->whereIn('payment_status', ['pending', 'partially_paid'])
                        ->sum('total_amount');

                    if (($outstanding + $total) > $creditLimit) {
                        $available = max(0.00, round($creditLimit - $outstanding, 2));
                        throw ValidationException::withMessages([
                            'payment_method' => "Credit limit exceeded. Your available credit is \${$available} (Limit: \${$creditLimit}, Outstanding: \${$outstanding}), but order total is \${$total}.",
                        ]);
                    }
                }
            }

            $shippingAddress = [
                'recipient' => $validated['customer_name'],
                'street' => $validated['street'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
            ];

            $crmCustomer = Customer::where('email', $validated['customer_email'])->first();

            $orderNumber = 'ORD-'.date('Ymd').'-'.strtoupper(str()->random(5));

            $paymentDueDate = null;
            $paymentTermId = null;
            $depositAmount = 0.00;
            $earlyDiscountAmount = 0.00;
            $earlyDiscountDeadline = null;
            $paymentMethodLabel = $validated['payment_method'];

            if ($selectedPaymentTerm) {
                $paymentTermId = $selectedPaymentTerm->id;
                $paymentMethodLabel = "Net Terms ({$selectedPaymentTerm->name})";
                $paymentDueDate = now()->addDays($selectedPaymentTerm->days)->toDateString();

                if ($selectedPaymentTerm->require_deposit && $selectedPaymentTerm->deposit_percentage > 0) {
                    $depositAmount = round($total * ((float) $selectedPaymentTerm->deposit_percentage / 100), 2);
                }

                if ($selectedPaymentTerm->early_payment_discount_percentage > 0) {
                    $earlyDiscountAmount = round($subtotal * ((float) $selectedPaymentTerm->early_payment_discount_percentage / 100), 2);
                }

                if ($selectedPaymentTerm->early_payment_days > 0) {
                    $earlyDiscountDeadline = now()->addDays($selectedPaymentTerm->early_payment_days)->toDateString();
                }
            }

            $adminNotes = null;
            if ($selectedPaymentTerm) {
                $adminNotes = "Placed under B2B payment terms: {$selectedPaymentTerm->name} (Net {$selectedPaymentTerm->days}d).";
                if ($depositAmount > 0) {
                    $adminNotes .= " Requires \${$depositAmount} ({$selectedPaymentTerm->deposit_percentage}%) upfront deposit.";
                }
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'frontend_user_id' => $frontendUser?->id,
                'customer_id' => $crmCustomer?->id,
                'user_id' => null,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'status' => 'pending',
                'payment_status' => $validated['payment_method'] === 'credit_card' ? 'paid' : 'pending',
                'payment_method' => $paymentMethodLabel,
                'payment_due_date' => $paymentDueDate,
                'payment_term_id' => $paymentTermId,
                'deposit_amount' => $depositAmount,
                'deposit_paid' => false,
                'early_payment_discount_amount' => $earlyDiscountAmount,
                'early_payment_discount_deadline' => $earlyDiscountDeadline,
                'late_fee_amount' => 0.00,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'shipping_fee' => $shipping,
                'discount_amount' => 0.00,
                'total_amount' => $total,
                'shipping_address' => $shippingAddress,
                'billing_address' => $shippingAddress,
                'customer_notes' => $validated['customer_notes'] ?? null,
                'admin_notes' => $adminNotes,
            ]);

            if ($frontendUser) {
                $frontendUser->increment('orders_count');
                $frontendUser->increment('total_spent', $total);
                $frontendUser->checkAndPromote();
            }

            foreach ($cart as $productId => $item) {
                $product = Product::find($productId);

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total_price' => round($item['price'] * $item['quantity'], 2),
                    'specifications_snapshot' => $product->specifications,
                ]);

                // Allocation & Stock decrement
                $hasSerials = $product->serialNumbers()->exists();
                if ($product->requires_serial_tracking || $hasSerials) {
                    app(SerialTrackingService::class)->allocateToOrder($product, $order, $orderItem, (int) $item['quantity']);
                    $product->syncTotalStock();
                } else {
                    $qtyToDeduct = (int) $item['quantity'];
                    $warehouses = $product->warehouses()->wherePivot('quantity', '>', 0)->get();
                    $remaining = $qtyToDeduct;
                    foreach ($warehouses as $wh) {
                        $currQty = (int) $wh->pivot->quantity;
                        $deduct = min($currQty, $remaining);
                        $product->warehouses()->updateExistingPivot($wh->id, ['quantity' => $currQty - $deduct]);
                        $remaining -= $deduct;
                        if ($remaining <= 0) {
                            break;
                        }
                    }
                    $product->decrement('stock_quantity', $qtyToDeduct);
                    $product->syncTotalStock();
                }
            }

            // Clear session cart and save last order number for graceful redirects
            session()->forget('cart');
            session()->put('last_order_number', $order->order_number);

            return redirect()->route('order.success', $order->order_number)
                ->with('success', 'Thank you! Your computer parts order has been placed successfully.');
        });
    }
}
