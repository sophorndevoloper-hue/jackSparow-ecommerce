<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Make;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\SerialNumber;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ComputerPartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Brands
        $brandsData = [
            ['name' => 'Intel', 'slug' => 'intel', 'website' => 'https://www.intel.com', 'description' => 'Leading producer of microprocessors and enterprise compute.'],
            ['name' => 'AMD', 'slug' => 'amd', 'website' => 'https://www.amd.com', 'description' => 'High-performance computing, EPYC and Ryzen processors.'],
            ['name' => 'NVIDIA', 'slug' => 'nvidia', 'website' => 'https://www.nvidia.com', 'description' => 'Pioneer of GPU-accelerated computing and AI datacenter GPUs.'],
            ['name' => 'ASUS', 'slug' => 'asus', 'website' => 'https://www.asus.com', 'description' => 'Premier motherboard, GPU, and server hardware manufacturer.'],
            ['name' => 'MSI', 'slug' => 'msi', 'website' => 'https://www.msi.com', 'description' => 'Global leader in motherboards and workstation components.'],
            ['name' => 'Gigabyte', 'slug' => 'gigabyte', 'website' => 'https://www.gigabyte.com', 'description' => 'Manufacturer of enterprise server motherboards and GPUs.'],
            ['name' => 'Corsair', 'slug' => 'corsair', 'website' => 'https://www.corsair.com', 'description' => 'High-performance memory, ATX 3.0 PSUs, and cooling.'],
            ['name' => 'Samsung', 'slug' => 'samsung', 'website' => 'https://www.samsung.com', 'description' => 'Industry benchmark enterprise & client NVMe SSD storage.'],
            ['name' => 'Western Digital', 'slug' => 'western-digital', 'website' => 'https://www.westerndigital.com', 'description' => 'Enterprise Ultrastar HDDs and WD_BLACK NVMe SSDs.'],
            ['name' => 'NZXT', 'slug' => 'nzxt', 'website' => 'https://www.nzxt.com', 'description' => 'PC chassis, Kraken AIO liquid coolers, and components.'],
            ['name' => 'Lian Li', 'slug' => 'lian-li', 'website' => 'https://www.lian-li.com', 'description' => 'Enthusiast aluminum chassis and modular cooling.'],
        ];

        $brands = [];
        foreach ($brandsData as $b) {
            $brands[$b['slug']] = Brand::firstOrCreate(['slug' => $b['slug']], $b);
        }

        // 2. Categories
        $categoriesData = [
            ['name' => 'Processors (CPU)', 'slug' => 'processors-cpu', 'description' => 'Desktop & Server CPUs from AMD and Intel.', 'sort_order' => 1],
            ['name' => 'Graphics Cards (GPU)', 'slug' => 'graphics-cards-gpu', 'description' => 'NVIDIA GeForce & AMD Radeon graphics cards.', 'sort_order' => 2],
            ['name' => 'Motherboards', 'slug' => 'motherboards', 'description' => 'AM5 and LGA1700 motherboards with DDR5 and PCIe 5.0.', 'sort_order' => 3],
            ['name' => 'Memory (RAM)', 'slug' => 'memory-ram', 'description' => 'Enterprise ECC and high-speed DDR5 memory kits.', 'sort_order' => 4],
            ['name' => 'Storage (SSD & HDD)', 'slug' => 'storage-ssd-hdd', 'description' => 'Gen4 & Gen5 PCIe NVMe M.2 SSDs and enterprise HDDs.', 'sort_order' => 5],
            ['name' => 'Power Supplies (PSU)', 'slug' => 'power-supplies-psu', 'description' => 'ATX 3.0 PCIe 5.0 modular power supplies.', 'sort_order' => 6],
            ['name' => 'Cooling Systems', 'slug' => 'cooling-systems', 'description' => 'AIO liquid CPU coolers, enterprise heatsinks.', 'sort_order' => 7],
            ['name' => 'PC Cases', 'slug' => 'pc-cases', 'description' => 'High-airflow chassis and rackmount cases.', 'sort_order' => 8],
            ['name' => 'Monitors', 'slug' => 'monitors', 'description' => 'High refresh rate monitors and color-critical panels.', 'sort_order' => 9],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['slug']] = Category::firstOrCreate(['slug' => $c['slug']], $c);
        }

        $wholesaleGroup = CustomerGroup::where('code', 'WHOLESALE_1')->first();
        $siGroup = CustomerGroup::where('code', 'SYSTEM_INTEGRATOR')->first();
        $enterpriseGroup = CustomerGroup::where('code', 'ENTERPRISE')->first();
        $mainWarehouse = Warehouse::first();

        // 3. Realistic Hardware Products with B2B Specs & Identifiers
        $productsData = [
            [
                'category_slug' => 'processors-cpu',
                'brand_slug' => 'amd',
                'name' => 'AMD Ryzen 9 7950X3D Desktop Processor',
                'slug' => 'amd-ryzen-9-7950x3d',
                'sku' => 'CPU-AMD-7950X3D',
                'mpn' => '100-100000908WOF',
                'upc_ean' => '730143314916',
                'hs_code' => '8473.30.1180',
                'unspsc_code' => '43201503',
                'short_description' => '16-core flagship CPU with AMD 3D V-Cache technology.',
                'description' => '16 cores and 32 threads built on 5nm Zen 4 architecture with 128MB L3 cache for workstation compute and elite gaming.',
                'price' => 699.00,
                'sale_price' => 649.00,
                'cost_price' => 520.00,
                'map_price' => 649.00,
                'min_margin_percentage' => 10.00,
                'moq' => 1,
                'case_pack_multiple' => 5,
                'stock_quantity' => 45,
                'low_stock_threshold' => 10,
                'requires_serial_tracking' => true,
                'manufacturer_warranty_months' => 36,
                'distributor_warranty_months' => 12,
                'form_factor' => 'Socket AM5',
                'socket' => 'AM5',
                'chipset' => 'X670E, B650',
                'tdp_watts' => 120,
                'power_requirement_watts' => 750,
                'weight_kg' => 0.280,
                'is_active' => true,
                'is_featured' => true,
                'warranty_period' => '3-Year Manufacturer Warranty',
                'specifications' => [
                    'Socket' => 'AM5',
                    'Cores / Threads' => '16 Cores / 32 Threads',
                    'Base Clock' => '4.2 GHz',
                    'Boost Clock' => '5.7 GHz',
                    'L3 Cache' => '128 MB (64MB 3D V-Cache)',
                    'TDP' => '120W',
                    'Memory Support' => 'DDR5 up to 5200 MT/s (EXPO)',
                    'PCIe Version' => 'PCIe 5.0 (24 Lanes)',
                ],
                'tiers' => [
                    ['group_id' => $wholesaleGroup?->id, 'min' => 5, 'max' => 19, 'price' => 595.00],
                    ['group_id' => $wholesaleGroup?->id, 'min' => 20, 'max' => null, 'price' => 575.00],
                    ['group_id' => $enterpriseGroup?->id, 'min' => 10, 'max' => null, 'price' => 565.00],
                ],
                'serials' => ['SN-7950X3D-9001', 'SN-7950X3D-9002', 'SN-7950X3D-9003', 'SN-7950X3D-9004'],
            ],
            [
                'category_slug' => 'processors-cpu',
                'brand_slug' => 'intel',
                'name' => 'Intel Core i9-14900K Flagship Processor',
                'slug' => 'intel-core-i9-14900k',
                'sku' => 'CPU-INT-14900K',
                'mpn' => 'BX8071514900K',
                'upc_ean' => '735858547635',
                'hs_code' => '8473.30.1180',
                'unspsc_code' => '43201503',
                'short_description' => '24-core unlocked desktop processor with 6.0 GHz Turbo.',
                'description' => '24 cores (8 P-cores + 16 E-cores) and 32 threads with hybrid architecture for extreme productivity and content creation.',
                'price' => 589.00,
                'sale_price' => 549.00,
                'cost_price' => 460.00,
                'map_price' => 549.00,
                'min_margin_percentage' => 10.00,
                'moq' => 1,
                'case_pack_multiple' => 5,
                'stock_quantity' => 30,
                'low_stock_threshold' => 6,
                'requires_serial_tracking' => true,
                'manufacturer_warranty_months' => 36,
                'distributor_warranty_months' => 12,
                'form_factor' => 'LGA1700',
                'socket' => 'LGA1700',
                'chipset' => 'Z790, B760',
                'tdp_watts' => 253,
                'power_requirement_watts' => 850,
                'weight_kg' => 0.290,
                'is_active' => true,
                'is_featured' => true,
                'warranty_period' => '3-Year Manufacturer Warranty',
                'specifications' => [
                    'Socket' => 'LGA1700',
                    'Cores / Threads' => '24 Cores (8P + 16E) / 32 Threads',
                    'Base Frequency' => '3.2 GHz',
                    'Max Turbo Frequency' => '6.0 GHz',
                    'Cache' => '36 MB Intel Smart Cache',
                    'Base Power' => '125W',
                    'Max Turbo Power' => '253W',
                    'Memory Support' => 'DDR5 5600 & DDR4 3200',
                ],
                'tiers' => [
                    ['group_id' => $wholesaleGroup?->id, 'min' => 5, 'max' => 19, 'price' => 510.00],
                    ['group_id' => $wholesaleGroup?->id, 'min' => 20, 'max' => null, 'price' => 495.00],
                ],
                'serials' => ['SN-14900K-8001', 'SN-14900K-8002', 'SN-14900K-8003'],
            ],
            [
                'category_slug' => 'graphics-cards-gpu',
                'brand_slug' => 'asus',
                'name' => 'ASUS ROG Strix GeForce RTX 4090 24GB OC Edition',
                'slug' => 'asus-rog-strix-rtx-4090-oc',
                'sku' => 'GPU-ASUS-4090-OC',
                'mpn' => 'ROG-STRIX-RTX4090-O24G-GAMING',
                'upc_ean' => '195553934091',
                'hs_code' => '8473.30.1180',
                'unspsc_code' => '43201401',
                'short_description' => 'Flagship Ada Lovelace 24GB graphics card with vapor chamber cooling.',
                'description' => 'Unparalleled AI acceleration and 4K/8K rendering powered by NVIDIA Ada Lovelace architecture, 24GB GDDR6X, and 16384 CUDA cores.',
                'price' => 1999.00,
                'sale_price' => null,
                'cost_price' => 1650.00,
                'map_price' => 1999.00,
                'min_margin_percentage' => 12.00,
                'moq' => 1,
                'case_pack_multiple' => 2,
                'stock_quantity' => 14,
                'low_stock_threshold' => 4,
                'requires_serial_tracking' => true,
                'manufacturer_warranty_months' => 36,
                'distributor_warranty_months' => 12,
                'form_factor' => '3.5-Slot ATX',
                'socket' => null,
                'chipset' => 'GeForce RTX 4090',
                'tdp_watts' => 450,
                'power_requirement_watts' => 1000,
                'weight_kg' => 2.500,
                'is_active' => true,
                'is_featured' => true,
                'warranty_period' => '3-Year Manufacturer Warranty',
                'specifications' => [
                    'VRAM' => '24 GB GDDR6X',
                    'Memory Bus' => '384-bit',
                    'Boost Clock' => '2640 MHz',
                    'CUDA Cores' => '16384',
                    'Interface' => 'PCI Express 4.0 x16',
                    'Power Connector' => '1x 16-pin (12VHPWR)',
                    'TDP' => '450W',
                    'Recommended PSU' => '1000W',
                ],
                'tiers' => [
                    ['group_id' => $wholesaleGroup?->id, 'min' => 2, 'max' => 5, 'price' => 1870.00],
                    ['group_id' => $wholesaleGroup?->id, 'min' => 6, 'max' => null, 'price' => 1820.00],
                    ['group_id' => $enterpriseGroup?->id, 'min' => 4, 'max' => null, 'price' => 1800.00],
                ],
                'serials' => ['SN-RTX4090-101', 'SN-RTX4090-102', 'SN-RTX4090-103'],
            ],
            [
                'category_slug' => 'motherboards',
                'brand_slug' => 'msi',
                'name' => 'MSI MEG X670E ACE Flagship AM5 Motherboard',
                'slug' => 'msi-meg-x670e-ace',
                'sku' => 'MB-MSI-X670E-ACE',
                'mpn' => 'MEG-X670E-ACE',
                'upc_ean' => '824142299881',
                'hs_code' => '8473.30.1180',
                'unspsc_code' => '43201513',
                'short_description' => 'Premium E-ATX AMD AM5 motherboard with 22+2+1 power phases.',
                'description' => 'Extreme VRM power design, onboard 10G Super LAN + 2.5G LAN, Wi-Fi 6E, dual PCIe 5.0 x16 slots, and 4x M.2 slots.',
                'price' => 699.00,
                'sale_price' => 649.00,
                'cost_price' => 510.00,
                'map_price' => 649.00,
                'min_margin_percentage' => 10.00,
                'moq' => 1,
                'case_pack_multiple' => 4,
                'stock_quantity' => 18,
                'low_stock_threshold' => 4,
                'requires_serial_tracking' => true,
                'manufacturer_warranty_months' => 36,
                'distributor_warranty_months' => 12,
                'form_factor' => 'E-ATX',
                'socket' => 'AM5',
                'chipset' => 'AMD X670E',
                'tdp_watts' => 50,
                'power_requirement_watts' => 750,
                'weight_kg' => 2.100,
                'is_active' => true,
                'is_featured' => true,
                'warranty_period' => '3-Year Manufacturer Warranty',
                'specifications' => [
                    'Socket' => 'AM5',
                    'Chipset' => 'AMD X670E',
                    'Form Factor' => 'E-ATX (277 x 304.8 mm)',
                    'Memory Support' => '4x DDR5 (Up to 8000+ MHz OC, Max 192GB)',
                    'PCIe Slots' => '2x PCIe 5.0 x16, 1x PCIe 5.0 x4',
                    'M.2 Slots' => '1x PCIe 5.0 x4 + 3x PCIe 4.0 x4',
                    'Networking' => 'Marvell 10GbE + Intel 2.5GbE + Wi-Fi 6E',
                ],
                'tiers' => [
                    ['group_id' => $wholesaleGroup?->id, 'min' => 4, 'max' => null, 'price' => 580.00],
                ],
                'serials' => ['SN-X670E-501', 'SN-X670E-502'],
            ],
            [
                'category_slug' => 'power-supplies-psu',
                'brand_slug' => 'corsair',
                'name' => 'Corsair HX1000i 1000W 80+ Platinum Fully Modular ATX 3.0 PSU',
                'slug' => 'corsair-hx1000i-1000w-platinum-psu',
                'sku' => 'PSU-COR-HX1000I',
                'mpn' => 'CP-9020259-NA',
                'upc_ean' => '840006659778',
                'hs_code' => '8504.40.6018',
                'unspsc_code' => '39121004',
                'short_description' => '1000W 80 PLUS Platinum certified ATX 3.0 power supply with iCUE digital monitoring.',
                'description' => 'Ultra-low ripple noise, 100% Japanese 105C capacitors, fluid dynamic bearing fan, and native 12VHPWR PCIe 5.0 power delivery.',
                'price' => 259.99,
                'sale_price' => null,
                'cost_price' => 195.00,
                'map_price' => 259.99,
                'min_margin_percentage' => 10.00,
                'moq' => 1,
                'case_pack_multiple' => 4,
                'stock_quantity' => 28,
                'low_stock_threshold' => 5,
                'requires_serial_tracking' => true,
                'manufacturer_warranty_months' => 120,
                'distributor_warranty_months' => 24,
                'form_factor' => 'ATX',
                'socket' => null,
                'chipset' => null,
                'tdp_watts' => 0,
                'power_requirement_watts' => 1000,
                'weight_kg' => 2.200,
                'is_active' => true,
                'is_featured' => false,
                'warranty_period' => '10-Year Manufacturer Warranty',
                'specifications' => [
                    'Total Wattage' => '1000W',
                    'Efficiency' => '80 PLUS Platinum & Cybenetics Platinum',
                    'ATX Standard' => 'ATX 3.0 / PCIe 5.0 Ready (12VHPWR Included)',
                    'Modularity' => 'Fully Modular',
                    'Capacitors' => '100% Japanese 105°C Electrolytic',
                ],
                'tiers' => [
                    ['group_id' => $wholesaleGroup?->id, 'min' => 4, 'max' => null, 'price' => 225.00],
                ],
                'serials' => ['SN-HX1000-01', 'SN-HX1000-02'],
            ],
            [
                'category_slug' => 'memory-ram',
                'brand_slug' => 'corsair',
                'name' => 'Corsair Dominator Titanium 64GB (2x32GB) DDR5-6000 CL30',
                'slug' => 'corsair-dominator-titanium-64gb-ddr5-6000',
                'sku' => 'RAM-COR-DOM-64G',
                'mpn' => 'CMP64GX5M2B6000C30',
                'upc_ean' => '840006692997',
                'hs_code' => '8473.30.1140',
                'unspsc_code' => '32101601',
                'short_description' => 'Enthusiast forged aluminum DDR5 kit with DHX patented cooling.',
                'description' => 'Precision crafted forged aluminum heatspreaders, 11 individually addressable RGB LEDs, Intel XMP 3.0 & AMD EXPO profiles.',
                'price' => 314.99,
                'sale_price' => 294.99,
                'cost_price' => 230.00,
                'map_price' => 294.99,
                'min_margin_percentage' => 10.00,
                'moq' => 1,
                'case_pack_multiple' => 10,
                'stock_quantity' => 35,
                'low_stock_threshold' => 8,
                'requires_serial_tracking' => false,
                'manufacturer_warranty_months' => 120,
                'distributor_warranty_months' => 24,
                'form_factor' => '288-pin DIMM',
                'socket' => null,
                'chipset' => null,
                'tdp_watts' => 15,
                'power_requirement_watts' => 500,
                'weight_kg' => 0.220,
                'is_active' => true,
                'is_featured' => true,
                'warranty_period' => 'Limited Lifetime Warranty',
                'specifications' => [
                    'Memory Type' => 'DDR5',
                    'Capacity' => '64 GB (2 x 32 GB)',
                    'Speed' => '6000 MT/s',
                    'Latency' => '30-36-36-76',
                    'Voltage' => '1.40V',
                    'Profile' => 'AMD EXPO & Intel XMP 3.0',
                ],
                'tiers' => [
                    ['group_id' => $wholesaleGroup?->id, 'min' => 5, 'max' => null, 'price' => 265.00],
                ],
            ],
            [
                'category_slug' => 'storage-ssd-hdd',
                'brand_slug' => 'samsung',
                'name' => 'Samsung 990 PRO 4TB PCIe 4.0 NVMe M.2 SSD with Heatsink',
                'slug' => 'samsung-990-pro-4tb-heatsink',
                'sku' => 'SSD-SAM-990PRO-4TB-HS',
                'mpn' => 'MZ-V9P4T0CW',
                'upc_ean' => '887276785233',
                'hs_code' => '8523.51.0000',
                'unspsc_code' => '43201801',
                'short_description' => '4TB flagship SSD with integrated slim heatsink and 7,450 MB/s reads.',
                'description' => 'Samsung in-house Pascal controller with 4GB LPDDR4 cache and 2400 TBW endurance for heavy data science, 8K video, and gaming.',
                'price' => 379.99,
                'sale_price' => 349.99,
                'cost_price' => 275.00,
                'map_price' => 349.99,
                'min_margin_percentage' => 10.00,
                'moq' => 1,
                'case_pack_multiple' => 10,
                'stock_quantity' => 40,
                'low_stock_threshold' => 8,
                'requires_serial_tracking' => true,
                'manufacturer_warranty_months' => 60,
                'distributor_warranty_months' => 12,
                'form_factor' => 'M.2 (2280)',
                'socket' => null,
                'chipset' => null,
                'tdp_watts' => 9,
                'power_requirement_watts' => 500,
                'weight_kg' => 0.080,
                'is_active' => true,
                'is_featured' => true,
                'warranty_period' => '5-Year / 2400 TBW Warranty',
                'specifications' => [
                    'Form Factor' => 'M.2 2280 with Integrated Heatsink',
                    'Interface' => 'PCIe Gen 4.0 x4, NVMe 2.0',
                    'Sequential Read' => 'Up to 7,450 MB/s',
                    'Sequential Write' => 'Up to 6,900 MB/s',
                    'Endurance' => '2400 TBW',
                    'DRAM Cache' => '4GB LPDDR4',
                ],
                'tiers' => [
                    ['group_id' => $wholesaleGroup?->id, 'min' => 10, 'max' => null, 'price' => 310.00],
                ],
                'serials' => ['SN-SAM990-4T-01', 'SN-SAM990-4T-02'],
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $data) {
            $cat = $categories[$data['category_slug']];
            $brand = $brands[$data['brand_slug']];

            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $cat->id,
                    'brand_id' => $brand->id,
                    'make_id' => Make::where('slug', $data['brand_slug'])->value('id'),
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'mpn' => $data['mpn'] ?? null,
                    'upc_ean' => $data['upc_ean'] ?? null,
                    'hs_code' => $data['hs_code'] ?? null,
                    'unspsc_code' => $data['unspsc_code'] ?? null,
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'],
                    'cost_price' => $data['cost_price'],
                    'map_price' => $data['map_price'] ?? null,
                    'min_margin_percentage' => $data['min_margin_percentage'] ?? 10.00,
                    'moq' => $data['moq'] ?? 1,
                    'case_pack_multiple' => $data['case_pack_multiple'] ?? 1,
                    'stock_quantity' => $data['stock_quantity'],
                    'low_stock_threshold' => $data['low_stock_threshold'],
                    'requires_serial_tracking' => $data['requires_serial_tracking'] ?? false,
                    'manufacturer_warranty_months' => $data['manufacturer_warranty_months'] ?? 36,
                    'distributor_warranty_months' => $data['distributor_warranty_months'] ?? 12,
                    'form_factor' => $data['form_factor'] ?? null,
                    'socket' => $data['socket'] ?? null,
                    'chipset' => $data['chipset'] ?? null,
                    'tdp_watts' => $data['tdp_watts'] ?? null,
                    'power_requirement_watts' => $data['power_requirement_watts'] ?? null,
                    'weight_kg' => $data['weight_kg'] ?? null,
                    'is_active' => $data['is_active'],
                    'is_featured' => $data['is_featured'],
                    'specifications' => $data['specifications'],
                    'specs' => $data['specifications'],
                    'warranty_period' => $data['warranty_period'],
                ],
            );

            // Seed Price Tiers
            if (! empty($data['tiers'])) {
                foreach ($data['tiers'] as $tier) {
                    ProductPriceTier::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'customer_group_id' => $tier['group_id'],
                            'min_quantity' => $tier['min'],
                        ],
                        [
                            'max_quantity' => $tier['max'],
                            'unit_price' => $tier['price'],
                            'currency' => 'USD',
                        ]
                    );
                }
            }

            // Seed Serials
            if (! empty($data['serials']) && $mainWarehouse) {
                foreach ($data['serials'] as $sn) {
                    SerialNumber::updateOrCreate(
                        ['serial_number' => $sn],
                        [
                            'product_id' => $product->id,
                            'warehouse_id' => $mainWarehouse->id,
                            'status' => SerialNumber::STATUS_IN_STOCK,
                            'cost_price' => $product->cost_price,
                            'inbound_date' => now()->toDateString(),
                        ]
                    );
                }
            }

            $createdProducts[] = $product;
        }

        // 4. Sample Customer & Orders
        $customerUser = User::where('email', 'customer@example.com')->first();
        if (! $customerUser) {
            $customerUser = User::create([
                'name' => 'Apex Systems Inc (OEM)',
                'email' => 'customer@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        if (Order::count() === 0 && count($createdProducts) >= 3) {
            $order1 = Order::create([
                'order_number' => 'ORD-20260905-9901',
                'user_id' => $customerUser->id,
                'customer_name' => 'Apex Systems Integrator',
                'customer_email' => 'customer@example.com',
                'customer_phone' => '+1 (555) 234-5678',
                'status' => 'delivered',
                'payment_status' => 'paid',
                'payment_method' => 'wire_transfer',
                'subtotal' => 3897.00,
                'tax_amount' => 0.00,
                'shipping_fee' => 45.00,
                'discount_amount' => 200.00,
                'total_amount' => 3742.00,
                'shipping_address' => [
                    'recipient' => 'Apex Systems Integration Facility',
                    'street' => '100 Silicon Way',
                    'city' => 'Austin',
                    'state' => 'TX',
                    'postal_code' => '78701',
                    'country' => 'United States',
                ],
                'billing_address' => [
                    'recipient' => 'Apex Systems Accounts Payable',
                    'street' => '100 Silicon Way',
                    'city' => 'Austin',
                    'state' => 'TX',
                    'postal_code' => '78701',
                    'country' => 'United States',
                ],
                'customer_notes' => 'Deliver to Warehouse Bay 3.',
                'admin_notes' => 'Net 30 invoice dispatched. FedEx Freight tracking #9401284901.',
                'created_at' => now()->subDays(2),
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $createdProducts[0]->id, // 7950X3D
                'product_name' => $createdProducts[0]->name,
                'product_sku' => $createdProducts[0]->sku,
                'unit_price' => 595.00,
                'quantity' => 5,
                'total_price' => 2975.00,
                'specifications_snapshot' => $createdProducts[0]->specifications,
            ]);
        }
    }
}
