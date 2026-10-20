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
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
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
        $order->load(['items.product', 'user', 'deliveries.shippingMethod', 'shippingMethod', 'paymentTerm']);

        return view('backend.orders.show', compact('order'));
    }

    /**
     * Update the status and payment status of an order.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $validated = $request->validated();

        // Enforce Early Payment Discount if order is settled on or before the early settlement deadline
        if ($validated['payment_status'] === 'paid' && $order->payment_status !== 'paid') {
            if ($order->early_payment_discount_deadline
                && now()->startOfDay()->lte($order->early_payment_discount_deadline)
                && (float) $order->early_payment_discount_amount > 0
                && (float) $order->discount_amount === 0.00) {
                $discount = (float) $order->early_payment_discount_amount;
                $validated['discount_amount'] = $discount;
                $validated['total_amount'] = max(0.00, round((float) $order->total_amount - $discount, 2));

                $note = "[Early settlement discount of \${$discount} applied on ".now()->toFormattedDateString().'].';
                $validated['admin_notes'] = trim(($validated['admin_notes'] ?? $order->admin_notes).' '.$note);
            }
        }

        $order->update($validated);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Mark upfront deposit as received for orders under payment terms.
     */
    public function markDepositPaid(Order $order): RedirectResponse
    {
        if ((float) $order->deposit_amount <= 0) {
            return back()->with('error', 'This order does not have an upfront deposit requirement.');
        }

        $order->update([
            'deposit_paid' => true,
            'deposit_paid_at' => now(),
            'payment_status' => 'partially_paid',
            'admin_notes' => trim($order->admin_notes.' '."[Upfront deposit of \${$order->deposit_amount} verified and recorded on ".now()->toFormattedDateString().'].'),
        ]);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', "Upfront deposit of \${$order->deposit_amount} marked as paid.");
    }
}
