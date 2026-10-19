<?php

use App\Models\Brand;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffPayroll;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserActivitySeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([
        RoleAndPermissionSeeder::class,
        BackendMenuSeeder::class,
    ]);

    $this->admin = User::factory()->create(['is_approved' => true]);
    $this->admin->assignRole(Role::findByName('admin', 'backend'));
});

it('renders user history index with KPI metrics and logged activities', function () {
    $this->seed(UserActivitySeeder::class);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.user-history.index'));

    $response->assertStatus(200);
    $response->assertSee('User History &amp; Activity Logs', false);
    $response->assertSee('Audit Trail');
    $response->assertSee('Total Activities');
    $response->assertSee('Active Operators');
    $response->assertSee('Sokha Pich');
    $response->assertSee('Staff');
    $response->assertSee('StaffAttendance');
    $response->assertSee('StaffPayroll');
});

it('filters user history by module, action, and search', function () {
    UserActivity::create([
        'user_id' => $this->admin->id,
        'user_name' => $this->admin->name,
        'user_email' => $this->admin->email,
        'action' => 'CHECK_IN',
        'module' => 'StaffAttendance',
        'description' => 'Target activity check-in',
        'ip_address' => '192.168.1.100',
    ]);

    UserActivity::create([
        'user_id' => $this->admin->id,
        'user_name' => $this->admin->name,
        'user_email' => $this->admin->email,
        'action' => 'DELETE',
        'module' => 'Staff',
        'description' => 'Target activity staff delete',
        'ip_address' => '192.168.1.101',
    ]);

    $filterResponse = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.user-history.index', [
            'module' => 'StaffAttendance',
            'action' => 'CHECK_IN',
        ]));

    $filterResponse->assertStatus(200);
    $filterResponse->assertSee('Target activity check-in');
    $filterResponse->assertDontSee('Target activity staff delete');
});

it('returns activity detail as JSON on show endpoint', function () {
    $activity = UserActivity::create([
        'user_id' => $this->admin->id,
        'user_name' => 'John Operator',
        'user_email' => 'operator@jacksparow.local',
        'action' => 'PAYROLL_GENERATED',
        'module' => 'StaffPayroll',
        'entity_id' => '2026-09',
        'description' => 'Generated September 2026 payroll',
        'properties' => ['staff_count' => 8, 'total' => 12000],
        'ip_address' => '10.0.0.5',
        'user_agent' => 'TestBrowser/1.0',
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.user-history.show', $activity));

    $response->assertStatus(200);
    $response->assertJson([
        'id' => $activity->id,
        'action' => 'PAYROLL_GENERATED',
        'module' => 'StaffPayroll',
        'entity_id' => '2026-09',
        'description' => 'Generated September 2026 payroll',
        'ip_address' => '10.0.0.5',
        'user' => [
            'name' => 'John Operator',
            'email' => 'operator@jacksparow.local',
        ],
    ]);
});

it('prunes old user activity logs and records the prune action', function () {
    // Old activity (60 days ago)
    $oldActivity = UserActivity::create([
        'user_id' => $this->admin->id,
        'user_name' => $this->admin->name,
        'user_email' => $this->admin->email,
        'action' => 'LOGIN',
        'module' => 'Auth',
        'description' => 'Ancient login',
        'created_at' => Carbon::now()->subDays(60),
    ]);

    // Recent activity (2 days ago)
    $recentActivity = UserActivity::create([
        'user_id' => $this->admin->id,
        'user_name' => $this->admin->name,
        'user_email' => $this->admin->email,
        'action' => 'LOGIN',
        'module' => 'Auth',
        'description' => 'Recent login',
        'created_at' => Carbon::now()->subDays(2),
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->delete(route('admin.user-history.clear', ['days' => 30]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('user_activities', ['id' => $oldActivity->id]);
    $this->assertDatabaseHas('user_activities', ['id' => $recentActivity->id]);

    // Ensure pruning itself logged an audit record
    $this->assertDatabaseHas('user_activities', [
        'module' => 'UserHistory',
        'action' => 'DELETE',
    ]);
});

it('renders monthly staff activity and payroll summary report', function () {
    $staff = Staff::create([
        'staff_code' => 'STF-0100',
        'first_name' => 'David',
        'last_name' => 'Miller',
        'phone' => '+855 12 345 678',
        'email' => 'david.miller@jacksparow.local',
        'department' => 'Hardware Lab',
        'role' => 'Hardware Diagnostic Specialist',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 1600.00,
        'salary_type' => 'monthly',
    ]);

    $month = Carbon::now()->format('Y-m');
    $today = Carbon::now()->toDateString();

    StaffAttendance::create([
        'staff_id' => $staff->id,
        'date' => $today,
        'check_in' => '08:00:00',
        'check_out' => '17:00:00',
        'work_hours' => 9.00,
        'overtime_hours' => 1.00,
        'status' => 'present',
    ]);

    StaffPayroll::create([
        'payroll_code' => 'PAY-2026-09-01',
        'staff_id' => $staff->id,
        'period_start' => Carbon::parse($month.'-01')->startOfMonth()->toDateString(),
        'period_end' => Carbon::parse($month.'-01')->endOfMonth()->toDateString(),
        'base_salary' => 1600.00,
        'overtime_amount' => 50.00,
        'bonus_amount' => 100.00,
        'deductions_amount' => 20.00,
        'net_salary' => 1730.00,
        'status' => 'paid',
        'payment_method' => 'bank_transfer',
        'paid_at' => Carbon::now(),
    ]);

    UserActivity::create([
        'user_id' => $this->admin->id,
        'user_name' => $this->admin->name,
        'user_email' => $this->admin->email,
        'action' => 'PAYROLL_GENERATED',
        'module' => 'StaffPayroll',
        'description' => 'Generated payroll for David Miller',
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.staff.monthly-report', ['month' => $month]));

    $response->assertStatus(200);
    $response->assertSee('Monthly Staff Activity &amp; Payroll Report', false);
    $response->assertSee('JackSparow B2B Hardware Corp.');
    $response->assertSee('David Miller');
    $response->assertSee('STF-0100');
    $response->assertSee('Hardware Lab');
    $response->assertSee('$1,600.00');
    $response->assertSee('$1,730.00');
    $response->assertSee('PAID');
    $response->assertSee('Administrative &amp; Staff Activity Audit Log', false);
    $response->assertSee('Generated payroll for David Miller');
    $response->assertSee('Prepared By: HR Manager');
    $response->assertSee('Verified By: Finance Director');
    $response->assertSee('Approved By: Managing Director');
});

it('automatically logs UserActivity when staff is created, updated, and toggled', function () {
    // 1. Create Staff
    $createResponse = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff.store'), [
            'staff_code' => 'STF-TEST-1',
            'first_name' => 'Alice',
            'last_name' => 'Cooper',
            'phone' => '+855 12 777 888',
            'department' => 'Inventory',
            'role' => 'Auditor',
            'employment_type' => 'full_time',
            'status' => 'active',
            'base_salary' => 1250.00,
            'salary_type' => 'monthly',
        ]);

    $createdStaff = Staff::where('staff_code', 'STF-TEST-1')->first();

    $createResponse->assertRedirect();
    $this->assertDatabaseHas('user_activities', [
        'module' => 'Staff',
        'action' => 'CREATE',
        'entity_id' => (string) $createdStaff->id,
    ]);

    // 2. Toggle Status
    $toggleResponse = $this->actingAs($this->admin, 'backend')
        ->patch(route('admin.staff.toggle-status', $createdStaff), [
            'status' => 'suspended',
        ]);

    $toggleResponse->assertRedirect();
    $this->assertDatabaseHas('user_activities', [
        'module' => 'Staff',
        'action' => 'STATUS_CHANGE',
        'entity_id' => (string) $createdStaff->id,
    ]);
});

it('automatically logs UserActivity on staff check-in and check-out', function () {
    $staff = Staff::create([
        'staff_code' => 'STF-TEST-2',
        'first_name' => 'Bob',
        'last_name' => 'Ross',
        'phone' => '+855 99 222 333',
        'department' => 'Warehouse',
        'role' => 'Logistics Associate',
        'employment_type' => 'full_time',
        'status' => 'active',
        'base_salary' => 1100.00,
        'salary_type' => 'monthly',
    ]);

    // Check In
    $checkInResponse = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff-attendance.check-in'), [
            'staff_id' => $staff->id,
            'date' => Carbon::today()->toDateString(),
            'check_in' => '08:15:00',
            'status' => 'late',
        ]);

    $checkInResponse->assertRedirect();
    $this->assertDatabaseHas('user_activities', [
        'module' => 'StaffAttendance',
        'action' => 'CHECK_IN',
    ]);

    $attendance = StaffAttendance::where('staff_id', $staff->id)->first();

    // Check Out
    $checkOutResponse = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.staff-attendance.check-out'), [
            'staff_id' => $staff->id,
            'date' => Carbon::today()->toDateString(),
            'check_out' => '17:15:00',
        ]);

    $checkOutResponse->assertRedirect();
    $this->assertDatabaseHas('user_activities', [
        'module' => 'StaffAttendance',
        'action' => 'CHECK_OUT',
    ]);
});

it('automatically logs user LOGIN and LOGOUT activities via authentication events', function () {
    $loginUser = User::factory()->create([
        'email' => 'auditor@jacksparow.local',
        'password' => Hash::make('Secret123!'),
        'is_approved' => true,
    ]);
    $loginUser->assignRole(Role::findByName('admin', 'backend'));

    // 1. Submit login request to admin.login
    $response = $this->post(route('admin.login'), [
        'email' => 'auditor@jacksparow.local',
        'password' => 'Secret123!',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertDatabaseHas('user_activities', [
        'action' => 'LOGIN',
        'module' => 'Auth',
        'user_email' => 'auditor@jacksparow.local',
    ]);

    // 2. Submit logout request
    $logoutResponse = $this->actingAs($loginUser, 'backend')
        ->post(route('admin.logout'));

    $logoutResponse->assertRedirect(route('admin.login'));
    $this->assertDatabaseHas('user_activities', [
        'action' => 'LOGOUT',
        'module' => 'Auth',
        'user_email' => 'auditor@jacksparow.local',
    ]);
});

it('automatically logs CREATE, UPDATE, and DELETE activities across domain models via AuditObserver', function () {
    UserActivity::clearRecentlyLogged();

    // 1. CREATE
    $brand = null;
    $this->actingAs($this->admin, 'backend');

    $brand = Brand::create([
        'name' => 'Corsair Gaming',
        'slug' => 'corsair-gaming',
        'description' => 'High performance hardware',
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('user_activities', [
        'action' => 'CREATE',
        'module' => 'Brand',
        'entity_id' => (string) $brand->id,
        'user_id' => $this->admin->id,
    ]);

    // 2. UPDATE with diff
    UserActivity::clearRecentlyLogged();
    $brand->update([
        'name' => 'Corsair Components',
        'description' => 'Updated component division',
    ]);

    $this->assertDatabaseHas('user_activities', [
        'action' => 'UPDATE',
        'module' => 'Brand',
        'entity_id' => (string) $brand->id,
    ]);

    $updateLog = UserActivity::where('action', 'UPDATE')
        ->where('module', 'Brand')
        ->where('entity_id', (string) $brand->id)
        ->first();

    expect($updateLog)->not->toBeNull();
    expect($updateLog->properties['changes'])->toHaveKey('name');
    expect($updateLog->properties['changes']['name']['old'])->toBe('Corsair Gaming');
    expect($updateLog->properties['changes']['name']['new'])->toBe('Corsair Components');

    // 3. DELETE
    UserActivity::clearRecentlyLogged();
    $brand->delete();

    $this->assertDatabaseHas('user_activities', [
        'action' => 'DELETE',
        'module' => 'Brand',
        'entity_id' => (string) $brand->id,
    ]);

    // 4. Verify User History view shows the recorded actions
    $historyView = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.user-history.index'));

    $historyView->assertStatus(200);
    $historyView->assertSee('Corsair Components');
    $historyView->assertSee('Brand');
});
