<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->category = Category::firstOrCreate(['slug' => 'import-test-cat'], ['name' => 'Import Test Cat']);
    $this->warehouse = Warehouse::firstOrCreate(['code' => 'WH-MAIN'], ['name' => 'Main Warehouse', 'location' => 'Zone A']);

    $this->admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $this->admin->assignRole($superadminRole);

    $this->product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Test Gaming GPU',
        'slug' => 'test-gaming-gpu',
        'sku' => 'GPU-TEST-01',
        'price' => 500.00,
        'cost_price' => 350.00,
        'stock_quantity' => 0,
        'requires_serial_tracking' => true,
    ]);
});

it('downloads serial import templates for all supported formats', function (string $format, string $expectedFilename) {
    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.serial-numbers.templates.download', ['format' => $format]));

    $response->assertOk();
    $disposition = (string) $response->headers->get('content-disposition');
    expect($disposition)->toContain($expectedFilename);
})->with([
    ['csv', 'serial_numbers_template.csv'],
    ['excel', 'serial_numbers_template.xlsx'],
    ['json', 'serial_numbers_template.json'],
    ['text', 'serial_numbers_template.txt'],
]);

it('successfully imports serial numbers via CSV file', function () {
    $csvContent = "serial_number,product_sku,warehouse_code\n".
        "CSV-SN-001,GPU-TEST-01,WH-MAIN\n".
        "CSV-SN-002,GPU-TEST-01,WH-MAIN\n";

    $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.serial-numbers.import'), [
            'file' => $file,
        ]);

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('success');

    expect(SerialNumber::where('serial_number', 'CSV-SN-001')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'CSV-SN-002')->exists())->toBeTrue();
    expect($this->product->fresh()->stock_quantity)->toBe(2);
});

it('successfully imports serial numbers via plain text file with fallback selectors', function () {
    $txtContent = "# Batch received 2026-09-10\n".
        "TXT-SN-100\n".
        "TXT-SN-101\n".
        "TXT-SN-102\n";

    $file = UploadedFile::fake()->createWithContent('serials.txt', $txtContent);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.serial-numbers.import'), [
            'file' => $file,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
        ]);

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('success');

    expect(SerialNumber::whereIn('serial_number', ['TXT-SN-100', 'TXT-SN-101', 'TXT-SN-102'])->count())->toBe(3);
    expect($this->product->fresh()->stock_quantity)->toBe(3);
});

it('successfully imports serial numbers via JSON file', function () {
    $jsonData = json_encode([
        [
            'serial_number' => 'JSON-SN-201',
            'product_sku' => 'GPU-TEST-01',
            'warehouse_code' => 'WH-MAIN',
        ],
        [
            'serial_number' => 'JSON-SN-202',
            'product_sku' => 'GPU-TEST-01',
            'warehouse_code' => 'WH-MAIN',
        ],
    ], JSON_PRETTY_PRINT);

    $file = UploadedFile::fake()->createWithContent('serials.json', $jsonData);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.serial-numbers.import'), [
            'file' => $file,
        ]);

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('success');

    expect(SerialNumber::where('serial_number', 'JSON-SN-201')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'JSON-SN-202')->exists())->toBeTrue();
    expect($this->product->fresh()->stock_quantity)->toBe(2);
});

it('successfully imports serial numbers via Excel file', function () {
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'serial_number');
    $sheet->setCellValue('B1', 'product_sku');
    $sheet->setCellValue('C1', 'warehouse_code');

    $sheet->setCellValue('A2', 'XLS-SN-301');
    $sheet->setCellValue('B2', 'GPU-TEST-01');
    $sheet->setCellValue('C2', 'WH-MAIN');

    $sheet->setCellValue('A3', 'XLS-SN-302');
    $sheet->setCellValue('B3', 'GPU-TEST-01');
    $sheet->setCellValue('C3', 'WH-MAIN');

    $tempPath = tempnam(sys_get_temp_dir(), 'test_xls_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $file = new UploadedFile(
        $tempPath,
        'serials.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.serial-numbers.import'), [
            'file' => $file,
        ]);

    if (file_exists($tempPath)) {
        unlink($tempPath);
    }

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('success');

    expect(SerialNumber::where('serial_number', 'XLS-SN-301')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'XLS-SN-302')->exists())->toBeTrue();
    expect($this->product->fresh()->stock_quantity)->toBe(2);
});

it('rejects import if serial numbers already exist in inventory', function () {
    SerialNumber::create([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'DUP-SN-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $csvContent = "serial_number,product_sku,warehouse_code\n".
        "DUP-SN-001,GPU-TEST-01,WH-MAIN\n".
        "NEW-SN-002,GPU-TEST-01,WH-MAIN\n";

    $file = UploadedFile::fake()->createWithContent('duplicate.csv', $csvContent);

    $response = $this->actingAs($this->admin, 'backend')
        ->from(route('admin.serial-numbers.index'))
        ->post(route('admin.serial-numbers.import'), [
            'file' => $file,
        ]);

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('error');

    expect(SerialNumber::where('serial_number', 'NEW-SN-002')->exists())->toBeFalse();
});

it('rejects import if the file contains internal duplicates', function () {
    $csvContent = "serial_number,product_sku,warehouse_code\n".
        "REPEAT-SN-001,GPU-TEST-01,WH-MAIN\n".
        "REPEAT-SN-001,GPU-TEST-01,WH-MAIN\n";

    $file = UploadedFile::fake()->createWithContent('internal_dup.csv', $csvContent);

    $response = $this->actingAs($this->admin, 'backend')
        ->from(route('admin.serial-numbers.index'))
        ->post(route('admin.serial-numbers.import'), [
            'file' => $file,
        ]);

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('error');
    expect(SerialNumber::where('serial_number', 'REPEAT-SN-001')->exists())->toBeFalse();
});

it('parses an uploaded file and returns serial numbers list for preview', function () {
    $csvContent = "serial_number,product_sku,warehouse_code\n".
        "PREVIEW-001,GPU-TEST-01,WH-MAIN\n".
        "PREVIEW-002,GPU-TEST-01,WH-MAIN\n";

    $file = UploadedFile::fake()->createWithContent('preview.csv', $csvContent);

    $response = $this->actingAs($this->admin, 'backend')
        ->postJson(route('admin.serial-numbers.parse-preview'), [
            'file' => $file,
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'count' => 2,
        'serials' => ['PREVIEW-001', 'PREVIEW-002'],
    ]);
    expect($response->json('text'))->toBe("PREVIEW-001\nPREVIEW-002");
});

it('prioritizes edited serials list from textarea over raw file on product edit', function () {
    $rawCsvContent = "serial_number\nRAW-001\nRAW-002";
    $file = UploadedFile::fake()->createWithContent('test.csv', $rawCsvContent);

    // User edited the preview textarea to add EDITED-003 instead of RAW-002
    $response = $this->actingAs($this->admin, 'backend')
        ->postJson(route('admin.products.serials.store', $this->product->id), [
            'warehouse_id' => $this->warehouse->id,
            'file' => $file,
            'serials_text' => "RAW-001\nEDITED-003",
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'count' => 2,
    ]);

    expect(SerialNumber::where('serial_number', 'RAW-001')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'EDITED-003')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'RAW-002')->exists())->toBeFalse();
});

it('prioritizes edited serials list from textarea over raw file in batch serial numbers import', function () {
    $rawCsvContent = "serial_number,product_sku,warehouse_code\nBATCH-RAW-01,GPU-TEST-01,WH-MAIN\nBATCH-RAW-02,GPU-TEST-01,WH-MAIN";
    $file = UploadedFile::fake()->createWithContent('batch.csv', $rawCsvContent);

    // User edited the preview textarea to fix the second serial to BATCH-EDITED-02
    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.serial-numbers.import'), [
            'file' => $file,
            'serials_text' => "BATCH-RAW-01\nBATCH-EDITED-02",
        ]);

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('success');

    expect(SerialNumber::where('serial_number', 'BATCH-RAW-01')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'BATCH-EDITED-02')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'BATCH-RAW-02')->exists())->toBeFalse();
});
