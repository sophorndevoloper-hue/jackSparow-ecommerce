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

        $baseQuery = Order::query()
            ->where(function ($q) use ($customerId) {
                $q->where('frontend_user_id', $customerId)
                    ->orWhere('customer_id', $customerId);
            });

        $totalOrdersCount = (clone $baseQuery)->count();
        $totalSpent = (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('total_amount');
        $activeOrdersCount = (clone $baseQuery)->whereIn('status', ['pending', 'processing', 'shipped'])->count();
        $deliveredOrdersCount = (clone $baseQuery)->where('status', 'delivered')->count();

        $orders = $baseQuery
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return view('frontend.my-orders', compact(
            'orders',
            'totalOrdersCount',
            'totalSpent',
            'activeOrdersCount',
            'deliveredOrdersCount'
        ));
    }
}
