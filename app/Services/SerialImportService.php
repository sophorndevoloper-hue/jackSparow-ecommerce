<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\Warehouse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SerialImportService
{
    public function __construct(
        protected SerialTrackingService $serialTrackingService
    ) {}

    /**
     * Parse an uploaded file into an array of serial items.
     *
     * Each item shape:
     * [
     *   'serial_number' => string,
     *   'product_sku' => ?string,
     *   'warehouse_code' => ?string,
     *   'cost_price' => ?float,
     * ]
     *
     * @return array<int, array{serial_number: string, product_sku: ?string, warehouse_code: ?string, cost_price: ?float}>
     */
    public function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

        return match ($extension) {
            'csv' => $this->parseCsv($file->getRealPath()),
            'xlsx', 'xls' => $this->parseExcel($file->getRealPath()),
            'json' => $this->parseJson($file->getRealPath()),
            'txt' => $this->parsePlainText($file->getRealPath()),
            default => throw new InvalidArgumentException("Unsupported file format '.{$extension}'. Please upload a CSV, Excel (.xlsx/.xls), JSON, or Plain Text (.txt) file."),
        };
    }

    /**
     * Process parsed items and ingest serial numbers into database.
     *
     * @param  array<int, array{serial_number: string, product_sku: ?string, warehouse_code: ?string, cost_price: ?float}>  $items
     * @return Collection<int, SerialNumber>
     */
    public function importItems(array $items, ?int $defaultProductId = null, ?int $defaultWarehouseId = null, ?float $defaultCostPrice = null): Collection
    {
        if (empty($items)) {
            throw new InvalidArgumentException('The uploaded file does not contain any valid serial number records.');
        }

        $defaultProduct = $defaultProductId ? Product::find($defaultProductId) : null;
        $defaultWarehouse = $defaultWarehouseId ? Warehouse::find($defaultWarehouseId) : null;

        // Cache lookup maps
        $productSkuMap = Product::pluck('id', 'sku')->toArray();
        $warehouseCodeMap = Warehouse::pluck('id', 'code')->toArray();

        $processedRecords = [];
        $rawSerials = [];

        foreach ($items as $index => $item) {
            $rowNum = $index + 1;
            $serial = strtoupper(trim((string) ($item['serial_number'] ?? '')));

            if ($serial === '') {
                continue;
            }

            $rawSerials[] = $serial;

            // Resolve product
            $productId = null;
            if (! empty($item['product_sku'])) {
                $sku = trim((string) $item['product_sku']);
                $productId = $productSkuMap[$sku] ?? null;
                if (! $productId) {
                    $prod = Product::where('sku', 'ilike', $sku)->orWhere('name', 'ilike', $sku)->first();
                    $productId = $prod?->id;
                }
            }
            if (! $productId && $defaultProduct) {
                $productId = $defaultProduct->id;
            }
            if (! $productId) {
                throw new InvalidArgumentException("Row #{$rowNum}: Serial '{$serial}' requires a valid hardware product SKU, or select a default product.");
            }

            // Resolve warehouse
            $warehouseId = null;
            if (! empty($item['warehouse_code'])) {
                $code = trim((string) $item['warehouse_code']);
                $warehouseId = $warehouseCodeMap[$code] ?? null;
                if (! $warehouseId) {
                    $wh = Warehouse::where('code', 'ilike', $code)->orWhere('name', 'ilike', $code)->first();
                    $warehouseId = $wh?->id;
                }
            }
            if (! $warehouseId && $defaultWarehouse) {
                $warehouseId = $defaultWarehouse->id;
            }
            if (! $warehouseId) {
                throw new InvalidArgumentException("Row #{$rowNum}: Serial '{$serial}' requires a valid destination warehouse code, or select a default warehouse.");
            }

            $cost = $item['cost_price'] ?? $defaultCostPrice;

            $processedRecords[] = [
                'serial_number' => $serial,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'cost_price' => $cost,
            ];
        }

        if (empty($processedRecords)) {
            throw new InvalidArgumentException('No non-empty serial numbers were found in the uploaded file.');
        }

        // Check for duplicates within the uploaded file
        $counts = array_count_values($rawSerials);
        $duplicatesInFile = array_keys(array_filter($counts, fn ($count) => $count > 1));
        if (! empty($duplicatesInFile)) {
            throw new InvalidArgumentException('The uploaded file contains duplicate serial numbers: '.implode(', ', array_slice($duplicatesInFile, 0, 10)).(count($duplicatesInFile) > 10 ? '...' : '').'. Each serial number must be unique.');
        }

        // Check for duplicates in existing database inventory
        $existing = SerialNumber::whereIn('serial_number', $rawSerials)->pluck('serial_number')->toArray();
        if (! empty($existing)) {
            throw new InvalidArgumentException('The following serial number(s) already exist in inventory: '.implode(', ', array_slice($existing, 0, 10)).(count($existing) > 10 ? '...' : '').'.');
        }

        return DB::transaction(function () use ($processedRecords) {
            $created = collect();
            $affectedProducts = [];
            $affectedWarehouses = [];

            foreach ($processedRecords as $record) {
                $product = Product::find($record['product_id']);
                $cost = $record['cost_price'] !== null ? (float) $record['cost_price'] : ($product?->cost_price ? (float) $product->cost_price : null);

                $sn = SerialNumber::create([
                    'product_id' => $record['product_id'],
                    'warehouse_id' => $record['warehouse_id'],
                    'serial_number' => $record['serial_number'],
                    'status' => SerialNumber::STATUS_IN_STOCK,
                    'cost_price' => $cost,
                    'inbound_date' => now()->toDateString(),
                ]);

                $created->push($sn);
                $affectedProducts[$record['product_id']] = true;
                $affectedWarehouses[$record['warehouse_id'].'-'.$record['product_id']] = [
                    'warehouse_id' => $record['warehouse_id'],
                    'product_id' => $record['product_id'],
                ];
            }

            // Keep warehouse stock counts in sync
            foreach ($affectedWarehouses as $pair) {
                $wh = Warehouse::find($pair['warehouse_id']);
                $prod = Product::find($pair['product_id']);
                if ($wh && $prod) {
                    $inStockCount = SerialNumber::where('product_id', $prod->id)
                        ->where('warehouse_id', $wh->id)
                        ->where('status', SerialNumber::STATUS_IN_STOCK)
                        ->count();

                    $wh->products()->syncWithoutDetaching([
                        $prod->id => ['quantity' => $inStockCount],
                    ]);
                }
            }

            // Sync total product stock and enable requires_serial_tracking
            foreach (array_keys($affectedProducts) as $pId) {
                $prod = Product::find($pId);
                if ($prod) {
                    if (! $prod->requires_serial_tracking) {
                        $prod->update(['requires_serial_tracking' => true]);
                    }
                    $prod->syncTotalStock();
                }
            }

            return $created;
        });
    }

    /**
     * Parse CSV file.
     *
     * @return array<int, array{serial_number: string, product_sku: ?string, warehouse_code: ?string, cost_price: ?float}>
     */
    protected function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new InvalidArgumentException('Unable to open CSV file for reading.');
        }

        $items = [];
        $header = null;

        while (($row = fgetcsv($handle)) !== false) {
            // Strip UTF-8 BOM if present
            if ($header === null && isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]);
            }

            // Filter out empty rows
            if (empty(array_filter($row, fn ($v) => trim((string) $v) !== ''))) {
                continue;
            }

            if ($header === null) {
                // Determine if this row is a header row
                $firstColLower = strtolower(trim((string) $row[0]));
                if (in_array($firstColLower, ['serial_number', 'serial', 'serial number', 'sn', 'barcode', 'sku', 'product_sku'])) {
                    $header = array_map(fn ($col) => strtolower(trim(str_replace([' ', '-'], '_', (string) $col))), $row);

                    continue;
                }
                // No header detected; assume standard column layout: serial_number, product_sku, warehouse_code, cost_price
                $header = ['serial_number', 'product_sku', 'warehouse_code', 'cost_price'];
            }

            $mapped = [];
            foreach ($header as $i => $colName) {
                $mapped[$colName] = isset($row[$i]) ? trim((string) $row[$i]) : null;
            }

            $serial = $mapped['serial_number'] ?? $mapped['serial'] ?? $mapped['sn'] ?? $mapped['barcode'] ?? ($row[0] ?? '');
            $sku = $mapped['product_sku'] ?? $mapped['sku'] ?? $mapped['product'] ?? ($row[1] ?? null);
            $whCode = $mapped['warehouse_code'] ?? $mapped['warehouse'] ?? ($row[2] ?? null);
            $cost = isset($mapped['cost_price']) && is_numeric($mapped['cost_price'])
                ? (float) $mapped['cost_price']
                : (isset($row[3]) && is_numeric($row[3]) ? (float) $row[3] : null);

            if (trim((string) $serial) !== '') {
                $items[] = [
                    'serial_number' => (string) $serial,
                    'product_sku' => ! empty($sku) ? (string) $sku : null,
                    'warehouse_code' => ! empty($whCode) ? (string) $whCode : null,
                    'cost_price' => $cost,
                ];
            }
        }

        fclose($handle);

        return $items;
    }

    /**
     * Parse Excel spreadsheet (.xlsx, .xls).
     *
     * @return array<int, array{serial_number: string, product_sku: ?string, warehouse_code: ?string, cost_price: ?float}>
     */
    protected function parseExcel(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [];
        }

        $items = [];
        $header = null;

        foreach ($rows as $row) {
            if (empty(array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== ''))) {
                continue;
            }

            if ($header === null) {
                $firstVal = strtolower(trim((string) ($row[0] ?? '')));
                if (in_array($firstVal, ['serial_number', 'serial', 'serial number', 'sn', 'barcode', 'sku', 'product_sku'])) {
                    $header = array_map(fn ($col) => strtolower(trim(str_replace([' ', '-'], '_', (string) $col))), $row);

                    continue;
                }
                $header = ['serial_number', 'product_sku', 'warehouse_code', 'cost_price'];
            }

            $mapped = [];
            foreach ($header as $i => $colName) {
                $mapped[$colName] = isset($row[$i]) ? trim((string) $row[$i]) : null;
            }

            $serial = $mapped['serial_number'] ?? $mapped['serial'] ?? $mapped['sn'] ?? $mapped['barcode'] ?? ($row[0] ?? '');
            $sku = $mapped['product_sku'] ?? $mapped['sku'] ?? $mapped['product'] ?? ($row[1] ?? null);
            $whCode = $mapped['warehouse_code'] ?? $mapped['warehouse'] ?? ($row[2] ?? null);
            $cost = isset($mapped['cost_price']) && is_numeric($mapped['cost_price'])
                ? (float) $mapped['cost_price']
                : (isset($row[3]) && is_numeric($row[3]) ? (float) $row[3] : null);

            if (trim((string) $serial) !== '') {
                $items[] = [
                    'serial_number' => (string) $serial,
                    'product_sku' => ! empty($sku) ? (string) $sku : null,
                    'warehouse_code' => ! empty($whCode) ? (string) $whCode : null,
                    'cost_price' => $cost,
                ];
            }
        }

        return $items;
    }

    /**
     * Parse JSON file.
     *
     * @return array<int, array{serial_number: string, product_sku: ?string, warehouse_code: ?string, cost_price: ?float}>
     */
    protected function parseJson(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new InvalidArgumentException('Unable to read JSON file.');
        }

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON format in uploaded file: '.json_last_error_msg());
        }

        // Support object wrappers like {"serials": [...]} or {"data": [...]}
        if (is_array($decoded) && ! isset($decoded[0])) {
            foreach (['serials', 'serial_numbers', 'data', 'items'] as $key) {
                if (isset($decoded[$key]) && is_array($decoded[$key])) {
                    $decoded = $decoded[$key];
                    break;
                }
            }
        }

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('JSON file must contain an array of serial numbers or serial objects.');
        }

        $items = [];
        foreach ($decoded as $entry) {
            if (is_string($entry) || is_numeric($entry)) {
                $serial = trim((string) $entry);
                if ($serial !== '') {
                    $items[] = [
                        'serial_number' => $serial,
                        'product_sku' => null,
                        'warehouse_code' => null,
                        'cost_price' => null,
                    ];
                }
            } elseif (is_array($entry)) {
                $serial = $entry['serial_number'] ?? $entry['serial'] ?? $entry['sn'] ?? $entry['barcode'] ?? null;
                if ($serial !== null && trim((string) $serial) !== '') {
                    $items[] = [
                        'serial_number' => trim((string) $serial),
                        'product_sku' => ! empty($entry['product_sku']) ? trim((string) $entry['product_sku']) : (! empty($entry['sku']) ? trim((string) $entry['sku']) : null),
                        'warehouse_code' => ! empty($entry['warehouse_code']) ? trim((string) $entry['warehouse_code']) : (! empty($entry['warehouse']) ? trim((string) $entry['warehouse']) : null),
                        'cost_price' => isset($entry['cost_price']) && is_numeric($entry['cost_price']) ? (float) $entry['cost_price'] : null,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Parse Plain Text file (.txt).
     *
     * @return array<int, array{serial_number: string, product_sku: ?string, warehouse_code: ?string, cost_price: ?float}>
     */
    protected function parsePlainText(string $path): array
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new InvalidArgumentException('Unable to read text file.');
        }

        $items = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, ',')) {
                $parts = str_getcsv($line);
                $serial = trim($parts[0] ?? '');
                $sku = ! empty($parts[1]) ? trim($parts[1]) : null;
                $wh = ! empty($parts[2]) ? trim($parts[2]) : null;
                $cost = isset($parts[3]) && is_numeric($parts[3]) ? (float) $parts[3] : null;

                if ($serial !== '') {
                    $items[] = [
                        'serial_number' => $serial,
                        'product_sku' => $sku,
                        'warehouse_code' => $wh,
                        'cost_price' => $cost,
                    ];
                }
            } else {
                $items[] = [
                    'serial_number' => $line,
                    'product_sku' => null,
                    'warehouse_code' => null,
                    'cost_price' => null,
                ];
            }
        }

        return $items;
    }

    /**
     * Generate starter template download response for given format.
     */
    public function downloadTemplate(string $format): StreamedResponse
    {
        $sampleProduct = Product::first();
        $sampleWarehouse = Warehouse::first();

        $sku = $sampleProduct?->sku ?? 'CPU-AMD-9950X';
        $whCode = $sampleWarehouse?->code ?? 'WH_SF';
        $cost = $sampleProduct?->cost_price ? number_format($sampleProduct->cost_price, 2, '.', '') : '490.00';

        return match (strtolower($format)) {
            'csv' => $this->streamCsvTemplate($sku, $whCode, $cost),
            'excel', 'xlsx' => $this->streamExcelTemplate($sku, $whCode, $cost),
            'json' => $this->streamJsonTemplate($sku, $whCode, $cost),
            'text', 'txt' => $this->streamTextTemplate($sku, $whCode, $cost),
            default => throw new InvalidArgumentException("Unknown template format '{$format}'. Allowed formats: csv, excel, json, text."),
        };
    }

    protected function streamCsvTemplate(string $sku, string $whCode, string $cost): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="serial_numbers_template.csv"',
        ];

        return response()->stream(function () use ($sku, $whCode, $cost) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['serial_number', 'product_sku', 'warehouse_code', 'cost_price']);
            fputcsv($handle, ['SN-SAMPLE-001', $sku, $whCode, $cost]);
            fputcsv($handle, ['SN-SAMPLE-002', $sku, $whCode, $cost]);
            fputcsv($handle, ['SN-SAMPLE-003', $sku, $whCode, $cost]);
            fclose($handle);
        }, 200, $headers);
    }

    protected function streamExcelTemplate(string $sku, string $whCode, string $cost): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="serial_numbers_template.xlsx"',
        ];

        return response()->stream(function () use ($sku, $whCode, $cost) {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Serial Numbers');

            // Header row
            $sheet->setCellValue('A1', 'serial_number');
            $sheet->setCellValue('B1', 'product_sku');
            $sheet->setCellValue('C1', 'warehouse_code');
            $sheet->setCellValue('D1', 'cost_price');

            // Style headers
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);

            // Sample rows
            $sheet->setCellValue('A2', 'SN-SAMPLE-001');
            $sheet->setCellValue('B2', $sku);
            $sheet->setCellValue('C2', $whCode);
            $sheet->setCellValue('D2', (float) $cost);

            $sheet->setCellValue('A3', 'SN-SAMPLE-002');
            $sheet->setCellValue('B3', $sku);
            $sheet->setCellValue('C3', $whCode);
            $sheet->setCellValue('D3', (float) $cost);

            $sheet->setCellValue('A4', 'SN-SAMPLE-003');
            $sheet->setCellValue('B4', $sku);
            $sheet->setCellValue('C4', $whCode);
            $sheet->setCellValue('D4', (float) $cost);

            foreach (['A', 'B', 'C', 'D'] as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, $headers);
    }

    protected function streamJsonTemplate(string $sku, string $whCode, string $cost): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="serial_numbers_template.json"',
        ];

        return response()->stream(function () use ($sku, $whCode, $cost) {
            $sample = [
                [
                    'serial_number' => 'SN-SAMPLE-001',
                    'product_sku' => $sku,
                    'warehouse_code' => $whCode,
                    'cost_price' => (float) $cost,
                ],
                [
                    'serial_number' => 'SN-SAMPLE-002',
                    'product_sku' => $sku,
                    'warehouse_code' => $whCode,
                    'cost_price' => (float) $cost,
                ],
                [
                    'serial_number' => 'SN-SAMPLE-003',
                    'product_sku' => $sku,
                    'warehouse_code' => $whCode,
                    'cost_price' => (float) $cost,
                ],
            ];
            echo json_encode($sample, JSON_PRETTY_PRINT);
        }, 200, $headers);
    }

    protected function streamTextTemplate(string $sku, string $whCode, string $cost): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="serial_numbers_template.txt"',
        ];

        return response()->stream(function () use ($sku, $whCode, $cost) {
            echo "# Serial Numbers Plain Text Import Template\n";
            echo "# Format: Enter one serial number per line, OR comma-separated (serial_number, product_sku, warehouse_code, cost_price)\n";
            echo "# Simple list format:\n";
            echo "SN-SAMPLE-001\n";
            echo "SN-SAMPLE-002\n";
            echo "SN-SAMPLE-003\n";
            echo "# Or full detail format:\n";
            echo "# SN-SAMPLE-004,{$sku},{$whCode},{$cost}\n";
        }, 200, $headers);
    }
}
