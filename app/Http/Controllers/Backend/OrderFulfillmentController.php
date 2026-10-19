<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\SerialNumber;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderFulfillmentController extends Controller
{
    /**
     * Display warehouse fulfillment queue of orders requiring packing and serial scanning.
     */
    public function index(Request $request): View
    {
        $query = Order::query()
            ->with(['items.product', 'items.serialNumbers', 'serialNumbers', 'deliveries'])
            ->whereNotIn('status', ['cancelled']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->whereIn('status', ['confirmed', 'processing', 'pending', 'preparing']);
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest('id')->paginate(15)->withQueryString();

        // Fulfillment Metrics
        $totalAwaitingFulfillment = Order::whereIn('status', ['confirmed', 'processing', 'pending', 'preparing'])->count();
        $readyToShipCount = Order::whereIn('status', ['confirmed', 'processing', 'pending', 'preparing'])
            ->get()
            ->filter(fn ($o) => $o->isReadyForShipment())
            ->count();
        $shippedTodayCount = Order::where('status', 'shipped')
            ->whereDate('updated_at', today())
            ->count();
        $totalSerialsScannedToday = SerialNumber::whereIn('status', [SerialNumber::STATUS_SHIPPED, SerialNumber::STATUS_SOLD, 'sold', 'SOLD'])
            ->whereDate('outbound_date', today())
            ->count();

        return view('backend.fulfillment.index', compact(
            'orders',
            'totalAwaitingFulfillment',
            'readyToShipCount',
            'shippedTodayCount',
            'totalSerialsScannedToday'
        ));
    }

    /**
     * Dedicated warehouse order fulfillment & serial scanning workstation screen.
     */
    public function show(Order $order): View
    {
        $order->load([
            'items.product',
            'items.serialNumbers',
            'serialNumbers',
            'deliveries.deliveryAgent',
            'shippingMethod',
        ]);

        // Get available IN_STOCK serial numbers for products in this order
        $productIds = $order->items->pluck('product_id')->filter()->unique();
        $availableSerials = SerialNumber::query()
            ->whereIn('product_id', $productIds)
            ->where('status', SerialNumber::STATUS_IN_STOCK)
            ->orderBy('serial_number')
            ->get()
            ->groupBy('product_id');

        return view('backend.fulfillment.show', compact('order', 'availableSerials'));
    }

    /**
     * Scan or select a hardware serial number to attach to an order item.
     * 1. Verify scanned serials match the reserved items.
     * 2. Update status to 'sold'.
     * 3. Set warranty_start_date = CURRENT_DATE and warranty_end_date = CURRENT_DATE + interval '36 months'.
     */
    public function attachSerial(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_item_id' => ['nullable', 'exists:order_items,id'],
            'serial_number' => ['required', 'string', 'max:255'],
        ]);

        $serialCode = trim($validated['serial_number']);
        $order->loadMissing(['items.product', 'items.serialNumbers']);

        $orderItem = null;
        if (! empty($validated['order_item_id'])) {
            $orderItem = $order->items->firstWhere('id', (int) $validated['order_item_id']);
        }

        $currentDate = now()->toDateString();
        $warrantyEndDate = now()->copy()->addMonths(36)->toDateString();

        // 1. Verify scanned serials match the reserved items
        $serial = SerialNumber::where('serial_number', $serialCode)->first();

        if (! $serial) {
            // Check if there is an eligible reserved item needing serial tracking
            if ($orderItem) {
                if ($orderItem->isFullyScanned()) {
                    return back()->with('error', "Verification failed: All reserved units for '{$orderItem->product_name}' have already been scanned.");
                }
            } else {
                $orderItem = $order->items->first(fn ($item) => $item->requiresSerialTracking() && ! $item->isFullyScanned());
            }

            if (! $orderItem || ! $orderItem->product_id) {
                return back()->with('error', "Verification failed: Scanned serial '{$serialCode}' does not match any reserved items in Order #{$order->order_number}.");
            }

            $product = $orderItem->product;

            $serial = SerialNumber::create([
                'product_id' => $product->id,
                'warehouse_id' => Warehouse::value('id'),
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'serial_number' => $serialCode,
                'status' => SerialNumber::STATUS_SOLD,
                'cost_price' => $product->cost_price ?? 0,
                'inbound_date' => $currentDate,
                'outbound_date' => $currentDate,
                'warranty_start_date' => $currentDate,
                'warranty_end_date' => $warrantyEndDate,
                'notes' => "Scanned and verified for reserved item '{$orderItem->product_name}' on Order #{$order->order_number}. 36-month warranty active.",
            ]);

            return back()->with('success', "Scanned serial '{$serial->serial_number}' verified for reserved item '{$orderItem->product_name}', status updated to 'sold', and 36-month warranty activated ({$currentDate} to {$warrantyEndDate}).");
        }

        // Serial exists: Verify matching with reserved items in this order
        if ($serial->order_id === $order->id) {
            return back()->with('info', "Serial Number '{$serialCode}' is already scanned and attached to this order.");
        }

        if ($serial->order_id && $serial->order_id !== $order->id) {
            $otherOrder = Order::find($serial->order_id);
            $orderRef = $otherOrder ? "#{$otherOrder->order_number}" : "Order #{$serial->order_id}";

            return back()->with('error', "Verification failed: Serial Number '{$serialCode}' is already allocated to {$orderRef}.");
        }

        if (in_array($serial->status, [SerialNumber::STATUS_SOLD, 'sold', 'SOLD', SerialNumber::STATUS_SHIPPED], true) && $serial->order_id !== $order->id) {
            return back()->with('error', "Verification failed: Serial Number '{$serialCode}' has already been sold.");
        }

        if (in_array($serial->status, [SerialNumber::STATUS_DEFECTIVE_SCRAP, SerialNumber::STATUS_RETURNED_TO_VENDOR], true)) {
            return back()->with('error', "Verification failed: Serial Number '{$serialCode}' is marked as {$serial->status} and cannot be shipped.");
        }

        // Verify product matches reserved items in order
        $matchingItems = $order->items->where('product_id', $serial->product_id);
        if ($matchingItems->isEmpty()) {
            $prodName = $serial->product?->name ?? "Product #{$serial->product_id}";

            return back()->with('error', "Verification failed: Scanned serial '{$serialCode}' belongs to '{$prodName}', which does not match any reserved items in Order #{$order->order_number}.");
        }

        if ($orderItem) {
            if ($orderItem->product_id !== $serial->product_id) {
                return back()->with('error', "Verification failed: Scanned serial '{$serialCode}' ({$serial->product?->name}) does not match the selected reserved item '{$orderItem->product_name}'.");
            }

            if ($orderItem->isFullyScanned()) {
                return back()->with('error', "Verification failed: All reserved units for '{$orderItem->product_name}' have already been scanned.");
            }
        } else {
            $orderItem = $matchingItems->first(fn ($item) => ! $item->isFullyScanned());

            if (! $orderItem) {
                $prodName = $serial->product?->name ?? 'this product';

                return back()->with('error', "Verification failed: All reserved units for '{$prodName}' in Order #{$order->order_number} have already been scanned.");
            }
        }

        // 2. Update status to 'sold'
        // 3. Set warranty_start_date = CURRENT_DATE and warranty_end_date = CURRENT_DATE + interval '36 months'
        $serial->markAsSold($order, $orderItem);

        return back()->with('success', "Scanned serial '{$serial->serial_number}' verified for reserved item '{$orderItem->product_name}', status updated to 'sold', and 36-month warranty activated ({$currentDate} to {$warrantyEndDate}).");
    }

    /**
     * Detach a serial number from an order item.
     */
    public function detachSerial(Order $order, SerialNumber $serialNumber): RedirectResponse
    {
        if ($serialNumber->order_id !== $order->id) {
            return back()->with('error', 'Serial number is not attached to this order.');
        }

        $code = $serialNumber->serial_number;
        $serialNumber->update([
            'order_id' => null,
            'order_item_id' => null,
            'status' => SerialNumber::STATUS_IN_STOCK,
            'warranty_start_date' => null,
            'warranty_end_date' => null,
            'notes' => "Detached from Order #{$order->order_number}, returned to inventory.",
        ]);

        return back()->with('success', "Serial number '{$code}' detached and returned to in-stock inventory.");
    }

    /**
     * Complete order fulfillment and mark as shipped for RMA tracking.
     */
    public function completeFulfillment(Request $request, Order $order): RedirectResponse
    {
        // Check if all serial tracking requirements are satisfied
        $requiredCount = $order->totalSerialTrackingRequiredCount();
        $attachedCount = $order->totalSerialsAttachedCount();

        if ($requiredCount > 0 && $attachedCount < $requiredCount) {
            $missing = $requiredCount - $attachedCount;

            return back()->with('error', "Cannot complete fulfillment: {$missing} required hardware serial numbers have not been scanned yet.");
        }

        $currentDate = now()->toDateString();
        $warrantyEndDate = now()->copy()->addMonths(36)->toDateString();

        // Ensure attached serials are in 'sold' status with 36 months warranty and outbound date
        foreach ($order->serialNumbers as $sn) {
            $sn->update([
                'status' => SerialNumber::STATUS_SOLD,
                'outbound_date' => $sn->outbound_date ?: $currentDate,
                'warranty_start_date' => $sn->warranty_start_date ?: $currentDate,
                'warranty_end_date' => $sn->warranty_end_date ?: $warrantyEndDate,
            ]);
        }

        // Update order status
        $order->update([
            'status' => 'shipped',
        ]);

        // Automatically create a delivery shipment record if one doesn't exist yet
        if (! $order->deliveries()->exists()) {
            $deliveryNumber = 'DEL-'.date('Y').'-'.strtoupper(Str::random(6));
            Delivery::create([
                'delivery_number' => $deliveryNumber,
                'order_id' => $order->id,
                'shipping_method_id' => $order->shipping_method_id,
                'carrier' => 'Standard Carrier',
                'tracking_number' => 'TRK-FDX-'.rand(100000, 999999),
                'status' => 'dispatched',
                'recipient_name' => $order->customer_name,
                'recipient_phone' => $order->customer_phone,
                'recipient_email' => $order->customer_email,
                'shipping_address' => $order->shipping_address ?? [
                    'recipient' => $order->customer_name,
                    'street' => 'Commercial Delivery',
                    'city' => 'Phnom Penh',
                    'country' => 'Cambodia',
                ],
                'dispatched_at' => now(),
                'notes' => "Dispatched with {$attachedCount} verified RMA serial numbers.",
            ]);
        }

        return redirect()->route('admin.fulfillment.show', $order->id)
            ->with('success', "Order #{$order->order_number} successfully fulfilled & marked as SHIPPED! All {$attachedCount} hardware serial numbers are active for RMA warranty tracking.");
    }
}
