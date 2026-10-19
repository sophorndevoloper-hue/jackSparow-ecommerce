<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Quote;
use App\Models\SerialNumber;
use App\Models\User;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\WarehouseSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([
        RoleAndPermissionSeeder::class,
        BackendMenuSeeder::class,
        WarehouseSeeder::class,
    ]);

    $this->admin = User::factory()->create(['is_approved' => true]);
    $this->admin->assignRole(Role::findByName('admin', 'backend'));
});

it('displays the analytics reporting dashboard with executive KPI cards', function () {
    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.reports.index'));

    $response->assertStatus(200);
    $response->assertSee('Financial, Inventory &amp; Sales Analytics', false);
    $response->assertSee('Realized Revenue');
    $response->assertSee('Gross Profit');
    $response->assertSee('A/R Outstanding');
    $response->assertSee('Dead Capital Risk');
    $response->assertSee('RFQ Win Rate');
});

it('calculates real gross margin by serial number, sku, and customer tier safely', function () {
    $goldGroup = CustomerGroup::create([
        'name' => 'Gold Tier Partner',
        'slug' => 'gold-tier-partner',
        'code' => 'GOLD',
        'default_discount_percentage' => 10.00,
        'payment_terms_days' => 30,
        'credit_limit' => 100000.00,
        'is_active' => true,
    ]);

    $customer = Customer::factory()->create([
        'company_name' => 'Apex Cloud Datacenter',
        'customer_group_id' => $goldGroup->id,
    ]);

    $category = Category::factory()->create(['name' => 'Graphics Cards']);
    $brand = Brand::factory()->create(['name' => 'NVIDIA']);

    $product = Product::factory()->create([
        'name' => 'GeForce RTX 4090 24GB',
        'sku' => 'GPU-4090-24G',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'cost_price' => 1200.00,
        'price' => 1600.00,
        'requires_serial_tracking' => true,
    ]);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'shipped',
        'payment_status' => 'paid',
    ]);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
        'unit_price' => 1600.00,
        'quantity' => 1,
        'total_price' => 1600.00,
    ]);

    $serial = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => 1,
        'order_id' => $order->id,
        'order_item_id' => $item->id,
        'serial_number' => 'SN-4090-PROFIT-001',
        'status' => SerialNumber::STATUS_SOLD,
        'cost_price' => 1200.00,
        'outbound_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.reports.index', ['tab' => 'gross-margin']));

    $response->assertStatus(200);
    $response->assertSee('SN-4090-PROFIT-001');
    $response->assertSee('GPU-4090-24G');
    $response->assertSee('Apex Cloud Datacenter');
    $response->assertSee('400.00'); // Gross profit: 1600 - 1200
    $response->assertSee('25%'); // Margin: (400 / 1600) * 100 = 25%
});

it('groups unpaid orders into A/R aging buckets and flags credit holds for 91+ days overdue', function () {
    $group = CustomerGroup::create([
        'name' => 'OEM Integrator',
        'slug' => 'oem-integrator',
        'code' => 'OEM',
        'payment_terms_days' => 30,
        'credit_limit' => 50000.00,
        'is_active' => true,
    ]);

    $overdueCompany = Customer::factory()->create([
        'company_name' => 'Delinquent Systems Inc',
        'customer_group_id' => $group->id,
        'credit_limit' => 50000.00,
        'credit_balance' => 45000.00,
    ]);

    // Order 95 days overdue
    Order::factory()->create([
        'customer_id' => $overdueCompany->id,
        'order_number' => 'ORD-OLD-95D',
        'total_amount' => 15000.00,
        'payment_status' => 'unpaid',
        'status' => 'delivered',
        'payment_due_date' => now()->subDays(95)->toDateString(),
    ]);

    // Order 15 days overdue
    Order::factory()->create([
        'customer_id' => $overdueCompany->id,
        'order_number' => 'ORD-RECENT-15D',
        'total_amount' => 5000.00,
        'payment_status' => 'unpaid',
        'status' => 'delivered',
        'payment_due_date' => now()->subDays(15)->toDateString(),
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.reports.index', ['tab' => 'ar-aging']));

    $response->assertStatus(200);
    $response->assertSee('Delinquent Systems Inc');
    $response->assertSee('20,000.00'); // Total AR balance
    $response->assertSee('15,000.00'); // 91+ days overdue
    $response->assertSee('CREDIT HOLD');
});

it('identifies stock aging and capital locked in dead warehouse inventory', function () {
    $product = Product::factory()->create([
        'name' => 'Legacy Sound Card Pro',
        'sku' => 'SC-LEGACY-71',
        'cost_price' => 85.00,
        'requires_serial_tracking' => true,
    ]);

    // 1 unit in stock for 100 days (dead stock)
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => 1,
        'serial_number' => 'SN-DEAD-100D',
        'status' => SerialNumber::STATUS_IN_STOCK,
        'cost_price' => 85.00,
        'inbound_date' => now()->subDays(100)->toDateString(),
    ]);

    // 1 unit in stock for 10 days (healthy)
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => 1,
        'serial_number' => 'SN-FRESH-10D',
        'status' => SerialNumber::STATUS_IN_STOCK,
        'cost_price' => 85.00,
        'inbound_date' => now()->subDays(10)->toDateString(),
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.reports.index', ['tab' => 'stock-aging']));

    $response->assertStatus(200);
    $response->assertSee('SC-LEGACY-71');
    $response->assertSee('Legacy Sound Card Pro');
    $response->assertSee('85.00'); // Dead capital risk
    $response->assertSee('170.00'); // Total tied capital: 2 * 85
});

it('calculates RFQ win rate and quotation SLA turnaround hours', function () {
    $order = Order::factory()->create(['total_amount' => 25000.00]);

    // Accepted Quote
    Quote::create([
        'quote_number' => 'RFQ-2026-0001',
        'client_name' => 'Apex Computing LLC',
        'company_name' => 'Apex Computing Inc',
        'company_tier' => 'gold',
        'status' => 'converted',
        'total_cost' => 18000.00,
        'total_quoted_price' => 25000.00,
        'quoted_at' => now()->subHours(4),
        'converted_order_id' => $order->id,
        'created_at' => now()->subHours(8),
    ]);

    // Rejected Quote
    Quote::create([
        'quote_number' => 'RFQ-2026-0002',
        'client_name' => 'Apex Computing LLC',
        'company_name' => 'Apex Computing Inc',
        'company_tier' => 'gold',
        'status' => 'rejected',
        'total_cost' => 10000.00,
        'total_quoted_price' => 12000.00,
        'quoted_at' => now()->subHours(2),
        'created_at' => now()->subHours(6),
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.reports.index', ['tab' => 'rfq-winrate']));

    $response->assertStatus(200);
    $response->assertSee('Gold');
    $response->assertSee('50%'); // 1 accepted / 2 closed = 50%
    $response->assertSee('25,000.00'); // Realized revenue
});

it('tracks RMA defect rates by brand and triggers elevated failure flags', function () {
    $brand = Brand::factory()->create(['name' => 'ThermalCool']);
    $product = Product::factory()->create([
        'name' => 'AIO Liquid Cooler 360',
        'sku' => 'CLR-AIO-360',
        'brand_id' => $brand->id,
        'requires_serial_tracking' => true,
    ]);

    // 10 Sold units
    for ($i = 1; $i <= 10; $i++) {
        SerialNumber::create([
            'product_id' => $product->id,
            'warehouse_id' => 1,
            'serial_number' => "SN-COOLER-SOLD-{$i}",
            'status' => SerialNumber::STATUS_SOLD,
            'cost_price' => 110.00,
        ]);
    }

    // 1 RMA unit (10% defect rate)
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => 1,
        'serial_number' => 'SN-COOLER-RMA-01',
        'status' => SerialNumber::STATUS_RETURNED_RMA,
        'cost_price' => 110.00,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.reports.index', ['tab' => 'rma-defects']));

    $response->assertStatus(200);
    $response->assertSee('ThermalCool');
    $response->assertSee('CLR-AIO-360');
    $response->assertSee('10%'); // 1 return / 10 sold = 10%
});

it('serves structured JSON data from reports api endpoint', function () {
    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.reports.data', ['report' => 'all']));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'meta' => ['generated_at', 'currency', 'start_date', 'end_date'],
        'data' => [
            'kpis',
            'gross_margin',
            'ar_aging',
            'stock_aging',
            'rfq_performance',
            'rma_defects',
        ],
    ]);
});

it('streams CSV exports for reporting datasets', function () {
    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.reports.export', ['report' => 'ar-aging']));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('handles empty database and division by zero calculations gracefully', function () {
    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.reports.data', ['report' => 'all', 'start_date' => '2020-01-01', 'end_date' => '2020-01-02']));

    $response->assertStatus(200);
    $data = $response->json('data');

    expect($data['kpis']['overall_margin_pct'])->toBe(0)
        ->and($data['kpis']['rfq_win_rate_pct'])->toBe(0)
        ->and($data['kpis']['overall_defect_rate_pct'])->toBe(0)
        ->and($data['gross_margin']['summary']['total_revenue'])->toBe(0)
        ->and($data['gross_margin']['summary']['overall_margin_pct'])->toBe(0)
        ->and($data['rfq_performance']['summary']['win_rate_pct'])->toBe(0)
        ->and($data['rma_defects']['summary']['overall_defect_rate_pct'])->toBe(0);
});
