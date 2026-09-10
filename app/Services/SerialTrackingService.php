<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SerialTrackingService
{
    /**
     * Ingest / Register a batch of serial numbers into a warehouse.
     *
     * @param  list<string>|string  $serials
     * @return Collection<int, SerialNumber>
     */
    public function ingestSerials(Product $product, Warehouse $warehouse, array|string $serials, ?float $costPrice = null, ?int $supplierId = null): Collection
    {
        $serialList = is_array($serials) ? $serials : preg_split('/[\r\n,]+/', (string) $serials);
        $cleanedSerials = [];

        foreach ($serialList as $rawSerial) {
            $serial = strtoupper(trim((string) $rawSerial));
            if ($serial === '') {
                continue;
            }
            $cleanedSerials[] = $serial;
        }

        if (empty($cleanedSerials)) {
            return collect();
        }

        // Check for duplicate serials within the batch
        $counts = array_count_values($cleanedSerials);
        $internalDuplicates = array_keys(array_filter($counts, fn ($count) => $count > 1));
        if (! empty($internalDuplicates)) {
            throw new InvalidArgumentException('Batch contains duplicate serial numbers: '.implode(', ', $internalDuplicates));
        }

        // Check for existing serial numbers in database
        $existing = SerialNumber::whereIn('serial_number', $cleanedSerials)->pluck('serial_number')->toArray();
        if (! empty($existing)) {
            throw new InvalidArgumentException('The following serial number(s) already exist in inventory: '.implode(', ', $existing));
        }

        return DB::transaction(function () use ($product, $warehouse, $cleanedSerials, $costPrice, $supplierId) {
            $created = collect();

            foreach ($cleanedSerials as $serial) {
                $record = SerialNumber::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'supplier_id' => $supplierId,
                    'serial_number' => $serial,
                    'status' => SerialNumber::STATUS_IN_STOCK,
                    'cost_price' => $costPrice ?: $product->cost_price,
                    'inbound_date' => now()->toDateString(),
                ]);

                $created->push($record);
            }

            if (! $product->requires_serial_tracking) {
                $product->update(['requires_serial_tracking' => true]);
            }

            // Keep warehouse stock in sync
            $currentQty = SerialNumber::where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->where('status', SerialNumber::STATUS_IN_STOCK)
                ->count();

            $product->warehouses()->syncWithoutDetaching([
                $warehouse->id => ['quantity' => $currentQty],
            ]);
            $product->syncTotalStock();

            return $created;
        });
    }

    /**
     * Allocate in-stock serial numbers to an order item.
     *
     * @return Collection<int, SerialNumber>
     */
    public function allocateToOrder(Product $product, Order $order, OrderItem $orderItem, int $quantity, ?Warehouse $warehouse = null): Collection
    {
        $query = SerialNumber::where('product_id', $product->id)
            ->where('status', SerialNumber::STATUS_IN_STOCK);

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        $available = $query->limit($quantity)->get();

        if ($available->count() < $quantity) {
            throw new InvalidArgumentException("Insufficient serialized stock available for '{$product->name}'. Requested {$quantity}, available {$available->count()}.");
        }

        foreach ($available as $unit) {
            $unit->update([
                'status' => SerialNumber::STATUS_ALLOCATED,
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
            ]);
        }

        return $available;
    }

    /**
     * Fulfill and ship serialized items, activating manufacturer/distributor warranties.
     *
     * @param  Collection<int, SerialNumber>|list<SerialNumber>  $units
     * @return Collection<int, SerialNumber>
     */
    public function shipUnits(Collection|array $units): Collection
    {
        $collection = $units instanceof Collection ? $units : collect($units);
        $now = Carbon::now();

        foreach ($collection as $unit) {
            $product = $unit->product;
            $warrantyMonths = (int) ($product?->manufacturer_warranty_months ?: 36);

            $unit->update([
                'status' => SerialNumber::STATUS_SHIPPED,
                'outbound_date' => $now->toDateString(),
                'warranty_start_date' => $now->toDateString(),
                'warranty_end_date' => $now->copy()->addMonths($warrantyMonths)->toDateString(),
            ]);

            // Deduct stock from warehouse
            if ($unit->warehouse_id && $product) {
                $whRecord = $product->warehouses()->where('warehouses.id', $unit->warehouse_id)->first();
                if ($whRecord) {
                    $newQty = max(0, ((int) $whRecord->pivot->quantity) - 1);
                    $product->warehouses()->updateExistingPivot($unit->warehouse_id, ['quantity' => $newQty]);
                    $product->syncTotalStock();
                }
            }
        }

        return $collection;
    }

    /**
     * Update the lifecycle status and optional notes of a serialized unit.
     */
    public function updateStatus(string $serialNumber, string $newStatus, ?string $notes = null): SerialNumber
    {
        $unit = SerialNumber::where('serial_number', strtoupper(trim($serialNumber)))->firstOrFail();

        $validStatuses = [
            SerialNumber::STATUS_IN_STOCK,
            SerialNumber::STATUS_ALLOCATED,
            SerialNumber::STATUS_SHIPPED,
            SerialNumber::STATUS_RETURNED_RMA,
            SerialNumber::STATUS_DEFECTIVE_SCRAP,
            SerialNumber::STATUS_RETURNED_TO_VENDOR,
            SerialNumber::STATUS_OTHER,
        ];

        if (! in_array($newStatus, $validStatuses, true)) {
            throw new InvalidArgumentException("Invalid serial status: {$newStatus}");
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === SerialNumber::STATUS_SHIPPED && ! $unit->outbound_date) {
            $now = Carbon::now();
            $warrantyMonths = (int) ($unit->product?->manufacturer_warranty_months ?: 36);
            $updateData['outbound_date'] = $now->toDateString();
            $updateData['warranty_start_date'] = $now->toDateString();
            $updateData['warranty_end_date'] = $now->copy()->addMonths($warrantyMonths)->toDateString();
        }

        if ($notes !== null) {
            $updateData['notes'] = $notes;
        }

        $unit->update($updateData);

        // Keep warehouse stock in sync if warehouse and product are associated
        if ($unit->warehouse_id && $unit->product) {
            $currentQty = SerialNumber::where('product_id', $unit->product_id)
                ->where('warehouse_id', $unit->warehouse_id)
                ->where('status', SerialNumber::STATUS_IN_STOCK)
                ->count();

            $unit->product->warehouses()->syncWithoutDetaching([
                $unit->warehouse_id => ['quantity' => $currentQty],
            ]);
            $unit->product->syncTotalStock();
        }

        return $unit;
    }

    /**
     * Process an RMA return for a serialized unit.
     */
    public function processRmaReturn(string $serialNumber, string $newStatus = SerialNumber::STATUS_RETURNED_RMA, ?string $notes = null): SerialNumber
    {
        $unit = SerialNumber::where('serial_number', strtoupper(trim($serialNumber)))->firstOrFail();

        $validRmaStatuses = [
            SerialNumber::STATUS_RETURNED_RMA,
            SerialNumber::STATUS_DEFECTIVE_SCRAP,
            SerialNumber::STATUS_RETURNED_TO_VENDOR,
            SerialNumber::STATUS_IN_STOCK,
        ];

        if (! in_array($newStatus, $validRmaStatuses, true)) {
            throw new InvalidArgumentException("Invalid RMA status: {$newStatus}");
        }

        $existingNotes = $unit->notes ? $unit->notes."\n" : '';
        $formattedNotes = $existingNotes.'['.now()->toDateTimeString()."] RMA Status -> {$newStatus}: {$notes}";

        return $this->updateStatus($serialNumber, $newStatus, $formattedNotes);
    }

    /**
     * Check warranty status and remaining coverage for a serial number.
     *
     * @return array{
     *     serial_number: string,
     *     product_name: string|null,
     *     status: string,
     *     is_under_warranty: bool,
     *     warranty_start_date: string|null,
     *     warranty_end_date: string|null,
     *     days_remaining: int
     * }
     */
    public function checkWarranty(string $serialNumber): array
    {
        $unit = SerialNumber::with('product')->where('serial_number', strtoupper(trim($serialNumber)))->firstOrFail();

        $isUnderWarranty = false;
        $daysRemaining = 0;

        if ($unit->warranty_end_date) {
            $endDate = Carbon::parse($unit->warranty_end_date);
            $today = Carbon::today();
            if ($endDate->greaterThanOrEqualTo($today)) {
                $isUnderWarranty = true;
                $daysRemaining = $today->diffInDays($endDate);
            }
        }

        return [
            'serial_number' => $unit->serial_number,
            'product_name' => $unit->product?->name,
            'status' => $unit->status,
            'is_under_warranty' => $isUnderWarranty,
            'warranty_start_date' => $unit->warranty_start_date?->toDateString(),
            'warranty_end_date' => $unit->warranty_end_date?->toDateString(),
            'days_remaining' => $daysRemaining,
        ];
    }

    /**
     * Delete an in-stock serialized unit and sync warehouse inventory.
     */
    public function deleteSerial(SerialNumber $serialNumber): bool
    {
        if ($serialNumber->status !== SerialNumber::STATUS_IN_STOCK) {
            throw new InvalidArgumentException("Cannot delete serial '{$serialNumber->serial_number}'. Only units with status IN_STOCK can be deleted.");
        }

        $product = $serialNumber->product;
        $warehouseId = $serialNumber->warehouse_id;

        $deleted = (bool) $serialNumber->delete();

        if ($product && $warehouseId) {
            $currentQty = SerialNumber::where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->where('status', SerialNumber::STATUS_IN_STOCK)
                ->count();

            $product->warehouses()->syncWithoutDetaching([
                $warehouseId => ['quantity' => $currentQty],
            ]);
            $product->syncTotalStock();
        }

        return $deleted;
    }
}
