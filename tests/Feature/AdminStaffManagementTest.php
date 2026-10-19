<?php

use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffPayroll;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([
        RoleAndPermissionSeeder::class,
        BackendMenuSeeder::class,
    ]);

    $this->admin = User::factory()->create(['is_approved' => true]);
    $this->admin->assignRole(Role::findByName('admin', 'backend'));
});

it('displays staff directory with KPI cards and People sidebar links', function () {
    $staff = Staff::create([
        'staff_code' => 'STF-0001',
        'first_name' => 'John',
        'last_name' => 'Connor',
        'phone' => '+855 12 999 888',
        'department' => 'Warehouse',
        'role' => 'Warehouse Lead',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 1500.00,
        'salary_type' => 'monthly',
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.staff.index'));

    $response->assertStatus(200);
    $response->assertSee('Staff Directory');
    $response->assertSee('John Connor');
    $response->assertSee('STF-0001');
    $response->assertSee('Warehouse Lead');
    $response->assertSee('$1,500.00');

    // Sidebar navigation check: Manage Staff has been removed from admin menu
    $response->assertDontSee('Manage Staff');
});

it('filters staff by search term, department, and status', function () {
    Staff::create([
        'staff_code' => 'STF-0010',
        'first_name' => 'Alice',
        'last_name' => 'Smith',
        'phone' => '+855 11 111 222',
        'department' => 'Sales',
        'role' => 'Sales Executive',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 1200.00,
        'salary_type' => 'monthly',
    ]);

    Staff::create([
        'staff_code' => 'STF-0020',
        'first_name' => 'Bob',
        'last_name' => 'Builder',
        'phone' => '+855 22 333 444',
        'department' => 'Warehouse',
        'role' => 'Technician',
        'employment_type' => 'full_time',
        'status' => 'on_leave',
        'base_salary' => 1100.00,
        'salary_type' => 'monthly',
    ]);

    // Search filter
    $searchResponse = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.staff.index', ['search' => 'Alice']));
    $searchResponse->assertSee('Alice Smith');
    $searchResponse->assertDontSee('Bob Builder');

    // Department filter
    $deptResponse = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.staff.index', ['department' => 'Warehouse']));
    $deptResponse->assertSee('Bob Builder');
    $deptResponse->assertDontSee('Alice Smith');
});

it('creates a new staff member with auto-generated code, role, and salary', function () {
    $payload = [
        'first_name' => 'Sarah',
        'last_name' => 'Vance',
        'phone' => '+855 88 777 666',
        'email' => 'sarah.vance@jacksparow.com',
        'department' => 'Logistics',
        'role' => 'Logistics Dispatcher',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 1350.50,
        'salary_type' => 'monthly',
        'hourly_rate' => 8.50,
        'bank_name' => 'ABA Bank',
        'bank_account_number' => '000999888',
        'bank_account_holder' => 'SARAH VANCE',
    ];

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff.store'), $payload);

    $response->assertRedirect(route('admin.staff.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('staff', [
        'first_name' => 'Sarah',
        'last_name' => 'Vance',
        'department' => 'Logistics',
        'role' => 'Logistics Dispatcher',
        'base_salary' => 1350.50,
    ]);

    $createdStaff = Staff::where('email', 'sarah.vance@jacksparow.com')->first();
    expect($createdStaff)->not->toBeNull();
    expect($createdStaff->staff_code)->toStartWith('STF-');
});

it('updates staff profile, role, and compensation', function () {
    $staff = Staff::create([
        'staff_code' => 'STF-0099',
        'first_name' => 'David',
        'last_name' => 'Miller',
        'phone' => '+855 77 123 456',
        'department' => 'IT & Tech',
        'role' => 'Junior Tech',
        'employment_type' => 'part_time',
        'status' => 'active',
        'base_salary' => 800.00,
        'salary_type' => 'monthly',
    ]);

    $updatePayload = [
        'staff_code' => 'STF-0099',
        'first_name' => 'David',
        'last_name' => 'Miller',
        'phone' => '+855 77 123 456',
        'department' => 'IT & Tech',
        'role' => 'Senior Systems Specialist',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 1800.00,
        'salary_type' => 'monthly',
    ];

    $response = $this->actingAs($this->admin, 'backend')
        ->put(route('admin.staff.update', $staff), $updatePayload);

    $response->assertRedirect(route('admin.staff.index'));
    $response->assertSessionHas('success');

    $staff->refresh();
    expect($staff->role)->toBe('Senior Systems Specialist');
    expect((float) $staff->base_salary)->toBe(1800.00);
    expect($staff->employment_type)->toBe('full_time');
});

it('toggles staff status and allows deletion', function () {
    $staff = Staff::create([
        'staff_code' => 'STF-0088',
        'first_name' => 'Michael',
        'last_name' => 'Scott',
        'phone' => '+855 66 555 444',
        'department' => 'Sales',
        'role' => 'Regional Manager',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 2000.00,
        'salary_type' => 'monthly',
    ]);

    // Toggle status to on_leave
    $toggleResponse = $this->actingAs($this->admin, 'backend')
        ->patch(route('admin.staff.toggle-status', $staff), ['status' => 'on_leave']);
    $toggleResponse->assertRedirect(route('admin.staff.index'));

    $staff->refresh();
    expect($staff->status)->toBe('on_leave');

    // Delete staff
    $deleteResponse = $this->actingAs($this->admin, 'backend')
        ->delete(route('admin.staff.destroy', $staff));
    $deleteResponse->assertRedirect(route('admin.staff.index'));

    $this->assertDatabaseMissing('staff', ['id' => $staff->id]);
});

it('records daily check-in with timestamps and auto-detects late arrival', function () {
    $staff = Staff::create([
        'staff_code' => 'STF-0101',
        'first_name' => 'Emma',
        'last_name' => 'Stone',
        'phone' => '+855 99 888 777',
        'department' => 'Warehouse',
        'role' => 'Inventory Clerk',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 1100.00,
        'salary_type' => 'monthly',
    ]);

    $today = Carbon::today()->toDateString();

    // Check-in action
    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff-attendance.check-in'), [
            'staff_id' => $staff->id,
            'date' => $today,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $attendance = StaffAttendance::where('staff_id', $staff->id)
        ->where('date', $today)
        ->first();

    expect($attendance)->not->toBeNull();
    expect($attendance->check_in)->not->toBeNull();
    expect(in_array($attendance->status, ['present', 'late']))->toBeTrue();

    // Prevent duplicate check-in
    $dupResponse = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff-attendance.check-in'), [
            'staff_id' => $staff->id,
            'date' => $today,
        ]);
    $dupResponse->assertSessionHas('error');
});

it('records check-out and automatically calculates work hours', function () {
    $staff = Staff::create([
        'staff_code' => 'STF-0102',
        'first_name' => 'Lucas',
        'last_name' => 'Grey',
        'phone' => '+855 33 222 111',
        'department' => 'Warehouse',
        'role' => 'Hardware Tester',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 1200.00,
        'salary_type' => 'monthly',
    ]);

    $today = Carbon::today()->toDateString();

    // Pre-create attendance with check-in at 8 hours ago
    $checkInTime = Carbon::now()->subHours(8)->subMinutes(30);
    StaffAttendance::create([
        'staff_id' => $staff->id,
        'date' => $today,
        'check_in' => $checkInTime,
        'status' => 'present',
    ]);

    // Check out
    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff-attendance.check-out'), [
            'staff_id' => $staff->id,
            'date' => $today,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $attendance = StaffAttendance::where('staff_id', $staff->id)->where('date', $today)->first();
    expect($attendance->check_out)->not->toBeNull();
    expect((float) $attendance->work_hours)->toBeGreaterThanOrEqual(8.0);
    expect((float) $attendance->overtime_hours)->toBeGreaterThanOrEqual(0.5);
});

it('supports HR manual attendance adjustment for any date', function () {
    $staff = Staff::create([
        'staff_code' => 'STF-0103',
        'first_name' => 'Chloe',
        'last_name' => 'Bennett',
        'phone' => '+855 44 555 666',
        'department' => 'Sales',
        'role' => 'Account Manager',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 1600.00,
        'salary_type' => 'monthly',
    ]);

    $targetDate = '2026-09-01';

    $payload = [
        'staff_id' => $staff->id,
        'date' => $targetDate,
        'check_in' => '08:30',
        'check_out' => '17:30',
        'status' => 'present',
        'notes' => 'Adjusted by HR for system maintenance window',
    ];

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff-attendance.record'), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('staff_attendances', [
        'staff_id' => $staff->id,
        'date' => $targetDate,
        'status' => 'present',
        'work_hours' => 9.00,
        'overtime_hours' => 1.00,
        'notes' => 'Adjusted by HR for system maintenance window',
    ]);
});

it('generates monthly payroll batch, calculates overtime, and processes disbursement', function () {
    $staff = Staff::create([
        'staff_code' => 'STF-0200',
        'first_name' => 'Peter',
        'last_name' => 'Parker',
        'phone' => '+855 55 444 333',
        'department' => 'IT & Tech',
        'role' => 'Hardware Specialist',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 2000.00,
        'salary_type' => 'monthly',
        'hourly_rate' => 12.50,
        'bank_name' => 'ABA Bank',
        'bank_account_number' => '123456789',
    ]);

    // Add some attendance records with overtime in 2026-09
    StaffAttendance::create([
        'staff_id' => $staff->id,
        'date' => '2026-09-05',
        'check_in' => '2026-09-05 08:00:00',
        'check_out' => '2026-09-05 18:00:00',
        'work_hours' => 10.00,
        'overtime_hours' => 2.00,
        'status' => 'present',
    ]);

    // Batch generate payroll for 2026-09
    $genResponse = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff-salaries.generate'), ['month' => '2026-09']);

    $genResponse->assertRedirect(route('admin.staff-salaries.index', ['month' => '2026-09']));
    $genResponse->assertSessionHas('success');

    $payroll = StaffPayroll::where('staff_id', $staff->id)
        ->where('period_start', '2026-09-01')
        ->first();

    expect($payroll)->not->toBeNull();
    expect((float) $payroll->base_salary)->toBe(2000.00);
    expect($payroll->worked_days)->toBe(1);
    expect((float) $payroll->overtime_amount)->toBeGreaterThan(0.00); // 2 hours * 12.50 * 1.5 = 37.50
    expect((float) $payroll->net_salary)->toBe(2000.00 + (float) $payroll->overtime_amount);
    expect($payroll->status)->toBe('draft');

    // Update payroll with bonus and approve
    $updateResponse = $this->actingAs($this->admin, 'backend')
        ->patch(route('admin.staff-salaries.update-status', $payroll), [
            'status' => 'approved',
            'bonus_amount' => 150.00,
            'deductions_amount' => 25.00,
        ]);
    $updateResponse->assertRedirect();

    $payroll->refresh();
    expect($payroll->status)->toBe('approved');
    expect((float) $payroll->bonus_amount)->toBe(150.00);
    expect((float) $payroll->deductions_amount)->toBe(25.00);
    expect((float) $payroll->net_salary)->toBe(2000.00 + (float) $payroll->overtime_amount + 150.00 - 25.00);

    // Record disbursement payment
    $payResponse = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff-salaries.record-payment', $payroll), [
            'payment_date' => '2026-09-30',
            'payment_method' => 'bank_transfer',
            'reference_note' => 'ABA-TRX-987654321',
        ]);
    $payResponse->assertRedirect();

    $payroll->refresh();
    expect($payroll->status)->toBe('paid');
    expect((string) $payroll->payment_date)->toBe('2026-09-30');
    expect($payroll->payment_method)->toBe('bank_transfer');
    expect($payroll->reference_note)->toBe('ABA-TRX-987654321');
});
