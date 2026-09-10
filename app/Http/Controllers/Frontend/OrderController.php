<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display the order success / invoice page.
     */
    public function show(string $orderNumber): View
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'user'])
            ->firstOrFail();

        return view('frontend.order-success', compact('order'));
    }

    /**
     * Display past orders for the authenticated customer.
     */
    public function myOrders(Request $request): View
    {
        $customerId = auth('frontend')->id();
        $userId = auth('backend')->id();

        $orders = Order::query()
            ->where(function ($q) use ($customerId, $userId) {
                if ($customerId) {
                    $q->where('frontend_user_id', $customerId)
                        ->orWhere('customer_id', $customerId);
                }
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->with(['items'])
            ->latest()
            ->paginate(10);

        return view('frontend.my-orders', compact('orders'));
    }
}
