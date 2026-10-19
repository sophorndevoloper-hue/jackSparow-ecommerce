<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffPayroll;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffMembers = [
            [
                'staff_code' => 'STF-0001',
                'first_name' => 'Sokha',
                'last_name' => 'Pich',
                'phone' => '+855 12 888 111',
                'email' => 'sokha.pich@jacksparow.local',
                'department' => 'Warehouse',
                'role' => 'Warehouse Lead & Intake Supervisor',
                'employment_type' => 'full_time',
                'status' => 'active',
                'join_date' => '2024-03-15',
                'base_salary' => 1400.00,
                'salary_type' => 'monthly',
                'hourly_rate' => 8.75,
                'bank_name' => 'ABA Bank',
                'bank_account_number' => '001 234 567',
                'bank_account_holder' => 'SOKHA PICH',
                'emergency_contact_name' => 'Kosal Pich (Brother)',
                'emergency_contact_phone' => '+855 12 888 112',
                'notes' => 'Certified warehouse manager with high-speed barcode scanning expertise.',
            ],
            [
                'staff_code' => 'STF-0002',
                'first_name' => 'Elena',
                'last_name' => 'Rostova',
                'phone' => '+855 10 777 222',
                'email' => 'elena.rostova@jacksparow.local',
                'department' => 'IT & Tech',
                'role' => 'Hardware Diagnostic Technician',
                'employment_type' => 'full_time',
                'status' => 'active',
                'join_date' => '2024-05-01',
                'base_salary' => 1650.00,
                'salary_type' => 'monthly',
                'hourly_rate' => 10.30,
                'bank_name' => 'Acleda Bank',
                'bank_account_number' => '5544-3322-1100',
                'bank_account_holder' => 'ELENA ROSTOVA',
                'emergency_contact_name' => 'Dmitri Rostov (Father)',
                'emergency_contact_phone' => '+855 10 777 223',
                'notes' => 'GPU and Motherboard test bench lead, handles RMA verification.',
            ],
            [
                'staff_code' => 'STF-0003',
                'first_name' => 'Marcus',
                'last_name' => 'Brody',
                'phone' => '+855 99 666 333',
                'email' => 'marcus.brody@jacksparow.local',
                'department' => 'Sales',
                'role' => 'Senior B2B Account Executive',
                'employment_type' => 'full_time',
                'status' => 'active',
                'join_date' => '2023-11-10',
                'base_salary' => 1800.00,
                'salary_type' => 'monthly',
                'hourly_rate' => 11.25,
                'bank_name' => 'Canadia Bank',
                'bank_account_number' => '009-887766-1',
                'bank_account_holder' => 'MARCUS BRODY',
                'emergency_contact_name' => 'Claire Brody (Spouse)',
                'emergency_contact_phone' => '+855 99 666 334',
                'notes' => 'Manages OEM and enterprise quote accounts.',
            ],
            [
                'staff_code' => 'STF-0004',
                'first_name' => 'Vannak',
                'last_name' => 'Chan',
                'phone' => '+855 88 555 444',
                'email' => 'vannak.chan@jacksparow.local',
                'department' => 'Warehouse',
                'role' => 'Inventory & Serial Scanner',
                'employment_type' => 'full_time',
                'status' => 'active',
                'join_date' => '2024-08-01',
                'base_salary' => 950.00,
                'salary_type' => 'monthly',
                'hourly_rate' => 6.00,
                'bank_name' => 'Wing Bank',
                'bank_account_number' => '0123 4567 8910',
                'bank_account_holder' => 'VANNAK CHAN',
                'emergency_contact_name' => 'Sopheap Chan (Mother)',
                'emergency_contact_phone' => '+855 88 555 445',
                'notes' => 'Dedicated fulfillment packing and outbound dispatch verification.',
            ],
            [
                'staff_code' => 'STF-0005',
                'first_name' => 'Jessica',
                'last_name' => 'Taylor',
                'phone' => '+855 77 444 555',
                'email' => 'jessica.taylor@jacksparow.local',
                'department' => 'Logistics',
                'role' => 'Logistics Dispatch Coordinator',
                'employment_type' => 'full_time',
                'status' => 'active',
                'join_date' => '2024-02-20',
                'base_salary' => 1250.00,
                'salary_type' => 'monthly',
                'hourly_rate' => 7.80,
                'bank_name' => 'ABA Bank',
                'bank_account_number' => '002 444 888',
                'bank_account_holder' => 'JESSICA TAYLOR',
                'emergency_contact_name' => 'Thomas Taylor (Brother)',
                'emergency_contact_phone' => '+855 77 444 556',
                'notes' => 'Oversees courier scheduling and local shipping fleet.',
            ],
            [
                'staff_code' => 'STF-0006',
                'first_name' => 'Liam',
                'last_name' => 'O\'Connor',
                'phone' => '+855 15 333 666',
                'email' => 'liam.oconnor@jacksparow.local',
                'department' => 'Finance',
                'role' => 'Accounts & Payroll Specialist',
                'employment_type' => 'full_time',
                'status' => 'active',
                'join_date' => '2023-09-01',
                'base_salary' => 1500.00,
                'salary_type' => 'monthly',
                'hourly_rate' => 9.40,
                'bank_name' => 'ABA Bank',
                'bank_account_number' => '003 777 999',
                'bank_account_holder' => 'LIAM O CONNOR',
                'emergency_contact_name' => 'Fiona O\'Connor (Sister)',
                'emergency_contact_phone' => '+855 15 333 667',
                'notes' => 'Maintains Accounts Payable and vendor credit terms.',
            ],
            [
                'staff_code' => 'STF-0007',
                'first_name' => 'Bopha',
                'last_name' => 'Meng',
                'phone' => '+855 16 222 777',
                'email' => 'bopha.meng@jacksparow.local',
                'department' => 'Warehouse',
                'role' => 'RMA & Warranty Coordinator',
                'employment_type' => 'full_time',
                'status' => 'active',
                'join_date' => '2024-06-15',
                'base_salary' => 1050.00,
                'salary_type' => 'monthly',
                'hourly_rate' => 6.55,
                'bank_name' => 'Acleda Bank',
                'bank_account_number' => '1122-3344-5566',
                'bank_account_holder' => 'BOPHA MENG',
                'emergency_contact_name' => 'Rithy Meng (Father)',
                'emergency_contact_phone' => '+855 16 222 778',
                'notes' => 'Processes customer warranty claims and return shipments to vendors.',
            ],
            [
                'staff_code' => 'STF-0008',
                'first_name' => 'Alex',
                'last_name' => 'Mercer',
                'phone' => '+855 93 111 888',
                'email' => 'alex.mercer@jacksparow.local',
                'department' => 'IT & Tech',
                'role' => 'Component Assembly Intern',
                'employment_type' => 'intern',
                'status' => 'active',
                'join_date' => '2026-08-01',
                'base_salary' => 500.00,
                'salary_type' => 'monthly',
                'hourly_rate' => 3.12,
                'bank_name' => null,
                'bank_account_number' => null,
                'bank_account_holder' => null,
                'emergency_contact_name' => 'Karen Mercer (Mother)',
                'emergency_contact_phone' => '+855 93 111 889',
                'notes' => 'Supports custom PC building and testing rigs.',
            ],
            [
                'staff_code' => 'STF-0009',
                'first_name' => 'Thida',
                'last_name' => 'Seng',
                'phone' => '+855 11 000 999',
                'email' => 'thida.seng@jacksparow.local',
                'department' => 'Customer Support',
                'role' => 'Support Desk Lead',
                'employment_type' => 'full_time',
                'status' => 'on_leave',
                'join_date' => '2024-01-10',
                'base_salary' => 1150.00,
                'salary_type' => 'monthly',
                'hourly_rate' => 7.20,
                'bank_name' => 'ABA Bank',
                'bank_account_number' => '004 888 111',
                'bank_account_holder' => 'THIDA SENG',
                'emergency_contact_name' => 'Vireak Seng (Husband)',
                'emergency_contact_phone' => '+855 11 000 998',
                'notes' => 'On annual leave until next week.',
            ],
        ];

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        foreach ($staffMembers as $data) {
            $staff = Staff::updateOrCreate(
                ['staff_code' => $data['staff_code']],
                $data
            );

            // 1. Seed Attendance for Yesterday (Completed Shifts)
            if ($staff->status === 'active') {
                $checkInYest = Carbon::parse("{$yesterday} 08:30:00");
                $checkOutYest = Carbon::parse("{$yesterday} 17:30:00");

                $attYest = StaffAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'date' => $yesterday],
                    [
                        'check_in' => $checkInYest,
                        'check_out' => $checkOutYest,
                        'work_hours' => 9.00,
                        'overtime_hours' => 1.00,
                        'status' => 'present',
                        'notes' => 'Full shift completed with 1h overtime.',
                    ]
                );
            }

            // 2. Seed Attendance for Today
            if ($staff->staff_code === 'STF-0001') {
                // On duty now (checked in at 08:24)
                StaffAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'date' => $today],
                    [
                        'check_in' => Carbon::parse("{$today} 08:24:00"),
                        'check_out' => null,
                        'work_hours' => 0.00,
                        'status' => 'present',
                        'notes' => 'On shift at Central Warehouse.',
                    ]
                );
            } elseif ($staff->staff_code === 'STF-0002') {
                // On duty now (checked in at 08:15)
                StaffAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'date' => $today],
                    [
                        'check_in' => Carbon::parse("{$today} 08:15:00"),
                        'check_out' => null,
                        'work_hours' => 0.00,
                        'status' => 'present',
                        'notes' => 'Testing GPU batch intake.',
                    ]
                );
            } elseif ($staff->staff_code === 'STF-0003') {
                // Checked in late at 09:35 AM
                StaffAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'date' => $today],
                    [
                        'check_in' => Carbon::parse("{$today} 09:35:00"),
                        'check_out' => null,
                        'work_hours' => 0.00,
                        'status' => 'late',
                        'notes' => 'Morning client demonstration visit.',
                    ]
                );
            } elseif ($staff->staff_code === 'STF-0004') {
                // Completed shift today
                StaffAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'date' => $today],
                    [
                        'check_in' => Carbon::parse("{$today} 08:00:00"),
                        'check_out' => Carbon::parse("{$today} 17:30:00"),
                        'work_hours' => 9.50,
                        'overtime_hours' => 1.50,
                        'status' => 'present',
                        'notes' => 'Fulfilled 45 order scans.',
                    ]
                );
            } elseif ($staff->staff_code === 'STF-0005') {
                // Half day
                StaffAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'date' => $today],
                    [
                        'check_in' => Carbon::parse("{$today} 08:30:00"),
                        'check_out' => Carbon::parse("{$today} 12:30:00"),
                        'work_hours' => 4.00,
                        'overtime_hours' => 0.00,
                        'status' => 'half_day',
                        'notes' => 'Half day personal leave.',
                    ]
                );
            } elseif ($staff->staff_code === 'STF-0009') {
                // On leave
                StaffAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'date' => $today],
                    [
                        'check_in' => null,
                        'check_out' => null,
                        'work_hours' => 0.00,
                        'overtime_hours' => 0.00,
                        'status' => 'on_leave',
                        'notes' => 'Approved annual leave.',
                    ]
                );
            }

            // 3. Seed Monthly Payroll Records
            $currentMonth = Carbon::now()->format('Y-m');
            $periodStart = Carbon::parse($currentMonth.'-01')->startOfMonth()->toDateString();
            $periodEnd = Carbon::parse($currentMonth.'-01')->endOfMonth()->toDateString();
            $payrollCode = 'PAY-'.str_replace('-', '', $currentMonth).'-'.str_pad((string) $staff->id, 4, '0', STR_PAD_LEFT);

            if ($staff->staff_code === 'STF-0001') {
                // Paid pay-slip
                StaffPayroll::updateOrCreate(
                    ['staff_id' => $staff->id, 'period_start' => $periodStart],
                    [
                        'payroll_code' => $payrollCode,
                        'period_end' => $periodEnd,
                        'base_salary' => $staff->base_salary,
                        'worked_days' => 22,
                        'overtime_amount' => 78.75,
                        'bonus_amount' => 100.00,
                        'deductions_amount' => 0.00,
                        'net_salary' => 1578.75,
                        'payment_method' => 'bank_transfer',
                        'payment_date' => Carbon::now()->toDateString(),
                        'status' => 'paid',
                        'reference_note' => 'ABA-TRX-99881122',
                    ]
                );
            } elseif ($staff->staff_code === 'STF-0002') {
                // Approved pay-slip
                StaffPayroll::updateOrCreate(
                    ['staff_id' => $staff->id, 'period_start' => $periodStart],
                    [
                        'payroll_code' => $payrollCode,
                        'period_end' => $periodEnd,
                        'base_salary' => $staff->base_salary,
                        'worked_days' => 21,
                        'overtime_amount' => 92.70,
                        'bonus_amount' => 150.00,
                        'deductions_amount' => 0.00,
                        'net_salary' => 1892.70,
                        'payment_method' => 'bank_transfer',
                        'payment_date' => null,
                        'status' => 'approved',
                        'reference_note' => 'Approved by Management. Ready for disbursement.',
                    ]
                );
            } elseif ($staff->staff_code === 'STF-0003') {
                // Draft pay-slip
                StaffPayroll::updateOrCreate(
                    ['staff_id' => $staff->id, 'period_start' => $periodStart],
                    [
                        'payroll_code' => $payrollCode,
                        'period_end' => $periodEnd,
                        'base_salary' => $staff->base_salary,
                        'worked_days' => 20,
                        'overtime_amount' => 0.00,
                        'bonus_amount' => 200.00,
                        'deductions_amount' => 0.00,
                        'net_salary' => 2000.00,
                        'payment_method' => 'bank_transfer',
                        'payment_date' => null,
                        'status' => 'draft',
                        'reference_note' => 'Pending monthly sales commission verification.',
                    ]
                );
            }
        }
    }
}
