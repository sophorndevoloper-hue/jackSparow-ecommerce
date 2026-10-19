<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\ShippingMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    /**
     * Display a listing of deliveries with status filtering and search.
     */
    public function index(Request $request): View
    {
        $query = Delivery::query()->with(['order', 'shippingMethod', 'deliveryAgent']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('carrier')) {
            $query->where('carrier', $request->input('carrier'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        $deliveries = $query->latest('updated_at')->paginate(15)->withQueryString();

        // Delivery KPI Metrics
        $totalDeliveries = Delivery::count();
        $pendingDispatch = Delivery::whereIn('status', ['pending', 'preparing'])->count();
        $inTransit = Delivery::whereIn('status', ['dispatched', 'in_transit', 'out_for_delivery'])->count();
        $deliveredCount = Delivery::where('status', 'delivered')->count();

        $carriers = Delivery::whereNotNull('carrier')->distinct()->pluck('carrier');

        return view('backend.deliveries.index', compact(
            'deliveries',
            'totalDeliveries',
            'pendingDispatch',
            'inTransit',
            'deliveredCount',
            'carriers'
        ));
    }

    /**
     * Show form to dispatch a new delivery.
     */
    public function create(Request $request): View
    {
        $selectedOrder = null;
        if ($request->filled('order_id')) {
            $selectedOrder = Order::with('items')->find($request->input('order_id'));
        }

        $availableOrders = Order::query()
            ->whereNotIn('status', ['cancelled'])
            ->latest('id')
            ->take(30)
            ->get();

        $shippingMethods = ShippingMethod::active()->ordered()->get();
        $deliveryAgents = DeliveryAgent::active()->get();

        return view('backend.deliveries.create', compact('availableOrders', 'selectedOrder', 'shippingMethods', 'deliveryAgents'));
    }

    /**
     * Store a newly created delivery and sync order status.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'shipping_method_id' => ['nullable', 'exists:shipping_methods,id'],
            'delivery_agent_id' => ['nullable', 'exists:delivery_agents,id'],
            'carrier' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:50'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:pending,preparing,dispatched,in_transit,out_for_delivery,delivered,cancelled'],
            'estimated_delivery_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $shippingAddress = [
            'recipient' => $validated['recipient_name'],
            'street' => $validated['street'],
            'city' => $validated['city'],
            'state' => $validated['state'] ?? '',
            'postal_code' => $validated['postal_code'] ?? '',
            'country' => $validated['country'] ?? 'United States',
        ];

        $deliveryNumber = 'DEL-'.date('Y').'-'.strtoupper(Str::random(6));

        $dispatchedAt = in_array($validated['status'], ['dispatched', 'in_transit', 'out_for_delivery', 'delivered']) ? now() : null;
        $deliveredAt = $validated['status'] === 'delivered' ? now() : null;

        $delivery = Delivery::create([
            'delivery_number' => $deliveryNumber,
            'order_id' => $order->id,
            'shipping_method_id' => $validated['shipping_method_id'] ?? null,
            'delivery_agent_id' => $validated['delivery_agent_id'] ?? null,
            'carrier' => $validated['carrier'] ?? 'Standard Carrier',
            'tracking_number' => $validated['tracking_number'] ?? null,
            'status' => $validated['status'],
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'] ?? null,
            'recipient_email' => $validated['recipient_email'] ?? null,
            'shipping_address' => $shippingAddress,
            'estimated_delivery_date' => $validated['estimated_delivery_date'] ?? null,
            'dispatched_at' => $dispatchedAt,
            'delivered_at' => $deliveredAt,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['delivery_agent_id']) && in_array($validated['status'], ['dispatched', 'in_transit', 'out_for_delivery'])) {
            $agent = DeliveryAgent::find($validated['delivery_agent_id']);
            if ($agent && $agent->status === 'available') {
                $agent->status = 'on_delivery';
                $agent->save();
            }
        }

        // Sync order status and shipping method
        if (! empty($validated['shipping_method_id'])) {
            $order->shipping_method_id = $validated['shipping_method_id'];
        }

        if (in_array($validated['status'], ['dispatched', 'in_transit', 'out_for_delivery'])) {
            $order->status = 'shipped';
        } elseif ($validated['status'] === 'delivered') {
            $order->status = 'delivered';
        }
        $order->save();

        return redirect()->route('admin.deliveries.show', $delivery->id)
            ->with('success', "Delivery '{$delivery->delivery_number}' created successfully for order #{$order->order_number}.");
    }

    /**
     * Display tracking and timeline details of a delivery.
     */
    public function show(Delivery $delivery): View
    {
        $delivery->load(['order.items.product', 'shippingMethod', 'deliveryAgent']);

        return view('backend.deliveries.show', compact('delivery'));
    }

    /**
     * Update delivery status, carrier, or tracking number.
     */
    public function update(Request $request, Delivery $delivery): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,preparing,dispatched,in_transit,out_for_delivery,delivered,cancelled'],
            'carrier' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'estimated_delivery_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($validated['status'], ['dispatched', 'in_transit', 'out_for_delivery']) && ! $delivery->dispatched_at) {
            $validated['dispatched_at'] = now();
        }

        if ($validated['status'] === 'delivered' && ! $delivery->delivered_at) {
            $validated['delivered_at'] = now();
        }

        $delivery->update($validated);

        // Sync Order Status
        $order = $delivery->order;
        if ($order) {
            if (in_array($validated['status'], ['dispatched', 'in_transit', 'out_for_delivery'])) {
                $order->update(['status' => 'shipped']);
            } elseif ($validated['status'] === 'delivered') {
                $order->update(['status' => 'delivered']);
            }
        }

        return back()->with('success', "Delivery '{$delivery->delivery_number}' status updated to {$delivery->statusLabel()}.");
    }

    /**
     * Update only delivery status quickly.
     */
    public function updateStatus(Request $request, Delivery $delivery): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,preparing,dispatched,in_transit,out_for_delivery,delivered,cancelled'],
        ]);

        $status = $validated['status'];
        $updates = ['status' => $status];

        if (in_array($status, ['dispatched', 'in_transit', 'out_for_delivery']) && ! $delivery->dispatched_at) {
            $updates['dispatched_at'] = now();
        }

        if ($status === 'delivered' && ! $delivery->delivered_at) {
            $updates['delivered_at'] = now();
        }

        $delivery->update($updates);

        $order = $delivery->order;
        if ($order) {
            if (in_array($status, ['dispatched', 'in_transit', 'out_for_delivery'])) {
                $order->update(['status' => 'shipped']);
            } elseif ($status === 'delivered') {
                $order->update(['status' => 'delivered']);
            }
        }

        return back()->with('success', "Delivery status changed to {$delivery->statusLabel()}.");
    }

    /**
     * Delete delivery record.
     */
    public function destroy(Delivery $delivery): RedirectResponse
    {
        $deliveryNum = $delivery->delivery_number;
        $delivery->delete();

        return redirect()->route('admin.deliveries.index')
            ->with('success', "Delivery '{$deliveryNum}' deleted successfully.");
    }
}
