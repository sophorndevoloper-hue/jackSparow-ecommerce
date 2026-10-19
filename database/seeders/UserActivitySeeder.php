<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first();
        $adminId = $adminUser?->id;
        $adminName = $adminUser?->name ?? 'Administrator';
        $adminEmail = $adminUser?->email ?? 'admin@jacksparow.local';

        $now = Carbon::now();

        $activities = [
            [
                'user_id' => $adminId,
                'user_name' => $adminName,
                'user_email' => $adminEmail,
                'action' => 'LOGIN',
                'module' => 'Authentication',
                'entity_id' => (string) $adminId,
                'description' => "User {$adminName} logged in via backend credentials.",
                'properties' => ['guard' => 'backend', 'auth_type' => 'password'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => $now->copy()->subDays(25)->setHour(8)->setMinute(15),
            ],
            [
                'user_id' => $adminId,
                'user_name' => $adminName,
                'user_email' => $adminEmail,
                'action' => 'CREATE',
                'module' => 'Staff',
                'entity_id' => 'STF-0001',
                'description' => "Registered new staff member Sokha Pich in department 'Warehouse'.",
                'properties' => ['department' => 'Warehouse', 'role' => 'Warehouse Lead', 'base_salary' => 1400.00],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => $now->copy()->subDays(20)->setHour(9)->setMinute(30),
            ],
            [
                'user_id' => $adminId,
                'user_name' => $adminName,
                'user_email' => $adminEmail,
                'action' => 'CHECK_IN',
                'module' => 'StaffAttendance',
                'entity_id' => '1',
                'description' => 'Recorded check-in for staff Sokha Pich (STF-0001) at 08:02 AM.',
                'properties' => ['check_in_time' => '08:02:00', 'status' => 'present', 'work_shift' => 'morning'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => $now->copy()->subDays(5)->setHour(8)->setMinute(2),
            ],
            [
                'user_id' => $adminId,
                'user_name' => $adminName,
                'user_email' => $adminEmail,
                'action' => 'CHECK_OUT',
                'module' => 'StaffAttendance',
                'entity_id' => '1',
                'description' => 'Recorded check-out for staff Sokha Pich (STF-0001) at 18:30 PM (1.50 hrs overtime).',
                'properties' => ['check_out_time' => '18:30:00', 'worked_hours' => 9.5, 'overtime_hours' => 1.5],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => $now->copy()->subDays(5)->setHour(18)->setMinute(30),
            ],
            [
                'user_id' => $adminId,
                'user_name' => $adminName,
                'user_email' => $adminEmail,
                'action' => 'PAYROLL_GENERATED',
                'module' => 'StaffPayroll',
                'entity_id' => $now->format('Y-m'),
                'description' => "Generated monthly payroll batch for period {$now->copy()->startOfMonth()->toDateString()} to {$now->copy()->endOfMonth()->toDateString()} (6 staff).",
                'properties' => ['period_start' => $now->copy()->startOfMonth()->toDateString(), 'period_end' => $now->copy()->endOfMonth()->toDateString(), 'staff_count' => 6],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => $now->copy()->subDays(2)->setHour(14)->setMinute(10),
            ],
            [
                'user_id' => $adminId,
                'user_name' => $adminName,
                'user_email' => $adminEmail,
                'action' => 'STATUS_CHANGE',
                'module' => 'StaffPayroll',
                'entity_id' => '1',
                'description' => 'Approved staff payroll statement #1 for staff Sokha Pich (STF-0001).',
                'properties' => ['status' => 'approved', 'net_salary' => 1400.00],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => $now->copy()->subDays(1)->setHour(11)->setMinute(0),
            ],
            [
                'user_id' => $adminId,
                'user_name' => $adminName,
                'user_email' => $adminEmail,
                'action' => 'DISBURSEMENT',
                'module' => 'StaffPayroll',
                'entity_id' => '1',
                'description' => 'Recorded salary disbursement of $1400.00 for staff Sokha Pich via bank_transfer.',
                'properties' => ['amount_paid' => 1400.00, 'payment_method' => 'bank_transfer', 'reference' => 'TXN-98412'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => $now->copy()->subHours(6),
            ],
            [
                'user_id' => $adminId,
                'user_name' => $adminName,
                'user_email' => $adminEmail,
                'action' => 'UPDATE',
                'module' => 'Staff',
                'entity_id' => 'STF-0002',
                'description' => 'Updated designation and contact phone for staff Elena Rostova (STF-0002).',
                'properties' => ['designation' => 'Senior Procurement Manager', 'phone' => '+855 10 777 222'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => $now->copy()->subHours(2),
            ],
        ];

        foreach ($activities as $act) {
            UserActivity::create($act);
        }
    }
}
