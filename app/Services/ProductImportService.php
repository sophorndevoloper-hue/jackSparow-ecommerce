<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product\Product;
use App\Models\Product\ProductPriceTier;
use App\Models\ProductModel;
use App\Models\Warehouse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImportService
{
    /**
     * Parse an uploaded file (CSV, Excel XLSX/XLS, JSON, or Plain Text) into normalized product record arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

        return match ($extension) {
            'csv' => $this->parseCsv($file->getRealPath()),
            'xlsx', 'xls' => $this->parseExcel($file->getRealPath()),
            'json' => $this->parseJson($file->getRealPath()),
            'txt' => $this->parsePlainText($file->getRealPath()),
            default => throw new InvalidArgumentException("Unsupported file format '.{$extension}'. Please upload a CSV, Excel (.xlsx/.xls), JSON, or Text file."),
        };
    }

    /**
     * Ingest or update products from parsed file rows.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array{default_category_id?: ?int, default_brand_id?: ?int, default_warehouse_id?: ?int, update_existing?: bool}  $options
     * @return array{imported: int, updated: int, skipped: int, errors: array<int, string>, products: Collection<int, Product>}
     */
    public function importItems(array $items, array $options = []): array
    {
        if (empty($items)) {
            throw new InvalidArgumentException('The uploaded file contains no product records to import.');
        }

        $defaultCategoryId = ! empty($options['default_category_id']) ? (int) $options['default_category_id'] : null;
        $defaultBrandId = ! empty($options['default_brand_id']) ? (int) $options['default_brand_id'] : null;
        $defaultWarehouseId = ! empty($options['default_warehouse_id']) ? (int) $options['default_warehouse_id'] : null;
        $updateExisting = (bool) ($options['update_existing'] ?? true);

        // Preload existing Category, Brand, and Model lookups (case-insensitive name => id)
        $categoriesByName = Category::all()->keyBy(fn ($c) => strtolower(trim($c->name)));
        $categoriesById = Category::pluck('id', 'id')->toArray();

        $brandsByName = Brand::all()->keyBy(fn ($b) => strtolower(trim($b->name)));
        $brandsById = Brand::pluck('id', 'id')->toArray();

        $modelsByName = ProductModel::all()->keyBy(fn ($m) => strtolower(trim($m->name)));

        $defaultWarehouse = null;
        if ($defaultWarehouseId) {
            $defaultWarehouse = Warehouse::find($defaultWarehouseId);
        }
        if (! $defaultWarehouse) {
            $defaultWarehouse = Warehouse::where('is_default', true)->first() ?? Warehouse::first();
        }

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $savedProducts = collect();

        foreach ($items as $index => $row) {
            $rowNumber = $index + 1;

            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                $errors[] = "Row #{$rowNumber}: Product name is required and was empty. Skipped.";
                $skippedCount++;

                continue;
            }

            // Determine SKU
            $rawSku = trim((string) ($row['sku'] ?? ''));
            $sku = $rawSku !== '' ? strtoupper($rawSku) : null;

            // Resolve Category
            $categoryId = null;
            $rawCat = trim((string) ($row['category'] ?? ''));
            if ($rawCat !== '') {
                if (is_numeric($rawCat) && isset($categoriesById[(int) $rawCat])) {
                    $categoryId = (int) $rawCat;
                } else {
                    $lowerCat = strtolower($rawCat);
                    if ($categoriesByName->has($lowerCat)) {
                        $categoryId = $categoriesByName->get($lowerCat)->id;
                    } else {
                        // Auto-create category on the fly
                        $newCat = Category::create([
                            'name' => $rawCat,
                            'slug' => str()->slug($rawCat).'-'.rand(100, 999),
                        ]);
                        $categoriesByName->put($lowerCat, $newCat);
                        $categoriesById[$newCat->id] = $newCat->id;
                        $categoryId = $newCat->id;
                    }
                }
            }
            if (! $categoryId && $defaultCategoryId) {
                $categoryId = $defaultCategoryId;
            }
            if (! $categoryId) {
                // Pick first existing category or create a fallback
                $firstCat = Category::first();
                if ($firstCat) {
                    $categoryId = $firstCat->id;
                } else {
                    $newCat = Category::create(['name' => 'General Hardware', 'slug' => 'general-hardware']);
                    $categoryId = $newCat->id;
                }
            }

            // Resolve Brand
            $brandId = null;
            $rawBrand = trim((string) ($row['brand'] ?? ''));
            if ($rawBrand !== '') {
                if (is_numeric($rawBrand) && isset($brandsById[(int) $rawBrand])) {
                    $brandId = (int) $rawBrand;
                } else {
                    $lowerBrand = strtolower($rawBrand);
                    if ($brandsByName->has($lowerBrand)) {
                        $brandId = $brandsByName->get($lowerBrand)->id;
                    } else {
                        $newBrand = Brand::create([
                            'name' => $rawBrand,
                            'slug' => str()->slug($rawBrand).'-'.rand(100, 999),
                        ]);
                        $brandsByName->put($lowerBrand, $newBrand);
                        $brandsById[$newBrand->id] = $newBrand->id;
                        $brandId = $newBrand->id;
                    }
                }
            }
            if (! $brandId && $defaultBrandId) {
                $brandId = $defaultBrandId;
            }

            // Resolve Model if provided
            $modelId = null;
            $rawModel = trim((string) ($row['model'] ?? ''));
            if ($rawModel !== '') {
                $lowerModel = strtolower($rawModel);
                if ($modelsByName->has($lowerModel)) {
                    $modelId = $modelsByName->get($lowerModel)->id;
                } elseif ($brandId) {
                    $newModel = ProductModel::create([
                        'brand_id' => $brandId,
                        'name' => $rawModel,
                        'slug' => str()->slug($rawModel).'-'.rand(100, 999),
                        'is_active' => true,
                    ]);
                    $modelsByName->put($lowerModel, $newModel);
                    $modelId = $newModel->id;
                }
            }

            // Price parsing (support both raw floats and formatted currency strings like $600.00)
            $cleanPrice = preg_replace('/[^\d.]/', '', (string) ($row['price'] ?? ''));
            $price = is_numeric($cleanPrice) ? max(0, (float) $cleanPrice) : 0.0;

            $cleanCost = preg_replace('/[^\d.]/', '', (string) ($row['cost_price'] ?? ''));
            $costPrice = is_numeric($cleanCost) ? max(0, (float) $cleanCost) : 0.0;

            $cleanSale = preg_replace('/[^\d.]/', '', (string) ($row['sale_price'] ?? ''));
            $salePrice = is_numeric($cleanSale) && (float) $cleanSale > 0 ? (float) $cleanSale : null;

            // Serial Tracking flag
            $requiresSerialTracking = false;
            if (isset($row['requires_serial_tracking'])) {
                $stVal = strtolower(trim((string) $row['requires_serial_tracking']));
                $requiresSerialTracking = in_array($stVal, ['1', 'true', 'yes', 'y', 'serialized', 'on'], true);
            }

            // Stock Quantity
            $initialStock = isset($row['stock_quantity']) && is_numeric($row['stock_quantity'])
                ? max(0, (int) $row['stock_quantity'])
                : 0;

            // Serialized items must have stock_quantity = 0 until actual company serials are registered
            if ($requiresSerialTracking) {
                $initialStock = 0;
            }

            // Auto-generate SKU if omitted
            if (! $sku) {
                $brandName = $brandId ? (Brand::find($brandId)?->name ?? 'PART') : 'PART';
                $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $brandName), 0, 4));
                $sku = $prefix.'-'.strtoupper(substr(str()->slug($name), 0, 10)).'-'.rand(1000, 9999);
            }

            // Extract Extended Technical Specifications (CPU, GPU, RAM, Storage, Screen, etc.)
            $specifications = $this->extractSpecifications($row);

            // Physical & Logistics
            $cleanWeight = isset($row['weight_kg']) ? preg_replace('/[^\d.]/', '', (string) $row['weight_kg']) : null;
            $weightKg = is_numeric($cleanWeight) ? (float) $cleanWeight : null;

            $cleanLen = isset($row['length_cm']) ? preg_replace('/[^\d.]/', '', (string) $row['length_cm']) : null;
            $lengthCm = is_numeric($cleanLen) ? (float) $cleanLen : null;

            $cleanWidth = isset($row['width_cm']) ? preg_replace('/[^\d.]/', '', (string) $row['width_cm']) : null;
            $widthCm = is_numeric($cleanWidth) ? (float) $cleanWidth : null;

            $cleanHeight = isset($row['height_cm']) ? preg_replace('/[^\d.]/', '', (string) $row['height_cm']) : null;
            $heightCm = is_numeric($cleanHeight) ? (float) $cleanHeight : null;

            $isHazmat = false;
            if (isset($row['is_hazmat'])) {
                $hz = strtolower(trim((string) $row['is_hazmat']));
                $isHazmat = in_array($hz, ['1', 'true', 'yes', 'y', 'hazmat', 'battery', 'lithium'], true);
            }

            // Pricing Safeguards & Wholesale Limits
            $cleanMap = isset($row['map_price']) ? preg_replace('/[^\d.]/', '', (string) $row['map_price']) : null;
            $mapPrice = is_numeric($cleanMap) ? (float) $cleanMap : null;

            $cleanMargin = isset($row['min_margin_percentage']) ? preg_replace('/[^\d.]/', '', (string) $row['min_margin_percentage']) : null;
            $minMarginPercentage = is_numeric($cleanMargin) ? (float) $cleanMargin : 10.0;

            $moq = isset($row['moq']) && is_numeric($row['moq']) ? max(1, (int) $row['moq']) : 1;
            $casePackMultiple = isset($row['case_pack_multiple']) && is_numeric($row['case_pack_multiple']) ? max(1, (int) $row['case_pack_multiple']) : 1;
            $maxOrderQuantity = isset($row['max_order_quantity']) && is_numeric($row['max_order_quantity']) ? max(1, (int) $row['max_order_quantity']) : null;

            // Warranty
            $manufacturerWarrantyMonths = isset($row['manufacturer_warranty_months']) && is_numeric($row['manufacturer_warranty_months']) ? (int) $row['manufacturer_warranty_months'] : null;
            $distributorWarrantyMonths = isset($row['distributor_warranty_months']) && is_numeric($row['distributor_warranty_months']) ? (int) $row['distributor_warranty_months'] : null;

            // Build attributes
            $attributes = [
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'model_id' => $modelId,
                'name' => $name,
                'sku' => $sku,
                'mpn' => ! empty($row['mpn']) ? trim((string) $row['mpn']) : null,
                'upc_ean' => ! empty($row['upc_ean']) ? trim((string) $row['upc_ean']) : null,
                'hs_code' => ! empty($row['hs_code']) ? trim((string) $row['hs_code']) : null,
                'unspsc_code' => ! empty($row['unspsc_code']) ? trim((string) $row['unspsc_code']) : null,
                'short_description' => ! empty($row['short_description']) ? trim((string) $row['short_description']) : null,
                'description' => ! empty($row['description']) ? trim((string) $row['description']) : null,
                'price' => $price,
                'cost_price' => $costPrice,
                'sale_price' => $salePrice,
                'map_price' => $mapPrice,
                'min_margin_percentage' => $minMarginPercentage,
                'moq' => $moq,
                'case_pack_multiple' => $casePackMultiple,
                'max_order_quantity' => $maxOrderQuantity,
                'stock_quantity' => $initialStock,
                'low_stock_threshold' => isset($row['low_stock_threshold']) && is_numeric($row['low_stock_threshold']) ? (int) $row['low_stock_threshold'] : 5,
                'requires_serial_tracking' => $requiresSerialTracking,
                'socket' => ! empty($row['socket']) ? trim((string) $row['socket']) : null,
                'chipset' => ! empty($row['chipset']) ? trim((string) $row['chipset']) : null,
                'form_factor' => ! empty($row['form_factor']) ? trim((string) $row['form_factor']) : null,
                'tdp_watts' => isset($row['tdp_watts']) && is_numeric($row['tdp_watts']) ? (int) $row['tdp_watts'] : null,
                'power_requirement_watts' => isset($row['power_requirement_watts']) && is_numeric($row['power_requirement_watts']) ? (int) $row['power_requirement_watts'] : null,
                'weight_kg' => $weightKg,
                'length_cm' => $lengthCm,
                'width_cm' => $widthCm,
                'height_cm' => $heightCm,
                'is_hazmat' => $isHazmat,
                'warranty_period' => ! empty($row['warranty_period']) ? trim((string) $row['warranty_period']) : null,
                'manufacturer_warranty_months' => $manufacturerWarrantyMonths,
                'distributor_warranty_months' => $distributorWarrantyMonths,
                'is_active' => isset($row['is_active']) ? in_array(strtolower(trim((string) $row['is_active'])), ['1', 'true', 'yes', 'y', 'active'], true) : true,
                'is_featured' => isset($row['is_featured']) ? in_array(strtolower(trim((string) $row['is_featured'])), ['1', 'true', 'yes', 'y', 'featured'], true) : false,
                'specifications' => ! empty($specifications) ? $specifications : null,
                'specs' => ! empty($specifications) ? $specifications : null,
            ];

            try {
                DB::transaction(function () use (
                    &$importedCount,
                    &$updatedCount,
                    &$skippedCount,
                    &$savedProducts,
                    $sku,
                    $name,
                    $attributes,
                    $row,
                    $updateExisting,
                    $requiresSerialTracking,
                    $initialStock,
                    $defaultWarehouse
                ) {
                    $existingProduct = Product::where('sku', $sku)->first();

                    if ($existingProduct) {
                        if (! $updateExisting) {
                            $skippedCount++;

                            return;
                        }

                        // Update existing product
                        unset($attributes['sku']); // Retain existing SKU
                        $existingProduct->update($attributes);

                        // If stock was provided for non-serialized product, sync to warehouse
                        if (! $requiresSerialTracking && $initialStock > 0 && $defaultWarehouse) {
                            $defaultWarehouse->setProductStock($existingProduct, $initialStock);
                            $existingProduct->syncTotalStock();
                        }

                        // Sync volume pricing tiers if provided
                        if (! empty($row['volume_pricing'])) {
                            $this->syncVolumePricingTiers($existingProduct, $row['volume_pricing']);
                        }

                        $savedProducts->push($existingProduct);
                        $updatedCount++;
                    } else {
                        // Create brand new product
                        $attributes['slug'] = str()->slug($name).'-'.rand(100, 999);
                        $newProduct = Product::create($attributes);

                        if (! $requiresSerialTracking && $initialStock > 0 && $defaultWarehouse) {
                            $defaultWarehouse->setProductStock($newProduct, $initialStock);
                            $newProduct->syncTotalStock();
                        }

                        // Sync volume pricing tiers if provided
                        if (! empty($row['volume_pricing'])) {
                            $this->syncVolumePricingTiers($newProduct, $row['volume_pricing']);
                        }

                        $savedProducts->push($newProduct);
                        $importedCount++;
                    }
                });
            } catch (\Throwable $e) {
                $errors[] = "Row #{$rowNumber} ('{$name}'): ".$e->getMessage();
                $skippedCount++;
            }
        }

        return [
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
            'products' => $savedProducts,
        ];
    }

    /**
     * Parse CSV file with BOM stripping and automatic delimiter detection.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('Could not read the uploaded CSV file.');
        }

        // Detect delimiter from first line
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        // Strip UTF-8 BOM if present
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

        $delimiter = ',';
        foreach ([',', ';', "\t", '|'] as $cand) {
            if (substr_count($firstLine, $cand) > substr_count($firstLine, $delimiter)) {
                $delimiter = $cand;
            }
        }

        rewind($handle);

        $headers = null;
        $items = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Check if empty row
            if (empty(array_filter($row, fn ($val) => $val !== null && trim((string) $val) !== ''))) {
                continue;
            }

            if ($headers === null) {
                // Clean headers
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]);
                $headers = array_map(fn ($col) => $this->normalizeColumnName((string) $col), $row);

                continue;
            }

            $mapped = [];
            foreach ($headers as $idx => $key) {
                if ($key !== '') {
                    $mapped[$key] = isset($row[$idx]) ? trim((string) $row[$idx]) : null;
                }
            }

            if (! empty($mapped)) {
                $items[] = $this->mapNormalizedRow($mapped);
            }
        }

        fclose($handle);

        return $items;
    }

    /**
     * Parse Excel spreadsheet (.xlsx, .xls).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseExcel(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, false, false);

        if (empty($rows)) {
            return [];
        }

        $headers = null;
        $items = [];

        foreach ($rows as $row) {
            if (empty(array_filter($row, fn ($val) => $val !== null && trim((string) $val) !== ''))) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(fn ($col) => $this->normalizeColumnName((string) $col), $row);

                continue;
            }

            $mapped = [];
            foreach ($headers as $idx => $key) {
                if ($key !== '') {
                    $mapped[$key] = isset($row[$idx]) ? trim((string) $row[$idx]) : null;
                }
            }

            if (! empty($mapped)) {
                $items[] = $this->mapNormalizedRow($mapped);
            }
        }

        return $items;
    }

    /**
     * Parse JSON file.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseJson(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new InvalidArgumentException('Could not read the uploaded JSON file.');
        }

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in uploaded file: '.json_last_error_msg());
        }

        // Support wrapped JSON objects e.g. {"products": [...]} or {"data": [...]}
        if (is_array($decoded) && ! isset($decoded[0])) {
            foreach (['products', 'items', 'data'] as $wrapKey) {
                if (isset($decoded[$wrapKey]) && is_array($decoded[$wrapKey])) {
                    $decoded = $decoded[$wrapKey];
                    break;
                }
            }
        }

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('JSON file must contain an array of product objects.');
        }

        $items = [];
        foreach ($decoded as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $mapped = [];
            foreach ($entry as $key => $val) {
                $mapped[$this->normalizeColumnName((string) $key)] = is_scalar($val) ? trim((string) $val) : $val;
            }

            $items[] = $this->mapNormalizedRow($mapped);
        }

        return $items;
    }

    /**
     * Parse Plain Text / CSV formatted file.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parsePlainText(string $path): array
    {
        return $this->parseCsv($path);
    }

    /**
     * Normalize column names to standard snake_case keys.
     */
    protected function normalizeColumnName(string $name): string
    {
        $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $name), '_'));

        return match ($clean) {
            'product_name', 'product', 'title', 'item_name', 'item' => 'name',
            'product_sku', 'item_code', 'code', 'part_number', 'part_no' => 'sku',
            'category_name', 'category_id', 'cat' => 'category',
            'brand_name', 'brand_id', 'manufacturer', 'make' => 'brand',
            'model_name', 'model_id', 'series' => 'model',
            'unit_price', 'selling_price', 'retail_price', 'regular_price', 'msrp' => 'price',
            'cost', 'buy_price', 'purchase_price', 'cogs' => 'cost_price',
            'discount_price', 'special_price', 'promo_price', 'promotional_sale_price' => 'sale_price',
            'stock', 'quantity', 'qty', 'initial_stock' => 'stock_quantity',
            'serial_tracking', 'serialized', 'is_serialized', 'has_serial', 'track_serial', 'requires_serial' => 'requires_serial_tracking',
            'manufacturer_part_number', 'mpn_code' => 'mpn',
            'barcode', 'upc', 'ean' => 'upc_ean',
            'tariff_code', 'hs', 'export_code' => 'hs_code',
            'unspsc', 'unspsc_code' => 'unspsc_code',
            'short_desc', 'summary' => 'short_description',
            'desc', 'details', 'full_description', 'technical_overview' => 'description',
            'cpu_socket' => 'socket',
            'chipset_compatibility', 'chip' => 'chipset',
            'form', 'factor' => 'form_factor',
            'tdp', 'power_watts' => 'tdp_watts',
            'recommended_psu', 'recommended_psu_watts', 'psu_watts', 'psu', 'power_requirement' => 'power_requirement_watts',
            'length', 'length_cm' => 'length_cm',
            'width', 'width_cm' => 'width_cm',
            'height', 'height_cm' => 'height_cm',
            'hazmat', 'hazardous', 'lithium_battery' => 'is_hazmat',
            'active', 'status', 'published' => 'is_active',
            'featured', 'portal_featured' => 'is_featured',
            'low_stock', 'low_stock_limit', 'alert_limit' => 'low_stock_threshold',
            'safety_margin', 'margin_floor', 'min_margin', 'margin_percentage', 'min_margin_percentage' => 'min_margin_percentage',
            'minimum_order_quantity', 'min_order_qty' => 'moq',
            'case_pack', 'pack_multiple' => 'case_pack_multiple',
            'max_order_qty', 'max_qty' => 'max_order_quantity',
            'minimum_advertised_price', 'map' => 'map_price',
            'mfg_warranty_months', 'manufacturer_warranty', 'mfg_warranty' => 'manufacturer_warranty_months',
            'dist_warranty_months', 'distributor_warranty', 'dist_warranty' => 'distributor_warranty_months',
            'warranty', 'warranty_text' => 'warranty_period',
            'tier_pricing', 'price_tiers', 'volume_tiers', 'pricing_tiers', 'tiers' => 'volume_pricing',
            default => $clean,
        };
    }

    /**
     * Map raw row dictionary into standardized product record.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function mapNormalizedRow(array $row): array
    {
        $base = [
            'name' => $row['name'] ?? null,
            'sku' => $row['sku'] ?? null,
            'category' => $row['category'] ?? null,
            'brand' => $row['brand'] ?? null,
            'model' => $row['model'] ?? null,
            'price' => $row['price'] ?? 0,
            'cost_price' => $row['cost_price'] ?? 0,
            'sale_price' => $row['sale_price'] ?? null,
            'stock_quantity' => $row['stock_quantity'] ?? 0,
            'requires_serial_tracking' => $row['requires_serial_tracking'] ?? null,
            'mpn' => $row['mpn'] ?? null,
            'upc_ean' => $row['upc_ean'] ?? null,
            'hs_code' => $row['hs_code'] ?? null,
            'unspsc_code' => $row['unspsc_code'] ?? null,
            'short_description' => $row['short_description'] ?? null,
            'description' => $row['description'] ?? null,
            'socket' => $row['socket'] ?? null,
            'chipset' => $row['chipset'] ?? null,
            'form_factor' => $row['form_factor'] ?? null,
            'tdp_watts' => $row['tdp_watts'] ?? null,
            'power_requirement_watts' => $row['power_requirement_watts'] ?? ($row['recommended_psu'] ?? null),
            'warranty_period' => $row['warranty_period'] ?? null,
            'manufacturer_warranty_months' => $row['manufacturer_warranty_months'] ?? null,
            'distributor_warranty_months' => $row['distributor_warranty_months'] ?? null,
            'is_active' => $row['is_active'] ?? '1',
            'is_featured' => $row['is_featured'] ?? '0',
            'low_stock_threshold' => $row['low_stock_threshold'] ?? 5,
            'weight_kg' => $row['weight_kg'] ?? ($row['weight'] ?? null),
            'length_cm' => $row['length_cm'] ?? null,
            'width_cm' => $row['width_cm'] ?? null,
            'height_cm' => $row['height_cm'] ?? null,
            'is_hazmat' => $row['is_hazmat'] ?? '1',
            'map_price' => $row['map_price'] ?? null,
            'min_margin_percentage' => $row['min_margin_percentage'] ?? '10.0',
            'moq' => $row['moq'] ?? 1,
            'case_pack_multiple' => $row['case_pack_multiple'] ?? 1,
            'max_order_quantity' => $row['max_order_quantity'] ?? null,
            'volume_pricing' => $row['volume_pricing'] ?? null,
        ];

        return array_merge($row, $base);
    }

    /**
     * Extract Extended Technical Specifications key-value pairs from row data.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, string>
     */
    protected function extractSpecifications(array $row): array
    {
        $specs = [];

        // 1. Direct recognized hardware specification columns
        $specMap = [
            'cpu' => 'Processor (CPU)',
            'processor' => 'Processor (CPU)',
            'gpu' => 'Graphics (GPU)',
            'graphics' => 'Graphics (GPU)',
            'ram' => 'Memory (RAM)',
            'memory' => 'Memory (RAM)',
            'storage' => 'Storage (SSD/HDD)',
            'ssd' => 'Storage (SSD/HDD)',
            'display' => 'Display Screen',
            'screen' => 'Display Screen',
            'battery' => 'Battery Capacity',
            'operating_system' => 'Operating System',
            'os' => 'Operating System',
            'wireless' => 'Wireless & Networking',
            'wifi' => 'Wireless & Networking',
            'connectivity' => 'Wireless & Networking',
            'ports' => 'I/O Ports',
            'keyboard' => 'Keyboard',
            'webcam' => 'Webcam',
            'camera' => 'Webcam',
            'audio' => 'Audio System',
            'cooling' => 'Cooling System',
            'color' => 'Chassis Color',
        ];

        foreach ($specMap as $colKey => $displayName) {
            if (! empty($row[$colKey]) && is_scalar($row[$colKey])) {
                $specs[$displayName] = trim((string) $row[$colKey]);
            }
        }

        // Add Weight spec if weight string or weight_kg is present
        if (! empty($row['weight_text']) && is_scalar($row['weight_text'])) {
            $specs['Weight'] = trim((string) $row['weight_text']);
        } elseif (! empty($row['weight']) && is_scalar($row['weight']) && ! is_numeric($row['weight'])) {
            $specs['Weight'] = trim((string) $row['weight']);
        } elseif (! empty($row['weight_kg']) && is_numeric($row['weight_kg'])) {
            $specs['Weight'] = trim((string) $row['weight_kg']).' kg';
        }

        // 2. Parse dedicated 'specifications' or 'specs' column if provided as JSON or key-value list
        $rawSpecs = $row['specifications'] ?? ($row['specs'] ?? null);
        if (! empty($rawSpecs)) {
            if (is_array($rawSpecs)) {
                $specs = array_merge($specs, $rawSpecs);
            } elseif (is_string($rawSpecs)) {
                $decoded = json_decode($rawSpecs, true);
                if (is_array($decoded)) {
                    $specs = array_merge($specs, $decoded);
                } else {
                    $pairs = preg_split('/[;\r\n|]+/', $rawSpecs);
                    foreach ($pairs as $pair) {
                        $pair = trim($pair);
                        if (str_contains($pair, ':')) {
                            [$k, $v] = explode(':', $pair, 2);
                            if (trim($k) !== '' && trim($v) !== '') {
                                $specs[trim($k)] = trim($v);
                            }
                        } elseif (str_contains($pair, '=')) {
                            [$k, $v] = explode('=', $pair, 2);
                            if (trim($k) !== '' && trim($v) !== '') {
                                $specs[trim($k)] = trim($v);
                            }
                        }
                    }
                }
            }
        }

        // 3. Any additional custom columns from the Excel file
        $standardColumns = [
            'name', 'sku', 'category', 'brand', 'model', 'price', 'cost_price', 'sale_price',
            'stock_quantity', 'requires_serial_tracking', 'mpn', 'upc_ean', 'hs_code', 'unspsc_code',
            'short_description', 'description', 'socket', 'chipset', 'form_factor', 'tdp_watts',
            'power_requirement_watts', 'recommended_psu', 'warranty_period', 'is_active', 'is_featured',
            'low_stock_threshold', 'specifications', 'specs', 'weight_kg', 'length_cm', 'width_cm', 'height_cm',
            'is_hazmat', 'map_price', 'min_margin_percentage', 'moq', 'case_pack_multiple', 'max_order_quantity',
            'manufacturer_warranty_months', 'distributor_warranty_months', 'volume_pricing', 'tier_pricing',
            'weight', 'weight_text',
        ];

        foreach ($row as $key => $val) {
            if (! in_array($key, $standardColumns, true) && ! isset($specMap[$key])) {
                if (is_scalar($val) && trim((string) $val) !== '') {
                    $title = ucwords(str_replace('_', ' ', $key));
                    $specs[$title] = trim((string) $val);
                }
            }
        }

        return $specs;
    }

    /**
     * Parse and sync B2B volume pricing breaks for a product.
     */
    protected function syncVolumePricingTiers(Product $product, mixed $volumePricing): void
    {
        $tiers = [];

        if (is_array($volumePricing)) {
            $tiers = $volumePricing;
        } elseif (is_string($volumePricing)) {
            $decoded = json_decode($volumePricing, true);
            if (is_array($decoded)) {
                $tiers = $decoded;
            } else {
                // Parse semicolon-separated entries e.g. "10-49: 570.00; 50+: 555.00"
                $parts = preg_split('/[;\r\n|]+/', $volumePricing);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (empty($part) || ! str_contains($part, ':')) {
                        continue;
                    }
                    [$qtyPart, $pricePart] = explode(':', $part, 2);
                    $qtyPart = trim($qtyPart);
                    $cleanPrice = preg_replace('/[^\d.]/', '', trim($pricePart));
                    if (! is_numeric($cleanPrice)) {
                        continue;
                    }
                    $price = (float) $cleanPrice;

                    if (str_contains($qtyPart, '-')) {
                        [$min, $max] = explode('-', $qtyPart, 2);
                        $minQty = (int) preg_replace('/[^\d]/', '', $min);
                        $maxQty = (int) preg_replace('/[^\d]/', '', $max);
                    } elseif (str_ends_with($qtyPart, '+')) {
                        $minQty = (int) preg_replace('/[^\d]/', '', $qtyPart);
                        $maxQty = null;
                    } else {
                        $minQty = (int) preg_replace('/[^\d]/', '', $qtyPart);
                        $maxQty = null;
                    }

                    if ($minQty > 0) {
                        $tiers[] = [
                            'min_quantity' => $minQty,
                            'max_quantity' => $maxQty > 0 ? $maxQty : null,
                            'unit_price' => $price,
                        ];
                    }
                }
            }
        }

        if (! empty($tiers)) {
            $product->priceTiers()->delete();
            foreach ($tiers as $tier) {
                $minQty = isset($tier['min_quantity']) ? (int) $tier['min_quantity'] : (isset($tier['min']) ? (int) $tier['min'] : 1);
                $maxQty = isset($tier['max_quantity']) && $tier['max_quantity'] !== '' ? (int) $tier['max_quantity'] : (isset($tier['max']) && $tier['max'] !== '' ? (int) $tier['max'] : null);
                $cleanPrice = isset($tier['unit_price']) ? preg_replace('/[^\d.]/', '', (string) $tier['unit_price']) : (isset($tier['price']) ? preg_replace('/[^\d.]/', '', (string) $tier['price']) : null);
                $unitPrice = is_numeric($cleanPrice) ? (float) $cleanPrice : null;

                if ($minQty > 0 && $unitPrice !== null) {
                    ProductPriceTier::create([
                        'product_id' => $product->id,
                        'customer_group_id' => ! empty($tier['customer_group_id']) ? (int) $tier['customer_group_id'] : null,
                        'min_quantity' => $minQty,
                        'max_quantity' => $maxQty,
                        'unit_price' => $unitPrice,
                        'currency' => 'USD',
                    ]);
                }
            }
        }
    }

    /**
     * Stream a starter CSV or Excel file for products import.
     */
    public function downloadTemplate(string $format = 'csv'): StreamedResponse
    {
        $headers = [
            'name',
            'sku',
            'category',
            'brand',
            'model',
            'price',
            'cost_price',
            'stock_quantity',
            'requires_serial_tracking',
            'mpn',
            'upc_ean',
            'socket',
            'chipset',
            'form_factor',
            'tdp_watts',
            'warranty_period',
            'short_description',
        ];

        $sampleRows = [
            [
                'Intel Core i7-14700K Desktop Processor (20 Cores, 28 Threads)',
                'CPU-INT-14700K',
                'Processors',
                'Intel',
                'Core i7',
                '419.99',
                '350.00',
                '0',
                '1', // Serialized: stock must be 0 until company serials are adjusted
                'BX8071514700K',
                '735858546942',
                'LGA1700',
                'Intel 700 Series',
                'Desktop',
                '125',
                '3 Years Manufacturer Warranty',
                'Latest 14th Gen Intel Core i7 desktop processor with integrated Intel UHD Graphics 770.',
            ],
            [
                'ASUS TUF Gaming GeForce RTX 4070 Ti SUPER 16GB OC Edition',
                'GPU-ASUS-4070TIS',
                'Graphics Cards',
                'ASUS',
                'TUF Gaming',
                '849.99',
                '720.00',
                '0',
                '1', // Serialized
                'TUF-RTX4070TIS-O16G-GAMING',
                '197105423851',
                '',
                'GeForce RTX 4070 Ti SUPER',
                'PCIe 4.0 x16',
                '285',
                '3 Years Manufacturer Warranty',
                'Military-grade capacitors and axial-tech fans designed for durability and extreme gaming performance.',
            ],
            [
                'Corsair Vengeance RGB 32GB (2x16GB) DDR5 6000MHz CL30',
                'RAM-COR-DDR5-32G',
                'Memory',
                'Corsair',
                'Vengeance RGB',
                '119.99',
                '85.00',
                '45', // Non-serialized: can have direct stock
                '0',
                'CMH32GX5M2B6000C30',
                '840006699569',
                '',
                'Intel XMP 3.0 / AMD EXPO',
                'DDR5 DIMM',
                '',
                'Lifetime Limited Warranty',
                'High performance overclocked DDR5 memory with ten-zone dynamic RGB lighting.',
            ],
            [
                'Samsung 990 PRO 2TB PCIe Gen 4.0 x4 NVMe M.2 2280 Internal SSD',
                'SSD-SAM-990PRO-2TB',
                'Storage',
                'Samsung',
                '990 PRO',
                '179.99',
                '130.00',
                '30', // Non-serialized: direct quantity
                '0',
                'MZ-V9P2T0B/AM',
                '887276688329',
                '',
                'Samsung Pascal Controller',
                'M.2 2280',
                '',
                '5 Years Limited Warranty',
                'Sequential read speeds up to 7,450 MB/s and write speeds up to 6,900 MB/s.',
            ],
        ];

        if ($format === 'xlsx') {
            return response()->streamDownload(function () use ($headers, $sampleRows) {
                $spreadsheet = new Spreadsheet;
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Products Template');

                // Add header
                $sheet->fromArray([$headers], null, 'A1');

                // Style header row
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E293B'],
                    ],
                ];
                $sheet->getStyle('A1:'.Coordinate::stringFromColumnIndex(count($headers)).'1')
                    ->applyFromArray($headerStyle);

                // Add sample rows
                $sheet->fromArray($sampleRows, null, 'A2');

                foreach (range(1, count($headers)) as $col) {
                    $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
                }

                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, 'products_import_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        // Default to CSV with UTF-8 BOM
        return response()->streamDownload(function () use ($headers, $sampleRows) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it with proper encoding
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'products_import_template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
