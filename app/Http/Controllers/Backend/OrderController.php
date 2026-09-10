<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\OrderDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of customer orders with status filtering.
     */
    public function index(Request $request, OrderDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $query = Order::query()->with(['user', 'items']);

        if ($request->filled('status')) {
            $query->status($request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->paymentStatus($request->input('payment_status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'ilike', "%{$search}%")
                    ->orWhere('customer_name', 'ilike', "%{$search}%")
                    ->orWhere('customer_email', 'ilike', "%{$search}%");
            });
        }

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();

        return view('backend.orders.index', compact('orders', 'dataTable'));
    }

    /**
     * Display details of a specific order.
     */
    public function show(Order $order): View
    {
        $order->load(['items.product', 'user']);

        return view('backend.orders.show', compact('order'));
    }

    /**
     * Update the status and payment status of an order.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order->update($request->validated());

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order status updated successfully.');
    }
}
