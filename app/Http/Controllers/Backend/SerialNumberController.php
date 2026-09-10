<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SerialNumberDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\Warehouse;
use App\Services\SerialImportService;
use App\Services\SerialTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SerialNumberController extends Controller
{
    public function __construct(
        protected SerialTrackingService $serialTrackingService,
        protected SerialImportService $serialImportService
    ) {}

    /**
     * Display listing of serial numbers with searching and filters.
     */
    public function index(Request $request, SerialNumberDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $query = SerialNumber::with(['product', 'warehouse', 'order']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('search')) {
            $search = strtoupper(trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('sku', 'ilike', "%{$search}%");
                    });
            });
        }

        $serialNumbers = $query->latest('updated_at')->paginate(25)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('backend.serial-numbers.index', compact('serialNumbers', 'warehouses', 'products', 'dataTable'));
    }

    /**
     * Ingest batch serial numbers.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'serials_text' => ['required', 'string'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $rawList = preg_split('/[\r\n,]+/', (string) $validated['serials_text']);
        $cleanedSerials = [];

        foreach ($rawList as $raw) {
            $serial = strtoupper(trim((string) $raw));
            if ($serial === '') {
                continue;
            }
            $cleanedSerials[] = $serial;
        }

        if (empty($cleanedSerials)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['serials_text' => 'Please enter at least one valid serial number.']);
        }

        // Check for duplicate serials within the submission
        $counts = array_count_values($cleanedSerials);
        $internalDuplicates = array_keys(array_filter($counts, fn ($count) => $count > 1));
        if (! empty($internalDuplicates)) {
            $msg = 'Your input contains duplicate serial numbers: '.implode(', ', $internalDuplicates).'. Each serial number must be unique.';

            return redirect()->back()
                ->withInput()
                ->withErrors(['serials_text' => $msg]);
        }

        // Check for serials that already exist in database
        $existing = SerialNumber::whereIn('serial_number', $cleanedSerials)->pluck('serial_number')->toArray();
        if (! empty($existing)) {
            $msg = 'The following serial number(s) already exist in inventory: '.implode(', ', $existing).'. Serial numbers must be globally unique.';

            return redirect()->back()
                ->withInput()
                ->withErrors(['serials_text' => $msg]);
        }

        $product = Product::findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        try {
            $created = $this->serialTrackingService->ingestSerials(
                $product,
                $warehouse,
                $cleanedSerials,
                $validated['cost_price'] ? (float) $validated['cost_price'] : null
            );

            return redirect()->route('admin.serial-numbers.index')
                ->with('success', "Successfully registered {$created->count()} serial numbers into {$warehouse->name}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['serials_text' => $e->getMessage()]);
        }
    }

    /**
     * Update serial number lifecycle status or notes (e.g. RMA or scrap).
     */
    public function update(Request $request, SerialNumber $serialNumber): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:IN_STOCK,ALLOCATED,SHIPPED,RETURNED_RMA,DEFECTIVE_SCRAP,RETURNED_TO_VENDOR,OTHER'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->serialTrackingService->updateStatus(
            $serialNumber->serial_number,
            $validated['status'],
            $validated['notes']
        );

        return redirect()->route('admin.serial-numbers.index')
            ->with('success', "Serial Number '{$serialNumber->serial_number}' status updated to {$validated['status']}.");
    }

    /**
     * Remove the specified serial number from stock.
     * Only serial numbers with status 'IN_STOCK' are permitted to be deleted.
     */
    public function destroy(SerialNumber $serialNumber): RedirectResponse
    {
        if ($serialNumber->status !== SerialNumber::STATUS_IN_STOCK) {
            return redirect()->route('admin.serial-numbers.index')
                ->with('error', "Cannot delete serial '{$serialNumber->serial_number}'. Only units with status 'IN_STOCK' can be deleted.");
        }

        $serial = $serialNumber->serial_number;
        $this->serialTrackingService->deleteSerial($serialNumber);

        return redirect()->route('admin.serial-numbers.index')
            ->with('success', "Serial Number '{$serial}' was successfully deleted from inventory.");
    }

    /**
     * Import batch serial numbers from uploaded file (CSV, Excel, JSON, Plain text).
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'product_id' => ['nullable', 'exists:products,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $items = $this->serialImportService->parseFile($request->file('file'));
            $created = $this->serialImportService->importItems(
                $items,
                ! empty($validated['product_id']) ? (int) $validated['product_id'] : null,
                ! empty($validated['warehouse_id']) ? (int) $validated['warehouse_id'] : null,
                isset($validated['cost_price']) && is_numeric($validated['cost_price']) ? (float) $validated['cost_price'] : null
            );

            return redirect()->route('admin.serial-numbers.index')
                ->with('success', "Successfully imported {$created->count()} serial numbers into inventory.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error processing file: '.$e->getMessage());
        }
    }

    /**
     * Stream starter template file for download.
     */
    public function downloadTemplate(string $format): StreamedResponse
    {
        return $this->serialImportService->downloadTemplate($format);
    }
}
